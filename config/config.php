<?php
/**
 * SIH — Configuración
 * Lee credenciales desde .env (raíz del proyecto)
 */

// ── Cargar .env ───────────────────────────────────────────────
$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    error_log("SIH CRÍTICO: No se encontró el archivo .env en: {$envFile}");
    die("Error de configuración: no se encontró .env. Ruta buscada: " . realpath(__DIR__ . '/..'));
}

// Leer línea a línea — compatible con \r\n (Windows) y \n (Linux)
$lineas = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lineas as $linea) {
    // Eliminar BOM y espacios
    $linea = trim($linea, " \t\r\n\0\x0B\xEF\xBB\xBF");
    if ($linea === '' || $linea[0] === '#') continue;
    $pos = strpos($linea, '=');
    if ($pos === false) continue;
    $key = trim(substr($linea, 0, $pos));
    $val = trim(substr($linea, $pos + 1));
    // Quitar comillas opcionales del valor: VAR="valor" o VAR='valor'
    if (strlen($val) >= 2) {
        $first = $val[0];
        $last  = $val[strlen($val) - 1];
        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            $val = substr($val, 1, -1);
        }
    }
    if (getenv($key) === false && !isset($_ENV[$key])) {
        putenv("{$key}={$val}");
        $_ENV[$key] = $val;
    }
}

// ── Helper ────────────────────────────────────────────────────
function env(string $key, ?string $default = null): string {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (!empty($_ENV[$key])) return $_ENV[$key];
    if ($default !== null) return $default;
    error_log("SIH: Variable '{$key}' no encontrada en .env");
    die("Error de configuración: falta la variable '{$key}'. Revisa tu archivo .env");
}

// ── Base de datos ─────────────────────────────────────────────
define('DB_HOST', env('DB_HOST'));
define('DB_PORT', env('DB_PORT', '5432'));
define('DB_NAME', env('DB_NAME'));
define('DB_USER', env('DB_USER'));
define('DB_PASS', env('DB_PASS'));

// ── URL base ──────────────────────────────────────────────────
define('APP_URL', env('APP_URL'));

// ── Correo SMTP ───────────────────────────────────────────────
define('MAIL_HOST',       env('MAIL_HOST'));
define('MAIL_PORT',       (int) env('MAIL_PORT', '465'));
define('MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', 'ssl'));
define('MAIL_USER',       env('MAIL_USER'));
define('MAIL_PASS',       env('MAIL_PASS'));
define('MAIL_FROM_NAME',  env('MAIL_FROM_NAME', 'SIH — Mesa de Ayuda'));
define('MAIL_NOTIFY_TO',  env('MAIL_NOTIFY_TO'));
