<?php
require_once 'core/database.php';

try {
    $db = Database::conectar();
    $query = $db->query("SELECT version()");
    $version = $query->fetch();
    
    echo "🟢 Conexión Exitosa!<br>";
    echo "Motor: " . $version['version'];
} catch (Exception $e) {
    echo "🔴 Fallo en el puente: " . $e->getMessage();
}