<?php
require_once '../controllers/celularesController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: celulares.php'); exit(); }

$data = CelularesController::ver($id);
if (isset($data['error'])) { header('Location: celulares.php'); exit(); }

$cel    = $data['celular'];
$linea  = trim($cel['r_linea']       ?? '—');
$nombre = trim($cel['r_responsable'] ?? 'Sin asignar');

$logoPath = __DIR__ . '/../assets/logo.png';
$logoB64  = file_exists($logoPath)
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
    : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta — <?= htmlspecialchars($linea) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            gap: 1.5rem;
        }

        /* ── Controles ──────────────────────────────────── */
        .controls {
            width: 100%; max-width: 560px;
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 1.25rem; padding: 1rem 1.5rem;
            display: flex; align-items: center;
            justify-content: space-between; gap: 1rem; flex-wrap: wrap;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
        }
        .back-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            text-decoration: none; font-size: .75rem; font-weight: 800;
            color: #64748b; padding: .5rem .9rem;
            border: 2px solid #e2e8f0; border-radius: .75rem; transition: all .2s;
        }
        .back-btn:hover { border-color: #e11d48; color: #e11d48; }
        .ctrl-title { font-size: .8rem; font-weight: 800; color: #0a0a0f; }
        .ctrl-sub   { font-size: .65rem; color: #94a3b8; font-weight: 600; margin-top: .1rem; }
        .controls-right { display: flex; align-items: center; gap: .6rem; }
        .qty-label { font-size: .7rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }
        .qty-input {
            width: 3.5rem; padding: .4rem .6rem;
            border: 2px solid #e2e8f0; border-radius: .6rem;
            font-size: .85rem; font-weight: 800; text-align: center;
            outline: none; transition: border-color .2s;
        }
        .qty-input:focus { border-color: #e11d48; }
        .print-btn {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .55rem 1.25rem; border-radius: .75rem;
            font-size: .72rem; font-weight: 900;
            letter-spacing: .08em; text-transform: uppercase;
            color: #fff; border: none; cursor: pointer;
            background: linear-gradient(135deg, #e11d48, #9f1239);
            box-shadow: 0 4px 15px rgba(225,29,72,.35); transition: all .2s;
        }
        .print-btn:hover { transform: translateY(-1px); }

        .preview-label {
            font-size: .65rem; font-weight: 900;
            letter-spacing: .15em; text-transform: uppercase; color: #94a3b8;
        }
        #print-container {
            display: flex; flex-direction: column;
            align-items: center; gap: .75rem;
        }

        /* ════════════════════════════════════════════
           ETIQUETA  7.62 cm × 2.54 cm
           96 dpi → 288 px × 96 px
        ════════════════════════════════════════════ */
        .sticker {
            width:  288px;
            height:  96px;
            background: #fff;
            border: 1.5px solid #d1d5db;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,.12);
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        /* Logo — columna izquierda */
        .s-logo {
            width: 72px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            padding: 8px;
        }
        .s-logo img {
            width:  52px;
            height: 52px;
            object-fit: contain;
        }

        /* Separador */
        .s-sep {
            width: 1.5px;
            height: 60px;
            background: #e5e7eb;
            flex-shrink: 0;
        }

        /* Texto */
        .s-body {
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 14px;
            gap: 5px;
            min-width: 0;
        }

        /* Nombre: pequeño, cabe aunque sea largo */
        .s-nombre {
            font-size: 8px;
            font-weight: 700;
            color: #000000;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        /* Número: máximo protagonismo, negro puro para térmica */
        .s-linea {
            font-size: 28px;
            font-weight: 900;
            color: #000000;
            letter-spacing: .06em;
            line-height: 1;
            white-space: nowrap;
            -webkit-text-stroke: 0.5px #000;
        }

        /* ════ IMPRESIÓN ════════════════════════════ */
        @media print {
            @page { size: 7.62cm 2.54cm; margin: 0; }

            html, body {
                margin: 0; padding: 0;
                background: white !important;
                width: 7.62cm;
                display: block !important;
            }
            .controls, .preview-label, .update-info { display: none !important; }
            #print-container { display: block; }

            .sticker {
                width: 7.62cm; height: 2.54cm;
                border: none; border-radius: 0; box-shadow: none;
                page-break-after: always; break-after: page;
            }
            .s-logo     { width: 1.9cm; padding: 2mm; }
            .s-logo img { width: 1.3cm; height: 1.3cm; }
            .s-sep      { height: 1.5cm; }
            .s-nombre   { font-size: 6.5pt; }
            .s-linea    { font-size: 22pt; -webkit-text-stroke: 0.5px #000; }

            * { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="controls">
    <div style="display:flex;align-items:center;gap:.75rem">
        <a href="celular_ver.php?id=<?= $id ?>" class="back-btn">&#8592; Volver</a>
        <div>
            <div class="ctrl-title">Etiqueta — Línea <?= htmlspecialchars($linea) ?></div>
            <div class="ctrl-sub">Datos en tiempo real &middot; al reasignar el nombre cambia solo</div>
        </div>
    </div>
    <div class="controls-right">
        <span class="qty-label">Copias</span>
        <input type="number" class="qty-input" id="qty" value="1" min="1" max="30">
        <button class="print-btn" onclick="imprimir()">&#128438; Imprimir</button>
    </div>
</div>

<div class="preview-label">Vista previa</div>

<div id="print-container">
    <div class="sticker" id="sticker-base">
        <?php if ($logoB64): ?>
        <div class="s-logo"><img src="<?= $logoB64 ?>" alt="Logo"></div>
        <div class="s-sep"></div>
        <?php endif; ?>
        <div class="s-body">
            <div class="s-nombre"><?= htmlspecialchars($nombre) ?></div>
            <div class="s-linea"><?= htmlspecialchars($linea) ?></div>
        </div>
    </div>
</div>

<div class="update-info" style="font-size:.65rem;color:#94a3b8;font-weight:600;text-align:center;">
    Al reasignar el equipo, abre esta página de nuevo y el nombre ya estará actualizado.
</div>

<script>
function imprimir() {
    const qty = parseInt(document.getElementById('qty').value) || 1;
    const base = document.getElementById('sticker-base');
    const container = document.getElementById('print-container');
    container.querySelectorAll('.sticker-copy').forEach(el => el.remove());
    for (let i = 1; i < qty; i++) {
        const c = base.cloneNode(true);
        c.id = ''; c.classList.add('sticker-copy');
        container.appendChild(c);
    }
    window.print();
}
document.getElementById('qty').addEventListener('input', function () {
    const qty = Math.max(1, Math.min(30, parseInt(this.value) || 1));
    this.value = qty;
    const base = document.getElementById('sticker-base');
    const container = document.getElementById('print-container');
    container.querySelectorAll('.sticker-copy').forEach(el => el.remove());
    for (let i = 1; i < qty; i++) {
        const c = base.cloneNode(true);
        c.id = ''; c.classList.add('sticker-copy');
        container.appendChild(c);
    }
});
</script>
</body>
</html>
