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

        $grid       = self::buildGrid($xlsx->rows());
        $cabecera   = self::parseCabecera($grid);
        $cpu        = self::parseCPU($grid);
        $accIzq     = self::parseAccesoriosIzquierda($grid);
        $accDer     = self::parseAccesoriosDerecha($grid);
        $accesorios = array_merge($accIzq, $accDer);
        $tipoEquipo = self::mapearTipoEquipo($cabecera['tipo_equipo']);

        return [
            'success'     => true,
            'principal'   => [
                'tipo'        => $tipoEquipo,
                'marca'       => $cpu['marca']      ?? '',
                'referencia'  => $cpu['referencia'] ?? '',
                'serial'      => $cpu['serial']     ?? '',
                'ip'          => $cpu['ip']         ?? '',
                'mac'         => $cpu['mac']        ?? '',
                'hostname'    => $cabecera['nombre_equipo'] ?? '',
                'responsable' => $cabecera['responsable']   ?? '',
            ],
            'cpu_detalle' => [
                'procesador'  => $cpu['procesador']  ?? '',
                'ram'         => $cpu['ram']         ?? '',
                'disco_duro'  => $cpu['disco_duro']  ?? '',
                'windows'     => $cpu['windows']     ?? '',
                'office'      => $cpu['office']      ?? '',
                'tipo_so'     => $cpu['tipo_so']     ?? '',
                'cd'          => $cpu['cd']          ?? '',
                'usuario'     => $cabecera['usuario']     ?? '',
                'dependencia' => $cabecera['dependencia'] ?? '',
                'fecha'       => $cabecera['fecha']       ?? '',
            ],
            'accesorios' => $accesorios,
        ];
    }

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
        return ($u === '' || $u === 'N/A' || $u === 'N.A' || $u === 'NA' || $u === 'NO APLICA' || $u === 'S/N' || $u === '-');
    }

    private static function val($v) {
        return self::isEmpty($v) ? null : trim($v);
    }

    private static function mapearTipoEquipo($tipo) {
        $mapa = [
            'Portatil'    => 'Computador',
            'Escritorio'  => 'Computador',
            'Todo en Uno' => 'Computador',
            'CPU'         => 'Computador',
            ''            => 'Computador',
        ];
        return isset($mapa[$tipo]) ? $mapa[$tipo] : $tipo;
    }

    private static function parseCabecera(array $grid) {
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
            'RESPONSABLE'   => 'responsable',
        ];

        foreach ($grid as $r => $row) {
            foreach ($row as $c => $val) {
                $key = self::norm($val);
                foreach ($labelMap as $label => $field) {
                    if (strpos($key, $label) !== false && empty($result[$field])) {
                        $right = self::g($grid, $r, $c + 1);
                        $below = self::g($grid, $r + 1, $c);
                        $found = '';
                        if (!self::isEmpty($right)) {
                            $found = $right;
                        } elseif (!self::isEmpty($below)) {
                            $found = $below;
                        }
                        if (!empty($found)) {
                            $result[$field] = $found;
                        }
                    }
                }
            }
        }

        if (!empty($result['fecha'])) {
            if (preg_match('/(\d{4}-\d{2}-\d{2})/', $result['fecha'], $m)) {
                $result['fecha'] = $m[1];
            }
        }

        return $result;
    }

    private static function parseCPU(array $grid) {
        $cpu = [];

        // Coincidencia EXACTA para etiquetas cortas
        // Evita que 'IP' coincida con 'TIPO S.O' (contiene "IP" en su interior)
        $exactMap = [
            'IP'  => 'ip',
            'MAC' => 'mac',
            'CD'  => 'cd',
            'RAM' => 'ram',
        ];

        // Coincidencia PARCIAL para etiquetas largas
        $labelMap = [
            'MARCA'      => 'marca',
            'REFERENCIA' => 'referencia',
            'SERIAL'     => 'serial',
            'SERIE'      => 'serial',
            'WINDOWS'    => 'windows',
            'TIPO S.O'   => 'tipo_so',
            'OFFICE'     => 'office',
            'PROCESADOR' => 'procesador',
            'DISCO DURO' => 'disco_duro',
        ];

        $cpuStart = null;
        foreach ($grid as $r => $row) {
            if (isset($row[1]) && self::norm($row[1]) === 'CPU') {
                $cpuStart = $r;
                break;
            }
        }
        if ($cpuStart === null) {
            return $cpu;
        }

        $seccionesFin = ['TECLADO', 'TARJETA RED', 'TELEFONO', 'BACKUP', 'ACTUALIZACIONES'];
        $cpuEnd = PHP_INT_MAX;
        foreach ($grid as $r => $row) {
            if ($r <= $cpuStart) {
                continue;
            }
            if (isset($row[1])) {
                $norm = self::norm($row[1]);
                foreach ($seccionesFin as $s) {
                    if (strpos($norm, $s) !== false) {
                        $cpuEnd = $r;
                        break 2;
                    }
                }
            }
        }

        foreach ($grid as $r => $row) {
            if ($r < $cpuStart || $r >= $cpuEnd) {
                continue;
            }
            $label = self::norm(self::g($grid, $r, 1));
            $valor = self::g($grid, $r, 2);

            // 1. Exacta primero
            if (isset($exactMap[$label]) && !isset($cpu[$exactMap[$label]])) {
                $v = self::val($valor);
                if ($v !== null) {
                    $cpu[$exactMap[$label]] = $v;
                }
                continue;
            }

            // 2. Parcial
            foreach ($labelMap as $patron => $campo) {
                if (strpos($label, $patron) !== false && !isset($cpu[$campo])) {
                    $v = self::val($valor);
                    if ($v !== null) {
                        $cpu[$campo] = $v;
                    }
                    break;
                }
            }
        }

        return $cpu;
    }

    private static function parseAccesoriosIzquierda(array $grid) {
        $accesorios = [];
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
            if (!isset($row[1])) {
                continue;
            }
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

            $datos = [
                'tipo'       => $nombre,
                'serial'     => null,
                'referencia' => null,
                'marca'      => null,
                'mac'        => null,
            ];

            foreach ($grid as $r => $row) {
                if ($r <= $rowStart || $r >= $rowEnd) {
                    continue;
                }
                $label = self::norm(self::g($grid, $r, 1));
                $valor = self::g($grid, $r, 2);
                foreach ($labelMap as $patron => $campo) {
                    if (strpos($label, $patron) !== false && $datos[$campo] === null) {
                        $v = self::val($valor);
                        if ($v !== null) {
                            $datos[$campo] = $v;
                        }
                        break;
                    }
                }
            }

            if ($datos['serial'] !== null || $datos['referencia'] !== null || $datos['marca'] !== null) {
                $ref = $datos['referencia'];
                if ($ref === null) {
                    $ref = $datos['marca'];
                }
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

    private static function parseAccesoriosDerecha(array $grid) {
        $accesorios   = [];
        $seccionesAcc = ['LECTOR', 'MONITOR', 'MOUSE', 'UPS', 'IMPRESORA'];
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

            $datos = [
                'tipo'       => ucfirst(strtolower($nombre)),
                'serial'     => null,
                'referencia' => null,
                'marca'      => null,
                'placa'      => null,
            ];

            foreach ($grid as $r => $row) {
                if ($r <= $rowStart || $r >= $rowEnd) {
                    continue;
                }
                $label = self::norm(self::g($grid, $r, 4));
                $valor = self::g($grid, $r, 5);
                foreach ($labelMap as $patron => $campo) {
                    if (strpos($label, $patron) !== false && $datos[$campo] === null) {
                        $v = self::val($valor);
                        if ($v !== null) {
                            $datos[$campo] = $v;
                        }
                        break;
                    }
                }
            }

            $tieneSerial = $datos['serial'] !== null;
            $tieneRef    = $datos['referencia'] !== null;
            $marcaValida = $datos['marca'] !== null && !self::isEmpty($datos['marca']);

            if ($tieneSerial || ($tieneRef && $marcaValida)) {
                $ref = $datos['referencia'];
                if ($ref === null) {
                    $ref = $datos['marca'];
                }
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
                echo json_encode(['success' => false, 'msg' => 'Error al subir el archivo. Codigo: ' . (isset($_FILES['file']['error']) ? $_FILES['file']['error'] : 'sin archivo')]);
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