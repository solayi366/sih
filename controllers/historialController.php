<?php
/**
 * historialController.php
 * Carga el historial de eventos de un activo con paginación.
 */
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/HistorialHelper.php';

class HistorialController
{
    public static function ver(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php'); exit();
        }

        $id    = max(0, (int)($_GET['id']   ?? 0));
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;

        if ($id <= 0) {
            header('Location: activos.php?msg=ID+inválido&tipo=danger'); exit();
        }

        try {
            $db = Database::conectar();

            // Datos básicos del activo
            $stmtA = $db->prepare("SELECT * FROM fun_read_activo_por_id(:id)");
            $stmtA->execute([':id' => $id]);
            $activo = $stmtA->fetch(PDO::FETCH_ASSOC);

            if (!$activo) {
                header('Location: activos.php?msg=Activo+no+encontrado&tipo=danger'); exit();
            }

            // Historial paginado
            $stmt = $db->prepare("SELECT * FROM fun_read_historial_activo(:id, :pag, :lim)");
            $stmt->execute([':id' => $id, ':pag' => $page, ':lim' => $limit]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total       = !empty($rows) ? (int)$rows[0]['total_registros'] : 0;
            $total_pages = max(1, (int)ceil($total / $limit));

            // Decodificar el snapshot JSON de cada fila
            $eventos = array_map(function ($r) {
                $snap = null;
                if (!empty($r['r_snapshot'])) {
                    $snap = json_decode($r['r_snapshot'], true);
                }
                return [
                    'id'          => $r['r_id_evento'],
                    'fecha'       => $r['r_fecha'],
                    'tipo'        => $r['r_tipo'],
                    'descripcion' => $r['r_descripcion'],
                    'usuario'     => $r['r_usuario'],
                    'snapshot'    => $snap,
                ];
            }, $rows);

            return [
                'activo'      => $activo,
                'eventos'     => $eventos,
                'page'        => $page,
                'total_pages' => $total_pages,
                'total'       => $total,
            ];

        } catch (Exception $e) {
            error_log('HistorialController::ver — ' . $e->getMessage());
            return [
                'activo'      => null,
                'eventos'     => [],
                'page'        => 1,
                'total_pages' => 1,
                'total'       => 0,
                'error'       => $e->getMessage(),
            ];
        }
    }
}
