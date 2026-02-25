<?php
require_once 'core/Database.php';

$user = 'admin_sih';
$pass = 'Colombia2026*';
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

try {
    $db = Database::conectar();

    // Primero aseguramos que existan las columnas necesarias
    $db->exec("ALTER TABLE tab_usuarios ADD COLUMN IF NOT EXISTS activo   BOOLEAN DEFAULT TRUE");
    $db->exec("ALTER TABLE tab_usuarios ADD COLUMN IF NOT EXISTS es_admin BOOLEAN DEFAULT FALSE");

    // Limpiamos intentos previos para evitar el error de "UNIQUE"
    $db->prepare("DELETE FROM tab_usuarios WHERE username = :u")->execute([':u' => $user]);

    // Insertamos con activo=TRUE y es_admin=TRUE
    $stmt = $db->prepare("INSERT INTO tab_usuarios (username, contrasena, activo, es_admin) VALUES (:u, :p, TRUE, TRUE)");
    $stmt->execute([':u' => $user, ':p' => $hashed_pass]);

    echo "✅ Usuario administrador creado con éxito.<br>";
    echo "Usuario: <strong>{$user}</strong><br>";
    echo "Contraseña: <strong>{$pass}</strong><br>";
    echo "Hash generado: " . $hashed_pass;

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
