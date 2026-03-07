<?php
/**
 * SIH — Importación masiva de celulares desde Excel
 * ARCHIVO: controllers/celularImportarController.php
 *
 * Parsea el .xlsx usando SimpleXLSX, normaliza los datos
 * y delega el INSERT masivo a fun_importar_celulares() via
 * CelularesController::importar().
 *
 * Columnas esperadas en el Excel (configurables vía POST):
 *   col_linea, col_imei, col_marca_modelo, col_cod_nom,
 *   col_cargo, col_pin, col_puk, col_observaciones
 * Los índices son base-0.
 */

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../controllers/celularesController.php';
require_once __DIR__ . '/../core/Csrf.php';

// ── Sólo POST autenticado ─────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit();
}

Csrf::verify('../public/celulares.php');

header('Content-Type: application/json; charset=utf-8');

// ── Validar archivo subido ────────────────────────────────────────────────────
if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'msg' => 'No se recibió ningún archivo o hubo un error al subirlo.']);
    exit();
}

$ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
if ($ext !== 'xlsx') {
    echo json_encode(['ok' => false, 'msg' => 'Solo se aceptan archivos .xlsx']);
    exit();
}

// ── Índices de columna (base-0) enviados desde el formulario ─────────────────
// Defaults coinciden con el layout estándar del Excel BASE_06_CELULARES
$col = [
    'linea'          => (int)($_POST['col_linea']          ?? 1),   // B
    'imei'           => (int)($_POST['col_imei']           ?? 2),   // C
    'marca_modelo'   => (int)($_POST['col_marca_modelo']   ?? 9),   // J
    'cod_nom'        => (int)($_POST['col_cod_nom']        ?? 8),   // I
    'cargo'          => (int)($_POST['col_cargo']          ?? 7),   // H
    'responsable'    => (int)($_POST['col_responsable']    ?? 6),   // G
    'pin'            => (int)($_POST['col_pin']            ?? 14),  // O
    'puk'            => (int)($_POST['col_puk']            ?? 15),  // P
    'observaciones'  => (int)($_POST['col_observaciones']  ?? 13),  // N
];

$fila_inicio = max(2, (int)($_POST['fila_inicio'] ?? 2)); // default: omitir encabezado

// ── Cargar y parsear el Excel ─────────────────────────────────────────────────
$tmpPath = $_FILES['archivo']['tmp_name'];

// SimpleXLSX está en vendor/ sin namespace, lo requerimos directamente
require_once __DIR__ . '/../vendor/SimpleXLSX.php';

use Shuchkin\SimpleXLSX;

$xlsx = SimpleXLSX::parse($tmpPath);
if (!$xlsx) {
    echo json_encode(['ok' => false, 'msg' => 'No se pudo leer el archivo: ' . SimpleXLSX::parseError()]);
    exit();
}

$rows = $xlsx->rows(0); // primera hoja, base-0

// ── Normalización de datos ────────────────────────────────────────────────────

/**
 * Limpia un IMEI:
 *  - Excel a veces lo guarda como float científico o con apóstrofe inicial
 *  - Truncamos a entero y convertimos a string
 */
function limpiarImei($raw): string {
    if ($raw === null || $raw === '') return '';
    $s = ltrim(trim((string)$raw), "'");        // quitar apóstrofe de forzado-texto Excel
    // Si viene en notación científica (ej: 3.54E+14) lo convertimos
    if (strpos($s, 'E') !== false || strpos($s, 'e') !== false) {
        $s = number_format((float)$s, 0, '', '');
    }
    // Si viene como float (355144112747768.0) quitamos el .0
    if (str_contains($s, '.')) {
        $s = rtrim(rtrim($s, '0'), '.');
    }
    return $s;
}

/**
 * Normaliza código de nómina.
 * Puede llegar como: "S02574", 7658.0, "8812.0", "S12902 " (con espacio)
 */
function limpiarCodNom($raw): string {
    if ($raw === null || $raw === '') return '';
    $s = trim((string)$raw);
    // Si es numérico flotante (7658.0 → "7658")
    if (is_numeric($s)) {
        $s = (string)(int)(float)$s;
    }
    return strtoupper(trim($s));
}

/**
 * Separa "SAMSUNG A04" → marca="SAMSUNG", modelo="A04"
 * "ARMOR X5"          → marca="ARMOR",   modelo="X5"
 * "NOKIA C21 Plus"    → marca="NOKIA",   modelo="C21 Plus"
 * Estrategia: primer token = marca, resto = modelo.
 */
function separarMarcaModelo($raw): array {
    $s = trim(strtoupper((string)$raw));
    if ($s === '') return ['marca' => '', 'modelo' => ''];
    $partes = preg_split('/\s+/', $s, 2);
    return [
        'marca'  => $partes[0],
        'modelo' => $partes[1] ?? $partes[0],
    ];
}

/**
 * Normaliza PIN/contraseña:
 *  - "2906.0"    → "2906"
 *  - "SIN BLOQUEO" → null
 *  - "PIN: 1406" → "1406"
 *  - null / ""   → null
 */
function limpiarPin($raw): ?string {
    if ($raw === null || $raw === '') return null;
    $s = trim((string)$raw);
    if ($s === '' || strtoupper($s) === 'SIN BLOQUEO') return null;
    // Quitar prefijo "PIN: "
    $s = preg_replace('/^PIN\s*:\s*/i', '', $s);
    // Si es número flotante
    if (is_numeric($s)) {
        $s = (string)(int)(float)$s;
    }
    return $s === '' ? null : $s;
}

// ── Construir array de registros ──────────────────────────────────────────────
$registros   = [];
$filas_vacias = 0;

foreach ($rows as $i => $row) {
    $numFila = $i + 1; // base-1 para mensajes de usuario
    if ($numFila < $fila_inicio) continue; // saltar encabezado(s)

    $linea = isset($row[$col['linea']]) ? trim((string)$row[$col['linea']]) : '';

    // Limpiar número de línea: puede venir como float (3102103496.0)
    if (is_numeric($linea)) {
        $linea = (string)(int)(float)$linea;
    }

    if ($linea === '') { $filas_vacias++; continue; } // fila completamente vacía

    $mm = separarMarcaModelo($row[$col['marca_modelo']] ?? '');

    $registros[] = [
        'linea'          => $linea,
        'imei'           => limpiarImei($row[$col['imei']] ?? ''),
        'marca'          => $mm['marca'],
        'modelo'         => $mm['modelo'],
        'cod_nom'        => limpiarCodNom($row[$col['cod_nom']] ?? ''),
        'cargo'          => trim((string)($row[$col['cargo']] ?? '')),
        'responsable'    => trim((string)($row[$col['responsable']] ?? '')),
        'pin'            => limpiarPin($row[$col['pin']] ?? null),
        'puk'            => limpiarPin($row[$col['puk']] ?? null),
        'observaciones'  => trim((string)($row[$col['observaciones']] ?? '')) ?: null,
    ];
}

if (empty($registros)) {
    echo json_encode(['ok' => false, 'msg' => "No se encontraron filas con datos (desde fila {$fila_inicio}). ¿El archivo tiene encabezado en la fila 1?"]);
    exit();
}

// ── Llamar al controller que ejecuta la función SQL ──────────────────────────
$resultados = CelularesController::importar($registros);

// ── Estadísticas de resumen ───────────────────────────────────────────────────
$ok_count  = 0;
$err_count = 0;
foreach ($resultados as $r) {
    if (($r['resultado'] ?? '') === 'OK') $ok_count++;
    else $err_count++;
}

echo json_encode([
    'ok'          => true,
    'total'       => count($registros),
    'insertados'  => $ok_count,
    'errores'     => $err_count,
    'filas_vacias'=> $filas_vacias,
    'resultados'  => $resultados,
]);