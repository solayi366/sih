<?php
require_once __DIR__ . '/../core/database.php';

class ActivoVerController {

    public static function ver() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header("Location: activos.php?msg=ID+de+activo+inv%C3%A1lido&tipo=danger");
            exit();
        }

        try {
            $db = Database::conectar();

            // ── Datos principales del activo ─────────────────────────────────
            $stmt = $db->prepare("SELECT * FROM fun_read_activo_por_id(:id)");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $activo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$activo) {
                header("Location: activos.php?msg=Activo+no+encontrado&tipo=danger");
                exit();
            }

            // ── Periféricos hijos (solo si es activo principal) ───────────────
            $hijos = [];
            if ($activo['r_id_padre'] === null) {
                $stmtHijos = $db->prepare(
                    "SELECT * FROM fun_read_perifericos_por_padre(:id_padre)"
                );
                $stmtHijos->bindParam(':id_padre', $id, PDO::PARAM_INT);
                $stmtHijos->execute();
                $hijos = $stmtHijos->fetchAll(PDO::FETCH_ASSOC);
            }

            return [
                'activo' => $activo,
                'hijos'  => $hijos,
            ];

        } catch (Exception $e) {
            error_log("Error en ActivoVerController: " . $e->getMessage());
            header("Location: activos.php?msg=" . urlencode("Error al cargar el activo") . "&tipo=danger");
            exit();
        }
    }
}