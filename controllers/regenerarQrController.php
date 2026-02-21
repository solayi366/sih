<?php
if (ob_get_level()) ob_clean();

require_once __DIR__ . '/../core/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'msg' => 'No autorizado']);
    exit();
}

header('Content-Type: application/json');

// Leer acción desde GET o POST indistintamente
$action = '';
if (!empty($_GET['action']))  $action = trim($_GET['action']);
if (!empty($_POST['action'])) $action = trim($_POST['action']);

try {
    $db = Database::conectar();

    // ── Stats ─────────────────────────────────────────────────────────────────
    if ($action === 'stats') {
        $total = $db->query("SELECT COUNT(*) FROM tab_activotec WHERE activo = TRUE")->fetchColumn();
        echo json_encode(['success' => true, 'total' => (int)$total]);
        exit();
    }

    // ── Regenerar TODOS ───────────────────────────────────────────────────────
    if ($action === 'regenerar_todos') {
        $activos = $db->query(
            "SELECT id_activo, codigo_qr FROM tab_activotec WHERE activo = TRUE ORDER BY id_activo"
        )->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare(
            "UPDATE tab_activotec SET codigo_qr = :nuevo_qr WHERE id_activo = :id"
        );

        $actualizados = 0;
        $errores      = 0;
        $log          = [];

        foreach ($activos as $act) {
            // Generar código único
            $nuevo_qr = 'QR-' . strtoupper(bin2hex(random_bytes(3)));
            for ($i = 0; $i < 5; $i++) {
                $dup = $db->prepare("SELECT 1 FROM tab_activotec WHERE codigo_qr = :qr AND id_activo != :id");
                $dup->execute([':qr' => $nuevo_qr, ':id' => $act['id_activo']]);
                if (!$dup->fetchColumn()) break;
                $nuevo_qr = 'QR-' . strtoupper(bin2hex(random_bytes(3)));
            }

            try {
                $stmt->execute([':nuevo_qr' => $nuevo_qr, ':id' => $act['id_activo']]);
                $actualizados++;
                $log[] = ['id' => $act['id_activo'], 'viejo' => $act['codigo_qr'], 'nuevo' => $nuevo_qr];
            } catch (Exception $e) {
                $errores++;
            }
        }

        echo json_encode([
            'success'      => true,
            'actualizados' => $actualizados,
            'errores'      => $errores,
            'log'          => $log,
        ]);
        exit();
    }

    // ── Regenerar uno ─────────────────────────────────────────────────────────
    if ($action === 'regenerar_uno') {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success' => false, 'msg' => 'ID inválido']); exit(); }

        $nuevo_qr = 'QR-' . strtoupper(bin2hex(random_bytes(3)));
        $db->prepare("UPDATE tab_activotec SET codigo_qr = :qr WHERE id_activo = :id")
           ->execute([':qr' => $nuevo_qr, ':id' => $id]);

        echo json_encode(['success' => true, 'nuevo_qr' => $nuevo_qr]);
        exit();
    }

    // Si llega aquí la acción no existe — devolver debug info
    echo json_encode([
        'success' => false,
        'msg'     => 'Acción no reconocida: "' . htmlspecialchars($action) . '"',
        'get'     => $_GET,
        'post'    => $_POST,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
exit();