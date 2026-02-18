<?php
require_once __DIR__ . '/../core/database.php';

$libreria = __DIR__ . '/../vendor/SimpleXLSX.php';
if (file_exists($libreria)) {
    require_once $libreria;
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'msg' => 'ERROR CRITICO: No se encuentra SimpleXLSX en: ' . $libreria]);
    exit();
}

use Shuchkin\SimpleXLSX;

class ActivoController {

    public static function getFormData() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::conectar();
        return [
            'tipos'   => $db->query("SELECT id_tipoequi, nom_tipo FROM tab_tipos ORDER BY nom_tipo")->fetchAll(),
            'marcas'  => $db->query("SELECT id_marca, nom_marca FROM tab_marca ORDER BY nom_marca")->fetchAll(),
            'modelos' => $db->query("SELECT id_modelo, nom_modelo, id_tipoequi FROM tab_modelo")->fetchAll(),
            'areas'   => $db->query("SELECT id_area, nom_area FROM tab_area ORDER BY nom_area")->fetchAll(),
            'padres'  => $db->query("SELECT id_activo, serial, referencia FROM tab_activotec WHERE id_padre_activo IS NULL")->fetchAll()
        ];
    }

    public static function analizarExcel($fileTmp) {
        if (!$xlsx = SimpleXLSX::parse($fileTmp)) {
            return ['success' => false, 'msg' => 'No se pudo leer el archivo .xlsx: ' . SimpleXLSX::parseError()];
        }

        $grid            = self::buildGrid($xlsx->rows());
        $cabecera        = self::parseCabecera($grid);
        $equipoData      = self::parseEquipoPrincipal($grid);
        $accIzq          = self::parseAccesoriosIzquierda($grid, $equipoData['seccion_fin']);
        $accDer          = self::parseAccesoriosDerecha($grid);
        $accesorios      = array_merge($accIzq, $accDer);
        $tipoEquipo      = self::mapearTipoEquipo($equipoData['seccion_nombre']);

        return [
            'success'     => true,
            'principal'   => [
                'tipo'        => $tipoEquipo,
                'marca'       => $equipoData['marca']       ?? '',
                'referencia'  => $equipoData['referencia']  ?? '',
                'serial'      => $equipoData['serial']      ?? '',
                'ip'          => $equipoData['ip']          ?? '',
                'mac'         => $equipoData['mac']         ?? '',
                'hostname'    => $cabecera['nombre_equipo'] ?? '',
                'responsable' => $cabecera['responsable']   ?? '',
            ],
            'cpu_detalle' => [
                'procesador'   => $equipoData['procesador']       ?? '',
                'ram'          => $equipoData['ram']              ?? '',
                'disco_duro'   => $equipoData['disco_duro']       ?? '',
                'almacenamiento'=> $equipoData['almacenamiento']  ?? '',
                'windows'      => $equipoData['windows']          ?? '',
                'so'           => $equipoData['sistema_operativo']?? '',
                'version_so'   => $equipoData['version']          ?? '',
                'office'       => $equipoData['office']           ?? '',
                'modelo'       => $equipoData['modelo']           ?? '',
                'usuario'      => $cabecera['usuario']            ?? '',
                'dependencia'  => $cabecera['dependencia']        ?? '',
                'fecha'        => $cabecera['fecha']              ?? '',
            ],
            'accesorios'  => $accesorios,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS BASE
    // ─────────────────────────────────────────────────────────────

    private static function buildGrid(array $rows) {
        $grid = [];
        foreach ($rows as $r => $row) {
            foreach ($row as $c => $val) {
                $v = trim((string)$val);
                if ($v !== '') {
                    $grid[$r][$c] = $v;
                }
            }
        }
        return $grid;
    }

    private static function g(array $grid, $r, $c) {
        return trim(isset($grid[$r][$c]) ? $grid[$r][$c] : '');
    }

    private static function norm($v) {
        return strtoupper(trim($v));
    }

    private static function isEmpty($v) {
        $u = self::norm($v);
        return ($u === '' || $u === 'N/A' || $u === 'N.A' || $u === 'NA'
             || $u === 'NO APLICA' || $u === 'S/N' || $u === '-');
    }

    private static function val($v) {
        return self::isEmpty($v) ? null : trim($v);
    }

    // ─────────────────────────────────────────────────────────────
    // MAPEO TIPO DE EQUIPO (nombre sección → nombre en tab_tipos)
    // ─────────────────────────────────────────────────────────────
    private static function mapearTipoEquipo($seccion) {
        // Claves de cabecera (campo "Portatil _X_")
        $mapa = [
            'CPU'         => 'Computador',
            'COMPUTADOR'  => 'Computador',
            'PORTATIL'    => 'Computador',
            'ESCRITORIO'  => 'Computador',
            'TODO EN UNO' => 'Computador',
            'AIO'         => 'Computador',
            'SERVIDOR'    => 'Servidor',
            'TABLET'      => 'Tablet',
            'CELULAR'     => 'Celular',
            'TELEFONO'    => 'Telefono',
            ''            => 'Computador',
        ];
        $key = self::norm($seccion);
        return isset($mapa[$key]) ? $mapa[$key] : $seccion;
    }

    // ─────────────────────────────────────────────────────────────
    // CABECERA (fecha, usuario, nombre equipo, dependencia, responsable)
    // ─────────────────────────────────────────────────────────────
    private static function parseCabecera(array $grid) {
        // Detectar tipo desde campo "Portatil _X_ Escritorio __" si existe
        $tipoEquipo = '';
        foreach ($grid as $r => $row) {
            foreach ($row as $c => $val) {
                if (stripos($val, 'Portatil') !== false && stripos($val, 'Escritorio') !== false) {
                    if (preg_match('/[Pp]ortatil\s*_X_/u', $val)) {
                        $tipoEquipo = 'Portatil';
                    } elseif (preg_match('/[Ee]scritorio\s*_X_/u', $val)) {
                        $tipoEquipo = 'Escritorio';
                    } elseif (preg_match('/[Tt]odo en [Uu]no\s*_X_/u', $val)) {
                        $tipoEquipo = 'Todo en Uno';
                    }
                    break 2;
                }
            }
        }

        $result = [
            'fecha'         => '',
            'usuario'       => '',
            'nombre_equipo' => '',
            'dependencia'   => '',
            'tipo_equipo'   => $tipoEquipo,
            'responsable'   => '',
        ];

        $labelMap = [
            'FECHA'         => 'fecha',
            'USUARIO'       => 'usuario',
            'NOMBRE EQUIPO' => 'nombre_equipo',
            'DEPENDENCIA'   => 'dependencia',
        ];

        foreach ($grid as $r => $row) {
            foreach ($row as $c => $val) {
                $key = self::norm($val);

                // Campos normales: etiqueta → valor a la derecha o abajo
                foreach ($labelMap as $label => $field) {
                    if (strpos($key, $label) !== false && empty($result[$field])) {
                        $right = self::g($grid, $r, $c + 1);
                        $below = self::g($grid, $r + 1, $c);
                        $found = !self::isEmpty($right) ? $right : (!self::isEmpty($below) ? $below : '');
                        if (!empty($found)) {
                            $result[$field] = $found;
                        }
                    }
                }

                // Responsable: puede estar en col E/F, o en formato B="Responsable:" → B_siguiente=nombre
                if (strpos($key, 'RESPONSABLE') !== false && empty($result['responsable'])) {
                    // Caso 1: valor a la derecha (col E → col F)
                    $right = self::g($grid, $r, $c + 1);
                    if (!self::isEmpty($right)) {
                        $result['responsable'] = $right;
                        continue;
                    }
                    // Caso 2: valor en la fila siguiente (B="Responsable:" → B+1=nombre)
                    $below = self::g($grid, $r + 1, $c);
                    if (!self::isEmpty($below)) {
                        $result['responsable'] = $below;
                    }
                }
            }
        }

        // Normalizar fecha
        if (!empty($result['fecha'])) {
            if (preg_match('/(\d{4}-\d{2}-\d{2})/', $result['fecha'], $m)) {
                $result['fecha'] = $m[1];
            }
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────
    // EQUIPO PRINCIPAL — detecta automáticamente la sección
    // (CPU, TABLET, SERVIDOR, etc.) y extrae sus datos
    // ─────────────────────────────────────────────────────────────
    private static function parseEquipoPrincipal(array $grid) {

        // Secciones que pueden ser el equipo principal
        $SECCIONES_PRINCIPALES = ['CPU', 'TABLET', 'COMPUTADOR', 'PORTATIL', 'SERVIDOR', 'AIO', 'CELULAR'];

        // Secciones que marcan el FIN de la sección principal
        $SECCIONES_FIN = ['TECLADO', 'TARJETA RED', 'TELEFONO', 'BACKUP', 'ACTUALIZACIONES', 'RESPONSABLE'];

        // Encontrar sección principal en col B (index 1)
        $seccionNombre = '';
        $seccionStart  = null;
        foreach ($grid as $r => $row) {
            if (!isset($row[1])) continue;
            $norm = self::norm($row[1]);
            if (in_array($norm, $SECCIONES_PRINCIPALES)) {
                $seccionNombre = $norm;
                $seccionStart  = $r;
                break;
            }
        }
        if ($seccionStart === null) {
            return ['seccion_nombre' => '', 'seccion_fin' => PHP_INT_MAX];
        }

        // Encontrar fin de sección
        $seccionFin = PHP_INT_MAX;
        foreach ($grid as $r => $row) {
            if ($r <= $seccionStart) continue;
            if (!isset($row[1])) continue;
            $norm = self::norm($row[1]);
            foreach ($SECCIONES_FIN as $s) {
                if (strpos($norm, $s) !== false) {
                    $seccionFin = $r;
                    break 2;
                }
            }
        }

        // Etiquetas con coincidencia EXACTA (evita colisiones como IP dentro de TIPO S.O)
        $exactMap = [
            'IP'      => 'ip',
            'MAC'     => 'mac',
            'CD'      => 'cd',
            'RAM'     => 'ram',
            'VERSION' => 'version',
        ];

        // Etiquetas con coincidencia PARCIAL
        $labelMap = [
            'MARCA'            => 'marca',
            'MODELO'           => 'modelo',
            'REFERENCIA'       => 'referencia',
            'SERIAL'           => 'serial',
            'SERIE'            => 'serial',
            'WINDOWS'          => 'windows',
            'SISTEMA OPERATIVO'=> 'sistema_operativo',
            'TIPO S.O'         => 'tipo_so',
            'OFFICE'           => 'office',
            'PROCESADOR'       => 'procesador',
            'DISCO DURO'       => 'disco_duro',
            'ALMACENAMIENTO'   => 'almacenamiento',
        ];

        $datos = ['seccion_nombre' => $seccionNombre, 'seccion_fin' => $seccionFin];

        foreach ($grid as $r => $row) {
            if ($r < $seccionStart || $r >= $seccionFin) continue;

            $label = self::norm(self::g($grid, $r, 1));
            $valor = self::g($grid, $r, 2);

            // Ignorar etiquetas que son encabezados de sección o contienen "ENVIA"
            if (in_array($label, $SECCIONES_PRINCIPALES)) continue;
            if (strpos($label, 'ENVIA') !== false) continue;

            // 1. Coincidencia exacta primero
            if (isset($exactMap[$label]) && !isset($datos[$exactMap[$label]])) {
                $v = self::val($valor);
                if ($v !== null) {
                    $datos[$exactMap[$label]] = $v;
                }
                continue;
            }

            // 2. Coincidencia parcial
            foreach ($labelMap as $patron => $campo) {
                if (strpos($label, $patron) !== false && !isset($datos[$campo])) {
                    $v = self::val($valor);
                    if ($v !== null) {
                        $datos[$campo] = $v;
                    }
                    break;
                }
            }
        }

        // Normalizar: disco_duro y almacenamiento son lo mismo
        if (!isset($datos['disco_duro']) && isset($datos['almacenamiento'])) {
            $datos['disco_duro'] = $datos['almacenamiento'];
        }

        // Normalizar: sistema_operativo y windows son lo mismo conceptualmente
        if (!isset($datos['windows']) && isset($datos['sistema_operativo'])) {
            $so = $datos['sistema_operativo'];
            if (isset($datos['version'])) {
                $so .= ' ' . $datos['version'];
            }
            $datos['windows'] = $so;
        }

        return $datos;
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESORIOS IZQUIERDA (col B/C): Teclado, Red, Teléfono
    // ─────────────────────────────────────────────────────────────
    private static function parseAccesoriosIzquierda(array $grid, $despuesDe) {
        $accesorios   = [];
        $seccionesAcc = [
            'TECLADO'                 => 'Teclado',
            'TARJETA RED INALAMBRICA' => 'Tarjeta Red Inalambrica',
            'TELEFONO'                => 'Telefono',
        ];
        $labelMap = [
            'MARCA'      => 'marca',
            'REFERENCIA' => 'referencia',
            'SERIE'      => 'serial',
            'SERIAL'     => 'serial',
            'MAC'        => 'mac',
        ];

        $posiciones = [];
        foreach ($grid as $r => $row) {
            if (!isset($row[1])) continue;
            $norm = self::norm($row[1]);
            foreach ($seccionesAcc as $patron => $nombre) {
                if (strpos($norm, $patron) !== false && !isset($posiciones[$nombre])) {
                    $posiciones[$nombre] = $r;
                }
            }
        }

        $nombres = array_keys($posiciones);
        $inicios = array_values($posiciones);
        $total   = count($nombres);

        for ($i = 0; $i < $total; $i++) {
            $nombre   = $nombres[$i];
            $rowStart = $inicios[$i];
            $rowEnd   = ($i + 1 < $total) ? $inicios[$i + 1] : PHP_INT_MAX;

            $datos = ['tipo' => $nombre, 'serial' => null, 'referencia' => null, 'marca' => null, 'mac' => null];

            foreach ($grid as $r => $row) {
                if ($r <= $rowStart || $r >= $rowEnd) continue;
                $label = self::norm(self::g($grid, $r, 1));
                $valor = self::g($grid, $r, 2);
                foreach ($labelMap as $patron => $campo) {
                    if (strpos($label, $patron) !== false && $datos[$campo] === null) {
                        $v = self::val($valor);
                        if ($v !== null) $datos[$campo] = $v;
                        break;
                    }
                }
            }

            if ($datos['serial'] !== null || $datos['referencia'] !== null || $datos['marca'] !== null) {
                $ref = $datos['referencia'] !== null ? $datos['referencia'] : $datos['marca'];
                $accesorios[] = [
                    'tipo'       => $datos['tipo'],
                    'serial'     => $datos['serial']     !== null ? $datos['serial'] : '',
                    'referencia' => $ref                 !== null ? $ref             : '',
                    'marca'      => $datos['marca']      !== null ? $datos['marca']  : '',
                    'mac'        => $datos['mac']        !== null ? $datos['mac']    : '',
                ];
            }
        }

        return $accesorios;
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESORIOS DERECHA (col E=4 / F=5)
    // ─────────────────────────────────────────────────────────────
    private static function parseAccesoriosDerecha(array $grid) {
        $accesorios   = [];
        $seccionesAcc = ['LECTOR', 'BASE LECTOR', 'MONITOR', 'MOUSE', 'UPS', 'IMPRESORA'];
        $labelMap     = [
            'MARCA'      => 'marca',
            'REFERENCIA' => 'referencia',
            'SERIE'      => 'serial',
            'SERIAL'     => 'serial',
            'PLACA'      => 'placa',
        ];

        $posiciones = [];
        foreach ($grid as $r => $row) {
            $val  = self::g($grid, $r, 4);
            $norm = self::norm($val);
            foreach ($seccionesAcc as $seccion) {
                if ($norm === $seccion && !isset($posiciones[$seccion])) {
                    $posiciones[$seccion] = $r;
                }
            }
        }

        $nombres = array_keys($posiciones);
        $inicios = array_values($posiciones);
        $total   = count($nombres);

        for ($i = 0; $i < $total; $i++) {
            $nombre   = $nombres[$i];
            $rowStart = $inicios[$i];
            $rowEnd   = ($i + 1 < $total) ? $inicios[$i + 1] : PHP_INT_MAX;

            $datos = ['tipo' => ucfirst(strtolower($nombre)), 'serial' => null, 'referencia' => null, 'marca' => null, 'placa' => null];

            foreach ($grid as $r => $row) {
                if ($r <= $rowStart || $r >= $rowEnd) continue;
                $label = self::norm(self::g($grid, $r, 4));
                $valor = self::g($grid, $r, 5);
                if (strpos($label, 'ENVIA') !== false) continue;
                foreach ($labelMap as $patron => $campo) {
                    if (strpos($label, $patron) !== false && $datos[$campo] === null) {
                        $v = self::val($valor);
                        if ($v !== null) $datos[$campo] = $v;
                        break;
                    }
                }
            }

            $tieneSerial = $datos['serial'] !== null;
            $tieneRef    = $datos['referencia'] !== null;
            $marcaValida = $datos['marca'] !== null && !self::isEmpty($datos['marca']);

            if ($tieneSerial || ($tieneRef && $marcaValida)) {
                $ref = $datos['referencia'] !== null ? $datos['referencia'] : $datos['marca'];
                $accesorios[] = [
                    'tipo'       => $datos['tipo'],
                    'serial'     => $datos['serial'] !== null ? $datos['serial'] : '',
                    'referencia' => $ref             !== null ? $ref             : '',
                    'marca'      => $datos['marca']  !== null ? $datos['marca']  : '',
                    'placa'      => $datos['placa']  !== null ? $datos['placa']  : '',
                ];
            }
        }

        return $accesorios;
    }

    // ─────────────────────────────────────────────────────────────
    // GUARDAR ACTIVO
    // ─────────────────────────────────────────────────────────────
    public static function store($postData) {
        try {
            $db = Database::conectar();
            $db->beginTransaction();

            $cod     = $postData['cod_responsable'];
            $nom     = isset($postData['nom_nuevo_empleado']) ? $postData['nom_nuevo_empleado'] : '';
            $id_area = isset($postData['id_area_nuevo'])      ? $postData['id_area_nuevo']      : '';

            if (!empty($nom) && !empty($id_area)) {
                $stmtEmp = $db->prepare("SELECT * FROM fun_create_empleado(:c, :n, :a)");
                $stmtEmp->execute([':c' => $cod, ':n' => $nom, ':a' => $id_area]);
            }

            $qr = "QR-" . strtoupper(bin2hex(random_bytes(3)));
            $stmtAct = $db->prepare("SELECT * FROM fun_create_activo(
                :ser, :qr, :host, :ref, :mac, :ip, :tipo, :marca, :mod, :est, :resp, :padre
            )");
            $stmtAct->execute([
                ':ser'   => $postData['serial'],
                ':qr'    => $qr,
                ':host'  => isset($postData['hostname'])   ? $postData['hostname']   : null,
                ':ref'   => isset($postData['referencia']) ? $postData['referencia'] : null,
                ':mac'   => isset($postData['mac_activo']) ? $postData['mac_activo'] : null,
                ':ip'    => isset($postData['ip_equipo'])  ? $postData['ip_equipo']  : null,
                ':tipo'  => $postData['id_tipoequi'],
                ':marca' => $postData['id_marca'],
                ':mod'   => !empty($postData['id_modelo']) ? $postData['id_modelo'] : null,
                ':est'   => $postData['estado'],
                ':resp'  => $cod,
                ':padre' => !empty($postData['id_padre_activo']) ? $postData['id_padre_activo'] : null,
            ]);

            $resPrincipal   = $stmtAct->fetch();
            $id_padre_nuevo = $resPrincipal['id_res'];

            if (!empty($postData['accesorios_json_final']) && $id_padre_nuevo > 0) {
                $accesorios = json_decode($postData['accesorios_json_final'], true);
                foreach ($accesorios as $acc) {
                    $qr_acc = "QR-" . strtoupper(bin2hex(random_bytes(3)));
                    $stmtAcc = $db->prepare("SELECT * FROM fun_create_activo(:ser, :qr, NULL, :ref, NULL, NULL, :tipo, :marca, NULL, :est, :resp, :padre)");
                    $stmtAcc->execute([
                        ':ser'   => !empty($acc['serial'])     ? $acc['serial']     : 'S/N-' . strtoupper(bin2hex(random_bytes(2))),
                        ':qr'    => $qr_acc,
                        ':ref'   => !empty($acc['referencia']) ? $acc['referencia'] : (isset($acc['tipo_nombre']) ? $acc['tipo_nombre'] : 'Accesorio'),
                        ':tipo'  => $acc['tipo_id'],
                        ':marca' => $postData['id_marca'],
                        ':est'   => 'Bueno',
                        ':resp'  => $cod,
                        ':padre' => $id_padre_nuevo,
                    ]);
                }
            }

            $db->commit();
            header("Location: ../public/activos.php?msg=Activo registrado correctamente&tipo=success");

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            header("Location: ../public/crear_activo.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
        }
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['action']) && $_GET['action'] === 'analizar') {
        ini_set('display_errors', 0);
        error_reporting(0);
        header('Content-Type: application/json');
        try {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'msg' => 'Error al subir el archivo.']);
                exit();
            }
            echo json_encode(ActivoController::analizarExcel($_FILES['file']['tmp_name']));
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'msg' => 'Error del servidor: ' . $e->getMessage()]);
        }
        exit();
    } else {
        ActivoController::store($_POST);
    }
}