<?php
require_once __DIR__ . '/../core/database.php';

$id = $_GET['id'] ?? '';
if (empty($id)) { echo json_encode(['success' => false]); exit; }

try {
    $db = Database::conectar();
    $stmt = $db->prepare("SELECT nom_emple, id_area FROM tab_empleados WHERE cod_nom = :id AND activo = TRUE");
    $stmt->execute([':id' => $id]);
    $emp = $stmt->fetch();

    if ($emp) {
        echo json_encode(['success' => true, 'nombre' => $emp['nom_emple'], 'id_area' => $emp['id_area']]);
    } else {
        echo json_encode(['success' => false]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}