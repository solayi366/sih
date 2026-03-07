<?php
require_once '../controllers/celularesController.php';
require_once '../core/Csrf.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$form = CelularesController::getDatosFormulario();
$marcas = $form['marcas'];

$estados_opciones = [
    'ASIGNADO'                   => 'Asignado',
    'EN REPOSICION'              => 'En Reposición',
    'EN PROCESO DE REASIGNACION' => 'En Proceso de Reasignación',
    'DE BAJA'                    => 'De Baja',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Celular | SIH_QR</title>
    <script>(function(){var t=localStorage.getItem('sihTheme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark');}})();</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{brand:{50:'#fff1f2',600:'#e11d48',700:'#be123c'}},fontFamily:{sans:['Plus Jakarta Sans','sans-serif']}}}}</script>
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
    <style>
        .dark .bg-white { background-color: rgba(16,14,24,0.90) !important; border-color: rgba(255,255,255,0.07) !important; }
        .dark input, .dark select, .dark textarea {
            background-color: rgba(22,18,34,0.85) !important;
            border-color: #475569 !important;
            color: #f1f5f9 !important;
        }
        .dark .text-slate-700 { color: #cbd5e1 !important; }
        .dark .bg-slate-50 { background-color: rgba(14,12,22,0.90) !important; }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative bg-slate-50/50 w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-3 sm:p-6 md:p-8 w-full">
            <div class="max-w-2xl mx-auto">

                <!-- Header -->
                <div class="flex items-center gap-3 mb-6">
                    <a href="celulares.php"
                       class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-brand-600 hover:border-brand-300 transition-all shadow-sm">
                        <i class="fas fa-arrow-left text-xs"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Nuevo Celular</h1>
                        <p class="text-slate-400 text-sm mt-0.5">Completa todos los campos del equipo.</p>
                    </div>
                </div>

                <!-- Formulario -->
                <form action="../controllers/celularesController.php?action=create" method="POST" class="space-y-5">
                    <?= Csrf::field() ?>

                    <!-- ── SECCIÓN: Equipo ───────────────────────────── -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <i class="fas fa-mobile-screen-button mr-1.5 text-brand-600"></i>Datos del Equipo
                            </p>
                        </div>
                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <!-- Línea -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Número de Línea <span class="text-brand-600">*</span>
                                </label>
                                <input type="text" name="linea" required maxlength="15"
                                       placeholder="Ej: 3102103496"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                            </div>

                            <!-- IMEI -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    IMEI <span class="text-brand-600">*</span>
                                </label>
                                <input type="text" name="imei" required maxlength="20"
                                       placeholder="Ej: 355144112747768"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                            </div>

                            <!-- Marca -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Marca <span class="text-brand-600">*</span>
                                </label>
                                <select name="id_marca_cel" id="selectMarca" required
                                        onchange="cargarModelos(this.value)"
                                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all bg-white">
                                    <option value="">Seleccione marca...</option>
                                    <?php foreach ($marcas as $m): ?>
                                    <option value="<?= $m['r_id'] ?>"><?= htmlspecialchars($m['r_nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Modelo (se carga vía AJAX según la marca) -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Modelo <span class="text-brand-600">*</span>
                                </label>
                                <select name="id_modelo_cel" id="selectModelo" required disabled
                                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all bg-white disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">Seleccione marca primero...</option>
                                </select>
                            </div>

                            <!-- Estado -->
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Estado <span class="text-brand-600">*</span>
                                </label>
                                <select name="estado" required
                                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all bg-white">
                                    <?php foreach ($estados_opciones as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $val === 'ASIGNADO' ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ── SECCIÓN: Responsable ─────────────────────── -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <i class="fas fa-user mr-1.5 text-brand-600"></i>Responsable
                            </p>
                        </div>
                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <!-- Cód. Nómina -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Código de Nómina <span class="text-brand-600">*</span>
                                </label>
                                <input type="text" name="cod_nom_responsable" id="inputCodNom" required
                                       maxlength="20" placeholder="Ej: S02574"
                                       oninput="buscarEmpleado(this.value)"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all uppercase">
                                <p id="infoEmpleado" class="mt-1.5 text-[11px] font-bold text-emerald-600 hidden"></p>
                            </div>

                            <!-- Cargo -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                                    Cargo <span class="text-brand-600">*</span>
                                </label>
                                <input type="text" name="cargo_responsable" id="inputCargo" required
                                       maxlength="120" placeholder="Se completa automáticamente o escribe el cargo"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- ── SECCIÓN: Credenciales ────────────────────── -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <i class="fas fa-lock mr-1.5 text-amber-500"></i>Credenciales
                                <span class="ml-2 text-[9px] font-bold text-amber-500 bg-amber-50 px-1.5 py-0.5 rounded">Solo admins</span>
                            </p>
                        </div>
                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">PIN</label>
                                <input type="text" name="pin" maxlength="20"
                                       placeholder="Dejar vacío si no tiene"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">PUK</label>
                                <input type="text" name="puk" maxlength="30"
                                       placeholder="Dejar vacío si se desconoce"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- ── SECCIÓN: Observaciones ───────────────────── -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <i class="fas fa-note-sticky mr-1.5 text-slate-400"></i>Observaciones
                            </p>
                        </div>
                        <div class="p-5">
                            <textarea name="observaciones" rows="3" maxlength="500"
                                      placeholder="Notas adicionales sobre el equipo (opcional)…"
                                      class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all resize-none"></textarea>
                        </div>
                    </div>

                    <!-- ── Botones ──────────────────────────────────── -->
                    <div class="flex gap-3 pb-8">
                        <a href="celulares.php"
                           class="flex-1 py-3 text-center text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-50 transition-all">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="flex-1 py-3 text-sm font-black text-white rounded-xl shadow-lg transition-all hover:scale-[1.02]"
                                style="background:linear-gradient(135deg,#e11d48,#9f1239)">
                            <i class="fas fa-plus mr-2"></i>Registrar Celular
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </main>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/dark_mode.js"></script>
    <script>
    // ── Select cascada: carga modelos según la marca ─────────────────────────
    function cargarModelos(idMarca) {
        const sel = document.getElementById('selectModelo');
        sel.innerHTML = '<option value="">Cargando...</option>';
        sel.disabled  = true;

        if (!idMarca) {
            sel.innerHTML = '<option value="">Seleccione marca primero...</option>';
            return;
        }

        fetch(`../controllers/celularParametrosController.php?action=modelos_por_marca&id_marca=${idMarca}`)
            .then(r => r.json())
            .then(data => {
                sel.innerHTML = '<option value="">Seleccione modelo...</option>';
                if (data.length === 0) {
                    sel.innerHTML = '<option value="">Sin modelos para esta marca</option>';
                } else {
                    data.forEach(m => {
                        sel.innerHTML += `<option value="${m.r_id_modelo}">${m.r_nom_modelo}</option>`;
                    });
                    sel.disabled = false;
                }
            })
            .catch(() => {
                sel.innerHTML = '<option value="">Error al cargar modelos</option>';
            });
    }

    // ── Autocompletar cargo al escribir el código de nómina ──────────────────
    let timerEmpl = null;
    function buscarEmpleado(cod) {
        clearTimeout(timerEmpl);
        const info = document.getElementById('infoEmpleado');
        if (cod.length < 3) { info.classList.add('hidden'); return; }

        timerEmpl = setTimeout(() => {
            fetch(`../controllers/get_empleado.php?id=${encodeURIComponent(cod.trim().toUpperCase())}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // cargo no está en tab_empleados: el usuario lo completa manualmente
                        info.textContent = '✓ ' + data.nombre;
                        info.classList.remove('hidden');
                    } else {
                        info.classList.add('hidden');
                    }
                })
                .catch(() => info.classList.add('hidden'));
        }, 400);
    }
    </script>
</body>
</html>
