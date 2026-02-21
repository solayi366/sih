<?php
/**
 * qrController.php
 * Genera QR en SVG usando la librería chillerlan/php-qrcode
 */

if (ob_get_level()) ob_clean();

if (session_status() === PHP_SESSION_NONE) session_start();
// El endpoint de QR es público: la imagen debe poder cargarse sin sesión.

require_once __DIR__ . '/../config/config.php';

// ── Autoload manual de chillerlan/php-qrcode ─────────────────────────────────
$vendorBase = __DIR__ . '/../vendor';

// settings-container (dependencia base)
spl_autoload_register(function(string $class) use ($vendorBase): void {
    if (strpos($class, 'chillerlan\\Settings\\') === 0) {
        $rel  = str_replace('chillerlan\\Settings\\', '', $class);
        $file = $vendorBase . '/php-settings-container/src/' . str_replace('\\', '/', $rel) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

// php-qrcode
spl_autoload_register(function(string $class) use ($vendorBase): void {
    if (strpos($class, 'chillerlan\\QRCode\\') === 0) {
        $rel  = str_replace('chillerlan\\QRCode\\', '', $class);
        $file = $vendorBase . '/php-qrcode/src/' . str_replace('\\', '/', $rel) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\Common\EccLevel;

// ── Parámetros ────────────────────────────────────────────────────────────────
$codigo   = isset($_GET['codigo'])   ? trim($_GET['codigo'])  : '';
$download = isset($_GET['download']) && $_GET['download'] === '1';

if (empty($codigo)) {
    http_response_code(400);
    exit('Codigo requerido');
}

// URL que codifica el QR
$url = rtrim(APP_URL, '/') . '/public/ver.php?qr=' . urlencode($codigo);

// ── Generar SVG ───────────────────────────────────────────────────────────────
$options = new QROptions([
    'outputType'  => QROutputInterface::MARKUP_SVG,
    'eccLevel'    => EccLevel::M,
    'outputBase64'=> false,   // SVG plano, no base64
    'svgDefs'     => '<style>rect{shape-rendering:crispEdges}</style>',
]);

$svg = (new QRCode($options))->render($url);

// ── Respuesta ─────────────────────────────────────────────────────────────────
if (ob_get_level()) ob_clean();

header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=86400');

if ($download) {
    $fname = 'QR_' . preg_replace('/[^A-Z0-9\-]/', '', strtoupper($codigo)) . '.svg';
    header('Content-Disposition: attachment; filename="' . $fname . '"');
}

echo $svg;
exit;
