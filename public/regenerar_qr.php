<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regenerar Códigos QR | SIH_QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <script>tailwind.config={theme:{extend:{colors:{brand:{50:'#fff1f2',100:'#ffe4e6',600:'#e11d48',700:'#be123c'}},fontFamily:{sans:['Plus Jakarta Sans','sans-serif']}}}}</script>
    <style>
        .red-gradient { background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%); }
        .glass-card   { background:rgba(255,255,255,0.75); backdrop-filter:blur(12px); border:2px solid rgba(225,29,72,0.08); }
        #progressBar  { transition: width 0.4s ease; }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden"
      style="background: radial-gradient(circle at top left, #fff1f2, #f8fafc);">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-6 md:p-10">
            <div class="max-w-3xl mx-auto space-y-6">

                <!-- CABECERA -->
                <div>
                    <span class="text-brand-600 font-extrabold text-xs uppercase tracking-[0.2em]">Herramientas</span>
                    <h1 class="text-3xl font-black text-slate-900 mt-1">
                        Regenerar Códigos <span class="text-brand-600">QR</span>
                    </h1>
                    <p class="text-slate-500 text-sm mt-2">
                        Regenera los códigos QR de todos los activos. Úsala cuando cambies
                        la IP o dominio del servidor para que los QR sigan funcionando.
                    </p>
                </div>

                <!-- URL ACTUAL -->
                <div class="glass-card rounded-3xl p-6">
                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-link text-brand-600"></i> URL codificada en los QR
                    </h3>
                    <div class="bg-slate-900 rounded-2xl px-5 py-3 flex items-center gap-3">
                        <i class="fas fa-globe text-brand-400 text-sm flex-shrink-0"></i>
                        <code class="text-emerald-400 text-sm font-mono break-all">
                            <?= htmlspecialchars(rtrim(APP_URL, '/') . '/public/ver.php?qr=QR-XXXXXX') ?>
                        </code>
                    </div>
                    <p class="text-xs text-slate-400 mt-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Para cambiar la URL edita <code class="bg-slate-100 px-1 rounded">config/config.php</code>
                        → constante <code class="bg-slate-100 px-1 rounded">APP_URL</code>, luego regenera.
                    </p>
                </div>

                <!-- STATS -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="glass-card rounded-3xl p-6 text-center">
                        <div class="text-3xl font-black text-slate-800" id="statTotal">—</div>
                        <div class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Total activos</div>
                    </div>
                    <div class="glass-card rounded-3xl p-6 text-center border-2 border-emerald-100">
                        <div class="text-3xl font-black text-emerald-600" id="statOk">—</div>
                        <div class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Regenerados</div>
                    </div>
                    <div class="glass-card rounded-3xl p-6 text-center border-2 border-brand-100">
                        <div class="text-3xl font-black text-brand-600" id="statErr">—</div>
                        <div class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Con error</div>
                    </div>
                </div>

                <!-- PANEL ACCIÓN -->
                <div class="glass-card rounded-3xl p-8 space-y-6">

                    <div id="progressContainer" class="hidden">
                        <div class="flex justify-between text-xs font-bold text-slate-500 mb-2">
                            <span id="progressLabel">Procesando…</span>
                            <span id="progressPct">0%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                            <div id="progressBar" class="h-3 red-gradient rounded-full" style="width:0%"></div>
                        </div>
                    </div>

                    <button id="btnRegenerar"
                            onclick="regenerarTodos()"
                            class="w-full flex items-center justify-center gap-3 px-8 py-4 red-gradient text-white rounded-2xl font-black text-base shadow-xl shadow-brand-500/30 hover:opacity-90 transition-all">
                        <i class="fas fa-qrcode text-xl"></i>
                        Regenerar TODOS los códigos QR
                    </button>

                    <div class="flex gap-3 p-4 bg-amber-50 rounded-2xl border-2 border-amber-100">
                        <i class="fas fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0"></i>
                        <p class="text-xs text-amber-700 font-medium leading-relaxed">
                            <strong>Importante:</strong> Los QR impresos anteriormente dejarán de funcionar.
                            Vuelve a imprimir las etiquetas desde la ficha de cada activo.
                        </p>
                    </div>
                </div>

                <!-- LOG -->
                <div id="logContainer" class="hidden">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Registro de cambios</h3>
                        <button onclick="document.getElementById('logTable').classList.toggle('hidden')"
                                class="text-xs text-slate-400 hover:text-brand-600 font-bold">Mostrar/Ocultar</button>
                    </div>
                    <div id="logTable" class="bg-white rounded-2xl border-2 border-slate-100 overflow-hidden">
                        <div class="overflow-x-auto max-h-72 overflow-y-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 border-b-2 border-slate-100 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 font-black text-slate-500 uppercase">ID</th>
                                        <th class="px-4 py-3 font-black text-slate-500 uppercase">QR anterior</th>
                                        <th class="px-4 py-3 font-black text-slate-500 uppercase">QR nuevo</th>
                                    </tr>
                                </thead>
                                <tbody id="logBody" class="divide-y divide-slate-50 font-mono"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script>
        // Cargar stats al abrir
        window.addEventListener('load', async () => {
            try {
                // La acción 'stats' va como parámetro GET (solo lectura, sin POST body)
                const r = await fetch('../controllers/regenerarQrController.php?action=stats');
                const d = await r.json();
                if (d.success) document.getElementById('statTotal').textContent = d.total;
            } catch(e) { console.error('Stats error:', e); }
        });

        async function regenerarTodos() {
            const btn       = document.getElementById('btnRegenerar');
            const progCont  = document.getElementById('progressContainer');
            const progBar   = document.getElementById('progressBar');
            const progLabel = document.getElementById('progressLabel');
            const progPct   = document.getElementById('progressPct');

            if (!confirm('¿Regenerar los QR de TODOS los activos?\n\nLos QR impresos anteriores dejarán de funcionar.')) return;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xl"></i> Regenerando…';
            progCont.classList.remove('hidden');
            progBar.style.width = '20%';
            progLabel.textContent = 'Conectando con el servidor…';
            progPct.textContent   = '20%';

            try {
                // Enviar acción en el BODY como form-data
                const formData = new FormData();
                formData.append('action', 'regenerar_todos');

                progBar.style.width = '50%';
                progPct.textContent = '50%';
                progLabel.textContent = 'Actualizando activos en la base de datos…';

                const r = await fetch('../controllers/regenerarQrController.php', {
                    method: 'POST',
                    body:   formData
                });

                const text = await r.text();

                // Parsear JSON con manejo de error
                let d;
                try {
                    d = JSON.parse(text);
                } catch(parseErr) {
                    throw new Error('Respuesta inválida del servidor: ' + text.substring(0, 200));
                }

                progBar.style.width = '100%';
                progPct.textContent = '100%';

                if (d.success) {
                    progLabel.textContent = '✓ Completado sin errores';
                    progBar.style.background = '#10b981';

                    document.getElementById('statOk').textContent  = d.actualizados;
                    document.getElementById('statErr').textContent  = d.errores;

                    // Llenar log
                    const tbody = document.getElementById('logBody');
                    tbody.innerHTML = '';
                    (d.log || []).forEach(row => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-slate-50';
                        tr.innerHTML = `
                            <td class="px-4 py-2 text-slate-400">#${row.id}</td>
                            <td class="px-4 py-2 text-slate-400 line-through">${row.viejo || '—'}</td>
                            <td class="px-4 py-2 text-brand-600 font-black">${row.nuevo}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                    document.getElementById('logContainer').classList.remove('hidden');

                    btn.innerHTML = '<i class="fas fa-check text-xl"></i> ¡Completado! ' + d.actualizados + ' activos actualizados';
                    btn.style.background = '#10b981';
                    btn.disabled = false;

                } else {
                    throw new Error(d.msg || 'Error desconocido');
                }

            } catch(e) {
                progBar.style.background = '#e11d48';
                progBar.style.width = '100%';
                progLabel.textContent = '✗ Error: ' + e.message;
                progPct.textContent = '';

                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-qrcode text-xl"></i> Reintentar';
            }
        }
    </script>
</body>
</html>