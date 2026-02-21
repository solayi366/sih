<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/MailService.php';

class NovedadesController
{
    // ── Lista interna (requiere login) ────────────────────────────────────────
    public static function listar(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../public/login.php'); exit();
        }

        $page  = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;

        try {
            $db   = Database::conectar();
            $stmt = $db->prepare('SELECT * FROM fun_read_novedades(:pag, :lim)');
            $stmt->bindParam(':pag', $page, PDO::PARAM_INT);
            $stmt->bindParam(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total   = !empty($rows) ? (int)$rows[0]['total_registros'] : 0;
            $t_pages = max(1, (int)ceil($total / $limit));

            return [
                'tickets'     => $rows,
                'page'        => $page,
                'total_pages' => $t_pages,
                'total'       => $total,
            ];
        } catch (Exception $e) {
            error_log('NovedadesController::listar — ' . $e->getMessage());
            return ['tickets' => [], 'page' => 1, 'total_pages' => 1, 'total' => 0, 'error' => $e->getMessage()];
        }
    }

    // ── Resolver ticket (requiere login) ─────────────────────────────────────
    public static function resolver(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403); exit('No autorizado');
        }

        $id      = (int)($_POST['id_novedad'] ?? 0);
        $solucion = trim($_POST['solucion'] ?? '');

        if ($id <= 0 || $solucion === '') {
            header('Location: ../public/novedades.php?msg=' . urlencode('Datos incompletos') . '&tipo=danger');
            exit();
        }

        try {
            $db   = Database::conectar();
            $stmt = $db->prepare('SELECT * FROM fun_update_novedad(:id, :estado, :desc)');
            $estado = 'RESUELTO';
            $stmt->bindParam(':id',     $id,       PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':desc',   $solucion);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($res && str_starts_with($res['msj'], 'SUCCESS')) {
                header('Location: ../public/novedades.php?msg=' . urlencode('Ticket #' . $id . ' cerrado correctamente') . '&tipo=success');
            } else {
                header('Location: ../public/novedades.php?msg=' . urlencode($res['msj'] ?? 'Error desconocido') . '&tipo=danger');
            }
        } catch (Exception $e) {
            header('Location: ../public/novedades.php?msg=' . urlencode('Error: ' . $e->getMessage()) . '&tipo=danger');
        }
        exit();
    }

    // ── API: buscar activos por código de nómina (pública) ───────────────────
    public static function apiMisActivos(): void
    {
        header('Content-Type: application/json');
        $cedula = trim($_GET['cedula'] ?? '');

        if (strlen($cedula) < 3) {
            echo json_encode(['encontrado' => false, 'mensaje' => 'Código inválido']);
            exit();
        }

        try {
            $db = Database::conectar();

            // Buscar empleado
            $stmt = $db->prepare(
                'SELECT nom_emple FROM tab_empleados WHERE cod_nom = :c AND activo = TRUE'
            );
            $stmt->execute([':c' => $cedula]);
            $emple = $stmt->fetchColumn();

            if (!$emple) {
                echo json_encode(['encontrado' => false, 'mensaje' => 'No se encontró un empleado activo con ese código']);
                exit();
            }

            // Buscar activos asignados
            $stmt2 = $db->prepare(
                "SELECT a.id_activo, t.nom_tipo, ma.nom_marca, mo.nom_modelo, a.serial, a.codigo_qr, a.referencia
                 FROM tab_activotec a
                 LEFT JOIN tab_tipos  t  ON a.id_tipoequi = t.id_tipoequi
                 LEFT JOIN tab_marca  ma ON a.id_marca    = ma.id_marca
                 LEFT JOIN tab_modelo mo ON a.id_modelo   = mo.id_modelo
                 WHERE a.cod_nom_responsable = :c AND a.activo = TRUE
                 ORDER BY a.id_padre_activo NULLS FIRST"
            );
            $stmt2->execute([':c' => $cedula]);
            $activos_raw = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $activos = array_map(function($a) {
                return [
                    'id'     => $a['id_activo'],
                    'tipo'   => $a['nom_tipo']   ?? 'Equipo',
                    'marca'  => $a['nom_marca']  ?? '',
                    'modelo' => $a['nom_modelo'] ?? '',
                    'serial' => $a['serial']     ?? 'S/N',
                    'foto_qr'=> rtrim(APP_URL, '/') . '/controllers/qrController.php?codigo=' . urlencode($a['codigo_qr']),
                ];
            }, $activos_raw);

            echo json_encode([
                'encontrado' => true,
                'empleado'   => $emple,
                'activos'    => $activos,
            ]);
        } catch (Exception $e) {
            echo json_encode(['encontrado' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
        }
        exit();
    }

    // ── Crear ticket desde portal público ────────────────────────────────────
    public static function crearTicket(): void
    {
        header('Content-Type: application/json');

        $cedula      = trim($_POST['cedula']      ?? '');
        $id_activo   = (int)($_POST['id_activo']  ?? 0);
        $tipo_dano   = trim($_POST['tipo_dano']   ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($cedula === '' || $id_activo <= 0 || $tipo_dano === '' || $descripcion === '') {
            echo json_encode(['success' => false, 'msg' => 'Faltan datos obligatorios']);
            exit();
        }

        // Procesar imagen si viene
        $foto_url = null;
        if (!empty($_FILES['foto']['tmp_name'])) {
            $foto_url = self::guardarFoto($_FILES['foto']);
        }

        try {
            $db   = Database::conectar();

            // Obtener nombre del empleado
            $stmt = $db->prepare('SELECT nom_emple FROM tab_empleados WHERE cod_nom = :c AND activo = TRUE');
            $stmt->execute([':c' => $cedula]);
            $nombre = $stmt->fetchColumn();

            if (!$nombre) {
                echo json_encode(['success' => false, 'msg' => 'Empleado no encontrado']);
                exit();
            }

            // Obtener datos del activo para el correo
            $stmtAct = $db->prepare(
                "SELECT a.referencia, a.codigo_qr FROM tab_activotec a WHERE a.id_activo = :id LIMIT 1"
            );
            $stmtAct->execute([':id' => $id_activo]);
            $rowAct    = $stmtAct->fetch(PDO::FETCH_ASSOC);
            $activo_ref = $rowAct['referencia'] ?? '';
            $activo_qr  = $rowAct['codigo_qr']  ?? '';

            // Insertar novedad directamente (el portal permite elegir activo específico)
            $ins = $db->prepare(
                "INSERT INTO tab_novedades
                    (cedula_reportante, nombre_reportante, id_activo, tipo_dano, descripcion, evidencia_foto, estado_ticket, activo)
                 VALUES (:ced, :nom, :act, :tipo, :desc, :foto, 'ABIERTO', TRUE)
                 RETURNING id_novedad"
            );
            $ins->execute([
                ':ced'  => $cedula,
                ':nom'  => $nombre,
                ':act'  => $id_activo,
                ':tipo' => $tipo_dano,
                ':desc' => $descripcion,
                ':foto' => $foto_url,
            ]);
            $id_ticket = $ins->fetchColumn();

            // ── Enviar notificación por correo ────────────────────────────────
            MailService::notificarNuevaNovedad([
                'id_novedad'   => $id_ticket,
                'fecha'        => date('Y-m-d H:i'),
                'nombre'       => $nombre,
                'cod_nom'      => $cedula,
                'tipo_dano'    => $tipo_dano,
                'descripcion'  => $descripcion,
                'activo_ref'   => $activo_ref ?? '',
                'activo_qr'    => $activo_qr  ?? '',
                'evidencia_foto'=> $foto_url,
            ]);

            echo json_encode(['success' => true, 'id_ticket' => $id_ticket]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'msg' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private static function guardarFoto(array $file): ?string
    {
        $uploadDir = __DIR__ . '/../uploads/novedades/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','webp','gif'];
        if (!in_array($ext, $allowed)) return null;

        $filename = 'nov_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest     = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return rtrim(APP_URL, '/') . '/uploads/novedades/' . $filename;
        }
        return null;
    }
}
