<?php
/**
 * SIH — Módulo Celulares
 * ARCHIVO: controllers/celularesController.php
 */

require_once __DIR__ . '/../core/database.php';

class CelularesController {

    private static function requireSession(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../public/login.php');
            exit();
        }
    }

    // ── LISTADO ───────────────────────────────────────────────────────────────
    public static function listar(): array {
        self::requireSession();
        $page   = isset($_GET['page'])   ? max(1, (int)$_GET['page']) : 1;
        $limit  = 15;
        $buscar = (isset($_GET['buscar']) && $_GET['buscar'] !== '') ? trim($_GET['buscar']) : null;
        $estado = (isset($_GET['estado']) && $_GET['estado'] !== '') ? trim($_GET['estado']) : null;

        try {
            $db   = Database::conectar();
            $stmt = $db->prepare("SELECT * FROM fun_read_celulares(:pag, :lim, :buscar, :estado)");
            $stmt->bindParam(':pag',    $page,   PDO::PARAM_INT);
            $stmt->bindParam(':lim',    $limit,  PDO::PARAM_INT);
            $stmt->bindParam(':buscar', $buscar);
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total_registros = !empty($rows) ? (int)$rows[0]['total_registros'] : 0;
            $total_pages     = max(1, (int)ceil($total_registros / $limit));

            return [
                'celulares'       => $rows,
                'page'            => $page,
                'total_pages'     => $total_pages,
                'total_registros' => $total_registros,
                'buscar'          => $buscar ?? '',
                'estado'          => $estado ?? '',
                'es_admin'        => !empty($_SESSION['es_admin']),
            ];
        } catch (Exception $e) {
            error_log("CelularesController::listar — " . $e->getMessage());
            return [
                'celulares' => [], 'page' => 1, 'total_pages' => 1,
                'total_registros' => 0, 'buscar' => '', 'estado' => '',
                'es_admin' => false, 'error' => $e->getMessage(),
            ];
        }
    }

    // ── DETALLE POR ID ────────────────────────────────────────────────────────
    public static function ver(int $id): array {
        self::requireSession();
        try {
            $db   = Database::conectar();
            $stmt = $db->prepare("SELECT * FROM fun_read_celular_por_id(:id)");
            $stmt->execute([':id' => $id]);
            $celular = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$celular) {
                return ['error' => 'Celular no encontrado o dado de baja.'];
            }

            $stmt2 = $db->prepare("SELECT * FROM fun_read_historial_celular(:id)");
            $stmt2->execute([':id' => $id]);
            $historial = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $credenciales = null;
            if (!empty($_SESSION['es_admin'])) {
                try {
                    $stmt3 = $db->prepare("SELECT * FROM fun_get_credenciales_celular(:id)");
                    $stmt3->execute([':id' => $id]);
                    $credenciales = $stmt3->fetch(PDO::FETCH_ASSOC) ?: null;
                } catch (Exception $e) {
                    error_log("CelularesController::ver credenciales — " . $e->getMessage());
                }
            }

            return [
                'celular'      => $celular,
                'historial'    => $historial,
                'credenciales' => $credenciales,
                'es_admin'     => !empty($_SESSION['es_admin']),
            ];
        } catch (Exception $e) {
            error_log("CelularesController::ver — " . $e->getMessage());
            return ['error' => 'Error al cargar el celular: ' . $e->getMessage()];
        }
    }

    // ── DATOS PARA FORMULARIOS ────────────────────────────────────────────────
    public static function getDatosFormulario(): array {
        self::requireSession();
        try {
            $marcas = Database::conectar()
                ->query("SELECT * FROM fun_read_marcas_cel()")
                ->fetchAll(PDO::FETCH_ASSOC);
            return ['marcas' => $marcas];
        } catch (Exception $e) {
            return ['marcas' => []];
        }
    }

    // ── DASHBOARD ─────────────────────────────────────────────────────────────
    public static function getDashboard(): array {
        self::requireSession();
        try {
            return Database::conectar()
                ->query("SELECT * FROM fun_get_dashboard_celulares()")
                ->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    // ── ROUTER DE ACCIONES ────────────────────────────────────────────────────
    // NOTA: Este controller vive en controllers/ y las vistas en public/
    // Las redirecciones usan ../public/ para resolver correctamente.
    public static function handleAction(): void {
        self::requireSession();
        $accion = $_GET['action'] ?? '';

        try {
            $db = Database::conectar();

            switch ($accion) {

                case 'create':
                    $stmt = $db->prepare("SELECT * FROM fun_create_celular(
                        :linea,:imei,:id_marca,:id_modelo,
                        :cod_nom,:cargo,:estado,:obs,:pin,:puk
                    )");
                    $stmt->execute([
                        ':linea'    => trim($_POST['linea']               ?? ''),
                        ':imei'     => trim($_POST['imei']                ?? ''),
                        ':id_marca' => (int)($_POST['id_marca_cel']       ?? 0),
                        ':id_modelo'=> (int)($_POST['id_modelo_cel']      ?? 0),
                        ':cod_nom'  => trim($_POST['cod_nom_responsable']  ?? ''),
                        ':cargo'    => trim($_POST['cargo_responsable']   ?? ''),
                        ':estado'   => $_POST['estado']                   ?? 'ASIGNADO',
                        ':obs'      => $_POST['observaciones']            ?: null,
                        ':pin'      => $_POST['pin']                      ?: null,
                        ':puk'      => $_POST['puk']                      ?: null,
                    ]);
                    $res = $stmt->fetch(PDO::FETCH_ASSOC);
                    $ok  = isset($res['id_res']) && (int)$res['id_res'] > 0;
                    if ($ok) {
                        header('Location: ../public/celulares.php?msg=' . urlencode($res['msj']) . '&tipo=success');
                    } else {
                        header('Location: ../public/celular_crear.php?msg=' . urlencode($res['msj'] ?? 'Error al crear.') . '&tipo=danger');
                    }
                    exit();

                case 'update':
                    $id   = (int)($_POST['id_celular'] ?? 0);
                    $pin  = ($_POST['pin'] ?? '') !== '' ? $_POST['pin'] : null;
                    $puk  = ($_POST['puk'] ?? '') !== '' ? $_POST['puk'] : null;
                    $stmt = $db->prepare("SELECT * FROM fun_update_celular(
                        :id,:linea,:imei,:id_marca,:id_modelo,
                        :cod_nom,:cargo,:estado,:obs,:pin,:puk
                    )");
                    $stmt->execute([
                        ':id'       => $id,
                        ':linea'    => trim($_POST['linea']               ?? ''),
                        ':imei'     => trim($_POST['imei']                ?? ''),
                        ':id_marca' => (int)($_POST['id_marca_cel']       ?? 0),
                        ':id_modelo'=> (int)($_POST['id_modelo_cel']      ?? 0),
                        ':cod_nom'  => trim($_POST['cod_nom_responsable']  ?? ''),
                        ':cargo'    => trim($_POST['cargo_responsable']   ?? ''),
                        ':estado'   => $_POST['estado']                   ?? 'ASIGNADO',
                        ':obs'      => $_POST['observaciones']            ?: null,
                        ':pin'      => $pin,
                        ':puk'      => $puk,
                    ]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $ok  = isset($row['filas_afectadas']) && (int)$row['filas_afectadas'] > 0;
                    if ($ok) {
                        header('Location: ../public/celular_ver.php?id=' . $id . '&msg=' . urlencode($row['msj']) . '&tipo=success');
                    } else {
                        header('Location: ../public/celular_editar.php?id=' . $id . '&msg=' . urlencode($row['msj'] ?? 'Error.') . '&tipo=danger');
                    }
                    exit();

                case 'delete':
                    $id   = (int)($_GET['id'] ?? 0);
                    $stmt = $db->prepare("SELECT * FROM fun_delete_celular(:id)");
                    $stmt->execute([':id' => $id]);
                    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
                    $ok   = isset($row['filas_afectadas']) && (int)$row['filas_afectadas'] > 0;
                    header('Location: ../public/celulares.php?msg=' . urlencode($row['msj'] ?? 'Operación fallida.') . '&tipo=' . ($ok ? 'success' : 'danger'));
                    exit();

                default:
                    header('Location: ../public/celulares.php');
                    exit();
            }

        } catch (Exception $e) {
            error_log("CelularesController::handleAction[$accion] — " . $e->getMessage());
            $back = match($accion) {
                'create' => '../public/celular_crear.php',
                'update' => '../public/celular_editar.php?id=' . (int)($_POST['id_celular'] ?? 0),
                default  => '../public/celulares.php',
            };
            header('Location: ' . $back . '?msg=' . urlencode('ERROR: ' . $e->getMessage()) . '&tipo=danger');
            exit();
        }
    }

    // ── IMPORTACIÓN MASIVA ────────────────────────────────────────────────────
    public static function importar(array $registros): array {
        self::requireSession();
        try {
            $db   = Database::conectar();
            $json = json_encode($registros, JSON_UNESCAPED_UNICODE);
            $stmt = $db->prepare("SELECT * FROM fun_importar_celulares(:json::JSONB)");
            $stmt->execute([':json' => $json]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("CelularesController::importar — " . $e->getMessage());
            return [['fila' => 0, 'linea' => '', 'resultado' => 'ERROR', 'detalle' => $e->getMessage()]];
        }
    }
}

// ── BLOQUE DE EJECUCIÓN DIRECTO ───────────────────────────────────────────────
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../core/Csrf.php';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'delete') {
        CelularesController::handleAction();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        Csrf::verify('../public/celulares.php');
        CelularesController::handleAction();
    }
}
