<?php
/**
 * HistorialHelper.php
 * Registra eventos de auditoría con snapshots proporcionales al tipo de cambio.
 *
 * Lógica de snapshots:
 *  - Solo cambió responsable → snapshot de asignación (antes → después)
 *  - Solo cambió estado      → snapshot de estado (antes → después) + identidad mínima
 *  - Cambió ficha del activo principal → ficha completa del principal
 *  - Se editó un periférico  → ficha completa de ese periférico
 *  - CREACION                → ficha completa + todos sus periféricos
 */
class HistorialHelper
{
    const CREACION         = 'CREACION';
    const EDICION          = 'EDICION';
    const ASIGNACION       = 'ASIGNACION';
    const CAMBIO_ESTADO    = 'CAMBIO_ESTADO';
    const NOVEDAD          = 'NOVEDAD';
    const NOVEDAD_RESUELTA = 'NOVEDAD_RESUELTA';
    const BAJA             = 'BAJA';

    // ─────────────────────────────────────────────────────────────────────────
    // API PÚBLICA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param PDO    $db
     * @param int    $id       ID del activo
     * @param string $tipo     Constante CREACION | EDICION | ...
     * @param string $usuario
     * @param array  $antes    Fila de fun_read_activo_por_id ANTES del update
     * @param array  $camposDinAntes  Mapa nombre→{etiqueta,valor} antes del update
     */
    public static function registrar(
        PDO    $db,
        int    $id,
        string $tipo,
        string $usuario,
        array  $antes = [],
        array  $camposDinAntes = []
    ): void {
        try {
            // ── CREACION → snapshot completo con periféricos ──────────────────
            if ($tipo === self::CREACION) {
                $snap = self::snapActivo($db, $id, true);
                $desc = self::descCreacion($snap);
                self::insertar($db, $id, $tipo, $desc, $usuario, $snap);
                return;
            }

            // ── EDICION → snapshot proporcional a lo que cambió ───────────────
            if ($tipo === self::EDICION && !empty($antes)) {
                $despues       = self::leerActivo($db, $id);
                if (!$despues) return;

                $cambiResp     = self::difResp($antes, $despues);
                $cambiEstado   = self::difEstado($antes, $despues);
                $cambiFicha    = self::difFicha($antes, $despues);
                $camposDinDes  = self::leerCamposDin($db, $id);
                $cambiDin      = self::difDin($camposDinAntes, $camposDinDes);

                // Nada cambió → no registrar
                if (!$cambiResp && !$cambiEstado && !$cambiFicha && !$cambiDin) return;

                // ── SOLO responsable ──────────────────────────────────────────
                if ($cambiResp && !$cambiFicha && !$cambiDin) {
                    $snap = [
                        'scope'           => 'ASIGNACION',
                        'responsable_ant' => trim($antes['r_responsable']      ?? 'Bodega'),
                        'cod_ant'         => trim($antes['r_cod_responsable']  ?? '—'),
                        'area_ant'        => trim($antes['r_area']             ?? '—'),
                        'responsable_nvo' => trim($despues['r_responsable']    ?? 'Bodega'),
                        'cod_nvo'         => trim($despues['r_cod_responsable']?? '—'),
                        'area_nvo'        => trim($despues['r_area']           ?? '—'),
                    ];
                    $desc = "Reasignación: {$snap['responsable_ant']} → {$snap['responsable_nvo']}";
                    self::insertar($db, $id, self::ASIGNACION, $desc, $usuario, $snap);
                    return;
                }

                // ── SOLO estado ───────────────────────────────────────────────
                if ($cambiEstado && !$cambiFicha && !$cambiResp && !$cambiDin) {
                    $snap = array_filter([
                        'scope'      => 'ESTADO',
                        'estado_ant' => trim($antes['r_estado']    ?? '—'),
                        'estado_nvo' => trim($despues['r_estado']  ?? '—'),
                        'tipo'       => trim($despues['r_tipo']        ?? ''),
                        'marca'      => trim($despues['r_marca']       ?? ''),
                        'referencia' => trim($despues['r_referencia']  ?? ''),
                        'serial'     => trim($despues['r_serial']      ?? ''),
                    ], fn($v) => $v !== '');
                    $snap['scope'] = 'ESTADO';
                    $desc = "Estado: {$snap['estado_ant']} → {$snap['estado_nvo']} | {$snap['tipo']} {$snap['marca']} {$snap['referencia']}";
                    self::insertar($db, $id, self::CAMBIO_ESTADO, $desc, $usuario, $snap);
                    return;
                }

                // ── Ficha completa (activo principal o periférico) ────────────
                $esPeriferico = !empty($despues['r_id_padre']);
                // Los periféricos no traen a sus "hijos" (no los tienen)
                $snap = self::snapActivo($db, $id, false);

                // Enriquecer con info de "antes" si también hubo cambio de estado o resp.
                if ($cambiEstado)   $snap['estado_anterior']       = trim($antes['r_estado']      ?? '—');
                if ($cambiResp)     $snap['responsable_anterior']  = trim($antes['r_responsable'] ?? 'Bodega');

                $desc = self::descEdicion($snap, $esPeriferico);
                self::insertar($db, $id, self::EDICION, $desc, $usuario, $snap);
                return;
            }

            // ── Otros tipos (NOVEDAD, BAJA…) → snapshot mínimo ───────────────
            $a    = self::leerActivo($db, $id);
            $snap = self::snapIdentidad($a ?? []);
            $desc = implode(' | ', array_filter([
                ($snap['tipo'] ?? '') . ' ' . ($snap['marca'] ?? ''),
                'Ref: ' . ($snap['referencia'] ?? '—'),
                'S/N: ' . ($snap['serial']     ?? '—'),
            ]));
            self::insertar($db, $id, $tipo, $desc, $usuario, $snap);

        } catch (Exception $e) {
            error_log('HistorialHelper::registrar — ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────────────────────────────────

    public static function leerActivo(PDO $db, int $id): ?array
    {
        $s = $db->prepare("SELECT * FROM fun_read_activo_por_id(:id)");
        $s->execute([':id' => $id]);
        return $s->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function leerCamposDin(PDO $db, int $id): array
    {
        try {
            $s = $db->prepare("SELECT * FROM fun_get_valores_activo(:id)");
            $s->execute([':id' => $id]);
            $mapa = [];
            foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
                if (!empty($r['valor'])) {
                    $mapa[$r['nombre']] = ['etiqueta' => $r['etiqueta'], 'valor' => $r['valor']];
                }
            }
            return $mapa;
        } catch (Exception $e) { return []; }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // COMPARADORES
    // ─────────────────────────────────────────────────────────────────────────

    public static function difResp(array $a, array $d): bool
    {
        return trim((string)($a['r_cod_responsable'] ?? '')) !== trim((string)($d['r_cod_responsable'] ?? ''));
    }

    public static function difEstado(array $a, array $d): bool
    {
        return trim($a['r_estado'] ?? '') !== trim($d['r_estado'] ?? '');
    }

    public static function difFicha(array $a, array $d): bool
    {
        foreach (['r_serial','r_referencia','r_hostname','r_ip','r_mac','r_id_tipo','r_id_marca','r_id_modelo','r_id_padre'] as $c) {
            if (trim((string)($a[$c] ?? '')) !== trim((string)($d[$c] ?? ''))) return true;
        }
        return false;
    }

    public static function difDin(array $antes, array $despues): bool
    {
        foreach (array_unique(array_merge(array_keys($antes), array_keys($despues))) as $n) {
            if (trim((string)($antes[$n]['valor']   ?? '')) !== trim((string)($despues[$n]['valor'] ?? ''))) return true;
        }
        return false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSTRUCTORES DE SNAPSHOT
    // ─────────────────────────────────────────────────────────────────────────

    private static function snapActivo(PDO $db, int $id, bool $conPerifericos): array
    {
        $a = self::leerActivo($db, $id);
        if (!$a) return [];

        $snap = array_filter([
            'scope'       => 'ACTIVO',
            'tipo'        => trim($a['r_tipo']        ?? ''),
            'marca'       => trim($a['r_marca']       ?? ''),
            'modelo'      => trim($a['r_modelo']      ?? ''),
            'referencia'  => trim($a['r_referencia']  ?? ''),
            'serial'      => trim($a['r_serial']      ?? ''),
            'estado'      => trim($a['r_estado']      ?? ''),
            'hostname'    => trim($a['r_hostname']    ?? ''),
            'ip'          => trim($a['r_ip']          ?? ''),
            'mac'         => trim($a['r_mac']         ?? ''),
            'responsable' => trim($a['r_responsable'] ?? ''),
            'cod_resp'    => trim($a['r_cod_responsable'] ?? ''),
            'area'        => trim($a['r_area']        ?? ''),
        ], fn($v) => $v !== '' && $v !== null);
        $snap['scope'] = 'ACTIVO';

        // Campos dinámicos (procesador, RAM, SO, versión…)
        $din = self::leerCamposDin($db, $id);
        if (!empty($din)) {
            $snap['campos_extra'] = array_map(
                fn($d) => ['etiqueta' => $d['etiqueta'], 'valor' => $d['valor']], $din
            );
        }

        // Periféricos (solo en creación)
        if ($conPerifericos) {
            try {
                $s = $db->prepare("SELECT * FROM fun_read_perifericos_por_padre(:id)");
                $s->execute([':id' => $id]);
                $hijos = $s->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($hijos)) {
                    $snap['perifericos'] = array_map(fn($h) => array_filter([
                        'tipo'       => trim($h['r_tipo']       ?? ''),
                        'marca'      => trim($h['r_marca']      ?? ''),
                        'referencia' => trim($h['r_referencia'] ?? ''),
                        'serial'     => trim($h['r_serial']     ?? ''),
                        'mac'        => trim($h['r_mac']        ?? ''),
                        'ip'         => trim($h['r_ip']         ?? ''),
                    ], fn($v) => $v !== ''), $hijos);
                }
            } catch (Exception $e) {}
        }

        return $snap;
    }

    private static function snapIdentidad(array $a): array
    {
        return array_filter([
            'scope'      => 'IDENTIDAD',
            'tipo'       => trim($a['r_tipo']       ?? ''),
            'marca'      => trim($a['r_marca']      ?? ''),
            'referencia' => trim($a['r_referencia'] ?? ''),
            'serial'     => trim($a['r_serial']     ?? ''),
            'estado'     => trim($a['r_estado']     ?? ''),
        ], fn($v) => $v !== '');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESCRIPCIONES
    // ─────────────────────────────────────────────────────────────────────────

    private static function descCreacion(array $s): string
    {
        return sprintf("Activo creado — %s %s | Ref: %s | S/N: %s | Asignado a: %s",
            $s['tipo'] ?? 'Equipo', $s['marca'] ?? '', $s['referencia'] ?? '—',
            $s['serial'] ?? '—', $s['responsable'] ?? 'Bodega');
    }

    private static function descEdicion(array $s, bool $esPeriferico): string
    {
        $pref = $esPeriferico ? 'Periférico actualizado' : 'Ficha actualizada';
        return sprintf("%s — %s %s | Ref: %s | S/N: %s",
            $pref, $s['tipo'] ?? 'Equipo', $s['marca'] ?? '',
            $s['referencia'] ?? '—', $s['serial'] ?? '—');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INSERCIÓN
    // ─────────────────────────────────────────────────────────────────────────

    private static function insertar(PDO $db, int $id, string $tipo, string $desc, string $usuario, array $snap): void
    {
        $stmt = $db->prepare("SELECT * FROM fun_registrar_evento_activo(:id,:tipo,:desc,:usr,:snap::jsonb)");
        $stmt->execute([
            ':id'   => $id,
            ':tipo' => $tipo,
            ':desc' => $desc,
            ':usr'  => $usuario,
            ':snap' => json_encode($snap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RENDER HTML
    // ─────────────────────────────────────────────────────────────────────────

    public static function renderSnapshot(array $snap): string
    {
        if (empty($snap)) return '';
        $scope = $snap['scope'] ?? 'ACTIVO';
        $html  = '<div class="mt-3 space-y-3 text-xs">';

        // ── Solo responsable cambió ───────────────────────────────────────────
        if ($scope === 'ASIGNACION') {
            $html .= self::sec('Cambio de Responsable',
                self::filaD('Antes', 'fa-user-minus', $snap['responsable_ant'] ?? '—', $snap['cod_ant'] ?? '', 'text-red-400') .
                self::filaD('Ahora', 'fa-user-check', $snap['responsable_nvo'] ?? '—', $snap['cod_nvo'] ?? '', 'text-emerald-600') .
                (!empty($snap['area_nvo']) ? self::fila('Área', 'fa-building', htmlspecialchars($snap['area_nvo'])) : '')
            );
            return $html . '</div>';
        }

        // ── Solo estado cambió ────────────────────────────────────────────────
        if ($scope === 'ESTADO') {
            $html .= self::sec('Cambio de Estado',
                self::fila('Antes',      'fa-circle-dot',   htmlspecialchars($snap['estado_ant'] ?? '—'), self::ce($snap['estado_ant'] ?? '') . ' line-through') .
                self::fila('Después',    'fa-circle-dot',   htmlspecialchars($snap['estado_nvo'] ?? '—'), self::ce($snap['estado_nvo'] ?? '')) .
                self::fila('Tipo',       'fa-desktop',      htmlspecialchars($snap['tipo']       ?? '')) .
                self::fila('Marca',      'fa-trademark',    htmlspecialchars($snap['marca']      ?? '')) .
                self::fila('Referencia', 'fa-tag',          htmlspecialchars($snap['referencia'] ?? '')) .
                self::fila('Serial',     'fa-barcode',      htmlspecialchars($snap['serial']     ?? ''), 'font-mono')
            );
            return $html . '</div>';
        }

        // ── Ficha del equipo ──────────────────────────────────────────────────
        $rEq = '';
        foreach (['tipo'=>['Tipo','fa-desktop'],'marca'=>['Marca','fa-trademark'],'modelo'=>['Modelo','fa-layer-group'],
                  'referencia'=>['Referencia','fa-tag'],'serial'=>['Serial','fa-barcode'],'estado'=>['Estado','fa-circle-dot']] as $k=>[$l,$i]) {
            if (!empty($snap[$k])) {
                $ex = $k==='estado' ? self::ce($snap[$k]) : ($k==='serial' ? 'font-mono' : '');
                $rEq .= self::fila($l, $i, htmlspecialchars($snap[$k]), $ex);
            }
        }
        // Estado anterior tachado si hubo cambio en la misma edición
        if (!empty($snap['estado_anterior'])) {
            $rEq .= self::fila('Estado ant.', 'fa-rotate-left', htmlspecialchars($snap['estado_anterior']), self::ce($snap['estado_anterior']) . ' opacity-50 line-through');
        }
        if ($rEq) $html .= self::sec('Equipo', $rEq);

        // Conectividad
        $rRed = '';
        foreach (['hostname'=>['Hostname','fa-server'],'ip'=>['IP','fa-network-wired'],'mac'=>['MAC','fa-wifi']] as $k=>[$l,$i]) {
            if (!empty($snap[$k])) $rRed .= self::fila($l, $i, htmlspecialchars($snap[$k]), 'font-mono');
        }
        if ($rRed) $html .= self::sec('Conectividad', $rRed);

        // Asignación
        $rAs = '';
        if (!empty($snap['responsable_anterior'])) {
            $rAs .= self::fila('Antes', 'fa-user-minus', htmlspecialchars($snap['responsable_anterior']), 'text-red-400 opacity-60 line-through');
        }
        if (!empty($snap['responsable'])) {
            $rAs .= self::fila('Responsable', 'fa-user',    htmlspecialchars($snap['responsable']));
            if (!empty($snap['cod_resp'])) $rAs .= self::fila('Código', 'fa-id-card', htmlspecialchars($snap['cod_resp']), 'font-mono');
        }
        if (!empty($snap['area'])) $rAs .= self::fila('Área', 'fa-building', htmlspecialchars($snap['area']));
        if ($rAs) $html .= self::sec('Asignación', $rAs);

        // Especificaciones dinámicas
        if (!empty($snap['campos_extra'])) {
            $rD = '';
            foreach ($snap['campos_extra'] as $c) {
                $et = is_array($c) ? ($c['etiqueta'] ?? '') : $c;
                $vl = is_array($c) ? ($c['valor']    ?? '') : $c;
                if ($vl) $rD .= self::fila(htmlspecialchars($et), 'fa-sliders', htmlspecialchars($vl));
            }
            if ($rD) $html .= self::sec('Especificaciones', $rD);
        }

        // Periféricos (en creación)
        if (!empty($snap['perifericos'])) {
            $rP = '';
            foreach ($snap['perifericos'] as $p) {
                $tipo = htmlspecialchars($p['tipo']       ?? '');
                $marc = htmlspecialchars($p['marca']      ?? '');
                $ref  = htmlspecialchars($p['referencia'] ?? '');
                $ser  = htmlspecialchars($p['serial']     ?? 'S/N');
                $mac  = !empty($p['mac']) ? "<span class='font-mono text-slate-400'> MAC: {$p['mac']}</span>" : '';
                $ip   = !empty($p['ip'])  ? "<span class='font-mono text-slate-400'> IP: {$p['ip']}</span>"  : '';
                $rP  .= "<div class='py-2 border-b border-slate-100 last:border-0'>
                    <div class='flex items-center gap-1.5 mb-0.5'>
                        <i class='fas fa-plug text-[9px] text-slate-400'></i>
                        <span class='font-black text-slate-700'>{$tipo}</span>
                        <span class='text-slate-500'>— {$marc}</span>
                        " . ($ref ? "<span class='text-slate-400 text-[10px]'>{$ref}</span>" : '') . "
                    </div>
                    <div class='flex flex-wrap gap-x-3 pl-4 text-[10px]'>
                        <span class='font-mono text-slate-400'>S/N: {$ser}</span>{$mac}{$ip}
                    </div>
                </div>";
            }
            $html .= self::sec('Periféricos', $rP);
        }

        return $html . '</div>';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MINI-HELPERS HTML
    // ─────────────────────────────────────────────────────────────────────────
    private static function sec(string $t, string $c): string {
        return "<div class='rounded-xl border border-slate-100 overflow-hidden'>
            <div class='bg-slate-50 px-3 py-1.5 border-b border-slate-100'>
                <span class='text-[9px] font-black uppercase tracking-widest text-slate-400'>{$t}</span>
            </div>
            <div class='divide-y divide-slate-50 px-3'>{$c}</div>
        </div>";
    }

    private static function fila(string $l, string $i, string $v, string $x = ''): string {
        return "<div class='flex items-center gap-2 py-1.5'>
            <i class='fas {$i} text-[9px] text-slate-400 w-3 shrink-0'></i>
            <span class='text-slate-500 shrink-0 w-24'>{$l}</span>
            <span class='font-bold text-slate-700 {$x} truncate'>{$v}</span>
        </div>";
    }

    private static function filaD(string $l, string $i, string $nom, string $cod, string $x = ''): string {
        $c = $cod ? "<span class='font-mono text-slate-400 text-[10px] ml-1'>({$cod})</span>" : '';
        return "<div class='flex items-center gap-2 py-1.5'>
            <i class='fas {$i} text-[9px] text-slate-400 w-3 shrink-0'></i>
            <span class='text-slate-500 shrink-0 w-24'>{$l}</span>
            <span class='font-bold {$x}'>" . htmlspecialchars($nom) . "{$c}</span>
        </div>";
    }

    private static function ce(string $e): string {
        return match($e) {
            'Bueno'      => 'text-emerald-600',
            'Malo'       => 'text-red-500',
            'Reparacion' => 'text-amber-500',
            'Baja'       => 'text-slate-400',
            default      => 'text-slate-600',
        };
    }
}
