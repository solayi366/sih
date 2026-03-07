<?php
require_once __DIR__ . '/../core/database.php';

class ActivosController {

    public static function listar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        $page        = isset($_GET['page'])   ? max(1, (int)$_GET['page']) : 1;
        $limit       = 10;
        $filtro      = isset($_GET['filtro']) && $_GET['filtro'] !== '' ? trim($_GET['filtro']) : null;
        $filtro_peri = isset($_GET['peri'])   && $_GET['peri']   !== '' ? trim($_GET['peri'])   : null;
        $buscar      = isset($_GET['buscar']) && $_GET['buscar'] !== '' ? trim($_GET['buscar']) : null;

        try {
            $db = Database::conectar();

            // ── Una sola query: activos + periféricos como JSON ───────────────
            // Reemplaza fun_read_activos_filtrado() + N × fun_read_perifericos_por_padre()
            $stmt = $db->prepare(
                "SELECT * FROM fun_read_activos_con_perifericos(:pag, :lim, :buscar, :tipo, :peri)"
            );
            $stmt->bindParam(':pag',    $page,        PDO::PARAM_INT);
            $stmt->bindParam(':lim',    $limit,       PDO::PARAM_INT);
            $stmt->bindParam(':buscar', $buscar);
            $stmt->bindParam(':tipo',   $filtro);
            $stmt->bindParam(':peri',   $filtro_peri);
            $stmt->execute();

            $activos_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Deserializar periféricos: la BD devuelve JSON string, PHP necesita array
            $activos = array_map(function (array $row): array {
                $row['perifericos'] = json_decode($row['perifericos'] ?? '[]', true) ?: [];
                return $row;
            }, $activos_raw);

            $total_registros = !empty($activos) ? (int)$activos[0]['total_registros'] : 0;
            $total_pages     = max(1, (int)ceil($total_registros / $limit));

            return [
                'activos'         => $activos,
                'page'            => $page,
                'total_pages'     => $total_pages,
                'total_registros' => $total_registros,
                'filtro'          => $filtro      ?? 'todos',
                'filtro_peri'     => $filtro_peri ?? 'todos-peri',
                'buscar'          => $buscar      ?? '',
            ];

        } catch (Exception $e) {
            error_log("Error en ActivosController::listar: " . $e->getMessage());
            return [
                'activos'         => [],
                'page'            => 1,
                'total_pages'     => 1,
                'total_registros' => 0,
                'filtro'          => $filtro      ?? 'todos',
                'filtro_peri'     => $filtro_peri ?? 'todos-peri',
                'buscar'          => $buscar      ?? '',
                'error'           => $e->getMessage(),
            ];
        }
    }
}
