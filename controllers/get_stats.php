<?php
session_start();
require_once '../core/database.php';

// Seguridad: Solo usuarios logueados
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $db = Database::conectar();
    $stmt = $db->query("SELECT * FROM fun_get_dashboard_stats()");
    $stats = $stmt->fetch();

    // Estructuramos la respuesta para que sea idéntica a la que esperaba el front original
    echo json_encode([
        'total' => $stats['total_activos'],
        'pendientes' => $stats['pendientes'],
        'operativos' => $stats['operativos'],
        'atencion' => $stats['atencion'],
        'estados' => json_decode($stats['json_estados']),
        'marcas' => json_decode($stats['json_marcas'])
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}