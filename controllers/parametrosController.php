<?php
require_once __DIR__ . '/../core/database.php';

class ParametrosController {
    
    /**
     * LÓGICA DE CONSULTA (GET)
     */
    public static function getHardwareData() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::conectar();
        return [
            'tipos'   => $db->query("SELECT * FROM fun_read_tipos()")->fetchAll(),
            'marcas'  => $db->query("SELECT * FROM fun_read_marcas()")->fetchAll(),
            'modelos' => $db->query("SELECT * FROM fun_read_modelos()")->fetchAll()
        ];
    }

    public static function getRRHHData() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::conectar();
        return [
            'areas'     => $db->query("SELECT * FROM fun_read_areas()")->fetchAll(),
            'empleados' => $db->query("SELECT * FROM fun_read_empleados()")->fetchAll()
        ];
    }

    /**
     * LÓGICA DE PROCESAMIENTO (POST)
     * Centraliza la creación de Tipos, Marcas, Modelos, Áreas y Empleados.
     */
    public static function store() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // 1. Seguridad básica
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../public/login.php");
            exit();
        }

        // 2. Parámetros de contexto
        $view_source = $_GET['view'] ?? 'hw';
        $entidad = $_GET['ent'] ?? '';
        $return_page = ($view_source === 'rrhh') ? 'parametros_rrhh.php' : 'parametros_hardware.php';

        try {
            $db = Database::conectar();
            $res = null;

            switch ($entidad) {
                case 'tipo':
                    $stmt = $db->prepare("SELECT * FROM fun_create_tipo(:nom)");
                    $stmt->execute([':nom' => $_POST['nom_tipo']]);
                    $res = $stmt->fetch();
                    break;

                case 'marca':
                    $stmt = $db->prepare("SELECT * FROM fun_create_marca(:nom)");
                    $stmt->execute([':nom' => $_POST['nom_marca']]);
                    $res = $stmt->fetch();
                    break;

                case 'modelo':
                    $stmt = $db->prepare("SELECT * FROM fun_create_modelo(:nom, :mar, :tip)");
                    $stmt->execute([
                        ':nom' => $_POST['nom_modelo'],
                        ':mar' => $_POST['id_marca'],
                        ':tip' => $_POST['id_tipoequi']
                    ]);
                    $res = $stmt->fetch();
                    break;

                case 'area':
                    $stmt = $db->prepare("SELECT * FROM fun_create_area(:nom)");
                    $stmt->execute([':nom' => $_POST['nom_area']]);
                    $res = $stmt->fetch();
                    break;

                case 'empleado':
                    $stmt = $db->prepare("SELECT * FROM fun_create_empleado(:cod, :nom, :area)");
                    $stmt->execute([
                        ':cod'  => $_POST['cod_nom'],
                        ':nom'  => $_POST['nom_emple'],
                        ':area' => $_POST['id_area']
                    ]);
                    $res = $stmt->fetch();
                    // Normalización de respuesta específica de fun_create_empleado
                    $res['id_res'] = ($res['cod_res'] !== '0' && $res['cod_res'] !== '-1') ? 1 : 0;
                    break;
            }

            if ($res && isset($res['id_res']) && $res['id_res'] > 0) {
                header("Location: ../public/$return_page?msg=" . urlencode($res['msj']) . "&tipo=success");
            } else {
                header("Location: ../public/$return_page?msg=" . urlencode($res['msj'] ?? 'Error desconocido') . "&tipo=danger");
            }

        } catch (Exception $e) {
            header("Location: ../public/$return_page?msg=" . urlencode("ERROR: " . $e->getMessage()) . "&tipo=danger");
        }
        exit();
    }
}

// Bloque de ejecución: Si el archivo se llama directamente vía POST, procesa el store
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ParametrosController::store();
}