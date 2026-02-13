<?php
require_once __DIR__ . '/../core/database.php';

class ParametrosController {
    
    // Obtiene datos para la vista de Hardware
    public static function getHardwareData() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::conectar();
        return [
            'tipos'   => $db->query("SELECT * FROM fun_read_tipos()")->fetchAll(),
            'marcas'  => $db->query("SELECT * FROM fun_read_marcas()")->fetchAll(),
            'modelos' => $db->query("SELECT * FROM fun_read_modelos()")->fetchAll()
        ];
    }

    // Obtiene datos para la vista de RRHH
    public static function getRRHHData() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::conectar();
        return [
            'areas'     => $db->query("SELECT * FROM fun_read_areas()")->fetchAll(),
            'empleados' => $db->query("SELECT * FROM fun_read_empleados()")->fetchAll()
        ];
    }
}