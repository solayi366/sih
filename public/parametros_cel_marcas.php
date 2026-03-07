<?php
require_once '../controllers/celularParametrosController.php';
require_once '../core/Csrf.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$marcas = CelularParametrosController::getMarcas();
$total  = count($marcas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcas Celular | SIH_QR</title>
    <script>(function(){var t=localStorage.getItem('sihTheme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark');}})();</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{brand:{50:'#fff1f2',600:'#e11d48',700:'#be123c'}},fontFamily:{sans:['Plus Jakarta Sans','sans-serif']}}}}</script>
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
    <style>
        .dark .bg-white { background-color:rgba(16,14,24,0.90)!important; border-color:rgba(255,255,255,0.07)!important; }
        .dark .divide-y.divide-slate-100>*+* { border-color:rgba(255,255,255,0.07)!important; }
        .dark input, .dark select { background-color:rgba(22,18,34,0.85)!important; border-color:#475569!important; color:#f1f5f9!important; }
        .dark .hover\:bg-slate-50:hover { background-color:rgba(22,18,34,0.85)!important; }
        .dark .bg-slate-50 { background-color:rgba(14,12,22,0.90)!important; }
        .modal-overlay { transition: opacity .2s; }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative bg-slate-50/50 w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-3 sm:p-6 md:p-8 w-full">
            <div class="max-w-2xl mx-auto">

                <!-- Header -->
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Marcas de Celular</h1>
                        <p class="text-slate-500 text-sm mt-0.5"><?= $total ?> marca<?= $total !== 1 ? 's' : '' ?> registrada<?= $total !== 1 ? 's' : '' ?>.</p>
                    </div>
                    <button onclick="abrirModal('modalCrear')"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs text-white shadow-lg hover:scale-105 transition-all shrink-0"
                            style="background:linear-gradient(135deg,#e11d48,#9f1239)">
                        <i class="fas fa-plus"></i>
                        <span class="hidden sm:inline">Nueva Marca</span>
                    </button>
                </div>

                <!-- Buscador -->
                <div class="mb-4">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="buscadorMarcas" placeholder="Buscar marca..."
                               class="w-full pl-9 pr-4 py-2.5 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all bg-white">
                    </div>
                </div>

                <p class="text-xs font-bold text-slate-400 mb-3" id="contadorMarcas"></p>

                <!-- Lista -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="hidden md:grid grid-cols-[50px_1fr_90px_90px_80px] gap-4 px-5 py-3 bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <span>ID</span><span>Marca</span><span class="text-center">Modelos</span><span class="text-center">Estado</span><span class="text-right">Acción</span>
                    </div>

                    <div id="listaMarcas" class="divide-y divide-slate-100">
                        <?php if (empty($marcas)): ?>
                        <div class="py-16 text-center text-slate-400">
                            <i class="fas fa-tag text-3xl mb-3 opacity-30 block"></i>
                            <p class="text-sm font-bold">No hay marcas registradas</p>
                        </div>
                        <?php endif; ?>

                        <?php foreach ($marcas as $m): ?>
                        <div class="fila-marca flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors"
                             data-nombre="<?= strtolower(htmlspecialchars($m['r_nombre'])) ?>">

                            <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-mobile-screen-button text-brand-600 text-sm"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($m['r_nombre']) ?></p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] font-mono text-brand-600">#<?= str_pad($m['r_id'], 3, '0', STR_PAD_LEFT) ?></span>
                                    <span class="text-[10px] font-bold text-slate-400">
                                        <?= $m['r_modelos'] ?> modelo<?= $m['r_modelos'] != 1 ? 's' : '' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Estado badge — fun_read_marcas_cel solo retorna activas -->
                            <div class="shrink-0">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-600">Activa</span>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <button onclick="abrirEdicion(<?= $m['r_id'] ?>, '<?= addslashes($m['r_nombre']) ?>')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-all"
                                        title="Editar">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>

                                <button onclick="Alerts.confirmDelete('../controllers/celularParametrosController.php?ent=marca_cel&action=delete&id=<?= $m['r_id'] ?>', 'Se desactivará la marca y sus modelos asociados.')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all"
                                        title="Desactivar">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="sinResultados" class="hidden py-16 text-center text-slate-400">
                        <i class="fas fa-search text-3xl mb-3 opacity-30"></i>
                        <p class="text-sm font-bold">Sin resultados</p>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Crear -->
    <div id="modalCrear" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Nueva Marca de Celular</h3>
                <button onclick="cerrarModal('modalCrear')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <form action="../controllers/celularParametrosController.php?ent=marca_cel&action=create" method="POST">
                <?= Csrf::field() ?>
                <div class="p-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre de la Marca</label>
                    <input type="text" name="nom_marca" required placeholder="Ej: SAMSUNG, NOKIA, MOTOROLA..."
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                </div>
                <div class="bg-slate-50 px-5 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalCrear')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl" style="background:linear-gradient(135deg,#e11d48,#9f1239)">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar -->
    <div id="modalEdicion" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Editar Marca</h3>
                <button onclick="cerrarModal('modalEdicion')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <form action="../controllers/celularParametrosController.php?ent=marca_cel&action=update" method="POST">
                <?= Csrf::field() ?>
                <input type="hidden" name="id_marca_cel" id="editId">
                <div class="p-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre de la Marca</label>
                    <input type="text" name="nom_marca" id="editNombre" required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                </div>
                <div class="bg-slate-50 px-5 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalEdicion')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl" style="background:linear-gradient(135deg,#e11d48,#9f1239)">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/dark_mode.js"></script>
    <script>
    // ── Modales ───────────────────────────────────────────────────────────────
    function abrirModal(id)  { document.getElementById(id).classList.remove('hidden'); }
    function cerrarModal(id) { document.getElementById(id).classList.add('hidden'); }
    document.querySelectorAll('.modal-overlay').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) cerrarModal(m.id); });
    });

    function abrirEdicion(id, nombre) {
        document.getElementById('editId').value     = id;
        document.getElementById('editNombre').value = nombre;
        abrirModal('modalEdicion');
    }

    // ── Restaurar marca (AJAX POST) ───────────────────────────────────────────
    function restaurarMarca(id, nombre) {
        Alerts.confirmHtml(
            '¿Reactivar marca?',
            `Se reactivará <strong>${nombre}</strong>.<br><small class="text-slate-400">Sus modelos deben reactivarse manualmente desde Modelos.</small>`,
            'Sí, reactivar',
            '#10b981',
            'question'
        ).then(confirmed => {
            if (!confirmed) return;
            const fd = new FormData();
            fd.append('id_marca_cel', id);
            fd.append('action', 'restore_marca');
            fetch('../controllers/celularParametrosController.php?action=restore_marca', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    Alerts.toast.fire({ icon: data.success ? 'success' : 'error', title: data.msg });
                    if (data.success) setTimeout(() => location.reload(), 1200);
                })
                .catch(() => Alerts.toast.fire({ icon: 'error', title: 'Error de red' }));
        });
    }

    // ── Filtro buscador ───────────────────────────────────────────────────────
    const todasFilas = Array.from(document.querySelectorAll('.fila-marca'));

    document.getElementById('buscadorMarcas').addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        let visible = 0;
        todasFilas.forEach(f => {
            const ok = !q || f.dataset.nombre.includes(q);
            f.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });
        document.getElementById('sinResultados').classList.toggle('hidden', visible > 0);
        document.getElementById('contadorMarcas').textContent =
            q ? `${visible} resultado${visible !== 1 ? 's' : ''} para "${q}"` : `${visible} marca${visible !== 1 ? 's' : ''}`;
    });

    document.getElementById('contadorMarcas').textContent =
        `${todasFilas.length} marca${todasFilas.length !== 1 ? 's' : ''}`;
    </script>
    <script src="../assets/js/alerts.js"></script>
</body>
</html>
