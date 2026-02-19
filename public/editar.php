<?php
require_once '../controllers/activoEditarController.php';
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = ActivoEditarController::getFormData($id);
$a    = $data['activo']; // alias corto para la vista
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Activo #<?= $a['r_id'] ?> | SIH_QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <link rel="stylesheet" href="../assets/css/custom.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#fff1f2', 100: '#ffe4e6', 600: '#e11d48', 700: '#be123c', 900: '#4c0519' }
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>

    <style>
        body { background-color: #f8fafc; }
        .red-gradient { background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%); }
        .input-group-ruby {
            display: flex; align-items: stretch; border: 2px solid #cbd5e1; border-radius: 0.75rem;
            overflow: hidden; transition: all 0.3s; background: #fff;
        }
        .input-group-ruby:focus-within { border-color: #e11d48; box-shadow: 0 0 0 4px rgba(225,29,72,0.1); }
        .input-icon-box {
            display: flex; align-items: center; justify-content: center; width: 3rem;
            border-right: 2px solid #e2e8f0; color: #94a3b8; cursor: pointer;
        }
        .input-ruby { flex: 1; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 700; outline: none; background: transparent; }
        .label-ruby { font-size: 0.6875rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 0.5rem; display: block; }
    </style>
</head>

<body class="text-slate-800 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50 w-full">
        <div class="flex-1 overflow-y-auto p-4 md:p-10 scroll-smooth w-full">

            <form action="../controllers/activoEditarController.php"
                  method="POST"
                  id="formEditar"
                  class="max-w-6xl mx-auto space-y-8">

                <!-- Campo oculto: ID del activo -->
                <input type="hidden" name="id_activo"  value="<?= $a['r_id'] ?>">
                <input type="hidden" name="codigo_qr"  value="<?= htmlspecialchars($a['r_qr'] ?? '') ?>">

                <!-- CABECERA -->
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-10 bg-brand-600 rounded-full shadow-[0_0_10px_#e11d48]"></div>
                        <div>
                            <h1 class="text-2xl font-black text-slate-900 tracking-tighter uppercase">
                                Editar <span class="text-brand-600">Activo #<?= $a['r_id'] ?></span>
                            </h1>
                            <p class="text-slate-400 text-xs font-bold mt-0.5 font-mono">
                                <?= htmlspecialchars($a['r_qr'] ?? '') ?>
                            </p>
                        </div>
                    </div>
                    <a href="ver.php?id=<?= $a['r_id'] ?>"
                       class="px-5 py-2.5 bg-white border-2 border-slate-200 text-slate-600 rounded-xl font-black text-xs hover:border-slate-400 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> VOLVER A LA FICHA
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                    <!-- ════ COLUMNA PRINCIPAL (8 cols) ════ -->
                    <div class="lg:col-span-8 space-y-6">

                        <!-- Datos del Equipo -->
                        <div class="bg-white rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-sm">
                            <h3 class="label-ruby !mb-8 pb-2 border-b-2 border-slate-50">Datos del Equipo</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div>
                                    <label class="label-ruby">Tipo de Equipo *</label>
                                    <div class="input-group-ruby">
                                        <select class="input-ruby cursor-pointer" name="id_tipoequi" id="selectTipo" required onchange="toggleCampos()">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($data['tipos'] as $t): ?>
                                            <option value="<?= $t['id_tipoequi'] ?>"
                                                    data-nombre="<?= htmlspecialchars($t['nom_tipo']) ?>"
                                                    <?= ($t['nom_tipo'] === $a['r_tipo']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t['nom_tipo']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="label-ruby">Marca *</label>
                                    <div class="input-group-ruby">
                                        <select class="input-ruby cursor-pointer" name="id_marca" id="selectMarca" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($data['marcas'] as $m): ?>
                                            <option value="<?= $m['id_marca'] ?>"
                                                    <?= ($m['nom_marca'] === $a['r_marca']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($m['nom_marca']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div id="col-modelo">
                                    <label class="label-ruby">Modelo</label>
                                    <div class="input-group-ruby">
                                        <select class="input-ruby cursor-pointer" name="id_modelo" id="selector-modelo">
                                            <option value="">(Genérico)</option>
                                            <?php foreach ($data['modelos'] as $mod): ?>
                                            <option value="<?= $mod['id_modelo'] ?>"
                                                    data-tipo="<?= $mod['id_tipoequi'] ?>"
                                                    <?= ($mod['nom_modelo'] === $a['r_modelo']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($mod['nom_modelo']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div id="col-referencia">
                                    <label class="label-ruby">Referencia</label>
                                    <div class="input-group-ruby">
                                        <input type="text" name="referencia" class="input-ruby"
                                               placeholder="Referencia técnica"
                                               value="<?= htmlspecialchars($a['r_referencia'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="label-ruby text-brand-600">Serial del Fabricante (S/N) *</label>
                                    <div class="input-group-ruby border-brand-100">
                                        <input type="text" name="serial" class="input-ruby font-mono uppercase text-base"
                                               placeholder="serial..."
                                               value="<?= htmlspecialchars($a['r_serial'] ?? '') ?>"
                                               required>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Red y Conectividad -->
                        <div id="grupo-redes" class="bg-white rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-sm" style="display:none;">
                            <h3 class="label-ruby !text-brand-600 mb-6">
                                <i class="fas fa-network-wired"></i> Red y Conectividad
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div id="grupo-hostname">
                                    <label class="label-ruby">Hostname</label>
                                    <input type="text" id="input-hostname" name="hostname"
                                           class="input-group-ruby px-4 py-2.5 text-xs font-bold font-mono outline-none"
                                           value="<?= htmlspecialchars($a['r_hostname'] ?? '') ?>">
                                </div>
                                <div>
                                    <label class="label-ruby">IP</label>
                                    <input type="text" id="input-ip" name="ip_equipo"
                                           class="input-group-ruby px-4 py-2.5 text-xs font-bold font-mono outline-none"
                                           placeholder="0.0.0.0"
                                           value="<?= htmlspecialchars($a['r_ip'] ?? '') ?>">
                                </div>
                                <div>
                                    <label class="label-ruby">MAC</label>
                                    <input type="text" id="input-mac" name="mac_activo"
                                           class="input-group-ruby px-4 py-2.5 text-xs font-bold font-mono outline-none"
                                           placeholder="00:00:00..."
                                           value="<?= htmlspecialchars($a['r_mac'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Información de solo lectura -->
                        <div class="bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200 p-6">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4">
                                <i class="fas fa-lock mr-1"></i> Datos de Solo Lectura
                            </p>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="label-ruby">ID Interno</span>
                                    <span class="font-mono font-black text-slate-500 text-sm">#<?= $a['r_id'] ?></span>
                                </div>
                                <div>
                                    <span class="label-ruby">Código QR</span>
                                    <span class="font-mono font-black text-brand-600 text-sm">
                                        <?= htmlspecialchars($a['r_qr'] ?? '—') ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ════ COLUMNA LATERAL (4 cols) ════ -->
                    <div class="lg:col-span-4 space-y-6">

                        <!-- Asignación / Responsable -->
                        <div class="bg-white rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-sm">
                            <h3 class="label-ruby !text-slate-400 mb-8 border-b border-slate-50 pb-2">Asignación</h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="label-ruby">Cédula Responsable</label>
                                    <div class="input-group-ruby">
                                        <button type="button" class="input-icon-box" onclick="buscarEmpleado()">
                                            <i class="fas fa-search" id="icon-search"></i>
                                        </button>
                                        <input type="text" name="cod_responsable" id="input-responsable"
                                               class="input-ruby"
                                               placeholder="ID Empleado"
                                               value="<?= htmlspecialchars($a['r_cod_responsable'] ?? '') ?>">
                                    </div>
                                    <div id="alerta-empleado"
                                         class="mt-3 p-3 bg-brand-50 rounded-xl border border-brand-100 text-[10px] font-bold text-brand-700
                                                <?= $a['r_responsable'] ? '' : 'hidden' ?>">
                                        <?php if ($a['r_responsable']): ?>
                                        <i class="fas fa-check-circle"></i>
                                        Verificado: <?= htmlspecialchars($a['r_responsable']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <input type="text" name="nom_nuevo_empleado" id="nom_nuevo_empleado"
                                       class="input-group-ruby w-full px-4 py-3 text-sm font-bold outline-none"
                                       placeholder="Nombre completo"
                                       value="<?= htmlspecialchars($a['r_responsable'] ?? '') ?>"
                                       <?= $a['r_responsable'] ? 'readonly' : '' ?>>

                                <select name="id_area_nuevo" id="id_area_nuevo"
                                        class="input-group-ruby w-full px-4 py-3 text-sm font-bold outline-none">
                                    <option value="">Seleccione Área...</option>
                                    <?php foreach ($data['areas'] as $area): ?>
                                    <option value="<?= $area['id_area'] ?>"
                                            <?= ($area['nom_area'] === $a['r_area']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($area['nom_area']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Equipo Padre (para periféricos) -->
                        <div id="grupo-padre" class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl border-2 border-slate-800">
                            <label class="label-ruby !text-brand-500">Equipo Principal (Padre)</label>
                            <select name="id_padre_activo"
                                    class="w-full bg-white/5 border-2 border-white/10 rounded-xl py-3 px-4 text-xs font-bold outline-none">
                                <option value="">No, es equipo principal</option>
                                <?php foreach ($data['padres'] as $p): ?>
                                <?php if ($p['id_activo'] === $a['r_id']) continue; // No listarse a sí mismo ?>
                                <option value="<?= $p['id_activo'] ?>"
                                        class="text-slate-800"
                                        <?= ($p['id_activo'] === $a['r_id_padre']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['serial'] ?? 'S/N') ?>
                                    (<?= htmlspecialchars($p['referencia'] ?? '—') ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="bg-white rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-sm">
                            <label class="label-ruby">Estado del Activo</label>
                            <div class="input-group-ruby">
                                <select name="estado" class="input-ruby">
                                    <option value="Bueno"      <?= ($a['r_estado'] === 'Bueno')      ? 'selected' : '' ?>>Bueno</option>
                                    <option value="Malo"       <?= ($a['r_estado'] === 'Malo')       ? 'selected' : '' ?>>Malo</option>
                                    <option value="Reparacion" <?= ($a['r_estado'] === 'Reparacion') ? 'selected' : '' ?>>En Reparación</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botón guardar -->
                        <button type="button" onclick="confirmarGuardar()"
                                class="w-full py-5 red-gradient text-white rounded-[2rem] font-black uppercase shadow-xl hover:scale-[1.02] transition-all flex items-center justify-center gap-3">
                            <i class="fas fa-save text-xl"></i> GUARDAR CAMBIOS
                        </button>

                        <!-- Botón cancelar -->
                        <a href="ver.php?id=<?= $a['r_id'] ?>"
                           class="w-full py-4 flex items-center justify-center gap-2 bg-white border-2 border-slate-200 text-slate-500 rounded-[2rem] font-black text-xs uppercase hover:border-slate-400 transition-all">
                            <i class="fas fa-times"></i> CANCELAR
                        </a>

                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Toast de notificaciones -->
    <div id="liveToast" class="fixed bottom-8 right-8 translate-y-24 opacity-0 transition-all duration-700 z-[100]">
        <div class="bg-slate-900 text-white rounded-3xl p-5 shadow-2xl flex items-center gap-4 min-w-[320px] border-2 border-white/10">
            <div id="toastIcon" class="w-12 h-12 rounded-2xl red-gradient flex items-center justify-center text-xl shadow-lg shadow-brand-500/20"></div>
            <div>
                <p id="toastMessage" class="font-bold text-sm tracking-tight"></p>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script>
        // ── Mostrar/ocultar sección de redes y padre según tipo ──────────────
        function toggleCampos() {
            const select     = document.getElementById('selectTipo');
            const nombreTipo = (select.options[select.selectedIndex]?.getAttribute('data-nombre') || '').toUpperCase();
            const esPC       = ['TABLET','COMPUTADOR','PORTATIL','PC','SERVIDOR','AIO'].some(t => nombreTipo.includes(t));
            document.getElementById('grupo-redes').style.display = esPC ? 'block' : 'none';
            document.getElementById('grupo-padre').style.display = esPC ? 'none'  : 'block';
        }

        // ── Buscar empleado por cédula (igual que en crear_activo) ───────────
        async function buscarEmpleado() {
            const id = document.getElementById('input-responsable').value.trim();
            if (!id) return;
            document.getElementById('icon-search').className = 'fas fa-spinner fa-spin';
            try {
                const res = await fetch(`../controllers/get_empleado.php?id=${id}`);
                const d   = await res.json();
                const alerta = document.getElementById('alerta-empleado');
                if (d.success) {
                    document.getElementById('nom_nuevo_empleado').value    = d.nombre;
                    document.getElementById('id_area_nuevo').value         = d.id_area;
                    document.getElementById('nom_nuevo_empleado').readOnly = true;
                    alerta.innerHTML   = `<i class='fas fa-check-circle'></i> Verificado: ${d.nombre}`;
                    alerta.classList.remove('hidden');
                } else {
                    document.getElementById('nom_nuevo_empleado').value    = '';
                    document.getElementById('nom_nuevo_empleado').readOnly = false;
                    alerta.innerHTML   = 'Custodio nuevo — complete el nombre y área';
                    alerta.classList.remove('hidden');
                }
            } finally {
                document.getElementById('icon-search').className = 'fas fa-search';
            }
        }

        // ── Confirmar antes de guardar ────────────────────────────────────────
        function confirmarGuardar() {
            document.getElementById('formEditar').submit();
        }

        // ── Toast de mensajes GET ─────────────────────────────────────────────
        window.addEventListener('load', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.has('msg')) {
                const toast   = document.getElementById('liveToast');
                const message = document.getElementById('toastMessage');
                const icon    = document.getElementById('toastIcon');
                const tipo    = params.get('tipo');

                message.textContent = params.get('msg');
                icon.innerHTML = tipo === 'success'
                    ? '<i class="fas fa-check"></i>'
                    : '<i class="fas fa-exclamation text-brand-200"></i>';

                toast.classList.remove('translate-y-24', 'opacity-0');
                setTimeout(() => toast.classList.add('translate-y-24', 'opacity-0'), 6000);
                window.history.replaceState({}, document.title, window.location.pathname + '?id=<?= $a['r_id'] ?>');
            }

            // Inicializar estado del formulario según el tipo actual
            toggleCampos();
        });
    </script>
</body>
</html>