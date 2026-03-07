<?php
require_once '../controllers/parametrosController.php';
require_once '../core/Csrf.php';
$data    = ParametrosController::getHardwareData();
$modelos = $data['modelos'];
$total   = count($modelos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modelos de Equipo | SIH_QR</title>
    <script>(function(){var t=localStorage.getItem('sihTheme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark');}})();</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{brand:{50:'#fff1f2',600:'#e11d48',700:'#be123c'}},fontFamily:{sans:['Plus Jakarta Sans','sans-serif']}}}}</script>
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">
    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative bg-slate-50/50 w-full">
        <div class="flex-1 overflow-y-auto p-4 md:p-8 w-full">
            <div class="max-w-4xl mx-auto">

                <!-- Header -->
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">Modelos</h1>
                        <p class="text-slate-500 text-sm mt-0.5">Catálogo de modelos por marca.</p>
                    </div>
                    <button onclick="abrirModal('modalCrear')"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs tracking-widest text-white shadow-lg hover:scale-105 transition-all shrink-0"
                            style="background:linear-gradient(135deg,#e11d48,#9f1239)">
                        <i class="fas fa-plus"></i>
                        <span class="hidden sm:inline">Añadir Modelo</span>
                        <span class="sm:hidden">Nuevo</span>
                    </button>
                </div>

                <!-- Buscador + Filtros -->
                <div class="mb-4 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="buscadorModelos" placeholder="Buscar modelo..."
                               class="w-full pl-9 pr-4 py-2.5 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all bg-white">
                    </div>
                    <div class="relative">
                        <select id="filtroMarcaMod" class="pl-4 pr-8 py-2.5 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none bg-white appearance-none cursor-pointer min-w-[150px]">
                            <option value="">Todas las marcas</option>
                            <?php
                            $marcas_unicas = [];
                            foreach ($modelos as $mod) {
                                if (!in_array($mod['r_marca'], $marcas_unicas)) $marcas_unicas[] = $mod['r_marca'];
                            }
                            sort($marcas_unicas);
                            foreach ($marcas_unicas as $mar): ?>
                            <option value="<?= htmlspecialchars($mar) ?>"><?= htmlspecialchars($mar) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                    <div class="relative">
                        <select id="filtroTipoMod" class="pl-4 pr-8 py-2.5 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none bg-white appearance-none cursor-pointer min-w-[150px]">
                            <option value="">Todos los tipos</option>
                            <?php
                            $tipos_unicos = [];
                            foreach ($modelos as $mod) {
                                if (!in_array($mod['r_tipo'], $tipos_unicos)) $tipos_unicos[] = $mod['r_tipo'];
                            }
                            sort($tipos_unicos);
                            foreach ($tipos_unicos as $tip): ?>
                            <option value="<?= htmlspecialchars($tip) ?>"><?= htmlspecialchars($tip) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>

                <!-- Contador -->
                <p class="text-xs font-bold text-slate-400 mb-3" id="contadorModelos"></p>

                <!-- Lista -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="hidden md:grid grid-cols-[60px_1fr_150px_120px_80px] gap-4 px-5 py-3 bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <span># ID</span>
                        <span>Modelo</span>
                        <span>Marca</span>
                        <span>Tipo</span>
                        <span class="text-right">Acción</span>
                    </div>

                    <div id="listaModelos" class="divide-y divide-slate-100">
                        <?php foreach ($modelos as $mod): ?>
                        <div class="fila-modelo flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors"
                             data-nombre="<?= strtolower(htmlspecialchars($mod['r_modelo'])) ?>"
                             data-marca="<?= htmlspecialchars($mod['r_marca']) ?>"
                             data-tipo="<?= htmlspecialchars($mod['r_tipo']) ?>">

                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                <i class="fas fa-cube text-slate-500 text-sm"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-800 text-sm truncate"><?= htmlspecialchars($mod['r_modelo']) ?></p>
                                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                    <span class="text-[10px] font-black text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded"><?= htmlspecialchars($mod['r_marca']) ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold"><?= htmlspecialchars($mod['r_tipo']) ?></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <button onclick="abrirEdicionModelo(<?= $mod['r_id_modelo'] ?>, '<?= addslashes($mod['r_modelo']) ?>', <?= $mod['r_id_marca'] ?>, <?= $mod['r_id_tipo'] ?>)"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-all">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                                <button onclick="Alerts.confirmDelete('../controllers/parametrosController.php?ent=modelo&action=delete&id=<?= $mod['r_id_modelo'] ?>')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="sinResultadosMod" class="hidden py-16 text-center text-slate-400">
                        <i class="fas fa-search text-3xl mb-3 opacity-30"></i>
                        <p class="text-sm font-bold">Sin resultados para tu búsqueda</p>
                    </div>

                    <?php if (empty($modelos)): ?>
                    <div class="py-16 text-center text-slate-400">
                        <i class="fas fa-cube text-3xl mb-3 opacity-30"></i>
                        <p class="text-sm font-bold">No hay modelos registrados</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Paginador -->
                <div id="paginadorModelos" class="flex items-center justify-between mt-5 gap-3 flex-wrap">
                    <span id="infoPaginaMod" class="text-xs font-bold text-slate-400"></span>
                    <div class="flex items-center gap-2">
                        <button id="btnPrevMod" onclick="cambiarPaginaMod(-1)"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl border-2 border-slate-200 text-xs font-black text-slate-500 hover:border-brand-600 hover:text-brand-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-left text-[10px]"></i> Anterior
                        </button>
                        <div id="numerosMod" class="flex gap-1"></div>
                        <button id="btnNextMod" onclick="cambiarPaginaMod(1)"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl border-2 border-slate-200 text-xs font-black text-slate-500 hover:border-brand-600 hover:text-brand-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            Siguiente <i class="fas fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Crear -->
    <div id="modalCrear" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-container bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Registrar Modelo</h3>
                <button onclick="cerrarModal('modalCrear')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=modelo&action=create" method="POST">
                    <?= Csrf::field() ?>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Marca Asociada</label>
                        <select name="id_marca" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                            <option value="">Seleccione marca...</option>
                            <?php foreach ($data['marcas'] as $mar): ?>
                            <option value="<?= $mar['r_id'] ?>"><?= htmlspecialchars($mar['r_nombre']) ?> (<?= htmlspecialchars($mar['r_tipo']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipo de Equipo</label>
                        <select name="id_tipoequi" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                            <option value="">Seleccione tipo...</option>
                            <?php foreach ($data['tipos'] as $tip): ?>
                            <option value="<?= $tip['r_id'] ?>"><?= htmlspecialchars($tip['r_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre del Modelo</label>
                        <input type="text" name="nom_modelo" placeholder="Ej: Latitude 5420, OptiPlex..." required
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                    </div>
                </div>
                <div class="bg-slate-50 px-5 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalCrear')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl transition-all" style="background:linear-gradient(135deg,#e11d48,#9f1239)">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar -->
    <div id="modalEdicion" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-container bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Editar Modelo</h3>
                <button onclick="cerrarModal('modalEdicion')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=modelo&action=update" method="POST">
                    <?= Csrf::field() ?>
                <input type="hidden" name="id_modelo" id="edit_id">
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Marca</label>
                        <select name="id_marca" id="edit_marca" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                            <?php foreach ($data['marcas'] as $mar): ?>
                            <option value="<?= $mar['r_id'] ?>"><?= htmlspecialchars($mar['r_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipo</label>
                        <select name="id_tipoequi" id="edit_tipo" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                            <?php foreach ($data['tipos'] as $tip): ?>
                            <option value="<?= $tip['r_id'] ?>"><?= htmlspecialchars($tip['r_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre</label>
                        <input type="text" name="nom_modelo" id="edit_nombre" required
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                    </div>
                </div>
                <div class="bg-slate-50 px-5 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalEdicion')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl transition-all" style="background:linear-gradient(135deg,#e11d48,#9f1239)">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/utils.js"></script>
    <script>
        function abrirEdicionModelo(id, nombre, idMarca, idTipo) {
            document.getElementById('edit_id').value     = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_marca').value  = idMarca;
            document.getElementById('edit_tipo').value   = idTipo;
            abrirModal('modalEdicion');
        }

        const POR_PAGINA_MOD = 15;
        let paginaMod = 1;
        const todasFilasMod = Array.from(document.querySelectorAll('.fila-modelo'));
        let filasFiltradasMod = [...todasFilasMod];

        function filtrarModelos() {
            const q     = document.getElementById('buscadorModelos').value.toLowerCase().trim();
            const marca = document.getElementById('filtroMarcaMod').value;
            const tipo  = document.getElementById('filtroTipoMod').value;
            filasFiltradasMod = todasFilasMod.filter(f => {
                const ok_txt   = !q     || f.dataset.nombre.includes(q);
                const ok_marca = !marca || f.dataset.marca === marca;
                const ok_tipo  = !tipo  || f.dataset.tipo  === tipo;
                return ok_txt && ok_marca && ok_tipo;
            });
            paginaMod = 1;
            renderMod();
        }

        function renderMod() {
            const total  = filasFiltradasMod.length;
            const totPag = Math.max(1, Math.ceil(total / POR_PAGINA_MOD));
            if (paginaMod > totPag) paginaMod = totPag;
            const ini = (paginaMod - 1) * POR_PAGINA_MOD;
            const fin = ini + POR_PAGINA_MOD;

            todasFilasMod.forEach(f => f.style.display = 'none');
            filasFiltradasMod.forEach((f, i) => { f.style.display = (i >= ini && i < fin) ? '' : 'none'; });

            document.getElementById('sinResultadosMod').classList.toggle('hidden', total > 0);

            const desde = total === 0 ? 0 : ini + 1;
            const hasta = Math.min(fin, total);
            document.getElementById('contadorModelos').textContent =
                total === 0 ? 'Sin resultados'
                : `Mostrando ${desde}–${hasta} de ${total} modelo${total !== 1 ? 's' : ''}`;

            document.getElementById('btnPrevMod').disabled = paginaMod <= 1;
            document.getElementById('btnNextMod').disabled = paginaMod >= totPag;
            document.getElementById('infoPaginaMod').textContent = totPag > 1 ? `Página ${paginaMod} de ${totPag}` : '';

            const numDiv = document.getElementById('numerosMod');
            numDiv.innerHTML = '';
            if (totPag > 1) {
                generarRango(paginaMod, totPag).forEach(n => {
                    if (n === '...') {
                        numDiv.innerHTML += `<span class="px-2 text-slate-400 text-xs font-bold self-center">…</span>`;
                    } else {
                        const cls = n === paginaMod
                            ? 'bg-brand-600 text-white border-brand-600'
                            : 'border-slate-200 text-slate-500 hover:border-brand-600 hover:text-brand-600';
                        numDiv.innerHTML += `<button onclick="irMod(${n})" class="w-8 h-8 flex items-center justify-center rounded-xl border-2 text-xs font-black transition-all ${cls}">${n}</button>`;
                    }
                });
            }
            document.getElementById('paginadorModelos').style.display = totPag <= 1 ? 'none' : 'flex';
        }

        function generarRango(actual, total) {
            if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
            if (actual <= 4) return [1,2,3,4,5,'...',total];
            if (actual >= total - 3) return [1,'...',total-4,total-3,total-2,total-1,total];
            return [1,'...',actual-1,actual,actual+1,'...',total];
        }

        function cambiarPaginaMod(dir) {
            const tot = Math.ceil(filasFiltradasMod.length / POR_PAGINA_MOD);
            paginaMod = Math.max(1, Math.min(tot, paginaMod + dir));
            renderMod();
        }

        function irMod(n) { paginaMod = n; renderMod(); }

        document.getElementById('buscadorModelos').addEventListener('input', filtrarModelos);
        document.getElementById('filtroMarcaMod').addEventListener('change', filtrarModelos);
        document.getElementById('filtroTipoMod').addEventListener('change', filtrarModelos);
        renderMod();
    </script>
    <script src="../assets/js/dark_mode.js"></script>
</body>
</html>
