<?php
require_once __DIR__ . '/../core/database.php';

class ActivoVerController {

    public static function ver() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // ── Autenticación ─────────────────────────────────────────────────────
        // El acceso por QR escaneado (?qr=) es público — no requiere login.
        // El acceso interno por ID (?id=) sí requiere sesión activa.
        $esAccesoQR = !empty($_GET['qr']);
        if (!$esAccesoQR && !isset($_SESSION['user_id'])) {
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
            header("Location: login.php" . ($redirect ? "?redirect={$redirect}" : ''));
            exit();
        }

        // ── Resolver ID ───────────────────────────────────────────────────────
        // Acepta ?id=N  (navegación interna)
        // o      ?qr=QR-XXXXX  (escaneo físico del código QR)
        $id = 0;

        if (!empty($_GET['id'])) {
            $id = (int)$_GET['id'];

        } elseif (!empty($_GET['qr'])) {
            try {
                $db   = Database::conectar();
                $stmt = $db->prepare(
                    "SELECT id_activo FROM tab_activotec
                     WHERE codigo_qr = :qr AND activo = TRUE
                     LIMIT 1"
                );
                $stmt->execute([':qr' => trim($_GET['qr'])]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $id = (int)$row['id_activo'];
                } else {
                    header("Location: activos.php?msg=" . urlencode("Código QR no encontrado") . "&tipo=danger");
                    exit();
                }
            } catch (Exception $e) {
                header("Location: activos.php?msg=" . urlencode("Error al buscar por QR") . "&tipo=danger");
                exit();
            }
        }

        if ($id <= 0) {
            header("Location: activos.php?msg=ID+inv%C3%A1lido&tipo=danger");
            exit();
        }

        try {
            $db = Database::conectar();

            $stmt = $db->prepare("SELECT * FROM fun_read_activo_por_id(:id)");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $activo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$activo) {
                header("Location: activos.php?msg=Activo+no+encontrado&tipo=danger");
                exit();
            }

            $hijos = [];
            if ($activo['r_id_padre'] === null) {
                $stmtH = $db->prepare("SELECT * FROM fun_read_perifericos_por_padre(:id_padre)");
                $stmtH->bindParam(':id_padre', $id, PDO::PARAM_INT);
                $stmtH->execute();
                $hijos = $stmtH->fetchAll(PDO::FETCH_ASSOC);
            }

            return ['activo' => $activo, 'hijos' => $hijos];

        } catch (Exception $e) {
            error_log("ActivoVerController: " . $e->getMessage());
            header("Location: activos.php?msg=" . urlencode("Error al cargar el activo") . "&tipo=danger");
            exit();
        }
    }
}