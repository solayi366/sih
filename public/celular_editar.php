<?php
require_once '../controllers/celularesController.php';
require_once '../core/Csrf.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: celulares.php?msg=' . urlencode('ID inválido.') . '&tipo=danger'); exit(); }

$data = CelularesController::ver($id);

// CORRECCIÓN: ver() ahora retorna ['error'=>'...'] en lugar de redirigir
if (isset($data['error'])) {
    header('Location: celulares.php?msg=' . urlencode($data['error']) . '&tipo=danger');
    exit();
}

$cel          = $data['celular'];
$es_admin     = $data['es_admin'];
$credenciales = $data['credenciales'];

$form   = CelularesController::getDatosFormulario();
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
    <title>Editar Celular | SIH_QR</title>
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
        .dark input, .dark select, .dark textarea { background-color: rgba(22,18,34,0.85) !important; border-color: #475569 !important; color: #f1f5f9 !important; }
        .dark .bg-slate-50\/60 { background-color: rgba(14,12,22,0.90) !important; }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative bg-slate-50/50 w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-3 sm:p-6 md:p-8 w-full">
            <div class="max-w-2xl mx-auto">

                <!-- Header -->
                <div class="flex items-center gap-3 mb-6">
                    <a href="celular_ver.php?id=<?= $id ?>"
                       class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-brand-600 transition-all shadow-sm">
                        <i class="fas fa-arrow-left text-xs"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">
                            Editar — Línea <?= htmlspecialchars($cel['r_linea']) ?>
                        </h1>
                        <p class="text-slate-400 text-sm mt-0.5">El cambio de responsable registra historial automáticamente.</p>
                    </div>
                </div>

                <form action="../controllers/celularesController.php?action=update" method="POST" class="space-y-5">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="id_celular" value="<?= $id ?>">

                    <!-- ── Datos del equipo ──────────────────────────── -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <i class="fas fa-mobile-screen-button mr-1.5 text-brand-600"></i>Datos del Equipo
                            </p>
                        </div>
                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Número de Línea <span class="text-brand-600">*</span></label>
                                <input type="text" name="linea" required maxlength="15"
                                       value="<?= htmlspecialchars($cel['r_linea']) ?>"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">IMEI <span class="text-brand-600">*</span></label>
                                <input type="text" name="imei" required maxlength="20"
                                       value="<?= htmlspecialchars($cel['r_imei']) ?>"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Marca <span class="text-brand-600">*</span></label>
                                <select name="id_marca_cel" id="selectMarca" required
                                        onchange="cargarModelos(this.value, null)"
                                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all bg-white">
                                    <option value="">Seleccione marca...</option>
                                    <?php foreach ($marcas as $m): ?>
                                    <option value="<?= $m['r_id'] ?>" <?= $m['r_id'] == $cel['r_id_marca_cel'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['r_nombre']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Modelo <span class="text-brand-600">*</span></label>
                                <select name="id_modelo_cel" id="selectModelo" required
                                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all bg-white">
                                    <option value="<?= $cel['r_id_modelo_cel'] ?>" selected><?= htmlspecialchars($cel['r_modelo']) ?></option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Estado <span class="text-brand-600">*</span></label>
                                <select name="estado" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all bg-white">
                                    <?php foreach ($estados_opciones as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $cel['r_estado'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ── Responsable ───────────────────────────────── -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <i class="fas fa-user mr-1.5 text-brand-600"></i>Responsable
                                <span class="ml-2 text-[9px] font-bold text-blue-500 bg-blue-50 px-1.5 py-0.5 rounded">Cambio genera historial</span>
                            </p>
                        </div>
                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Código de Nómina <span class="text-brand-600">*</span></label>
                                <input type="text" name="cod_nom_responsable" id="inputCodNom" required
                                       maxlength="20" value="<?= htmlspecialchars($cel['r_cod_nom']) ?>"
                                       oninput="buscarEmpleado(this.value)"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                                <p id="infoEmpleado" class="mt-1.5 text-[11px] font-bold text-emerald-600 hidden"></p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cargo <span class="text-brand-600">*</span></label>
                                <input type="text" name="cargo_responsable" required
                                       maxlength="120" value="<?= htmlspecialchars($cel['r_cargo']) ?>"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- ── Credenciales (solo admin) ─────────────────── -->
                    <?php if ($es_admin): ?>
                    <div class="bg-white rounded-2xl border border-amber-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-amber-100 bg-amber-50/60">
                            <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">
                                <i class="fas fa-lock mr-1.5"></i>Credenciales
                                <span class="ml-2 text-[9px] font-bold text-amber-500">Vacío = sin cambio</span>
                            </p>
                        </div>
                        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">PIN</label>
                                <input type="text" name="pin" maxlength="20"
                                       value="<?= htmlspecialchars($credenciales['r_pin'] ?? '') ?>"
                                       placeholder="Vacío = sin cambio"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-amber-400 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">PUK</label>
                                <input type="text" name="puk" maxlength="30"
                                       value="<?= htmlspecialchars($credenciales['r_puk'] ?? '') ?>"
                                       placeholder="Vacío = sin cambio"
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-amber-400 outline-none transition-all">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ── Observaciones ─────────────────────────────── -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <i class="fas fa-note-sticky mr-1.5"></i>Observaciones
                            </p>
                        </div>
                        <div class="p-5">
                            <textarea name="observaciones" rows="3" maxlength="500"
                                      class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all resize-none"><?= htmlspecialchars($cel['r_observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3 pb-8">
                        <a href="celular_ver.php?id=<?= $id ?>"
                           class="flex-1 py-3 text-center text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-50 transition-all">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="flex-1 py-3 text-sm font-black text-white rounded-xl shadow-lg transition-all hover:scale-[1.02]"
                                style="background:linear-gradient(135deg,#2563eb,#1d4ed8)">
                            <i class="fas fa-save mr-2"></i>Guardar Cambios
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
    const ID_MODELO_ACTUAL = <?= (int)$cel['r_id_modelo_cel'] ?>;

    window.addEventListener('DOMContentLoaded', () => {
        const idMarca = document.getElementById('selectMarca').value;
        if (idMarca) cargarModelos(idMarca, ID_MODELO_ACTUAL);
    });

    function cargarModelos(idMarca, preseleccionarId = null) {
        const sel = document.getElementById('selectModelo');
        if (!idMarca) { sel.innerHTML = '<option value="">Seleccione marca primero...</option>'; return; }
        sel.innerHTML = '<option value="">Cargando...</option>';
        sel.disabled  = true;

        fetch(`../controllers/celularParametrosController.php?action=modelos_por_marca&id_marca=${idMarca}`)
            .then(r => r.json())
            .then(data => {
                sel.innerHTML = '<option value="">Seleccione modelo...</option>';
                data.forEach(m => {
                    const sel2 = (preseleccionarId && m.r_id_modelo == preseleccionarId) ? 'selected' : '';
                    sel.innerHTML += `<option value="${m.r_id_modelo}" ${sel2}>${m.r_nom_modelo}</option>`;
                });
                sel.disabled = data.length === 0;
                if (data.length === 0) sel.innerHTML = '<option value="">Sin modelos</option>';
            })
            .catch(() => sel.innerHTML = '<option value="">Error al cargar</option>');
    }

    let timerEmpl = null;
    function buscarEmpleado(cod) {
        clearTimeout(timerEmpl);
        const info = document.getElementById('infoEmpleado');
        if (cod.length < 3) { info.classList.add('hidden'); return; }
        timerEmpl = setTimeout(() => {
            fetch(`../controllers/get_empleado.php?id=${encodeURIComponent(cod.trim().toUpperCase())}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) { info.textContent = '✓ ' + data.nombre; info.classList.remove('hidden'); }
                    else info.classList.add('hidden');
                }).catch(() => info.classList.add('hidden'));
        }, 400);
    }
    </script>
</body>
</html>
