<?php
require_once 'core/Database.php';

$user = 'admin_sih';
$pass = 'Colombia2026*'; 
// Usamos DEFAULT para asegurar que tu versión de PHP lo reconozca sin problemas
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT); 

try {
    $db = Database::conectar();
    
    // Limpiamos intentos previos para evitar el error de "UNIQUE"
    $db->prepare("DELETE FROM tab_usuarios WHERE username = :u")->execute([':u' => $user]);

    // Insertamos usando tu función unificada o inserción directa para probar
    $stmt = $db->prepare("INSERT INTO tab_usuarios (username, contrasena, activo) VALUES (:u, :p, true)");
    $stmt->execute([':u' => $user, ':p' => $hashed_pass]);
    
    echo "✅ Usuario re-creado con éxito.<br>";
    echo "Hash generado: " . $hashed_pass;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}