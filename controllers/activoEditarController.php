<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/HistorialHelper.php';

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

            // ── Campos dinámicos actuales del activo ─────────────────────────
            $campos_dinamicos = [];
            try {
                $stmtD = $db->prepare("
                    SELECT c.id_campo, c.nombre, c.etiqueta, c.tipo_dato, c.icono, c.opciones, c.is_base,
                           v.valor
                    FROM tab_campos c
                    INNER JOIN tab_tipo_campos tc ON tc.id_campo = c.id_campo
                        AND tc.id_tipoequi = (SELECT id_tipoequi FROM tab_activotec WHERE id_activo = :id)
                        AND tc.activo = TRUE
                    LEFT JOIN tab_activo_campos_valores v ON v.id_campo = c.id_campo AND v.id_activo = :id2
                    WHERE c.activo = TRUE AND c.is_base = FALSE
                    ORDER BY COALESCE(tc.orden, c.orden), c.id_campo
                ");
                $stmtD->execute([':id' => $id, ':id2' => $id]);
                $campos_dinamicos = $stmtD->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) { /* tabla no existe aún */ }

            // ── Listas para los selectores ────────────────────────────────────
            return [
                'activo'  => $activo,
                'tipos'   => $db->query("SELECT id_tipoequi, nom_tipo FROM tab_tipos ORDER BY nom_tipo")->fetchAll(),
                'marcas'  => $db->query("SELECT id_marca, nom_marca FROM tab_marca ORDER BY nom_marca")->fetchAll(),
                'modelos' => $db->query("SELECT id_modelo, nom_modelo, id_tipoequi, id_marca FROM tab_modelo")->fetchAll(),
                'areas'   => $db->query("SELECT id_area, nom_area FROM tab_area ORDER BY nom_area")->fetchAll(),
                'campos_dinamicos' => $campos_dinamicos,
                'padres'  => $db->query(
                    "SELECT id_activo, serial, COALESCE(hostname, serial, 'Sin identificador') AS hostname, referencia FROM tab_activotec
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
            $password_nuevo = !empty($postData['password_activo']) ? $postData['password_activo'] : null;

            // ── Leer estado ANTES del update ──────────────────────────────────
            $antes         = HistorialHelper::leerActivo($db, $id);
            $camposDinAntes = HistorialHelper::leerCamposDin($db, $id);

            $stmtUpd = $db->prepare(
                "SELECT * FROM fun_update_activo(
                    :id, :ser, :qr, :host, :ref, :mac, :ip,
                    :tipo, :marca, :mod, :est, :resp, :padre, :pwd
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
                ':pwd'   => $password_nuevo,
            ]);

            $res = $stmtUpd->fetch();

            if ($res['filas_afectadas'] < 1) {
                $db->rollBack();
                header("Location: ../public/editar.php?id={$id}&msg=" . urlencode($res['msj']) . "&tipo=danger");
                exit();
            }

            // ── Guardar campos dinámicos ─────────────────────────────────────
            if (!empty($postData['campos_dinamicos_json'])) {
                $valoresDin = json_decode($postData['campos_dinamicos_json'], true);
                if (is_array($valoresDin) && count($valoresDin) > 0) {
                    $stmtDin = $db->prepare("SELECT * FROM fun_save_valores_activo(:id, :vals::jsonb)");
                    $stmtDin->execute([':id' => $id, ':vals' => json_encode($valoresDin)]);
                }
            }

            $db->commit();

            // ── Registrar historial proporcional al cambio ────────────────────
            $usuario = $_SESSION['username'] ?? (string)($_SESSION['user_id'] ?? 'sistema');
            HistorialHelper::registrar($db, $id, HistorialHelper::EDICION, $usuario, $antes ?? [], $camposDinAntes ?? []);

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
    require_once __DIR__ . '/../core/Csrf.php';
    Csrf::verify('../public/editar.php?id=' . ((int)($_POST['id_activo'] ?? 0)));
    ActivoEditarController::update($_POST);
}