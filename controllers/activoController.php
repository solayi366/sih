<?php
require_once __DIR__ . '/../core/database.php';

class ActivoController {
    
    // Trae toda la información para los selectores del formulario
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

    // Procesa el POST del formulario
    public static function store($postData) {
        try {
            $db = Database::conectar();
            $db->beginTransaction();

            // 1. Verificar/Crear Empleado
            $cod = $postData['cod_responsable'];
            $nom = $postData['nom_nuevo_empleado'];
            $id_area = $postData['id_area_nuevo'];

            if (!empty($nom) && !empty($id_area)) {
                $stmtEmp = $db->prepare("SELECT * FROM fun_create_empleado(:c, :n, :a)");
                $stmtEmp->execute([':c' => $cod, ':n' => $nom, ':a' => $id_area]);
            }

            // 2. Crear Activo Principal (Usando el SP que definimos)
            $stmtAct = $db->prepare("SELECT * FROM fun_create_activo(
                :ser, :qr, :host, :ref, :mac, :ip, :tipo, :marca, :mod, :est, :resp, :padre
            )");
            
            // Generamos el QR (Puedes usar tu lógica de generación aquí)
            $qr = "QR-" . strtoupper(bin2hex(random_bytes(3)));

            $stmtAct->execute([
                ':ser'   => $postData['serial'],
                ':qr'    => $qr,
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

            $db->commit();
            return ["success" => true, "msg" => "Activo registrado correctamente con el QR: $qr"];
        } catch (Exception $e) {
            $db->rollBack();
            return ["success" => false, "msg" => "Error: " . $e->getMessage()];
        }
    }
}