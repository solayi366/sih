<?php
require_once '../controllers/parametrosController.php';
$tipos  = ParametrosController::getTipos();
$campos = ParametrosController::getTodosCampos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tipos de Equipo | SIH_QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { 50: '#fff1f2', 100:'#ffe4e6', 600: '#e11d48', 700: '#be123c', 900:'#881337' } },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        .campo-card { transition: all .2s; }
        .campo-card.activo-tipo { border-color: #e11d48 !important; background: #fff1f2; }
        .campo-card.activo-tipo .badge-activo { display: flex !important; }
        .badge-activo { display: none; }
        .toggle-campo { cursor: pointer; }
        .requerido-dot { width:8px; height:8px; background:#e11d48; border-radius:50%; display:inline-block; }
        .tipo-row { cursor: pointer; transition: background .15s; }
        .tipo-row.selected { background: #fff1f2 !important; }
        .tipo-row.selected td:first-child { border-left: 4px solid #e11d48; }
        .pill-tipo { font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; padding:2px 8px; border-radius:999px; }
        .drag-handle { cursor: grab; color:#94a3b8; }
        .drag-handle:active { cursor: grabbing; }
        [data-dragging="true"] { opacity: .4; }
    </style>
</head>

<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50 w-full">
        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth w-full">
            <div class="max-w-[1300px] mx-auto">

                <!-- ── Header ── -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Tipos de Equipo</h1>
                        <p class="text-slate-500 font-medium mt-1">Configura qué campos tiene cada tipo de dispositivo.</p>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="abrirModal('modalNuevoCampo')"
                                class="px-6 py-3 rounded-2xl font-black text-xs tracking-widest border-2 border-slate-200 text-slate-600 hover:border-brand-600 hover:text-brand-600 transition-all flex items-center gap-2">
                            <i class="fas fa-sliders"></i> Nuevo Campo
                        </button>
                        <button onclick="abrirModal('modalCrear')"
                                class="px-8 py-3 rounded-2xl font-black text-xs tracking-widest shadow-xl hover:scale-105 transition-all flex items-center gap-3 text-white"
                                style="background:linear-gradient(135deg,#e11d48,#9f1239)">
                            <i class="fas fa-plus"></i> Añadir Tipo
                        </button>
                    </div>
                </div>

                <!-- ── Alerta URL ── -->
                <?php if (isset($_GET['msg'])): ?>
                <div class="mb-6 px-5 py-3 rounded-2xl text-sm font-bold <?= $_GET['tipo'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                    <i class="fas <?= $_GET['tipo'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($_GET['msg']) ?>
                </div>
                <?php endif; ?>

                <!-- ── Layout 2 columnas ── -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- ── Col izq: Lista de tipos ── -->
                    <div class="lg:col-span-4">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Categorías</span>
                                <span class="pill-tipo bg-slate-100 text-slate-500"><?= count($tipos) ?> tipos</span>
                            </div>
                            <div class="divide-y divide-slate-50">
                                <?php foreach ($tipos as $i => $t): ?>
                                <div class="tipo-row flex items-center px-5 py-4 hover:bg-slate-50 <?= $i === 0 ? 'selected' : '' ?>"
                                    data-id="<?= $t['r_id'] ?>"
                                    data-nombre="<?= htmlspecialchars($t['r_nombre']) ?>"
                                    onclick="seleccionarTipo(<?= $t['r_id'] ?>, '<?= htmlspecialchars($t['r_nombre']) ?>')">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-black text-slate-800 text-sm"><?= htmlspecialchars($t['r_nombre']) ?></p>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">#<?= str_pad($t['r_id'], 3, '0', STR_PAD_LEFT) ?></p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span id="conteo-<?= $t['r_id'] ?>" class="pill-tipo bg-brand-50 text-brand-600">...</span>
                                        <button onclick="event.stopPropagation(); abrirEdicion(<?= $t['r_id'] ?>, '<?= htmlspecialchars($t['r_nombre']) ?>')"
                                                class="text-slate-300 hover:text-brand-600 transition-colors p-1">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button onclick="event.stopPropagation(); Alerts.confirmDelete('../controllers/parametrosController.php?ent=tipo&action=delete&id=<?= $t['r_id'] ?>')"
                                                class="text-slate-300 hover:text-rose-500 transition-colors p-1">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- ── Col der: Configurador de campos ── -->
                    <div class="lg:col-span-8">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-900">
                                <div>
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Configurando campos para</p>
                                    <h2 class="text-white font-black text-lg" id="titulo-tipo-activo">—</h2>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] text-slate-500 font-bold">Campos activos: <span id="count-activos" class="text-brand-500">0</span></span>
                                    <i class="fas fa-puzzle-piece text-slate-600 text-xl"></i>
                                </div>
                            </div>

                            <!-- Loading state -->
                            <div id="panel-loading" class="p-12 text-center">
                                <i class="fas fa-spinner fa-spin text-3xl text-slate-300 mb-4"></i>
                                <p class="text-slate-400 text-sm font-bold">Cargando campos...</p>
                            </div>

                            <!-- Campos grid -->
                            <div id="panel-campos" class="hidden p-6">

                                <!-- Campos base -->
                                <div class="mb-6">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Campos Base del Sistema</span>
                                        <div class="flex-1 h-px bg-slate-100"></div>
                                        <span class="pill-tipo bg-slate-100 text-slate-400">No eliminables</span>
                                    </div>
                                    <div id="grid-campos-base" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <!-- renderizado por JS -->
                                    </div>
                                </div>

                                <!-- Campos extra -->
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Campos Personalizados</span>
                                        <div class="flex-1 h-px bg-slate-100"></div>
                                        <button onclick="abrirModal('modalNuevoCampo')" class="text-[10px] font-black text-brand-600 hover:underline">
                                            + Crear nuevo
                                        </button>
                                    </div>
                                    <div id="grid-campos-extra" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <!-- renderizado por JS -->
                                    </div>
                                    <div id="sin-campos-extra" class="hidden text-center py-8 text-slate-300">
                                        <i class="fas fa-puzzle-piece text-3xl mb-2"></i>
                                        <p class="text-xs font-bold">No hay campos personalizados.<br>Crea uno con el botón de arriba.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- ══════════════════════════════════════════════════════════
         MODALES
    ══════════════════════════════════════════════════════════ -->

    <!-- Modal Crear Tipo -->
    <div id="modalCrear" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Registrar Nuevo Tipo</h3>
                <button onclick="cerrarModal('modalCrear')" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=tipo&action=create" method="POST">
                <div class="p-8 space-y-4">
                    <label class="block text-xs font-bold text-slate-600 tracking-wide mb-2">Nombre del Dispositivo</label>
                    <input type="text" name="nom_tipo" placeholder="Ej: Celular, Drone, Cámara IP..."
                           required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-50 transition-all">
                </div>
                <div class="bg-slate-50 px-6 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalCrear')" class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-500">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-black rounded-xl hover:shadow-lg transition-all">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Tipo -->
    <div id="modalEdicion" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Actualizar Categoría</h3>
                <button onclick="cerrarModal('modalEdicion')" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=tipo&action=update" method="POST">
                <input type="hidden" name="id_tipo" id="edit_id">
                <div class="p-8 space-y-4">
                    <label class="block text-xs font-bold text-slate-600 tracking-wide mb-2">Modificar Nombre</label>
                    <input type="text" name="nom_tipo" id="edit_nombre" required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 focus:outline-none transition-all">
                </div>
                <div class="bg-slate-50 px-6 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalEdicion')" class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-500">Cerrar</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-black rounded-xl hover:shadow-lg transition-all">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Nuevo Campo Personalizado -->
    <div id="modalNuevoCampo" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-white text-xs font-black tracking-widest">Crear Campo Personalizado</h3>
                    <p class="text-slate-500 text-[10px] mt-0.5">El campo quedará disponible para asignar a cualquier tipo</p>
                </div>
                <button onclick="cerrarModal('modalNuevoCampo')" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-8 space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Etiqueta (visible)</label>
                        <input type="text" id="nc_etiqueta" placeholder="Ej: IMEI, Versión SO..."
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold focus:border-brand-600 outline-none transition-all"
                               oninput="autoNombre()">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Nombre técnico</label>
                        <input type="text" id="nc_nombre" placeholder="imei_dispositivo"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold font-mono focus:border-brand-600 outline-none transition-all">
                        <p class="text-[10px] text-slate-400 mt-1">Solo letras, números y _</p>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Tipo de Dato</label>
                    <div class="grid grid-cols-5 gap-2" id="selector-tipo-dato">
                        <?php
                        $tipos_dato = [
                            'texto'    => ['fa-font',        'Texto'],
                            'numero'   => ['fa-hashtag',     'Número'],
                            'booleano' => ['fa-toggle-on',   'Sí/No'],
                            'fecha'    => ['fa-calendar-alt','Fecha'],
                            'lista'    => ['fa-list',        'Lista'],
                        ];
                        foreach ($tipos_dato as $val => $info): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="nc_tipo_dato" value="<?= $val ?>" class="sr-only" <?= $val === 'texto' ? 'checked' : '' ?> onchange="toggleOpcionesLista()">
                            <div class="tipo-dato-pill border-2 border-slate-200 rounded-xl p-3 text-center hover:border-brand-600 transition-all"
                                 data-val="<?= $val ?>">
                                <i class="fas <?= $info[0] ?> text-slate-400 text-lg mb-1 block"></i>
                                <span class="text-[10px] font-black text-slate-500"><?= $info[1] ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="bloque-opciones-lista" class="hidden">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Opciones (una por línea)</label>
                    <textarea id="nc_opciones" rows="4" placeholder="Opción 1&#10;Opción 2&#10;Opción 3"
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-mono focus:border-brand-600 outline-none resize-none transition-all"></textarea>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Ícono (FontAwesome)</label>
                    <div class="flex gap-3 flex-wrap" id="selector-icono">
                        <?php
                        $iconos = ['fa-tag','fa-mobile-alt','fa-sim-card','fa-wifi','fa-memory','fa-hdd','fa-microchip','fa-calendar-alt','fa-battery-full','fa-camera','fa-cube','fa-code','fa-globe','fa-info-circle'];
                        foreach ($iconos as $ic): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="nc_icono" value="<?= $ic ?>" class="sr-only" <?= $ic === 'fa-tag' ? 'checked' : '' ?>>
                            <div class="icono-pill w-10 h-10 border-2 border-slate-200 rounded-xl flex items-center justify-center hover:border-brand-600 transition-all"
                                 data-ic="<?= $ic ?>">
                                <i class="fas <?= $ic ?> text-slate-400"></i>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 flex gap-3">
                <button type="button" onclick="cerrarModal('modalNuevoCampo')" class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-500">Cancelar</button>
                <button type="button" onclick="crearCampo()" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-black rounded-xl hover:shadow-lg transition-all">
                    <i class="fas fa-plus mr-2"></i>Crear Campo
                </button>
            </div>
        </div>
    </div>

    <!-- Modal detalle campo (requerido + orden) -->
    <div id="modalCampoConfig" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest" id="mcc-titulo">Configurar Campo</h3>
                <button onclick="cerrarModal('modalCampoConfig')" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-5">
                <input type="hidden" id="mcc-id-campo">
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                    <div>
                        <p class="text-sm font-black text-slate-700">Campo Obligatorio</p>
                        <p class="text-[10px] text-slate-400">El usuario no podrá guardar sin completarlo</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="mcc-requerido" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-brand-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                    </label>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Orden de aparición</label>
                    <input type="number" id="mcc-orden" min="1" max="99" value="10"
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-bold text-center focus:border-brand-600 outline-none">
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 flex gap-3">
                <button onclick="cerrarModal('modalCampoConfig')" class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-500">Cancelar</button>
                <button onclick="guardarConfigCampo()" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-black rounded-xl hover:shadow-lg transition-all">Guardar</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/utils.js"></script>
    <script>
    // ── Estado global ─────────────────────────────────────────────────────────
    const TODOS_LOS_CAMPOS = <?= json_encode($campos) ?>;
    let tipoActivo = null;
    let camposDelTipo = {};   // {id_campo: {requerido, orden, activo}}

    // ── Selección de tipo ─────────────────────────────────────────────────────
    async function seleccionarTipo(id, nombre) {
        // Marcar fila
        document.querySelectorAll('.tipo-row').forEach(r => r.classList.remove('selected'));
        const row = document.querySelector(`.tipo-row[data-id="${id}"]`);
        if (row) row.classList.add('selected');

        tipoActivo = id;
        document.getElementById('titulo-tipo-activo').textContent = nombre;
        document.getElementById('panel-loading').classList.remove('hidden');
        document.getElementById('panel-campos').classList.add('hidden');

        // Cargar campos de este tipo
        try {
            const res  = await fetch(`../controllers/parametrosController.php?action=getCamposTipo&id_tipo=${id}`);
            const data = await res.json();

            // Mapear campos activos del tipo
            camposDelTipo = {};
            data.forEach(c => {
                camposDelTipo[c.id_campo] = { requerido: c.requerido, orden: c.orden };
            });

            renderCampos();
        } catch(e) {
            console.error(e);
        }
    }

    // ── Renderizar grid de campos ─────────────────────────────────────────────
    function renderCampos() {
        const gridBase  = document.getElementById('grid-campos-base');
        const gridExtra = document.getElementById('grid-campos-extra');
        gridBase.innerHTML  = '';
        gridExtra.innerHTML = '';

        let countActivos = 0;
        const base  = TODOS_LOS_CAMPOS.filter(c => c.is_base);
        const extra = TODOS_LOS_CAMPOS.filter(c => !c.is_base);

        base.forEach(c  => { const el = crearCampoCard(c); gridBase.appendChild(el); if (camposDelTipo[c.id_campo]) countActivos++; });
        extra.forEach(c => { const el = crearCampoCard(c); gridExtra.appendChild(el); if (camposDelTipo[c.id_campo]) countActivos++; });

        const sinExtra = document.getElementById('sin-campos-extra');
        sinExtra.classList.toggle('hidden', extra.length > 0);

        document.getElementById('count-activos').textContent = countActivos;
        document.getElementById('panel-loading').classList.add('hidden');
        document.getElementById('panel-campos').classList.remove('hidden');

        // Actualizar conteo en sidebar
        actualizarConteoTipo(tipoActivo, countActivos);
    }

    function crearCampoCard(c) {
        const estaActivo = !!camposDelTipo[c.id_campo];
        const conf = camposDelTipo[c.id_campo] || {};
        const tipoLabel = { texto:'Texto', numero:'Número', booleano:'Sí/No', fecha:'Fecha', lista:'Lista' };

        const div = document.createElement('div');
        div.className = `campo-card border-2 rounded-2xl p-4 relative ${estaActivo ? 'activo-tipo border-brand-200 bg-brand-50' : 'border-slate-100 bg-white'}`;
        div.dataset.id = c.id_campo;

        div.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 ${estaActivo ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-400'}">
                    <i class="fas ${c.icono} text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <p class="font-black text-slate-800 text-sm truncate">${c.etiqueta}</p>
                        ${c.is_base ? '<span class="pill-tipo bg-slate-100 text-slate-400">Base</span>' : ''}
                        ${conf.requerido ? '<span class="pill-tipo bg-red-50 text-brand-600">Requerido</span>' : ''}
                    </div>
                    <p class="text-[10px] text-slate-400 font-mono">${c.nombre} · ${tipoLabel[c.tipo_dato] || c.tipo_dato}</p>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between gap-2">
                ${estaActivo && !c.is_base ? `
                    <button onclick="abrirConfigCampo(${c.id_campo}, '${c.etiqueta}')"
                            class="text-[10px] font-black text-brand-600 hover:underline">
                        <i class="fas fa-sliders mr-1"></i>Configurar
                    </button>` : '<span></span>'}

                <label class="relative inline-flex items-center cursor-pointer ml-auto">
                    <input type="checkbox" class="sr-only peer toggle-campo"
                           data-id="${c.id_campo}" data-base="${c.is_base ? '1' : '0'}"
                           ${estaActivo ? 'checked' : ''}
                           onchange="toggleCampo(this, ${c.is_base})">
                    <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:bg-brand-600
                                after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                                after:bg-white after:rounded-full after:h-4 after:w-4
                                after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>
        `;
        return div;
    }

    // ── Toggle campo ON/OFF ───────────────────────────────────────────────────
    async function toggleCampo(checkbox, isBase) {
        const idCampo = parseInt(checkbox.dataset.id);
        const activo  = checkbox.checked;
        const conf    = camposDelTipo[idCampo] || {};

        const body = new URLSearchParams({
            action:     'toggleCampo',
            id_tipo:    tipoActivo,
            id_campo:   idCampo,
            activo:     activo ? '1' : '0',
            requerido:  conf.requerido ? '1' : '0',
            orden:      conf.orden || 99
        });

        try {
            const res  = await fetch('../controllers/parametrosController.php', { method:'POST', body });
            const data = await res.json();
            if (data.success) {
                if (activo) {
                    camposDelTipo[idCampo] = { requerido: false, orden: 99 };
                } else {
                    delete camposDelTipo[idCampo];
                    // Si es base y se desactiva, mostrar warning si quedan pocos
                }
                renderCampos();
            } else {
                checkbox.checked = !activo; // revertir
                Swal.fire('Error', data.msg, 'error');
            }
        } catch(e) {
            checkbox.checked = !activo;
        }
    }

    // ── Modal configurar campo (requerido + orden) ────────────────────────────
    function abrirConfigCampo(idCampo, etiqueta) {
        const conf = camposDelTipo[idCampo] || {};
        document.getElementById('mcc-id-campo').value = idCampo;
        document.getElementById('mcc-titulo').textContent = 'Configurar: ' + etiqueta;
        document.getElementById('mcc-requerido').checked  = !!conf.requerido;
        document.getElementById('mcc-orden').value        = conf.orden || 10;
        abrirModal('modalCampoConfig');
    }

    async function guardarConfigCampo() {
        const idCampo   = parseInt(document.getElementById('mcc-id-campo').value);
        const requerido = document.getElementById('mcc-requerido').checked;
        const orden     = parseInt(document.getElementById('mcc-orden').value) || 10;

        const body = new URLSearchParams({
            action: 'toggleCampo', id_tipo: tipoActivo,
            id_campo: idCampo, activo: '1',
            requerido: requerido ? '1' : '0', orden
        });
        const res  = await fetch('../controllers/parametrosController.php', { method:'POST', body });
        const data = await res.json();
        if (data.success) {
            camposDelTipo[idCampo] = { requerido, orden };
            cerrarModal('modalCampoConfig');
            renderCampos();
        } else {
            Swal.fire('Error', data.msg, 'error');
        }
    }

    // ── Crear campo personalizado ─────────────────────────────────────────────
    async function crearCampo() {
        const etiqueta  = document.getElementById('nc_etiqueta').value.trim();
        const nombre    = document.getElementById('nc_nombre').value.trim();
        const tipo_dato = document.querySelector('input[name="nc_tipo_dato"]:checked')?.value || 'texto';
        const icono     = document.querySelector('input[name="nc_icono"]:checked')?.value || 'fa-tag';
        const opcionesRaw = document.getElementById('nc_opciones').value.trim();

        if (!etiqueta || !nombre) return Swal.fire('Atención', 'Completa etiqueta y nombre técnico', 'warning');
        if (!/^[a-z0-9_]+$/i.test(nombre)) return Swal.fire('Atención', 'El nombre técnico solo puede tener letras, números y _', 'warning');

        let opciones = null;
        if (tipo_dato === 'lista') {
            const arr = opcionesRaw.split('\n').map(s => s.trim()).filter(Boolean);
            if (arr.length < 2) return Swal.fire('Atención', 'Agrega al menos 2 opciones para el campo lista', 'warning');
            opciones = JSON.stringify(arr);
        }

        const body = new URLSearchParams({ action:'crearCampo', etiqueta, nombre, tipo_dato, icono, opciones: opciones || '' });
        const res  = await fetch('../controllers/parametrosController.php', { method:'POST', body });
        const data = await res.json();

        if (data.success) {
            // Agregar al array global sin recargar
            TODOS_LOS_CAMPOS.push({
                id_campo: data.id, nombre, etiqueta, tipo_dato, icono,
                opciones, is_base: false, orden: 99
            });
            cerrarModal('modalNuevoCampo');
            renderCampos();
            Swal.fire({ title:'¡Campo creado!', text:'Ya está disponible para asignar a este tipo.', icon:'success', timer:2000, showConfirmButton:false });
        } else {
            Swal.fire('Error', data.msg, 'error');
        }
    }

    // ── Auto-nombre técnico desde etiqueta ───────────────────────────────────
    function autoNombre() {
        const et = document.getElementById('nc_etiqueta').value;
        document.getElementById('nc_nombre').value = et
            .toLowerCase().trim()
            .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
            .replace(/\s+/g,'_').replace(/[^a-z0-9_]/g,'');
    }

    function toggleOpcionesLista() {
        const tipo = document.querySelector('input[name="nc_tipo_dato"]:checked')?.value;
        document.getElementById('bloque-opciones-lista').classList.toggle('hidden', tipo !== 'lista');
    }

    // ── Resaltar selección de tipo dato e ícono ───────────────────────────────
    document.querySelectorAll('#selector-tipo-dato input').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.tipo-dato-pill').forEach(p => p.classList.remove('border-brand-600','bg-brand-50'));
            document.querySelector(`.tipo-dato-pill[data-val="${radio.value}"]`)?.classList.add('border-brand-600','bg-brand-50');
        });
    });
    document.querySelectorAll('#selector-icono input').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.icono-pill').forEach(p => p.classList.remove('border-brand-600','bg-brand-50'));
            document.querySelector(`.icono-pill[data-ic="${radio.value}"]`)?.classList.add('border-brand-600','bg-brand-50');
        });
    });

    // ── Actualizar conteo en la lista lateral ─────────────────────────────────
    function actualizarConteoTipo(id, count) {
        const span = document.getElementById(`conteo-${id}`);
        if (span) span.textContent = `${count} campos`;
    }

    // ── Cargar conteos iniciales ──────────────────────────────────────────────
    async function cargarConteos() {
        <?php foreach ($tipos as $t): ?>
        fetch(`../controllers/parametrosController.php?action=getCamposTipo&id_tipo=<?= $t['r_id'] ?>`)
            .then(r => r.json())
            .then(data => actualizarConteoTipo(<?= $t['r_id'] ?>, data.length));
        <?php endforeach; ?>
    }

    // ── Edición inline de tipo ────────────────────────────────────────────────
    function abrirEdicion(id, nombre) {
        document.getElementById('edit_id').value     = id;
        document.getElementById('edit_nombre').value = nombre;
        abrirModal('modalEdicion');
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        // Seleccionar primer tipo automáticamente
        const primera = document.querySelector('.tipo-row');
        if (primera) {
            primera.click();
        } else {
            document.getElementById('panel-loading').innerHTML =
                '<p class="text-slate-400 font-bold text-sm text-center py-12">Crea un tipo de equipo primero.</p>';
        }

        cargarConteos();

        // Resaltar primer tipo dato e ícono en modal
        document.querySelector('.tipo-dato-pill[data-val="texto"]')?.classList.add('border-brand-600','bg-brand-50');
        document.querySelector('.icono-pill[data-ic="fa-tag"]')?.classList.add('border-brand-600','bg-brand-50');
    });
    </script>
</body>
</html>