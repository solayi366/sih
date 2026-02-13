<?php
session_start();
require_once '../core/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $db = Database::conectar();
        $stmt = $db->prepare("SELECT * FROM fun_read_usuario(NULL, :user)");
        $stmt->bindParam(':user', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['r_pass'])) {
            // Login exitoso
            $_SESSION['user_id'] = $user['r_id'];
            $_SESSION['username'] = $user['r_user'];
            header("Location: ../public/dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Credenciales incorrectas o usuario inactivo.";
            header("Location: ../public/login.php");
            exit();
        }

    } catch (Exception $e) {
        $_SESSION['error'] = "Error en el sistema. Intente más tarde.";
        header("Location: ../public/login.php");
        exit();
    }
}