<?php
require_once __DIR__ . '/../core/database.php';

class ActivosController {

    public static function listar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        // Parámetros desde la URL
        $page        = isset($_GET['page'])   ? max(1, (int)$_GET['page']) : 1;
        $limit       = 10;
        $filtro      = isset($_GET['filtro']) && $_GET['filtro'] !== ''   ? trim($_GET['filtro']) : null;
        $filtro_peri = isset($_GET['peri'])   && $_GET['peri']   !== ''   ? trim($_GET['peri'])   : null;
        $buscar      = isset($_GET['buscar']) && $_GET['buscar'] !== ''   ? trim($_GET['buscar']) : null;

        try {
            $db = Database::conectar();

            // ── Llamar a la función con filtros reales en PostgreSQL ──────────
            $stmt = $db->prepare(
                "SELECT * FROM fun_read_activos_filtrado(:pag, :lim, :buscar, :tipo, :peri)"
            );
            $stmt->bindParam(':pag',    $page,        PDO::PARAM_INT);
            $stmt->bindParam(':lim',    $limit,       PDO::PARAM_INT);
            $stmt->bindParam(':buscar', $buscar);   // VARCHAR o NULL
            $stmt->bindParam(':tipo',   $filtro);   // VARCHAR o NULL
            $stmt->bindParam(':peri',   $filtro_peri); // VARCHAR o NULL
            $stmt->execute();

            $activos_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ── Cargar periféricos de cada activo ─────────────────────────────
            $stmt_peri = $db->prepare(
                "SELECT * FROM fun_read_perifericos_por_padre(:id_padre)"
            );
            $activos = [];
            foreach ($activos_raw as $activo) {
                $stmt_peri->bindParam(':id_padre', $activo['r_id'], PDO::PARAM_INT);
                $stmt_peri->execute();
                $activo['perifericos'] = $stmt_peri->fetchAll(PDO::FETCH_ASSOC);
                $activos[] = $activo;
            }

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
            error_log("Error en ActivosController: " . $e->getMessage());
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