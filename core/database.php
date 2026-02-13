<?php
require_once __DIR__ . '/../config/config.php';

class Database {
    private static $instancia = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Reportar errores como excepciones
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devolver arrays asociativos
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Usar preparación real de Postgres
                PDO::NULL_EMPTY_STRING       => true                    // Convertir strings vacíos a NULL
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
            
        } catch (PDOException $e) {
            // En producción, esto debería ir a un archivo de LOG, no a pantalla
            error_log("Error de Conexión SIH: " . $e->getMessage());
            die("Error crítico: No se pudo establecer la conexión con el motor de datos.");
        }
    }

    // Método para obtener la conexión única
    public static function conectar() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia->pdo;
    }

    // Evitar la clonación de la instancia (Seguridad)
    private function __clone() {}
}