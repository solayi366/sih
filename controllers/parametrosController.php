<?php
require_once __DIR__ . '/../core/database.php';

class ParametrosController {
    
    /**
     * CONSULTAS (GET): Mantenemos integridad total para todas las vistas
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
     * MÉTODO RESTAURADO: Necesario para parametros_tipos.php
     */
    public static function getTipos() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::conectar();
        return $db->query("SELECT * FROM fun_read_tipos()")->fetchAll();
    }

    /**
     * PROCESAMIENTO (STORE / UPDATE / DELETE)
     * Router unificado para todas las acciones de parámetros.
     */
    public static function handleAction() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Verificación de seguridad básica
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../public/login.php");
            exit();
        }

        $entidad = $_GET['ent'] ?? '';
        $accion = $_GET['action'] ?? 'create';
        
        // Mapeo dinámico de retorno para mantener el contexto (tipos, marcas, etc)
        $return_page = "parametros_{$entidad}s.php";

        try {
            $db = Database::conectar();
            $res = null;

            switch ($entidad) {
                case 'tipo':
                    if ($accion === 'create') {
                        $stmt = $db->prepare("SELECT * FROM fun_create_tipo(:nom)");
                        $stmt->execute([':nom' => $_POST['nom_tipo']]);
                        $res = $stmt->fetch();
                    } elseif ($accion === 'update') {
                        $stmt = $db->prepare("SELECT * FROM fun_update_tipo(:id, :nom)");
                        $stmt->execute([':id' => $_POST['id_tipo'], ':nom' => $_POST['nom_tipo']]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => $row['filas_afectadas'], 'msj' => $row['msj']];
                    } elseif ($accion === 'delete') {
                        $stmt = $db->prepare("SELECT * FROM fun_delete_tipo(:id)");
                        $stmt->execute([':id' => $_GET['id']]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => $row['filas_afectadas'], 'msj' => $row['msj']];
                    }
                    break;

                case 'marca':
                    if ($accion === 'create') {
                        $stmt = $db->prepare("SELECT * FROM fun_create_marca(:nom, :tip)");
                        $stmt->execute([
                            ':nom' => $_POST['nom_marca'],
                            ':tip' => $_POST['id_tipoequi']
                        ]);
                        $res = $stmt->fetch();
                    } elseif ($accion === 'update') {
                        $stmt = $db->prepare("SELECT * FROM fun_update_marca(:id, :nom)");
                        $stmt->execute([
                            ':id' => $_POST['id_marca'],
                            ':nom' => $_POST['nom_marca']
                        ]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => $row['filas_afectadas'], 'msj' => $row['msj']];
                    } elseif ($accion === 'delete') {
                        $stmt = $db->prepare("SELECT * FROM fun_delete_marca(:id)");
                        $stmt->execute([':id' => $_GET['id']]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => $row['filas_afectadas'], 'msj' => $row['msj']];
                    }
                    break;

                case 'modelo': // BLOQUE AÑADIDO: Maneja la entidad modelos
                    if ($accion === 'create') {
                        $stmt = $db->prepare("SELECT * FROM fun_create_modelo(:nom, :mar, :tip)");
                        $stmt->execute([
                            ':nom' => $_POST['nom_modelo'],
                            ':mar' => $_POST['id_marca'],
                            ':tip' => $_POST['id_tipoequi']
                        ]);
                        $res = $stmt->fetch();
                    } elseif ($accion === 'update') {
                        $stmt = $db->prepare("SELECT * FROM fun_update_modelo(:id, :nom, :mar, :tip)");
                        $stmt->execute([
                            ':id'  => $_POST['id_modelo'],
                            ':nom' => $_POST['nom_modelo'],
                            ':mar' => $_POST['id_marca'],
                            ':tip' => $_POST['id_tipoequi']
                        ]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => $row['filas_afectadas'], 'msj' => $row['msj']];
                    } elseif ($accion === 'delete') {
                        $stmt = $db->prepare("SELECT * FROM fun_delete_modelo(:id)");
                        $stmt->execute([':id' => $_GET['id']]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => $row['filas_afectadas'], 'msj' => $row['msj']];
                    }
                    break;

                case 'area':
                    if ($accion === 'create') {
                        $stmt = $db->prepare("SELECT * FROM fun_create_area(:nom)");
                        $stmt->execute([':nom' => $_POST['nom_area']]);
                        $res = $stmt->fetch();
                    } elseif ($accion === 'update') {
                        $stmt = $db->prepare("SELECT * FROM fun_update_area(:id, :nom)");
                        $stmt->execute([':id' => $_POST['id_area'], ':nom' => $_POST['nom_area']]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => $row['filas_afectadas'], 'msj' => $row['msj']];
                    } elseif ($accion === 'delete') {
                        $stmt = $db->prepare("SELECT * FROM fun_delete_area(:id)");
                        $stmt->execute([':id' => $_GET['id']]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => $row['filas_afectadas'], 'msj' => $row['msj']];
                    }
                    break;

                case 'empleado':
                    if ($accion === 'create') {
                        $stmt = $db->prepare("SELECT * FROM fun_create_empleado(:cod, :nom, :area)");
                        $stmt->execute([
                            ':cod' => $_POST['cod_nom'], 
                            ':nom' => $_POST['nom_emple'], 
                            ':area' => $_POST['id_area']
                        ]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => ($row['cod_res'] !== '0' ? 1 : 0), 'msj' => $row['msj']];
                    } elseif ($accion === 'update') {
                        $stmt = $db->prepare("SELECT * FROM fun_update_empleado(:cod, :nom, :area)");
                        $stmt->execute([
                            ':cod' => $_POST['cod_nom'], 
                            ':nom' => $_POST['nom_emple'], 
                            ':area' => $_POST['id_area']
                        ]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => $row['filas_afectadas'], 'msj' => $row['msj']];
                    } elseif ($accion === 'delete') {
                        $stmt = $db->prepare("SELECT * FROM fun_delete_empleado(:id)");
                        $stmt->execute([':id' => $_GET['id']]);
                        $row = $stmt->fetch();
                        $res = ['id_res' => $row['filas_afectadas'], 'msj' => $row['msj']];
                    }
                    break;
            }

            if ($res && isset($res['id_res']) && $res['id_res'] > 0) {
                header("Location: ../public/$return_page?msg=" . urlencode($res['msj']) . "&tipo=success");
            } else {
                header("Location: ../public/$return_page?msg=" . urlencode($res['msj'] ?? 'Operación fallida') . "&tipo=danger");
            }

        } catch (Exception $e) {
            header("Location: ../public/$return_page?msg=" . urlencode("ERROR: " . $e->getMessage()) . "&tipo=danger");
        }
        exit();
    }
}

/**
 * Bloque de ejecución: Procesa peticiones POST y acciones DELETE vía GET
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && $_GET['action'] === 'delete')) {
    ParametrosController::handleAction();
}