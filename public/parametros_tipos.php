<?php
require_once '../controllers/parametrosController.php';
$tipos  = ParametrosController::getTipos();
$campos = ParametrosController::getTodosCampos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de Equipo | SIH_QR</title>
    <script>(function(){var t=localStorage.getItem('sihTheme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark');}})();</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{brand:{50:'#fff1f2',100:'#ffe4e6',600:'#e11d48',700:'#be123c',900:'#881337'}},fontFamily:{sans:['Plus Jakarta Sans','sans-serif']}}}}</script>
    <style>
        .campo-card { transition: all .2s; }
        .campo-card.activo-tipo { border-color: #e11d48 !important; background: #fff1f2; }
        .dark .campo-card.activo-tipo { background: rgba(225,29,72,0.12) !important; }
        .badge-activo { display: none; }
        .pill-tipo { font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; padding:2px 8px; border-radius:999px; }
        .tipo-row { cursor: pointer; transition: background .15s; }
        .tipo-row.selected { background: #fff1f2 !important; }
        .dark .tipo-row.selected { background: rgba(225,29,72,0.12) !important; }
        .tipo-row.selected .tipo-border { border-left: 3px solid #e11d48; }
    </style>
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">
    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative bg-slate-50/50 w-full">
        <div class="flex-1 overflow-y-auto p-4 md:p-6 w-full">
            <div class="max-w-[1300px] mx-auto">

                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                    <div>
                        <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">Tipos de Equipo</h1>
                        <p class="text-slate-500 text-sm mt-0.5">Configura los campos de cada categoría.</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="abrirModal('modalNuevoCampo')"
                                class="flex items-center gap-2 px-3 py-2.5 rounded-xl font-black text-xs tracking-widest border-2 border-slate-200 text-slate-600 hover:border-brand-600 hover:text-brand-600 transition-all">
                            <i class="fas fa-sliders"></i>
                            <span class="hidden sm:inline">Nuevo Campo</span>
                        </button>
                        <button onclick="abrirModal('modalCrear')"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs tracking-widest text-white shadow-lg hover:scale-105 transition-all"
                                style="background:linear-gradient(135deg,#e11d48,#9f1239)">
                            <i class="fas fa-plus"></i>
                            <span class="hidden sm:inline">Añadir Tipo</span>
                            <span class="sm:hidden">Nuevo</span>
                        </button>
                    </div>
                </div>

                <!-- Alert URL -->
                <?php if (isset($_GET['msg'])): ?>
                <div class="mb-4 px-4 py-3 rounded-xl text-sm font-bold <?= $_GET['tipo']==='success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                    <i class="fas <?= $_GET['tipo']==='success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($_GET['msg']) ?>
                </div>
                <?php endif; ?>

                <!-- Tabs móvil -->
                <div class="flex lg:hidden gap-1 mb-4 bg-white rounded-2xl border border-slate-200 p-1 shadow-sm">
                    <button id="tab-tipos" onclick="switchTab('tipos')"
                            class="flex-1 py-2 rounded-xl text-xs font-black transition-all bg-slate-900 text-white">
                        <i class="fas fa-list mr-1"></i> Tipos
                    </button>
                    <button id="tab-campos" onclick="switchTab('campos')"
                            class="flex-1 py-2 rounded-xl text-xs font-black transition-all text-slate-500">
                        <i class="fas fa-puzzle-piece mr-1"></i> Campos
                    </button>
                </div>

                <!-- Layout 2 col en desktop, tabs en móvil -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

                    <!-- Col izq: Lista de tipos -->
                    <div id="panel-tipos" class="lg:col-span-4">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Categorías</span>
                                <span class="pill-tipo bg-slate-100 text-slate-500"><?= count($tipos) ?> tipos</span>
                            </div>
                            <div class="divide-y divide-slate-100">
                                <?php foreach ($tipos as $i => $t): ?>
                                <div class="tipo-row tipo-border flex items-center gap-3 px-4 py-3.5 <?= $i===0 ? 'selected' : '' ?>"
                                     data-id="<?= $t['r_id'] ?>"
                                     data-nombre="<?= htmlspecialchars($t['r_nombre']) ?>"
                                     onclick="seleccionarTipo(<?= $t['r_id'] ?>, '<?= htmlspecialchars($t['r_nombre']) ?>')">
                                    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center shrink-0">
                                        <i class="fas fa-microchip text-brand-600 text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-black text-slate-800 text-sm truncate"><?= htmlspecialchars($t['r_nombre']) ?></p>
                                        <p class="text-[10px] text-slate-400 font-mono">#<?= str_pad($t['r_id'],3,'0',STR_PAD_LEFT) ?> · <span id="conteo-<?= $t['r_id'] ?>" class="text-brand-600">...</span></p>
                                    </div>
                                    <div class="flex gap-1 shrink-0">
                                        <button onclick="event.stopPropagation(); abrirEdicion(<?= $t['r_id'] ?>, '<?= htmlspecialchars($t['r_nombre']) ?>')"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:text-brand-600 hover:bg-brand-50 transition-all">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button onclick="event.stopPropagation(); Alerts.confirmDelete('../controllers/parametrosController.php?ent=tipo&action=delete&id=<?= $t['r_id'] ?>')"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <?php if (empty($tipos)): ?>
                                <div class="py-12 text-center text-slate-400">
                                    <i class="fas fa-microchip text-3xl mb-2 opacity-30"></i>
                                    <p class="text-xs font-bold">Crea un tipo de equipo primero</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Col der: Configurador de campos -->
                    <div id="panel-campos" class="hidden lg:block lg:col-span-8">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100 bg-slate-900 flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Configurando campos para</p>
                                    <h2 class="text-white font-black text-base" id="titulo-tipo-activo">—</h2>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] text-slate-500 font-bold">Activos: <span id="count-activos" class="text-brand-500">0</span></span>
                                    <i class="fas fa-puzzle-piece text-slate-600 text-lg"></i>
                                </div>
                            </div>

                            <div id="panel-loading" class="p-12 text-center">
                                <i class="fas fa-spinner fa-spin text-3xl text-slate-300 mb-3"></i>
                                <p class="text-slate-400 text-sm font-bold">Cargando campos...</p>
                            </div>

                            <div id="panel-campos-inner" class="hidden p-5">
                                <div class="mb-5">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Campos Base</span>
                                        <div class="flex-1 h-px bg-slate-100"></div>
                                        <span class="pill-tipo bg-slate-100 text-slate-400">No eliminables</span>
                                    </div>
                                    <div id="grid-campos-base" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Campos Personalizados</span>
                                        <div class="flex-1 h-px bg-slate-100"></div>
                                        <button onclick="abrirModal('modalNuevoCampo')" class="text-[10px] font-black text-brand-600 hover:underline">+ Crear nuevo</button>
                                    </div>
                                    <div id="grid-campos-extra" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
                                    <div id="sin-campos-extra" class="hidden text-center py-8 text-slate-300">
                                        <i class="fas fa-puzzle-piece text-3xl mb-2"></i>
                                        <p class="text-xs font-bold">No hay campos personalizados.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- ════ MODALES ════ -->

    <!-- Crear tipo -->
    <div id="modalCrear" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-container bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Nuevo Tipo</h3>
                <button onclick="cerrarModal('modalCrear')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=tipo&action=create" method="POST">
                <div class="p-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre del Dispositivo</label>
                    <input type="text" name="nom_tipo" placeholder="Ej: Celular, Drone, Cámara IP..." required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                </div>
                <div class="bg-slate-50 px-5 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalCrear')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl" style="background:linear-gradient(135deg,#e11d48,#9f1239)">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Editar tipo -->
    <div id="modalEdicion" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-container bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Editar Tipo</h3>
                <button onclick="cerrarModal('modalEdicion')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=tipo&action=update" method="POST">
                <input type="hidden" name="id_tipo" id="edit_id">
                <div class="p-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre</label>
                    <input type="text" name="nom_tipo" id="edit_nombre" required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                </div>
                <div class="bg-slate-50 px-5 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalEdicion')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl" style="background:linear-gradient(135deg,#e11d48,#9f1239)">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Nuevo campo personalizado -->
    <div id="modalNuevoCampo" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-container bg-white w-full sm:max-w-lg sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-white text-xs font-black tracking-widest">Crear Campo Personalizado</h3>
                    <p class="text-slate-500 text-[10px] mt-0.5">Disponible para asignar a cualquier tipo</p>
                </div>
                <button onclick="cerrarModal('modalNuevoCampo')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <div class="overflow-y-auto flex-1 p-5 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Etiqueta (visible)</label>
                        <input type="text" id="nc_etiqueta" placeholder="Ej: IMEI, Versión SO..."
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold focus:border-brand-600 outline-none transition-all"
                               oninput="autoNombre()">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre técnico</label>
                        <input type="text" id="nc_nombre" placeholder="imei_dispositivo"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold font-mono focus:border-brand-600 outline-none transition-all">
                        <p class="text-[10px] text-slate-400 mt-1">Solo letras, números y _</p>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipo de Dato</label>
                    <div class="grid grid-cols-5 gap-2" id="selector-tipo-dato">
                        <?php
                        $tipos_dato=['texto'=>['fa-font','Texto'],'numero'=>['fa-hashtag','Número'],'booleano'=>['fa-toggle-on','Sí/No'],'fecha'=>['fa-calendar-alt','Fecha'],'lista'=>['fa-list','Lista']];
                        foreach($tipos_dato as $val=>$info):
                        ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="nc_tipo_dato" value="<?= $val ?>" class="sr-only" <?= $val==='texto'?'checked':'' ?> onchange="toggleOpcionesLista()">
                            <div class="tipo-dato-pill border-2 border-slate-200 rounded-xl p-2 text-center hover:border-brand-600 transition-all <?= $val==='texto'?'border-brand-600 bg-brand-50':'' ?>" data-val="<?= $val ?>">
                                <i class="fas <?= $info[0] ?> text-slate-400 text-base mb-1 block"></i>
                                <span class="text-[9px] font-black text-slate-500"><?= $info[1] ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div id="bloque-opciones-lista" class="hidden">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Opciones (una por línea)</label>
                    <textarea id="nc_opciones" rows="3" placeholder="Opción 1&#10;Opción 2&#10;Opción 3"
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-mono focus:border-brand-600 outline-none resize-none transition-all"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Ícono</label>
                    <div class="flex gap-2 flex-wrap" id="selector-icono">
                        <?php
                        $iconos=['fa-tag','fa-mobile-alt','fa-sim-card','fa-wifi','fa-memory','fa-hdd','fa-microchip','fa-calendar-alt','fa-battery-full','fa-camera','fa-cube','fa-code','fa-globe','fa-info-circle'];
                        foreach($iconos as $ic):
                        ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="nc_icono" value="<?= $ic ?>" class="sr-only" <?= $ic==='fa-tag'?'checked':'' ?>>
                            <div class="icono-pill w-9 h-9 border-2 border-slate-200 rounded-xl flex items-center justify-center hover:border-brand-600 transition-all <?= $ic==='fa-tag'?'border-brand-600 bg-brand-50':'' ?>" data-ic="<?= $ic ?>">
                                <i class="fas <?= $ic ?> text-slate-400 text-sm"></i>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-5 py-4 flex gap-3 shrink-0">
                <button type="button" onclick="cerrarModal('modalNuevoCampo')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                <button type="button" onclick="crearCampo()" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl" style="background:linear-gradient(135deg,#e11d48,#9f1239)">
                    <i class="fas fa-plus mr-1"></i> Crear Campo
                </button>
            </div>
        </div>
    </div>

    <!-- Config campo -->
    <div id="modalCampoConfig" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-container bg-white w-full sm:max-w-sm sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest" id="mcc-titulo">Configurar Campo</h3>
                <button onclick="cerrarModal('modalCampoConfig')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <div class="p-5 space-y-4">
                <input type="hidden" id="mcc-id-campo">
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                    <div>
                        <p class="text-sm font-black text-slate-700">Campo Obligatorio</p>
                        <p class="text-[10px] text-slate-400">No se podrá guardar sin completarlo</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="mcc-requerido" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-brand-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                    </label>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Orden de aparición</label>
                    <input type="number" id="mcc-orden" min="1" max="99" value="10"
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold text-center focus:border-brand-600 outline-none">
                </div>
            </div>
            <div class="bg-slate-50 px-5 py-4 flex gap-3">
                <button onclick="cerrarModal('modalCampoConfig')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                <button onclick="guardarConfigCampo()" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl" style="background:linear-gradient(135deg,#e11d48,#9f1239)">Guardar</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/utils.js"></script>
    <script>
    const TODOS_LOS_CAMPOS = <?= json_encode($campos) ?>;
    let tipoActivo = null;
    let camposDelTipo = {};

    // Tabs móvil
    function switchTab(tab) {
        const panelTipos  = document.getElementById('panel-tipos');
        const panelCampos = document.getElementById('panel-campos');
        const btnTipos    = document.getElementById('tab-tipos');
        const btnCampos   = document.getElementById('tab-campos');
        if (tab === 'tipos') {
            panelTipos.classList.remove('hidden');
            panelCampos.classList.add('hidden', 'lg:block');
            btnTipos.classList.add('bg-slate-900','text-white');
            btnTipos.classList.remove('text-slate-500');
            btnCampos.classList.remove('bg-slate-900','text-white');
            btnCampos.classList.add('text-slate-500');
        } else {
            panelCampos.classList.remove('hidden');
            panelTipos.classList.add('hidden', 'lg:block');
            btnCampos.classList.add('bg-slate-900','text-white');
            btnCampos.classList.remove('text-slate-500');
            btnTipos.classList.remove('bg-slate-900','text-white');
            btnTipos.classList.add('text-slate-500');
        }
    }

    async function seleccionarTipo(id, nombre) {
        document.querySelectorAll('.tipo-row').forEach(r => r.classList.remove('selected'));
        const row = document.querySelector(`.tipo-row[data-id="${id}"]`);
        if (row) row.classList.add('selected');
        tipoActivo = id;
        document.getElementById('titulo-tipo-activo').textContent = nombre;
        document.getElementById('panel-loading').classList.remove('hidden');
        document.getElementById('panel-campos-inner').classList.add('hidden');

        // En móvil, al seleccionar un tipo ir al tab de campos
        if (window.innerWidth < 1024) switchTab('campos');

        try {
            const res  = await fetch(`../controllers/parametrosController.php?action=getCamposTipo&id_tipo=${id}`);
            const data = await res.json();
            camposDelTipo = {};
            data.forEach(c => { camposDelTipo[c.id_campo] = {requerido: c.requerido, orden: c.orden}; });
            renderCampos();
        } catch(e) { console.error(e); }
    }

    function renderCampos() {
        const gridBase  = document.getElementById('grid-campos-base');
        const gridExtra = document.getElementById('grid-campos-extra');
        gridBase.innerHTML  = '';
        gridExtra.innerHTML = '';
        let countActivos = 0;
        const base  = TODOS_LOS_CAMPOS.filter(c => c.is_base);
        const extra = TODOS_LOS_CAMPOS.filter(c => !c.is_base);
        base.forEach(c  => { gridBase.appendChild(crearCampoCard(c));  if(camposDelTipo[c.id_campo]) countActivos++; });
        extra.forEach(c => { gridExtra.appendChild(crearCampoCard(c)); if(camposDelTipo[c.id_campo]) countActivos++; });
        document.getElementById('sin-campos-extra').classList.toggle('hidden', extra.length > 0);
        document.getElementById('count-activos').textContent = countActivos;
        document.getElementById('panel-loading').classList.add('hidden');
        document.getElementById('panel-campos-inner').classList.remove('hidden');
        actualizarConteoTipo(tipoActivo, countActivos);
    }

    function crearCampoCard(c) {
        const estaActivo = !!camposDelTipo[c.id_campo];
        const conf = camposDelTipo[c.id_campo] || {};
        const tipoLabel = {texto:'Texto',numero:'Número',booleano:'Sí/No',fecha:'Fecha',lista:'Lista'};
        const div = document.createElement('div');
        div.className = `campo-card border-2 rounded-2xl p-3.5 relative ${estaActivo ? 'activo-tipo border-brand-200' : 'border-slate-100 bg-white'}`;
        div.dataset.id = c.id_campo;
        div.innerHTML = `
            <div class="flex items-start gap-2.5">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 ${estaActivo?'bg-brand-600 text-white':'bg-slate-100 text-slate-400'}">
                    <i class="fas ${c.icono} text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1 flex-wrap">
                        <p class="font-black text-slate-800 text-xs truncate">${c.etiqueta}</p>
                        ${c.is_base ? '<span class="pill-tipo bg-slate-100 text-slate-400">Base</span>':''}
                        ${conf.requerido ? '<span class="pill-tipo bg-red-50 text-brand-600">Requerido</span>':''}
                    </div>
                    <p class="text-[10px] text-slate-400 font-mono">${tipoLabel[c.tipo_dato]||c.tipo_dato}</p>
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between gap-2">
                ${estaActivo && !c.is_base ? `<button onclick="abrirConfigCampo(${c.id_campo},'${c.etiqueta}')" class="text-[10px] font-black text-brand-600 hover:underline"><i class="fas fa-sliders mr-1"></i>Config.</button>` : '<span></span>'}
                <label class="relative inline-flex items-center cursor-pointer ml-auto">
                    <input type="checkbox" class="sr-only peer toggle-campo"
                           data-id="${c.id_campo}" ${estaActivo?'checked':''}
                           onchange="toggleCampo(this,${c.is_base})">
                    <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-brand-600
                                after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                                after:bg-white after:rounded-full after:h-4 after:w-4
                                after:transition-all peer-checked:after:translate-x-4"></div>
                </label>
            </div>`;
        return div;
    }

    async function toggleCampo(checkbox, isBase) {
        const idCampo = parseInt(checkbox.dataset.id);
        const activo  = checkbox.checked;
        const conf    = camposDelTipo[idCampo] || {};
        const body    = new URLSearchParams({action:'toggleCampo',id_tipo:tipoActivo,id_campo:idCampo,activo:activo?'1':'0',requerido:conf.requerido?'1':'0',orden:conf.orden||99});
        try {
            const res  = await fetch('../controllers/parametrosController.php',{method:'POST',body});
            const data = await res.json();
            if (data.success) { if(activo) camposDelTipo[idCampo]={requerido:false,orden:99}; else delete camposDelTipo[idCampo]; renderCampos(); }
            else { checkbox.checked=!activo; Swal.fire('Error',data.msg,'error'); }
        } catch(e) { checkbox.checked=!activo; }
    }

    function abrirConfigCampo(idCampo, etiqueta) {
        const conf = camposDelTipo[idCampo] || {};
        document.getElementById('mcc-id-campo').value    = idCampo;
        document.getElementById('mcc-titulo').textContent= 'Config: ' + etiqueta;
        document.getElementById('mcc-requerido').checked = !!conf.requerido;
        document.getElementById('mcc-orden').value       = conf.orden || 10;
        abrirModal('modalCampoConfig');
    }

    async function guardarConfigCampo() {
        const idCampo   = parseInt(document.getElementById('mcc-id-campo').value);
        const requerido = document.getElementById('mcc-requerido').checked;
        const orden     = parseInt(document.getElementById('mcc-orden').value) || 10;
        const body      = new URLSearchParams({action:'toggleCampo',id_tipo:tipoActivo,id_campo:idCampo,activo:'1',requerido:requerido?'1':'0',orden});
        const res  = await fetch('../controllers/parametrosController.php',{method:'POST',body});
        const data = await res.json();
        if (data.success) { camposDelTipo[idCampo]={requerido,orden}; cerrarModal('modalCampoConfig'); renderCampos(); }
        else Swal.fire('Error',data.msg,'error');
    }

    async function crearCampo() {
        const etiqueta  = document.getElementById('nc_etiqueta').value.trim();
        const nombre    = document.getElementById('nc_nombre').value.trim();
        const tipo_dato = document.querySelector('input[name="nc_tipo_dato"]:checked')?.value||'texto';
        const icono     = document.querySelector('input[name="nc_icono"]:checked')?.value||'fa-tag';
        const opcionesRaw = document.getElementById('nc_opciones').value.trim();
        if (!etiqueta||!nombre) return Swal.fire('Atención','Completa etiqueta y nombre técnico','warning');
        if (!/^[a-z0-9_]+$/i.test(nombre)) return Swal.fire('Atención','El nombre técnico solo puede tener letras, números y _','warning');
        let opciones = null;
        if (tipo_dato==='lista') {
            const arr = opcionesRaw.split('\n').map(s=>s.trim()).filter(Boolean);
            if (arr.length<2) return Swal.fire('Atención','Agrega al menos 2 opciones','warning');
            opciones = JSON.stringify(arr);
        }
        const body = new URLSearchParams({action:'crearCampo',etiqueta,nombre,tipo_dato,icono,opciones:opciones||''});
        const res  = await fetch('../controllers/parametrosController.php',{method:'POST',body});
        const data = await res.json();
        if (data.success) {
            TODOS_LOS_CAMPOS.push({id_campo:data.id,nombre,etiqueta,tipo_dato,icono,opciones,is_base:false,orden:99});
            cerrarModal('modalNuevoCampo');
            renderCampos();
            Swal.fire({title:'¡Campo creado!',icon:'success',timer:2000,showConfirmButton:false});
        } else Swal.fire('Error',data.msg,'error');
    }

    function autoNombre() {
        document.getElementById('nc_nombre').value = document.getElementById('nc_etiqueta').value
            .toLowerCase().trim().normalize('NFD').replace(/[\u0300-\u036f]/g,'')
            .replace(/\s+/g,'_').replace(/[^a-z0-9_]/g,'');
    }
    function toggleOpcionesLista() {
        const tipo = document.querySelector('input[name="nc_tipo_dato"]:checked')?.value;
        document.getElementById('bloque-opciones-lista').classList.toggle('hidden',tipo!=='lista');
    }

    document.querySelectorAll('#selector-tipo-dato input').forEach(r => {
        r.addEventListener('change', () => {
            document.querySelectorAll('.tipo-dato-pill').forEach(p => p.classList.remove('border-brand-600','bg-brand-50'));
            document.querySelector(`.tipo-dato-pill[data-val="${r.value}"]`)?.classList.add('border-brand-600','bg-brand-50');
        });
    });
    document.querySelectorAll('#selector-icono input').forEach(r => {
        r.addEventListener('change', () => {
            document.querySelectorAll('.icono-pill').forEach(p => p.classList.remove('border-brand-600','bg-brand-50'));
            document.querySelector(`.icono-pill[data-ic="${r.value}"]`)?.classList.add('border-brand-600','bg-brand-50');
        });
    });

    function actualizarConteoTipo(id, count) {
        const span = document.getElementById(`conteo-${id}`);
        if (span) span.textContent = `${count} campos`;
    }

    async function cargarConteos() {
        <?php foreach($tipos as $t): ?>
        fetch(`../controllers/parametrosController.php?action=getCamposTipo&id_tipo=<?= $t['r_id'] ?>`)
            .then(r=>r.json()).then(data=>actualizarConteoTipo(<?= $t['r_id'] ?>,data.length));
        <?php endforeach; ?>
    }

    function abrirEdicion(id, nombre) {
        document.getElementById('edit_id').value     = id;
        document.getElementById('edit_nombre').value = nombre;
        abrirModal('modalEdicion');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const primera = document.querySelector('.tipo-row');
        if (primera) primera.click();
        else document.getElementById('panel-loading').innerHTML = '<p class="text-slate-400 font-bold text-sm text-center py-12">Crea un tipo primero.</p>';
        cargarConteos();
    });
    </script>
    <script src="../assets/js/dark_mode.js"></script>
</body>
</html>
