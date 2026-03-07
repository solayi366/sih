<?php
session_start();
require_once '../core/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $db   = Database::conectar();
    $stmt = $db->query("SELECT * FROM fun_get_dashboard_celulares()");
    $row  = $stmt->fetch();

    echo json_encode([
        'total'         => (int)$row['total_celulares'],
        'asignados'     => (int)$row['asignados'],
        'en_reposicion' => (int)$row['en_reposicion'],
        'en_reasignacion'=> (int)$row['en_reasignacion'],
        'de_baja'       => (int)$row['de_baja'],
        'estados'       => json_decode($row['json_estados']),
        'marcas'        => json_decode($row['json_marcas']),
        'areas'         => json_decode($row['json_areas']),
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
