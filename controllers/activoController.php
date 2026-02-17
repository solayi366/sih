<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../vendor/SimpleXLSX.php';

class ActivoController {
    
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
     * ANALIZADOR DE EXCEL POR COORDENADAS (Gama Alta)
     * Basado exactamente en el archivo: HOJA DE VIDA 06TAB13 PT JOSE LUIS.xlsx
     */
    public static function analizarExcel($fileTmp) {
        if ($xlsx = SimpleXLSX::parse($fileTmp)) {
            $rows = $xlsx->rows();

            // Mapeo exacto basado en el formato visual (rows[fila-1][columna_index])
            // Columna A=0, B=1, C=2, D=3, E=4...
            $tipoRaw       = $rows[10][1] ?? ''; // Fila 11, Col B
            $marcaRaw      = $rows[11][1] ?? ''; // Fila 12, Col B
            $refRaw        = $rows[13][1] ?? ''; // Fila 14, Col B
            $serialRaw     = $rows[14][1] ?? ''; // Fila 15, Col B
            $ipRaw         = $rows[17][1] ?? ''; // Fila 18, Col B
            $macRaw        = $rows[18][1] ?? ''; // Fila 19, Col B
            $responsableRaw = $rows[23][1] ?? ''; // Fila 24, Col B (JOSE LUIS GARAVITO - S12810)

            // Extraer solo el código del responsable (ej. S12810)
            $parts = explode(' - ', $responsableRaw);
            $cedula = (count($parts) > 1) ? trim(end($parts)) : '';

            $data = [
                'success' => true,
                'principal' => [
                    'tipo'       => trim($tipoRaw),
                    'marca'      => trim($marcaRaw),
                    'referencia' => trim($refRaw),
                    'serial'     => trim($serialRaw),
                    'ip'         => trim($ipRaw),
                    'mac'        => trim($macRaw),
                    'responsable'=> $cedula
                ],
                'accesorios' => []
            ];

            // --- SECCIÓN ACCESORIOS (Panel Derecho) ---
            // LECTOR: Marca en E12, Ref en E13, Serie en E14
            if (!empty($rows[13][4]) && $rows[13][4] !== 'N.A') {
                $data['accesorios'][] = [
                    'tipo'   => 'LECTOR',
                    'serial' => trim($rows[13][4]), // E14
                    'ref'    => trim($rows[12][4])  // E13
                ];
            }

            // BASE LECTOR: Marca en E18, Ref en E19, Serie en E20
            if (!empty($rows[19][4]) && $rows[19][4] !== 'N.A') {
                $data['accesorios'][] = [
                    'tipo'   => 'BASE LECTOR',
                    'serial' => trim($rows[19][4]), // E20
                    'ref'    => trim($rows[18][4])  // E19
                ];
            }

            return $data;
        } else {
            return ['success' => false, 'msg' => SimpleXLSX::parseError()];
        }
    }

    public static function store($postData) {
        try {
            $db = Database::conectar();
            $db->beginTransaction();

            $cod = $postData['cod_responsable'];
            
            // 1. Si el empleado es nuevo, se crea en la tabla tab_empleados
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

            // 3. Crear Accesorios desde el JSON
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

// ROUTER (IMPORTANTE: Esto debe estar FUERA de la clase y BIEN ESTRUCTURADO)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['action']) && $_GET['action'] == 'analizar') {
        header('Content-Type: application/json');
        echo json_encode(ActivoController::analizarExcel($_FILES['file']['tmp_name']));
        exit();
    } else {
        ActivoController::store($_POST);
    }
}