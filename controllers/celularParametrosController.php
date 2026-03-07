<?php
/**
 * SIH — Módulo Celulares
 * ARCHIVO: controllers/celularParametrosController.php
 *
 * Responsabilidades:
 *   - CRUD de marcas de celulares (tab_marcas_cel)
 *   - CRUD de modelos de celulares (tab_modelos_cel)
 *   - Endpoint AJAX: modelos por marca (para select cascada en formularios)
 *   - Endpoint AJAX: restore marca desactivada
 *
 * Patrón idéntico a parametrosController.php del proyecto base.
 */

require_once __DIR__ . '/../core/database.php';

class CelularParametrosController {

    private static function requireSession(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../public/login.php');
            exit();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSULTAS — lectura para vistas
    // ─────────────────────────────────────────────────────────────────────────

    /** Retorna todas las marcas activas con conteo de modelos. */
    public static function getMarcas(): array {
        self::requireSession();
        try {
            return Database::conectar()
                ->query("SELECT * FROM fun_read_marcas_cel()")
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("CelularParametrosController::getMarcas — " . $e->getMessage());
            return [];
        }
    }

    /** Retorna todos los modelos activos con su marca. */
    public static function getModelos(): array {
        self::requireSession();
        try {
            return Database::conectar()
                ->query("SELECT * FROM fun_read_modelos_cel()")
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("CelularParametrosController::getModelos — " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENDPOINT AJAX — modelos por marca (select cascada)
    // GET: controllers/celularParametrosController.php?action=modelos_por_marca&id_marca=X
    // Retorna JSON con [{r_id_modelo, r_nom_modelo}, ...]
    // ─────────────────────────────────────────────────────────────────────────
    public static function apiModelosPorMarca(int $id_marca): void {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db   = Database::conectar();
            $stmt = $db->prepare("SELECT * FROM fun_read_modelos_por_marca(:id)");
            $stmt->execute([':id' => $id_marca]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            echo json_encode([]);
        }
        exit();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENDPOINT AJAX — restaurar marca desactivada
    // POST: controllers/celularParametrosController.php?action=restore_marca
    // Body: id_marca_cel
    // Retorna JSON {success, msg}
    // ─────────────────────────────────────────────────────────────────────────
    public static function apiRestoreMarca(): void {
        header('Content-Type: application/json; charset=utf-8');
        self::requireSession();
        try {
            $id   = (int)($_POST['id_marca_cel'] ?? 0);
            $db   = Database::conectar();
            $stmt = $db->prepare("SELECT * FROM fun_restore_marca_cel(:id)");
            $stmt->execute([':id' => $id]);
            $row  = $stmt->fetch(PDO::FETCH_ASSOC);
            $ok   = isset($row['filas_afectadas']) && (int)$row['filas_afectadas'] > 0;
            echo json_encode(['success' => $ok, 'msg' => $row['msj'] ?? 'Error']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
        }
        exit();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ROUTER UNIFICADO para acciones de formulario (POST) y delete (GET)
    // Redirige con ?msg=...&tipo=success|danger igual que parametrosController
    // ─────────────────────────────────────────────────────────────────────────
    public static function handleAction(): void {
        self::requireSession();

        $entidad = $_GET['ent']    ?? '';
        $accion  = $_GET['action'] ?? 'create';

        // Página de retorno según entidad
        $return_page = match($entidad) {
            'marca_cel'  => 'parametros_cel_marcas.php',
            'modelo_cel' => 'parametros_cel_modelos.php',
            default      => 'celulares.php',
        };

        try {
            $db  = Database::conectar();
            $res = null;

            switch ($entidad) {

                // ── MARCAS ────────────────────────────────────────────────
                case 'marca_cel':
                    if ($accion === 'create') {
                        $stmt = $db->prepare("SELECT * FROM fun_create_marca_cel(:nom)");
                        $stmt->execute([':nom' => trim($_POST['nom_marca'] ?? '')]);
                        $res  = $stmt->fetch(PDO::FETCH_ASSOC);
                        // fun_create_marca_cel devuelve {id_res, msj}
                    } elseif ($accion === 'update') {
                        $stmt = $db->prepare("SELECT * FROM fun_update_marca_cel(:id, :nom)");
                        $stmt->execute([
                            ':id'  => (int)($_POST['id_marca_cel'] ?? 0),
                            ':nom' => trim($_POST['nom_marca'] ?? ''),
                        ]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        // fun_update devuelve {filas_afectadas, msj} → normalizar a {id_res, msj}
                        $res = ['id_res' => $row['filas_afectadas'] ?? 0, 'msj' => $row['msj'] ?? ''];
                    } elseif ($accion === 'delete') {
                        $stmt = $db->prepare("SELECT * FROM fun_delete_marca_cel(:id)");
                        $stmt->execute([':id' => (int)($_GET['id'] ?? 0)]);
                        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
                        $res  = ['id_res' => $row['filas_afectadas'] ?? 0, 'msj' => $row['msj'] ?? ''];
                    }
                    break;

                // ── MODELOS ───────────────────────────────────────────────
                case 'modelo_cel':
                    if ($accion === 'create') {
                        $stmt = $db->prepare("SELECT * FROM fun_create_modelo_cel(:id_marca, :nom)");
                        $stmt->execute([
                            ':id_marca' => (int)($_POST['id_marca_cel'] ?? 0),
                            ':nom'      => trim($_POST['nom_modelo'] ?? ''),
                        ]);
                        $res = $stmt->fetch(PDO::FETCH_ASSOC);
                    } elseif ($accion === 'update') {
                        $stmt = $db->prepare("SELECT * FROM fun_update_modelo_cel(:id, :id_marca, :nom)");
                        $stmt->execute([
                            ':id'       => (int)($_POST['id_modelo_cel'] ?? 0),
                            ':id_marca' => (int)($_POST['id_marca_cel']  ?? 0),
                            ':nom'      => trim($_POST['nom_modelo'] ?? ''),
                        ]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $res = ['id_res' => $row['filas_afectadas'] ?? 0, 'msj' => $row['msj'] ?? ''];
                    } elseif ($accion === 'delete') {
                        $stmt = $db->prepare("SELECT * FROM fun_delete_modelo_cel(:id)");
                        $stmt->execute([':id' => (int)($_GET['id'] ?? 0)]);
                        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
                        $res  = ['id_res' => $row['filas_afectadas'] ?? 0, 'msj' => $row['msj'] ?? ''];
                    }
                    break;
            }

            // ── Redirigir con mensaje ─────────────────────────────────────
            $ok = isset($res['id_res']) && (int)$res['id_res'] > 0;
            header('Location: ../public/' . $return_page . '?msg=' . urlencode($res['msj'] ?? 'Operación fallida.') . '&tipo=' . ($ok ? 'success' : 'danger'));

        } catch (Exception $e) {
            error_log("CelularParametrosController::handleAction[$entidad/$accion] — " . $e->getMessage());
            header('Location: ../public/' . $return_page . '?msg=' . urlencode('ERROR: ' . $e->getMessage()) . '&tipo=danger');
        }
        exit();
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// BLOQUE DE EJECUCIÓN
// ─────────────────────────────────────────────────────────────────────────────
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {

    if (session_status() === PHP_SESSION_NONE) session_start();

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // ── Endpoints AJAX GET ────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($action === 'modelos_por_marca') {
            CelularParametrosController::apiModelosPorMarca((int)($_GET['id_marca'] ?? 0));
        }
    }

    // ── Endpoints AJAX POST ───────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'restore_marca') {
            CelularParametrosController::apiRestoreMarca();
        }
    }

    // ── Acciones de formulario (POST normal o GET/delete) ─────────────────
    $accionesAjax = ['modelos_por_marca', 'restore_marca'];
    if (!in_array($action, $accionesAjax, true)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../core/Csrf.php';
            Csrf::verify('../public/parametros_cel_marcas.php');
        }
        CelularParametrosController::handleAction();
    }
}
