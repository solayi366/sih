<?php
require_once __DIR__ . '/../core/database.php';

class ActivoEditarController {

    public static function getFormData(int $id): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../public/login.php");
            exit();
        }

        if ($id <= 0) {
            header("Location: ../public/activos.php?msg=ID+de+activo+inv%C3%A1lido&tipo=danger");
            exit();
        }

        try {
            $db = Database::conectar();

            // ── Datos actuales del activo ─────────────────────────────────────
            $stmtActivo = $db->prepare("SELECT * FROM fun_read_activo_por_id(:id)");
            $stmtActivo->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtActivo->execute();
            $activo = $stmtActivo->fetch(PDO::FETCH_ASSOC);

            if (!$activo) {
                header("Location: ../public/activos.php?msg=Activo+no+encontrado&tipo=danger");
                exit();
            }

            // ── Listas para los selectores ────────────────────────────────────
            return [
                'activo'  => $activo,
                'tipos'   => $db->query("SELECT id_tipoequi, nom_tipo FROM tab_tipos ORDER BY nom_tipo")->fetchAll(),
                'marcas'  => $db->query("SELECT id_marca, nom_marca FROM tab_marca ORDER BY nom_marca")->fetchAll(),
                'modelos' => $db->query("SELECT id_modelo, nom_modelo, id_tipoequi FROM tab_modelo")->fetchAll(),
                'areas'   => $db->query("SELECT id_area, nom_area FROM tab_area ORDER BY nom_area")->fetchAll(),
                'padres'  => $db->query(
                    "SELECT id_activo, serial, referencia FROM tab_activotec
                     WHERE id_padre_activo IS NULL AND activo = TRUE
                     ORDER BY id_activo DESC"
                )->fetchAll(),
            ];

        } catch (Exception $e) {
            error_log("Error en ActivoEditarController::getFormData: " . $e->getMessage());
            header("Location: ../public/activos.php?msg=" . urlencode("Error al cargar el activo") . "&tipo=danger");
            exit();
        }
    }

    public static function update(array $postData): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../public/login.php");
            exit();
        }

        $id = isset($postData['id_activo']) ? (int)$postData['id_activo'] : 0;
        if ($id <= 0) {
            header("Location: ../public/activos.php?msg=ID+inv%C3%A1lido&tipo=danger");
            exit();
        }

        try {
            $db = Database::conectar();
            $db->beginTransaction();

            $cod     = $postData['cod_responsable']    ?? null;
            $nom     = $postData['nom_nuevo_empleado'] ?? '';
            $id_area = $postData['id_area_nuevo']      ?? '';

            // Si ingresaron datos de empleado nuevo, crearlo/actualizarlo
            if (!empty($nom) && !empty($id_area) && !empty($cod)) {
                $stmtEmp = $db->prepare("SELECT * FROM fun_create_empleado(:c, :n, :a)");
                $stmtEmp->execute([':c' => $cod, ':n' => $nom, ':a' => $id_area]);
            }

            $id_padre = !empty($postData['id_padre_activo']) ? (int)$postData['id_padre_activo'] : null;

            $stmtUpd = $db->prepare(
                "SELECT * FROM fun_update_activo(
                    :id, :ser, :qr, :host, :ref, :mac, :ip,
                    :tipo, :marca, :mod, :est, :resp, :padre
                )"
            );
            $stmtUpd->execute([
                ':id'    => $id,
                ':ser'   => $postData['serial']      ?? null,
                ':qr'    => $postData['codigo_qr']   ?? null,
                ':host'  => $postData['hostname']    ?? null,
                ':ref'   => $postData['referencia']  ?? null,
                ':mac'   => $postData['mac_activo']  ?? null,
                ':ip'    => $postData['ip_equipo']   ?? null,
                ':tipo'  => $postData['id_tipoequi'] ?? null,
                ':marca' => $postData['id_marca']    ?? null,
                ':mod'   => !empty($postData['id_modelo']) ? (int)$postData['id_modelo'] : null,
                ':est'   => $postData['estado']      ?? 'Bueno',
                ':resp'  => $cod,
                ':padre' => $id_padre,
            ]);

            $res = $stmtUpd->fetch();

            if ($res['filas_afectadas'] < 1) {
                $db->rollBack();
                header("Location: ../public/editar.php?id={$id}&msg=" . urlencode($res['msj']) . "&tipo=danger");
                exit();
            }

            $db->commit();
            header("Location: ../public/ver.php?id={$id}&msg=Activo+actualizado+correctamente&tipo=success");

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en ActivoEditarController::update: " . $e->getMessage());
            header("Location: ../public/editar.php?id={$id}&msg=" . urlencode("Error al guardar los cambios") . "&tipo=danger");
        }
        exit();
    }
}

// ── Router: POST → actualizar ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ActivoEditarController::update($_POST);
}