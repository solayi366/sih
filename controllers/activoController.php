<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../vendor/SimpleXLSX.php'; // Asegúrate de haber creado este archivo

class ActivoController {
    
    // Trae datos para selectores
    public static function getFormData() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::conectar();
        return [
            'tipos'    => $db->query("SELECT id_tipoequi, nom_tipo FROM tab_tipos WHERE activo = TRUE ORDER BY nom_tipo")->fetchAll(),
            'marcas'   => $db->query("SELECT id_marca, nom_marca FROM tab_marca WHERE activo = TRUE ORDER BY nom_marca")->fetchAll(),
            'modelos'  => $db->query("SELECT id_modelo, nom_modelo, id_tipoequi FROM tab_modelo WHERE activo = TRUE")->fetchAll(),
            'areas'    => $db->query("SELECT id_area, nom_area FROM tab_area WHERE activo = TRUE ORDER BY nom_area")->fetchAll(),
            'padres'   => $db->query("SELECT id_activo, serial, referencia FROM tab_activotec WHERE activo = TRUE AND id_padre_activo IS NULL")->fetchAll()
        ];
    }

    /**
     * ANALIZADOR DE EXCEL DE PRECISIÓN (Gama Alta)
     * Basado en la estructura de tu Hoja de Vida real
     */
    public static function analizarExcel($fileTmp) {
        if ($xlsx = SimpleXLSX::parse($fileTmp)) {
            $rows = $xlsx->rows(); // Convierte el Excel en un array 0-indexed

            // Mapeo por coordenadas (Fila - 1 porque el array empieza en 0)
            // Celda B11 -> rows[10][1]
            $tipoRaw   = $rows[10][1] ?? '';
            $marcaRaw  = $rows[11][1] ?? '';
            $refRaw    = $rows[12][1] ?? '';
            $serialRaw = $rows[14][1] ?? '';
            $ipRaw     = $rows[17][1] ?? '';
            $macRaw    = $rows[18][1] ?? '';
            $respRaw   = $rows[23][1] ?? ''; // "JOSE LUIS GARAVITO - S12810"

            // Extraer solo la cédula del final del responsable
            $parts = explode(' - ', $respRaw);
            $cedula = (count($parts) > 1) ? trim(end($parts)) : '';

            $data = [
                'success' => true,
                'principal' => [
                    'tipo'       => $tipoRaw,
                    'marca'      => $marcaRaw,
                    'referencia' => $refRaw,
                    'serial'     => $serialRaw,
                    'ip'         => $ipRaw,
                    'mac'        => $macRaw,
                    'responsable'=> $cedula
                ],
                'accesorios' => []
            ];

            // Detección de Accesorios (Panel Derecho: Columnas D y E)
            // Lector (E14)
            if (!empty($rows[13][4])) {
                $data['accesorios'][] = [
                    'tipo'   => 'LECTOR',
                    'serial' => $rows[13][4],
                    'ref'    => $rows[12][4] ?? ''
                ];
            }
            // Base Lector (E19)
            if (!empty($rows[18][4]) && $rows[18][4] !== 'N.A') {
                $data['accesorios'][] = [
                    'tipo'   => 'BASE LECTOR',
                    'serial' => $rows[18][4],
                    'ref'    => $rows[17][4] ?? ''
                ];
            }

            return $data;
        } else {
            return ['success' => false, 'msg' => SimpleXLSX::parseError()];
        }
    }

    // Guardado Masivo Transaccional
    public static function store($postData) {
        try {
            $db = Database::conectar();
            $db->beginTransaction();

            $cod = $postData['cod_responsable'];
            
            // 1. Si el empleado es nuevo, crearlo
            if (!empty($postData['nom_nuevo_empleado'])) {
                $stmtE = $db->prepare("SELECT * FROM fun_create_empleado(:c, :n, :a)");
                $stmtE->execute([':c' => $cod, ':n' => $postData['nom_nuevo_empleado'], ':a' => $postData['id_area_nuevo']]);
            }

            // 2. Crear Activo Principal
            $stmtA = $db->prepare("SELECT * FROM fun_create_activo(:ser, :qr, :host, :ref, :mac, :ip, :tipo, :marca, :mod, :est, :resp, :padre)");
            $qr_p = "QR-" . strtoupper(bin2hex(random_bytes(3)));
            
            $stmtA->execute([
                ':ser'   => $postData['serial'],
                ':qr'    => $qr_p,
                ':host'  => $postData['hostname'] ?? null,
                ':ref'   => $postData['referencia'] ?? null,
                ':mac'   => $postData['mac_activo'] ?? null,
                ':ip'    => $postData['ip_equipo'] ?? null,
                ':tipo'  => $postData['id_tipoequi'],
                ':marca' => $postData['id_marca'],
                ':mod'   => !empty($postData['id_modelo']) ? $postData['id_modelo'] : null,
                ':est'   => $postData['estado'],
                ':resp'  => $cod,
                ':padre' => !empty($postData['id_padre_activo']) ? $postData['id_padre_activo'] : null
            ]);

            $resP = $stmtA->fetch();
            $id_padre = $resP['id_res'];

            // 3. Procesar Accesorios del JSON
            if (!empty($postData['accesorios_json_final']) && $id_padre > 0) {
                $accs = json_decode($postData['accesorios_json_final'], true);
                foreach ($accs as $a) {
                    $stmtAcc = $db->prepare("SELECT * FROM fun_create_activo(:ser, :qr, NULL, :ref, NULL, NULL, :tipo, :marca, NULL, 'Bueno', :resp, :padre)");
                    $stmtAcc->execute([
                        ':ser'   => !empty($a['serial']) ? $a['serial'] : 'S/N-GEN-' . bin2hex(random_bytes(2)),
                        ':qr'    => "QR-" . strtoupper(bin2hex(random_bytes(3))),
                        ':ref'   => $a['referencia'],
                        ':tipo'  => $a['tipo_id'],
                        ':marca' => $postData['id_marca'],
                        ':resp'  => $cod,
                        ':padre' => $id_padre
                    ]);
                }
            }

            $db->commit();
            header("Location: ../public/activos.php?msg=Registro exitoso&tipo=success");
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            header("Location: ../public/crear_activo.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
        }
        exit();
    }
}

// Router
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['action']) && $_GET['action'] == 'analizar') {
        header('Content-Type: application/json');
        echo json_encode(ActivoController::analizarExcel($_FILES['file']['tmp_name']));
        exit();
    } else {
        ActivoController::store($_POST);
    }
}