<?php
/**
 * SIH — Exportar a Excel
 * Genera .xlsx con formato Hoja de Vida usando PHP puro (sin Python).
 *
 * ?modo=individual&id=N  → hoja de vida de un activo
 * ?modo=general          → inventario filtrado por tipo(s)
 */
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/HojaVidaXlsx.php';

class ExportarController
{
    public static function manejar(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

        $modo = $_GET['modo'] ?? 'general';
        if ($modo === 'individual') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) { header('Location: activos.php?msg=ID+inv%C3%A1lido&tipo=danger'); exit(); }
            self::exportarIndividual($id);
        } else {
            self::exportarGeneral($_GET['tipos'] ?? []);
        }
    }

    // ── Individual ────────────────────────────────────────────────────────
    private static function exportarIndividual(int $id): void
    {
        $db = Database::conectar();

        $stmt = $db->prepare("SELECT * FROM fun_read_activo_por_id(:id)");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $activo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$activo) { header('Location: activos.php?msg=No+encontrado&tipo=danger'); exit(); }

        $stmtH = $db->prepare("SELECT * FROM fun_read_perifericos_por_padre(:p)");
        $stmtH->bindParam(':p', $id, PDO::PARAM_INT);
        $stmtH->execute();
        $hijos = $stmtH->fetchAll(PDO::FETCH_ASSOC);

        // ── Campos dinámicos del activo ───────────────────────────────────
        $campos_dinamicos = [];
        try {
            $stmtD = $db->prepare("SELECT * FROM fun_get_valores_activo(:id)");
            $stmtD->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtD->execute();
            $raw = $stmtD->fetchAll(PDO::FETCH_ASSOC);
            $campos_dinamicos = array_filter($raw, fn($c) => !($c['is_base'] ?? false));
        } catch (Exception $e) { /* función no existe aún */ }

        $novedades = self::getNovedades($db, $id);
        foreach ($hijos as $h)
            $novedades = array_merge($novedades, self::getNovedades($db, (int)$h['r_id']));

        $slug   = preg_replace('/[^A-Za-z0-9_-]/', '_', $activo['r_hostname'] ?? $activo['r_qr'] ?? "activo_{$id}");
        $fname  = "HOJA_DE_VIDA_{$slug}_".date('Ymd').'.xlsx';
        $tmp    = sys_get_temp_dir().'/'.$fname;

        $gen = new HojaVidaXlsx();
        $gen->agregarHoja($activo, $hijos, $novedades, $campos_dinamicos);
        $gen->guardar($tmp);

        self::send($tmp, $fname);
    }

    // ── General ───────────────────────────────────────────────────────────
    private static function exportarGeneral(array $tipos): void
    {
        $db    = Database::conectar();
        $tipos = array_values(array_filter(array_map('trim', $tipos)));

        $base = "
            SELECT a.id_activo, a.serial AS r_serial, a.codigo_qr AS r_qr,
                   a.hostname AS r_hostname, a.referencia AS r_referencia,
                   a.mac_activo AS r_mac, a.ip_equipo AS r_ip, a.estado AS r_estado,
                   t.nom_tipo  AS r_tipo,  m.nom_marca  AS r_marca,
                   mo.nom_modelo AS r_modelo,
                   e.nom_emple AS r_responsable, e.cod_nom AS r_cod_responsable,
                   ar.nom_area AS r_area
            FROM tab_activotec a
            INNER JOIN tab_tipos     t  ON a.id_tipoequi        = t.id_tipoequi
            INNER JOIN tab_marca     m  ON a.id_marca            = m.id_marca
            LEFT  JOIN tab_modelo   mo  ON a.id_modelo           = mo.id_modelo
            LEFT  JOIN tab_empleados e  ON a.cod_nom_responsable = e.cod_nom
            LEFT  JOIN tab_area     ar  ON e.id_area             = ar.id_area
            WHERE a.activo = TRUE AND a.id_padre_activo IS NULL";

        if (!empty($tipos)) {
            $ph   = implode(',', array_fill(0, count($tipos), '?'));
            $stmt = $db->prepare($base." AND LOWER(t.nom_tipo) IN ($ph) ORDER BY t.nom_tipo,a.id_activo");
            $stmt->execute(array_map('strtolower', $tipos));
        } else {
            $stmt = $db->prepare($base." ORDER BY t.nom_tipo,a.id_activo");
            $stmt->execute();
        }

        $activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($activos)) {
            header('Location: activos.php?msg='.urlencode('Sin activos para exportar').'&tipo=warning');
            exit();
        }

        $stmtH = $db->prepare("SELECT * FROM fun_read_perifericos_por_padre(:p)");
        $stmtD = $db->prepare("SELECT * FROM fun_get_valores_activo(:id)");
        $gen   = new HojaVidaXlsx();

        foreach ($activos as $act) {
            $rid = (int)$act['id_activo'];
            $stmtH->bindParam(':p', $rid, PDO::PARAM_INT);
            $stmtH->execute();
            $hijos = $stmtH->fetchAll(PDO::FETCH_ASSOC);

            $campos_dinamicos = [];
            try {
                $stmtD->bindParam(':id', $rid, PDO::PARAM_INT);
                $stmtD->execute();
                $campos_dinamicos = $stmtD->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) { /* función no existe aún */ }

            $novedades = self::getNovedades($db, $rid);
            $gen->agregarHoja($act, $hijos, $novedades, $campos_dinamicos);
        }

        $label = !empty($tipos) ? strtoupper(implode('_', array_map(fn($t)=>substr($t,0,8),$tipos))) : 'TODOS';
        $fname = "INVENTARIO_{$label}_".date('Ymd').'.xlsx';
        $tmp   = sys_get_temp_dir().'/'.$fname;
        $gen->guardar($tmp);
        self::send($tmp, $fname);
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    private static function getNovedades(\PDO $db, int $id): array
    {
        $s = $db->prepare("
            SELECT n.id_novedad, n.fecha_reporte, n.cedula_reportante,
                   n.nombre_reportante, n.tipo_dano, n.descripcion, n.estado_ticket
            FROM tab_novedades n
            WHERE n.id_activo=:id AND n.activo=TRUE
            ORDER BY n.fecha_reporte DESC
        ");
        $s->bindParam(':id', $id, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function send(string $path, string $fname): void
    {
        if (!file_exists($path)) {
            header('Location: activos.php?msg='.urlencode('Error al generar el Excel').'&tipo=danger');
            exit();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fname.'"');
        header('Content-Length: '.filesize($path));
        header('Cache-Control: max-age=0');
        readfile($path);
        unlink($path);
        exit();
    }
}

ExportarController::manejar();
