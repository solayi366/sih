<?php
require_once '../controllers/parametrosController.php';
require_once '../core/Csrf.php';
$data = ParametrosController::getRRHHData();
$empleados = $data['empleados'];
$total = count($empleados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios de Elementos Tecnológicos | SIH_QR</title>
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
                        <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">Usuarios</h1>
                        <p class="text-slate-500 text-sm mt-0.5">Personal responsable del equipamiento.</p>
                    </div>
                    <button onclick="abrirModal('modalCrear')"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs tracking-widest text-white shadow-lg hover:scale-105 transition-all shrink-0"
                            style="background:linear-gradient(135deg,#e11d48,#9f1239)">
                        <i class="fas fa-user-plus"></i>
                        <span class="hidden sm:inline">Añadir Usuario</span>
                        <span class="sm:hidden">Nuevo</span>
                    </button>
                </div>

                <!-- Buscador + Filtro -->
                <div class="mb-4 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="buscadorEmpleados" placeholder="Buscar por nombre, ficha o área..."
                               class="w-full pl-9 pr-4 py-2.5 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all bg-white">
                    </div>
                    <div class="relative">
                        <select id="filtroArea" class="pl-4 pr-8 py-2.5 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none bg-white appearance-none cursor-pointer min-w-[160px]">
                            <option value="">Todas las áreas</option>
                            <?php
                            $areas_unicas = array_unique(array_column($empleados, 'r_area'));
                            sort($areas_unicas);
                            foreach ($areas_unicas as $area): ?>
                            <option value="<?= htmlspecialchars($area) ?>"><?= htmlspecialchars($area) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>

                <!-- Contador -->
                <p class="text-xs font-bold text-slate-400 mb-3" id="contadorEmpleados"></p>

                <!-- Lista -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <!-- Cabecera desktop -->
                    <div class="hidden md:grid grid-cols-[130px_1fr_180px_80px] gap-4 px-5 py-3 bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <span>Ficha / ID</span>
                        <span>Nombre</span>
                        <span>Área</span>
                        <span class="text-right">Acción</span>
                    </div>

                    <div id="listaEmpleados" class="divide-y divide-slate-100">
                        <?php foreach ($empleados as $e): ?>
                        <div class="fila-empleado flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors group"
                             data-nombre="<?= strtolower(htmlspecialchars($e['r_nombre'])) ?>"
                             data-codigo="<?= strtolower(htmlspecialchars($e['r_codigo'])) ?>"
                             data-area="<?= htmlspecialchars($e['r_area']) ?>">

                            <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-black text-sm shrink-0">
                                <?= strtoupper(substr($e['r_nombre'], 0, 1)) ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-800 text-sm truncate"><?= htmlspecialchars($e['r_nombre']) ?></p>
                                <div class="flex flex-wrap items-center gap-2 mt-0.5">
                                    <span class="font-mono text-[10px] text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded"><?= htmlspecialchars($e['r_codigo']) ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded truncate max-w-[140px]"><?= htmlspecialchars($e['r_area']) ?></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <button onclick="abrirEdicionEmpleado('<?= addslashes($e['r_codigo']) ?>', '<?= addslashes($e['r_nombre']) ?>', <?= $e['r_id_area'] ?>)"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-all">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                                <button onclick="Alerts.confirmDelete('../controllers/parametrosController.php?ent=empleado&action=delete&id=<?= $e['r_codigo'] ?>')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="sinResultados" class="hidden py-16 text-center text-slate-400">
                        <i class="fas fa-search text-3xl mb-3 opacity-30"></i>
                        <p class="text-sm font-bold">Sin resultados para tu búsqueda</p>
                        <p class="text-xs mt-1">Intenta con otro nombre o ficha</p>
                    </div>

                    <?php if (empty($empleados)): ?>
                    <div class="py-16 text-center text-slate-400">
                        <i class="fas fa-users text-3xl mb-3 opacity-30"></i>
                        <p class="text-sm font-bold">No hay usuarios registrados</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Paginador -->
                <div id="paginadorEmpleados" class="flex items-center justify-between mt-5 gap-3 flex-wrap">
                    <span id="infoPaginaEmp" class="text-xs font-bold text-slate-400"></span>
                    <div class="flex items-center gap-2">
                        <button id="btnPrevEmp" onclick="cambiarPaginaEmp(-1)"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl border-2 border-slate-200 text-xs font-black text-slate-500 hover:border-brand-600 hover:text-brand-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-left text-[10px]"></i> Anterior
                        </button>
                        <div id="numerosEmp" class="flex gap-1"></div>
                        <button id="btnNextEmp" onclick="cambiarPaginaEmp(1)"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl border-2 border-slate-200 text-xs font-black text-slate-500 hover:border-brand-600 hover:text-brand-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            Siguiente <i class="fas fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Crear -->
    <!-- Modal Crear -->
    <div id="modalCrear" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-container bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Nuevo Usuario</h3>
                <button onclick="cerrarModal('modalCrear')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form id="formCrear" onsubmit="submitEmpleado(event,'create')">
                <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">ID / Ficha</label>
                        <input type="text" name="cod_nom" placeholder="Ej: E0123..." required
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre Completo</label>
                        <input type="text" name="nom_emple" placeholder="Nombre completo..." required
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Área</label>
                        <select name="id_area" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                            <option value="">Seleccione área...</option>
                            <?php foreach($data['areas'] as $a): ?>
                            <option value="<?= $a['r_id'] ?>" data-nombre="<?= htmlspecialchars($a['r_nombre']) ?>"><?= htmlspecialchars($a['r_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="bg-slate-50 px-5 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalCrear')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                    <button type="submit" id="btnCrear" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl transition-all" style="background:linear-gradient(135deg,#e11d48,#9f1239)">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar -->
    <div id="modalEdicion" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-container bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Editar Usuario</h3>
                <button onclick="cerrarModal('modalEdicion')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form id="formEditar" onsubmit="submitEmpleado(event,'update')">
                <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cédula (no editable)</label>
                        <input type="text" name="cod_nom" id="edit_codigo" readonly
                               class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm font-bold text-slate-400 cursor-not-allowed outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre Completo</label>
                        <input type="text" name="nom_emple" id="edit_nombre" required
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Área</label>
                        <select name="id_area" id="edit_area" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                            <?php foreach($data['areas'] as $a): ?>
                            <option value="<?= $a['r_id'] ?>" data-nombre="<?= htmlspecialchars($a['r_nombre']) ?>"><?= htmlspecialchars($a['r_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="bg-slate-50 px-5 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalEdicion')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                    <button type="submit" id="btnEditar" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl transition-all" style="background:linear-gradient(135deg,#e11d48,#9f1239)">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/utils.js"></script>
    <script>
        // ── Abrir modal de edición precargando datos ───────────────────────────
        function abrirEdicionEmpleado(codigo, nombre, idArea) {
            document.getElementById('edit_codigo').value = codigo;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_area').value   = idArea;
            abrirModal('modalEdicion');
        }

        // ── Actualizar el token CSRF en todos los formularios de la página ──
        function refrescarCsrf(nuevoToken) {
            document.querySelectorAll('input[name="csrf_token"]').forEach(el => {
                el.value = nuevoToken;
            });
        }

        // ── Submit AJAX: no recarga la página, actualiza el DOM ───────────────
        async function submitEmpleado(e, accion) {
            e.preventDefault();
            const form   = e.target;
            const btn    = form.querySelector('[type=submit]');
            const txtOrig = btn.textContent;
            btn.disabled    = true;
            btn.textContent = 'Guardando…';

            try {
                const fd  = new FormData(form);
                const res = await fetch(
                    `../controllers/parametrosController.php?ent=empleado&action=${accion}`,
                    { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd }
                );
                const json = await res.json();

                if (json.success) {
                    if (accion === 'create') {
                        agregarFilaDOM(fd);
                        form.reset();
                    } else {
                        actualizarFilaDOM(fd);
                    }
                    // Actualizar token CSRF en ambos formularios para la próxima operación
                    if (json.csrf) refrescarCsrf(json.csrf);
                    cerrarModal(accion === 'create' ? 'modalCrear' : 'modalEdicion');
                    Alerts.success('¡Listo!', json.msg);
                } else {
                    // Aunque falle, el token pudo haber rotado — actualizarlo igual
                    if (json.csrf) refrescarCsrf(json.csrf);
                    Alerts.error('Error', json.msg);
                }
            } catch (err) {
                Alerts.error('Error de conexión', err.message);
            } finally {
                btn.disabled    = false;
                btn.textContent = txtOrig;
            }
        }

        // ── Construir HTML de una fila de empleado ────────────────────────────
        function htmlFila(codigo, nombre, areaNombre, idArea) {
            const inicial = nombre.charAt(0).toUpperCase();
            const codigoSafe   = codigo.replace(/'/g, "\\'");
            const nombreSafe   = nombre.replace(/'/g, "\\'");
            return `
            <div class="fila-empleado flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors group"
                 data-nombre="${nombre.toLowerCase()}"
                 data-codigo="${codigo.toLowerCase()}"
                 data-area="${areaNombre}">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-black text-sm shrink-0">
                    ${inicial}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-800 text-sm truncate">${nombre}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-0.5">
                        <span class="font-mono text-[10px] text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded">${codigo}</span>
                        <span class="text-[10px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded truncate max-w-[140px]">${areaNombre}</span>
                    </div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button onclick="abrirEdicionEmpleado('${codigoSafe}','${nombreSafe}',${idArea})"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-all">
                        <i class="fas fa-pen text-xs"></i>
                    </button>
                    <button onclick="Alerts.confirmDelete('../controllers/parametrosController.php?ent=empleado&action=delete&id=${codigoSafe}')"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>
            </div>`;
        }

        // ── Agregar fila nueva al DOM y al array de filas ────────────────────
        function agregarFilaDOM(fd) {
            const codigo    = fd.get('cod_nom').trim();
            const nombre    = fd.get('nom_emple').trim();
            const idArea    = fd.get('id_area');
            const sel       = document.querySelector(`#formCrear select[name=id_area]`);
            const areaNombre = sel.options[sel.selectedIndex]?.dataset.nombre ?? '';

            const tmp = document.createElement('div');
            tmp.innerHTML = htmlFila(codigo, nombre, areaNombre, idArea).trim();
            const nuevaFila = tmp.firstElementChild;

            document.getElementById('listaEmpleados').appendChild(nuevaFila);
            todasFilasEmp.push(nuevaFila);

            // Agregar área al filtro si es nueva
            const filtroArea = document.getElementById('filtroArea');
            const opciones   = Array.from(filtroArea.options).map(o => o.value);
            if (areaNombre && !opciones.includes(areaNombre)) {
                const opt = document.createElement('option');
                opt.value       = areaNombre;
                opt.textContent = areaNombre;
                filtroArea.appendChild(opt);
            }

            filtrarEmpleados();
        }

        // ── Actualizar fila existente en el DOM ──────────────────────────────
        function actualizarFilaDOM(fd) {
            const codigo     = fd.get('cod_nom').trim();
            const nombre     = fd.get('nom_emple').trim();
            const idArea     = fd.get('id_area');
            const sel        = document.querySelector('#formEditar select[name=id_area]');
            const areaNombre = sel.options[sel.selectedIndex]?.dataset.nombre ?? '';

            // Buscar la fila existente por código
            const fila = todasFilasEmp.find(f => f.dataset.codigo === codigo.toLowerCase());
            if (!fila) return;

            const tmp = document.createElement('div');
            tmp.innerHTML = htmlFila(codigo, nombre, areaNombre, idArea).trim();
            const nuevaFila = tmp.firstElementChild;

            // Reemplazar en el DOM
            fila.replaceWith(nuevaFila);

            // Actualizar referencia en el array
            const idx = todasFilasEmp.indexOf(fila);
            todasFilasEmp[idx] = nuevaFila;

            // Re-aplicar el filtro actual para que la fila respete el estado visible
            filtrarEmpleados();
        }

        // ── Filtro + paginación (sin cambios de lógica) ───────────────────────
        const POR_PAGINA_EMP = 15;
        let paginaEmp = 1;
        const todasFilasEmp = Array.from(document.querySelectorAll('.fila-empleado'));
        let filasFiltradasEmp = [...todasFilasEmp];

        function filtrarEmpleados() {
            const q    = document.getElementById('buscadorEmpleados').value.toLowerCase().trim();
            const area = document.getElementById('filtroArea').value;
            filasFiltradasEmp = todasFilasEmp.filter(f => {
                const ok_txt  = !q || f.dataset.nombre.includes(q) || f.dataset.codigo.includes(q) || f.dataset.area.toLowerCase().includes(q);
                const ok_area = !area || f.dataset.area === area;
                return ok_txt && ok_area;
            });
            paginaEmp = 1;
            renderEmp();
        }

        function renderEmp() {
            const total  = filasFiltradasEmp.length;
            const totPag = Math.max(1, Math.ceil(total / POR_PAGINA_EMP));
            if (paginaEmp > totPag) paginaEmp = totPag;
            const ini = (paginaEmp - 1) * POR_PAGINA_EMP;
            const fin = ini + POR_PAGINA_EMP;

            todasFilasEmp.forEach(f => f.style.display = 'none');
            filasFiltradasEmp.forEach((f, i) => { f.style.display = (i >= ini && i < fin) ? '' : 'none'; });

            document.getElementById('sinResultados').classList.toggle('hidden', total > 0);

            const desde = total === 0 ? 0 : ini + 1;
            const hasta = Math.min(fin, total);
            document.getElementById('contadorEmpleados').textContent =
                total === 0 ? 'Sin resultados'
                : `Mostrando ${desde}–${hasta} de ${total} usuario${total !== 1 ? 's' : ''}`;

            document.getElementById('btnPrevEmp').disabled = paginaEmp <= 1;
            document.getElementById('btnNextEmp').disabled = paginaEmp >= totPag;
            document.getElementById('infoPaginaEmp').textContent = totPag > 1 ? `Página ${paginaEmp} de ${totPag}` : '';

            const numDiv = document.getElementById('numerosEmp');
            numDiv.innerHTML = '';
            if (totPag > 1) {
                generarRango(paginaEmp, totPag).forEach(n => {
                    if (n === '...') {
                        numDiv.innerHTML += `<span class="px-2 text-slate-400 text-xs font-bold self-center">…</span>`;
                    } else {
                        const cls = n === paginaEmp
                            ? 'bg-brand-600 text-white border-brand-600'
                            : 'border-slate-200 text-slate-500 hover:border-brand-600 hover:text-brand-600';
                        numDiv.innerHTML += `<button onclick="irEmp(${n})" class="w-8 h-8 flex items-center justify-center rounded-xl border-2 text-xs font-black transition-all ${cls}">${n}</button>`;
                    }
                });
            }
            document.getElementById('paginadorEmpleados').style.display = totPag <= 1 ? 'none' : 'flex';
        }

        function generarRango(actual, total) {
            if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
            if (actual <= 4) return [1,2,3,4,5,'...',total];
            if (actual >= total - 3) return [1,'...',total-4,total-3,total-2,total-1,total];
            return [1,'...',actual-1,actual,actual+1,'...',total];
        }

        function cambiarPaginaEmp(dir) {
            const tot = Math.ceil(filasFiltradasEmp.length / POR_PAGINA_EMP);
            paginaEmp = Math.max(1, Math.min(tot, paginaEmp + dir));
            renderEmp();
        }

        function irEmp(n) { paginaEmp = n; renderEmp(); }

        document.getElementById('buscadorEmpleados').addEventListener('input', filtrarEmpleados);
        document.getElementById('filtroArea').addEventListener('change', filtrarEmpleados);
        renderEmp();
    </script>
    <script src="../assets/js/dark_mode.js"></script>
</body>
</html>