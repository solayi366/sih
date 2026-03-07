<?php
/**
 * SIH — Vista: Importar Celulares desde Excel
 * ARCHIVO: public/celular_importar.php
 */
require_once '../config/config.php';
require_once '../core/Csrf.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Celulares | SIH_QR</title>
    <script>(function(){var t=localStorage.getItem('sihTheme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark');}})();</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{brand:{50:'#fff1f2',100:'#ffe4e6',600:'#e11d48',700:'#be123c',900:'#881337'}},fontFamily:{sans:['Plus Jakarta Sans','sans-serif']}}}}</script>
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
    <style>
        .dark .bg-white  { background-color: rgba(16,14,24,0.90) !important; border-color: rgba(255,255,255,0.07) !important; }
        .dark .bg-slate-50 { background-color: rgba(14,12,22,0.90) !important; }
        .dark .text-slate-700 { color: #cbd5e1 !important; }
        .dark .text-slate-500 { color: #94a3b8 !important; }
        .dark .border-slate-200 { border-color: rgba(255,255,255,0.07) !important; }
        .dark .border-slate-100 { border-color: rgba(22,18,34,0.85) !important; }
        .dark input, .dark select {
            background-color: rgba(22,18,34,0.85) !important;
            border-color: #475569 !important;
            color: #f1f5f9 !important;
        }
        /* Zona drop */
        #dropZone { transition: all .2s; }
        #dropZone.drag-over {
            border-color: #e11d48 !important;
            background-color: #fff1f2 !important;
        }
        .dark #dropZone.drag-over { background-color: #2d1a20 !important; }

        /* Tabla resultados */
        .res-ok  { background-color: #f0fdf4; color: #15803d; }
        .res-err { background-color: #fff1f2; color: #be123c; }
        .dark .res-ok  { background-color: #052e16; color: #4ade80; }
        .dark .res-err { background-color: #2d0a14; color: #fca5a5; }

        /* Animación entrada */
        @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fadeUp .3s ease forwards; }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative bg-slate-50/50 w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-3 sm:p-6 md:p-8 w-full">
            <div class="max-w-4xl mx-auto space-y-6">

                <!-- ── Header ─────────────────────────────────────────────── -->
                <div class="flex items-center gap-3">
                    <a href="celulares.php"
                       class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-brand-600 hover:border-brand-300 transition-all shadow-sm">
                        <i class="fas fa-arrow-left text-xs"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Importar Celulares</h1>
                        <p class="text-slate-400 text-sm mt-0.5">Carga masiva desde Excel (.xlsx)</p>
                    </div>
                </div>

                <!-- ── Card principal ──────────────────────────────────────── -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                    <!-- Sección encabezado card -->
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center gap-2">
                        <i class="fas fa-file-excel text-emerald-500 text-sm"></i>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Archivo Excel</p>
                    </div>

                    <div class="p-5 space-y-5">

                        <!-- Zona drag & drop -->
                        <div id="dropZone"
                             class="border-2 border-dashed border-slate-300 rounded-2xl p-10 flex flex-col items-center justify-center gap-3 cursor-pointer hover:border-brand-600 transition-all"
                             onclick="document.getElementById('archivoInput').click()">
                            <div id="dropIcon" class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-cloud-arrow-up text-3xl text-slate-400"></i>
                            </div>
                            <div class="text-center">
                                <p class="font-bold text-slate-700">Arrastra el archivo aquí</p>
                                <p class="text-xs text-slate-400 mt-1">o haz clic para seleccionar · Solo <strong>.xlsx</strong></p>
                            </div>
                            <div id="fileInfo" class="hidden items-center gap-2 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2">
                                <i class="fas fa-file-excel text-emerald-500"></i>
                                <span id="fileName" class="text-sm font-bold text-emerald-700"></span>
                                <span id="fileSize" class="text-xs text-emerald-500"></span>
                            </div>
                        </div>
                        <input type="file" id="archivoInput" accept=".xlsx" class="hidden">

                        <!-- ── Configuración de columnas ───────────────────── -->
                        <details class="group">
                            <summary class="flex items-center gap-2 cursor-pointer text-xs font-black text-slate-400 uppercase tracking-widest hover:text-brand-600 transition-colors select-none list-none">
                                <i class="fas fa-sliders text-xs group-open:rotate-90 transition-transform"></i>
                                Configuración de columnas (avanzado)
                                <i class="fas fa-chevron-down text-[10px] ml-auto group-open:rotate-180 transition-transform"></i>
                            </summary>

                            <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-4">
                                <p class="text-xs text-slate-500">
                                    Indica la <strong>letra de columna</strong> en el Excel donde está cada dato.
                                    Los valores por defecto corresponden al formato estándar <em>BASE_06_CELULARES</em>.
                                </p>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                    <?php
                                    $campos = [
                                        'col_linea'         => ['label' => 'Línea telefónica', 'default' => 'B'],
                                        'col_imei'          => ['label' => 'IMEI',              'default' => 'C'],
                                        'col_marca_modelo'  => ['label' => 'Marca / Modelo',    'default' => 'J'],
                                        'col_cod_nom'       => ['label' => 'Cód. Nómina',       'default' => 'I'],
                                        'col_cargo'         => ['label' => 'Cargo',             'default' => 'H'],
                                        'col_pin'           => ['label' => 'PIN / Contraseña',  'default' => 'O'],
                                        'col_puk'           => ['label' => 'PUK',               'default' => 'P'],
                                        'col_observaciones' => ['label' => 'Observaciones',     'default' => 'N'],
                                    ];
                                    foreach ($campos as $name => $info): ?>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                            <?= $info['label'] ?>
                                        </label>
                                        <input type="text" name="<?= $name ?>" id="<?= $name ?>"
                                               value="<?= $info['default'] ?>"
                                               maxlength="2"
                                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-center font-bold text-slate-700 uppercase focus:border-brand-600 focus:outline-none transition-all">
                                    </div>
                                    <?php endforeach; ?>

                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                            Fila de inicio (datos)
                                        </label>
                                        <input type="number" id="fila_inicio" value="2" min="1"
                                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-center font-bold text-slate-700 focus:border-brand-600 focus:outline-none transition-all">
                                    </div>
                                </div>
                            </div>
                        </details>

                        <!-- ── Info de columnas del Excel ─────────────────── -->
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-xs text-slate-500 space-y-1.5">
                            <p class="font-black text-slate-600 text-[10px] uppercase tracking-widest mb-2">
                                <i class="fas fa-circle-info mr-1 text-blue-400"></i>Formato esperado del Excel
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 font-mono">
                                <span><strong>B</strong> — LINEA</span>
                                <span><strong>C</strong> — IMEI</span>
                                <span><strong>H</strong> — CARGO RESPONSABLE</span>
                                <span><strong>I</strong> — CODIGO NOMINA</span>
                                <span><strong>J</strong> — MARCA CELULAR (ej: <em>SAMSUNG A04</em>)</span>
                                <span><strong>N</strong> — OBSERVACIONES</span>
                                <span><strong>O</strong> — CONTRASEÑA / PIN</span>
                                <span><strong>P</strong> — PUK</span>
                            </div>
                            <p class="text-[10px] mt-1 text-slate-400">
                                La columna J debe tener el formato <em>"MARCA MODELO"</em> en una sola celda (ej: SAMSUNG A04, NOKIA C21 Plus).
                                Marca y modelo se crean automáticamente si no existen.
                            </p>
                        </div>

                        <!-- Botón importar -->
                        <button id="btnImportar" onclick="ejecutarImportacion()"
                                disabled
                                class="w-full py-3.5 bg-brand-600 text-white rounded-xl font-black uppercase tracking-widest shadow-lg shadow-brand-600/20 hover:bg-brand-700 transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <i id="btnIcon" class="fas fa-file-import"></i>
                            <span id="btnText">Selecciona un archivo primero</span>
                        </button>
                    </div>
                </div>

                <!-- ── Resultados ──────────────────────────────────────────── -->
                <div id="seccionResultados" class="hidden fade-up">

                    <!-- Resumen -->
                    <div id="resumenCards" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4"></div>

                    <!-- Tabla detallada -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <i class="fas fa-list-check mr-1.5 text-brand-600"></i>Detalle por fila
                            </p>
                            <div class="flex gap-2">
                                <button onclick="filtrarResultados('todos')"   id="fBtn-todos"  class="filter-btn active text-[10px] font-black px-3 py-1 rounded-lg bg-slate-800 text-white">Todos</button>
                                <button onclick="filtrarResultados('OK')"      id="fBtn-OK"     class="filter-btn text-[10px] font-black px-3 py-1 rounded-lg bg-slate-100 text-slate-500">✓ OK</button>
                                <button onclick="filtrarResultados('ERROR')"   id="fBtn-ERROR"  class="filter-btn text-[10px] font-black px-3 py-1 rounded-lg bg-slate-100 text-slate-500">✗ Errores</button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 bg-slate-50/60">
                                        <th class="px-4 py-3 text-left font-black text-slate-400 uppercase tracking-widest text-[10px]">#</th>
                                        <th class="px-4 py-3 text-left font-black text-slate-400 uppercase tracking-widest text-[10px]">Línea</th>
                                        <th class="px-4 py-3 text-left font-black text-slate-400 uppercase tracking-widest text-[10px]">Cód. Nómina</th>
                                        <th class="px-4 py-3 text-left font-black text-slate-400 uppercase tracking-widest text-[10px]">Estado</th>
                                        <th class="px-4 py-3 text-left font-black text-slate-400 uppercase tracking-widest text-[10px]">Detalle</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaResultados" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Acción post-importación -->
                    <div class="flex gap-3 mt-4">
                        <a href="celulares.php"
                           class="flex-1 py-3 bg-brand-600 text-white rounded-xl font-black text-sm uppercase tracking-widest text-center hover:bg-brand-700 transition-all shadow-lg shadow-brand-600/20">
                            <i class="fas fa-mobile-screen-button mr-2"></i>Ver inventario
                        </a>
                        <button onclick="reiniciarFormulario()"
                                class="flex-1 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-sm hover:border-brand-300 hover:text-brand-600 transition-all">
                            <i class="fas fa-rotate-left mr-2"></i>Importar otro archivo
                        </button>
                    </div>
                </div>

            </div><!-- /max-w -->
        </div><!-- /overflow-y -->
    </main>

<script>
// ── Estado global ─────────────────────────────────────────────────────────────
let archivoSeleccionado = null;
let todosResultados     = [];

// ── Drag & Drop ───────────────────────────────────────────────────────────────
const dropZone    = document.getElementById('dropZone');
const archivoInput = document.getElementById('archivoInput');

dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', e => { dropZone.classList.remove('drag-over'); });
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) procesarArchivo(file);
});

archivoInput.addEventListener('change', () => {
    if (archivoInput.files[0]) procesarArchivo(archivoInput.files[0]);
});

function procesarArchivo(file) {
    if (!file.name.toLowerCase().endsWith('.xlsx')) {
        Alerts.warningHtml('Formato incorrecto', 'Solo se aceptan archivos <strong>.xlsx</strong>');
        return;
    }
    archivoSeleccionado = file;

    // UI feedback
    document.getElementById('fileInfo').classList.remove('hidden');
    document.getElementById('fileInfo').classList.add('flex');
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = '(' + (file.size / 1024).toFixed(1) + ' KB)';
    document.getElementById('dropIcon').innerHTML = '<i class="fas fa-file-excel text-3xl text-emerald-500"></i>';

    // Habilitar botón
    const btn = document.getElementById('btnImportar');
    btn.disabled = false;
    document.getElementById('btnText').textContent = 'Importar ahora';
}

// ── Convertir letra de columna a índice base-0 ────────────────────────────────
function colLetraAIndice(letra) {
    letra = letra.trim().toUpperCase();
    let n = 0;
    for (let i = 0; i < letra.length; i++) {
        n = n * 26 + (letra.charCodeAt(i) - 64);
    }
    return n - 1; // base-0
}

// ── Ejecutar importación ──────────────────────────────────────────────────────
async function ejecutarImportacion() {
    if (!archivoSeleccionado) return;

    const btn  = document.getElementById('btnImportar');
    const icon = document.getElementById('btnIcon');
    const text = document.getElementById('btnText');

    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';
    text.textContent = 'Procesando...';

    const fd = new FormData();
    fd.append('archivo', archivoSeleccionado);
    fd.append('csrf_token', '<?= Csrf::token() ?>');

    // Columnas
    const campos = ['col_linea','col_imei','col_marca_modelo','col_cod_nom','col_cargo','col_pin','col_puk','col_observaciones'];
    campos.forEach(c => {
        const el = document.getElementById(c);
        if (el) fd.append(c, colLetraAIndice(el.value || 'A'));
    });

    const fi = document.getElementById('fila_inicio');
    if (fi) fd.append('fila_inicio', fi.value || '2');

    try {
        const res  = await fetch('../controllers/celularImportarController.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (!data.ok) {
            Alerts.error('Error', data.msg || 'Error desconocido');
            btn.disabled = false;
            icon.className = 'fas fa-file-import';
            text.textContent = 'Importar ahora';
            return;
        }

        todosResultados = data.resultados || [];
        mostrarResultados(data);

    } catch (e) {
        console.error(e);
        Alerts.error('Error de conexión', 'No se pudo comunicar con el servidor.');
        btn.disabled = false;
        icon.className = 'fas fa-file-import';
        text.textContent = 'Importar ahora';
    }
}

// ── Mostrar sección de resultados ────────────────────────────────────────────
function mostrarResultados(data) {
    // Tarjetas resumen
    const pct = data.total > 0 ? Math.round(data.insertados / data.total * 100) : 0;
    document.getElementById('resumenCards').innerHTML = `
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center fade-up">
            <p class="text-3xl font-black text-slate-800">${data.total}</p>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Filas leídas</p>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-4 text-center fade-up">
            <p class="text-3xl font-black text-emerald-600">${data.insertados}</p>
            <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mt-1">Insertados</p>
        </div>
        <div class="bg-white rounded-2xl border border-rose-200 shadow-sm p-4 text-center fade-up">
            <p class="text-3xl font-black text-rose-600">${data.errores}</p>
            <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mt-1">Con error</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center fade-up">
            <p class="text-3xl font-black text-slate-600">${pct}%</p>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Éxito</p>
        </div>
    `;

    renderTabla(todosResultados);

    const seccion = document.getElementById('seccionResultados');
    seccion.classList.remove('hidden');
    seccion.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Renderizar tabla de resultados ────────────────────────────────────────────
function renderTabla(filas) {
    const tbody = document.getElementById('tablaResultados');
    if (!filas || filas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">Sin resultados</td></tr>';
        return;
    }
    tbody.innerHTML = filas.map(r => {
        const esOk = r.resultado === 'OK';
        return `
        <tr class="${esOk ? 'res-ok' : 'res-err'}">
            <td class="px-4 py-2.5 font-mono font-bold">${r.fila ?? ''}</td>
            <td class="px-4 py-2.5 font-mono">${r.linea ?? ''}</td>
            <td class="px-4 py-2.5 font-mono">${r.cod_nom ?? ''}</td>
            <td class="px-4 py-2.5">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black
                    ${esOk ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'}">
                    <i class="fas ${esOk ? 'fa-check' : 'fa-xmark'}"></i>
                    ${r.resultado}
                </span>
            </td>
            <td class="px-4 py-2.5 text-[11px]">${r.detalle ?? ''}</td>
        </tr>`;
    }).join('');
}

// ── Filtros de tabla ──────────────────────────────────────────────────────────
function filtrarResultados(filtro) {
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.classList.remove('bg-slate-800','text-white');
        b.classList.add('bg-slate-100','text-slate-500');
    });
    const activo = document.getElementById('fBtn-' + filtro);
    if (activo) {
        activo.classList.add('bg-slate-800','text-white');
        activo.classList.remove('bg-slate-100','text-slate-500');
    }

    const filtrado = filtro === 'todos' ? todosResultados : todosResultados.filter(r => r.resultado === filtro);
    renderTabla(filtrado);
}

// ── Reiniciar formulario ──────────────────────────────────────────────────────
function reiniciarFormulario() {
    archivoSeleccionado = null;
    todosResultados     = [];
    document.getElementById('archivoInput').value = '';
    document.getElementById('fileInfo').classList.add('hidden');
    document.getElementById('fileInfo').classList.remove('flex');
    document.getElementById('dropIcon').innerHTML = '<i class="fas fa-cloud-arrow-up text-3xl text-slate-400"></i>';
    document.getElementById('btnImportar').disabled = true;
    document.getElementById('btnIcon').className = 'fas fa-file-import';
    document.getElementById('btnText').textContent = 'Selecciona un archivo primero';
    document.getElementById('seccionResultados').classList.add('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
<script src="../assets/js/dark_mode.js"></script>
</body>
</html>