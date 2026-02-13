<?php
require_once __DIR__ . '/../core/database.php';

class ActivosController {
    
    public static function listar() {
        // 1. Validar Sesión
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        // 2. Parámetros de Paginación
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;

        try {
            $db = Database::conectar();
            
            // 3. Consumir el Corazón del Sistema (PostgreSQL)
            $stmt = $db->prepare("SELECT * FROM fun_read_activos(:pag, :lim)");
            $stmt->bindParam(':pag', $page, PDO::PARAM_INT);
            $stmt->bindParam(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $activos = $stmt->fetchAll();
            
            // 4. Cálculos de Paginación
            $total_registros = (!empty($activos)) ? $activos[0]['total_registros'] : 0;
            $total_pages = ceil($total_registros / $limit);

            // Devolvemos los datos listos para la vista
            return [
                'activos' => $activos,
                'page' => $page,
                'total_pages' => $total_pages,
                'total_registros' => $total_registros
            ];

        } catch (Exception $e) {
            error_log("Error en ActivosController: " . $e->getMessage());
            return ['activos' => [], 'page' => 1, 'total_pages' => 1, 'error' => $e->getMessage()];
        }
    }
}