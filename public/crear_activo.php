<?php
require_once '../controllers/activoController.php';
$data = ActivoController::getFormData();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Activo | SIH_QR</title>
        <!-- Dark mode: aplicar clase antes del render para evitar flash -->
    <script>
        (function(){
            var t = localStorage.getItem('sihTheme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <link rel="stylesheet" href="../assets/css/custom.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
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
        .input-ruby { flex: 1; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 700; outline: none; }
        .label-ruby { font-size: 0.6875rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 0.5rem; display: block; }
    </style>
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
</head>

<body class="text-slate-800 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative bg-slate-50/50 w-full">
        <div class="flex-1 overflow-y-auto p-4 md:p-10 scroll-smooth w-full">
            
            <form action="../controllers/activoController.php" method="POST" id="formCrear" class="max-w-6xl mx-auto space-y-8">
                
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-10 bg-brand-600 rounded-full shadow-[0_0_10px_#e11d48]"></div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tighter uppercase">Registrar <span class="text-brand-600">Nuevo Activo</span></h1>
                    </div>
                    <button type="button" class="px-5 py-2.5 bg-white border-2 border-slate-200 text-brand-600 rounded-xl font-black text-xs hover:border-brand-600 transition-all flex items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCargaExcel">
                        <i class="fas fa-file-excel"></i> IMPORTAR HOJA DE VIDA
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-8 space-y-6">
                        <div class="bg-white rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-sm">
                            <h3 class="label-ruby !mb-8 pb-2 border-b-2 border-slate-50">Datos del Equipo</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="label-ruby">Tipo de Equipo *</label>
                                    <div class="input-group-ruby">
                                        <select class="input-ruby cursor-pointer" name="id_tipoequi" id="selectTipo" required onchange="toggleCampos()">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($data['tipos'] as $t): ?>
                                                <option value="<?= $t['id_tipoequi'] ?>" data-nombre="<?= $t['nom_tipo'] ?>"><?= $t['nom_tipo'] ?></option>
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
                                                <option value="<?= $m['id_marca'] ?>"><?= $m['nom_marca'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div id="col-modelo" style="display:none;"><label class="label-ruby">Modelo</label><div class="input-group-ruby"><select class="input-ruby cursor-pointer" name="id_modelo" id="selector-modelo"><option value="">(Genérico)</option><?php foreach ($data['modelos'] as $mod): ?><option value="<?= $mod['id_modelo'] ?>" data-tipo="<?= $mod['id_tipoequi'] ?>"><?= $mod['nom_modelo'] ?></option><?php endforeach; ?></select></div></div>
                                <!-- col-referencia y col-serial ahora son dinámicos -->
                            </div>
                        </div>

                        <!-- ── CAMPOS DINÁMICOS POR TIPO ─────────────────────────── -->
                        <div id="panel-campos-dinamicos" class="hidden">
                            <!-- Spinner mientras carga -->
                            <div id="campos-loading" class="bg-white rounded-[2.5rem] border-2 border-slate-100 p-8 flex items-center gap-4">
                                <i class="fas fa-spinner fa-spin text-brand-600 text-xl"></i>
                                <span class="text-xs font-bold text-slate-400">Cargando campos del tipo seleccionado...</span>
                            </div>
                            <!-- Grid de campos -->
                            <div id="campos-grid" class="hidden bg-white rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-sm">
                                <h3 class="label-ruby !mb-6 pb-2 border-b-2 border-slate-50" id="campos-titulo">
                                    <i class="fas fa-sliders mr-1 text-brand-600"></i> Campos del Dispositivo
                                </h3>
                                <div id="campos-contenedor" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- renderizado por JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Campos de red ocultos para compatibilidad con Excel import y controller -->
                        <input type="hidden" name="hostname"    id="input-hostname">
                        <input type="hidden" name="ip_equipo"   id="input-ip">
                        <input type="hidden" name="mac_activo"  id="input-mac">
                        <input type="hidden" name="referencia"  id="input-referencia">
                        <input type="hidden" name="serial"      id="input-serial" required>

                        <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-sm font-black uppercase tracking-widest text-brand-500">Accesorios Rápidos</h3>
                                <div class="flex gap-2">
                                    <select id="quickAdd" class="bg-white/10 border-0 rounded-lg text-xs font-bold px-3 py-2 outline-none">
                                        <option value="" class="text-slate-800">Añadir tipo...</option>
                                        <?php foreach($data['tipos'] as $t): ?>
                                            <option value="<?= $t['id_tipoequi'] ?>" data-nombre="<?= $t['nom_tipo'] ?>" class="text-slate-800"><?= $t['nom_tipo'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" onclick="addAccessory()" class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center hover:bg-brand-700 transition-colors">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left" id="tablaAccesorios">
                                    <thead class="border-b border-white/10">
                                        <tr class="text-[9px] font-black text-slate-500 uppercase tracking-widest">
                                            <th class="pb-4 px-2">Tipo</th>
                                            <th class="pb-4 px-2">Serial / Ref</th>
                                            <th class="pb-4 text-right">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 space-y-6">
                        <div class="bg-white rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-sm">
                            <h3 class="label-ruby !text-slate-400 mb-8 border-b border-slate-50 pb-2">Asignación</h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="label-ruby">Cédula Responsable</label>
                                    <div class="input-group-ruby">
                                        <button type="button" class="input-icon-box" onclick="buscarEmpleado()"><i class="fas fa-search" id="icon-search"></i></button>
                                        <input type="text" name="cod_responsable" id="input-responsable" class="input-ruby" placeholder="ID Empleado">
                                    </div>
                                    <div id="alerta-empleado" class="mt-3 p-3 bg-brand-50 rounded-xl border border-brand-100 text-[10px] font-bold text-brand-700"></div>
                                </div>
                                <input type="text" name="nom_nuevo_empleado" id="nom_nuevo_empleado" class="input-group-ruby w-full px-4 py-3 text-sm font-bold outline-none" placeholder="Nombre completo">
                                <select name="id_area_nuevo" id="id_area_nuevo" class="input-group-ruby w-full px-4 py-3 text-sm font-bold outline-none">
                                    <option value="">Seleccione Área...</option>
                                    <?php foreach ($data['areas'] as $a): ?><option value="<?= $a['id_area'] ?>"><?= $a['nom_area'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="grupo-padre" class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl border-2 border-slate-800">
                            <label class="label-ruby !text-brand-500">Equipo Principal (Padre)</label>
                            <select name="id_padre_activo" class="w-full bg-white/5 border-2 border-white/10 rounded-xl py-3 px-4 text-xs font-bold outline-none">
                                <option value="">No, es equipo principal</option>
                                <?php foreach ($data['padres'] as $p): ?><option value="<?= $p['id_activo'] ?>" class="text-slate-800"><?= $p['serial'] ?> (<?= $p['referencia'] ?>)</option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="bg-white rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-sm">
                            <label class="label-ruby">Estado Inicial</label>
                            <div class="input-group-ruby">
                                <select name="estado" class="input-ruby"><option value="Bueno">Bueno</option><option value="Malo">Malo</option><option value="Reparacion">En Reparación</option></select>
                            </div>
                        </div>

                        <input type="hidden" name="accesorios_json_final" id="accesoriosJson">
                        <input type="hidden" name="campos_dinamicos_json" id="campos_dinamicos_json">
                        <button type="button" onclick="prepararYEnviar()" class="w-full py-5 red-gradient text-white rounded-[2rem] font-black uppercase shadow-xl hover:scale-[1.02] transition-all flex items-center justify-center gap-3">
                            <i class="fas fa-save text-xl"></i> GUARDAR TODO
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <div class="modal fade fixed inset-0 z-[1060] hidden overflow-y-auto" id="modalCargaExcel">
        <div class="modal-dialog relative w-auto max-w-lg my-8 mx-auto px-4">
            <div class="modal-content border-2 border-brand-100 shadow-2xl bg-white rounded-[2.5rem] overflow-hidden">
                <div class="p-6 bg-slate-900 text-white flex justify-between items-center">
                    <h5 class="text-xs font-black uppercase tracking-widest">Analizar Hoja de Vida</h5>
                    <button type="button" class="text-white/50" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-10 text-center">
                    <i class="fas fa-file-upload text-3xl text-brand-600 mb-4"></i>
                    <p class="text-xs text-slate-500 font-bold uppercase mb-8">Suba el archivo .xlsx para detectar los datos.</p>
                    <input type="file" id="excelFileInput" accept=".xlsx" class="text-xs font-bold text-slate-400">
                </div>
                <div class="p-6 bg-slate-50 flex gap-4">
                    <button type="button" class="flex-1 py-3 text-xs font-bold text-slate-400" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="btnAnalizar" onclick="procesarExcel()" class="flex-1 py-3 red-gradient text-white rounded-xl text-xs font-black shadow-lg uppercase">Analizar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script>
        // Función para añadir accesorios a la tabla
        function addAccessory() {
            const select = document.getElementById('quickAdd');
            const id = select.value;
            const nombre = select.options[select.selectedIndex]?.getAttribute('data-nombre');
            if(!id) return;
            const tbody = document.querySelector('#tablaAccesorios tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="py-4 px-2"><input type="hidden" class="acc-id" value="${id}"><span class="text-xs font-black text-brand-500 uppercase">${nombre}</span></td>
                <td class="py-4 px-2 space-y-2">
                    <input type="text" class="acc-serial block w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-[11px] text-white font-mono outline-none" placeholder="Serial">
                    <input type="text" class="acc-ref block w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-[11px] text-slate-400 outline-none" placeholder="Ref">
                </td>
                <td class="py-4 text-right"><button type="button" class="text-slate-500 hover:text-brand-600 p-2" onclick="this.closest('tr').remove()"><i class="fas fa-trash-alt text-xs"></i></button></td>
            `;
            tbody.appendChild(row);
            select.value = "";
        }

        /**
         * PROCESAR EXCEL (Lógica Inteligente)
         * Envía el archivo, recibe el JSON e inyecta en el DOM sin recargar.
         */
        async function procesarExcel() {
            const fileInput = document.getElementById('excelFileInput');
            if (!fileInput.files[0]) return;
            const btn = document.getElementById('btnAnalizar');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            try {
                const res = await fetch('../controllers/activoController.php?action=analizar', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.success) {
                    const p = data.principal;

                    // 1. Seleccionar Tipo de equipo (esto dispara cargarCamposTipo)
                    seleccionarPorTexto('selectTipo', p.tipo);

                    // 2. Esperar a que se carguen los campos dinámicos del tipo, luego llenar
                    await new Promise(resolve => setTimeout(resolve, 600));

                    // Llenar campos dinámicos por nombre de campo
                    const mapeoExcel = {
                        'serial':    p.serial,
                        'referencia': p.referencia,
                        'hostname':  p.hostname,
                        'ip_equipo': p.ip,
                        'mac_activo': p.mac,
                    };
                    for (const [nombre, valor] of Object.entries(mapeoExcel)) {
                        if (!valor) continue;
                        // Buscar el input dinámico por data-campo-nombre
                        const div = document.querySelector(`#campos-contenedor [data-campo-nombre="${nombre}"]`);
                        if (div) {
                            const inp = div.querySelector('input,select,textarea');
                            if (inp) { inp.value = valor; inp.dispatchEvent(new Event('input')); }
                        }
                        // También llenar los hidden de compatibilidad
                        const hiddenMap = {
                            'serial':'input-serial','hostname':'input-hostname',
                            'ip_equipo':'input-ip','mac_activo':'input-mac','referencia':'input-referencia'
                        };
                        if (hiddenMap[nombre]) {
                            const h = document.getElementById(hiddenMap[nombre]);
                            if (h) h.value = valor;
                        }
                    }

                    // También llenar el responsable
                    document.getElementById('input-responsable').value = p.responsable || '';

                    // 4. Marca: buscar en el select, si no existe crearla via API
                    await seleccionarOCrearMarca(p.marca);

                    // 5. Inyectar Accesorios
                    document.querySelector('#tablaAccesorios tbody').innerHTML = "";
                    data.accesorios.forEach(acc => {
                        const quickAdd = document.getElementById('quickAdd');
                        for(let opt of quickAdd.options) {
                            if(opt.text.toUpperCase().includes(acc.tipo.toUpperCase())) {
                                quickAdd.value = opt.value;
                                addAccessory();
                                const lastRow = document.querySelector('#tablaAccesorios tbody tr:last-child');
                                lastRow.querySelector('.acc-serial').value = acc.serial || '';
                                lastRow.querySelector('.acc-ref').value    = acc.referencia || acc.ref || '';
                                break;
                            }
                        }
                    });

                    bootstrap.Modal.getInstance(document.getElementById('modalCargaExcel')).hide();
                    buscarEmpleado();
                }
            } finally { 
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // Helper para encontrar ID por coincidencia de texto
        // Selecciona una opción en un <select> por id, buscando coincidencia parcial de texto
        function seleccionarPorTexto(selectId, texto) {
            const select = document.getElementById(selectId);
            if (!select || !texto) return false;
            const search = texto.toUpperCase().trim();

            // Palabras clave alternativas para tipo computador
            const aliasComputador = ['COMPUTADOR', 'PORTATIL', 'PORTATIL', 'PC', 'LAPTOP', 'NOTEBOOK', 'AIO', 'TODO EN UNO', 'SERVIDOR'];
            const esComputador = aliasComputador.some(a => search.includes(a));

            for (let opt of select.options) {
                const optText = opt.text.toUpperCase().trim();
                // Coincidencia directa
                if (optText.includes(search) || search.includes(optText)) {
                    select.value = opt.value;
                    select.dispatchEvent(new Event('change'));
                    return true;
                }
                // Si es un tipo computador, aceptar cualquier opción que contenga palabras clave
                if (esComputador && aliasComputador.some(a => optText.includes(a))) {
                    select.value = opt.value;
                    select.dispatchEvent(new Event('change'));
                    return true;
                }
            }
            return false;
        }

        // Busca la marca en el select; si no existe la crea en la BD y agrega la opción
        async function seleccionarOCrearMarca(marca) {
            if (!marca) return;

            // Intentar seleccionar primero
            const encontrada = seleccionarPorTexto('selectMarca', marca);
            if (encontrada) return;

            // No existe → crear via API
            try {
                const fd = new FormData();
                fd.append('accion', 'crear_marca');
                fd.append('nom_marca', marca.trim());
                // Enviar el tipo de equipo seleccionado para asociar la marca correctamente
                const tipoSeleccionado = document.getElementById('selectTipo')?.value || 0;
                fd.append('id_tipo', tipoSeleccionado);
                const res  = await fetch('../controllers/parametrosController.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success && data.id) {
                    // Agregar la nueva opción al select y seleccionarla
                    const select  = document.getElementById('selectMarca');
                    const option  = new Option(marca.trim(), data.id, true, true);
                    select.add(option);
                    select.value = data.id;
                    console.log('Marca creada automáticamente:', marca, 'ID:', data.id);
                } else {
                    console.warn('No se pudo crear la marca:', data.msg || 'error desconocido');
                }
            } catch(e) {
                console.warn('Error al crear marca:', e);
            }
        }

        function prepararYEnviar() {
            // 1. Recoger accesorios
            const accesorios = [];
            document.querySelectorAll('#tablaAccesorios tbody tr').forEach(tr => {
                accesorios.push({
                    tipo_id: tr.querySelector('.acc-id').value,
                    serial: tr.querySelector('.acc-serial').value,
                    referencia: tr.querySelector('.acc-ref').value
                });
            });
            document.getElementById('accesoriosJson').value = JSON.stringify(accesorios);

            // 2. Sincronizar campos base dinámicos → hidden del controller
            const mapeo = {
                'serial':    'input-serial',
                'hostname':  'input-hostname',
                'ip_equipo': 'input-ip',
                'mac_activo':'input-mac',
                'referencia':'input-referencia',
            };
            document.querySelectorAll('#campos-contenedor [data-campo-nombre]').forEach(div => {
                const nombre = div.dataset.campoNombre;
                const hiddenId = mapeo[nombre];
                if (hiddenId) {
                    const input = div.querySelector('input,select,textarea');
                    const hidden = document.getElementById(hiddenId);
                    if (input && hidden) hidden.value = input.value;
                }
            });

            // 3. Recoger valores de campos dinámicos extra (no base) como JSON
            const valoresDin = {};
            document.querySelectorAll('[name^="campo_din_"]').forEach(inp => {
                const id = inp.name.replace('campo_din_', '');
                if (inp.type === 'checkbox') {
                    if (inp.checked) valoresDin[id] = '1';
                } else if (inp.value !== '') {
                    valoresDin[id] = inp.value;
                }
            });
            document.getElementById('campos_dinamicos_json').value = JSON.stringify(valoresDin);

            // 4. Validar serial (obligatorio siempre)
            const serial = document.getElementById('input-serial').value.trim();
            if (!serial) {
                // Intentar encontrar el campo serial en el formulario dinámico
                const serialDin = document.querySelector('#campos-contenedor [data-campo-nombre="serial"] input');
                if (serialDin && serialDin.value.trim()) {
                    document.getElementById('input-serial').value = serialDin.value.trim();
                } else {
                    alert('El campo Serial es obligatorio.');
                    return;
                }
            }

            document.getElementById('formCrear').submit();
        }

        // ── CAMPOS DINÁMICOS ──────────────────────────────────────────────────────
        // Genera el input correcto según tipo_dato del campo
        function renderInput(campo, valorActual = '') {
            const id  = `campo_din_${campo.id_campo}`;
            const req = campo.requerido ? 'required' : '';
            const cls = 'input-ruby';

            switch (campo.tipo_dato) {
                case 'numero':
                    return `<input type="number" id="${id}" name="campo_din_${campo.id_campo}"
                                class="${cls}" value="${valorActual}" ${req} step="any" placeholder="0">`;

                case 'booleano':
                    const chk = valorActual === '1' || valorActual === 'true' || valorActual === 'SI';
                    return `<div class="flex items-center gap-3 py-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="campo_din_${campo.id_campo}" value="0">
                            <input type="checkbox" id="${id}" name="campo_din_${campo.id_campo}"
                                   value="1" class="sr-only peer" ${chk ? 'checked' : ''} ${req}>
                            <div class="w-12 h-6 bg-slate-200 rounded-full peer peer-checked:bg-brand-600
                                        after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                                        after:bg-white after:rounded-full after:h-5 after:w-5
                                        after:transition-all peer-checked:after:translate-x-6"></div>
                        </label>
                        <span class="text-xs font-bold text-slate-500">Sí / No</span>
                    </div>`;

                case 'fecha':
                    return `<input type="date" id="${id}" name="campo_din_${campo.id_campo}"
                                class="${cls}" value="${valorActual}" ${req}>`;

                case 'lista':
                    try {
                        const opts = JSON.parse(campo.opciones || '[]');
                        const optsHtml = opts.map(o =>
                            `<option value="${o}" ${valorActual === o ? 'selected' : ''}>${o}</option>`
                        ).join('');
                        return `<select id="${id}" name="campo_din_${campo.id_campo}" class="${cls} cursor-pointer" ${req}>
                                    <option value="">Seleccione...</option>${optsHtml}
                                </select>`;
                    } catch(e) {
                        return `<input type="text" id="${id}" name="campo_din_${campo.id_campo}" class="${cls}" value="${valorActual}" ${req}>`;
                    }

                default: // texto + campos base especiales
                    let placeholder = '';
                    let extra = '';
                    if (campo.nombre === 'serial')     { placeholder = 'Número de serie...'; extra = 'font-mono uppercase'; }
                    if (campo.nombre === 'hostname')    { placeholder = 'PC-USUARIO-01'; extra = 'font-mono'; }
                    if (campo.nombre === 'ip_equipo')   { placeholder = '192.168.1.100'; extra = 'font-mono'; }
                    if (campo.nombre === 'mac_activo')  { placeholder = '00:1A:2B:3C:4D:5E'; extra = 'font-mono'; }
                    return `<input type="text" id="${id}" name="campo_din_${campo.id_campo}"
                                class="${cls} ${extra}" value="${valorActual}" ${req} placeholder="${placeholder}">`;
            }
        }

        // Carga los campos del tipo seleccionado y renderiza el formulario
        async function cargarCamposTipo(idTipo) {
            if (!idTipo) {
                document.getElementById('panel-campos-dinamicos').classList.add('hidden');
                return;
            }

            document.getElementById('panel-campos-dinamicos').classList.remove('hidden');
            document.getElementById('campos-loading').classList.remove('hidden');
            document.getElementById('campos-grid').classList.add('hidden');

            try {
                const res  = await fetch(`../controllers/parametrosController.php?action=getCamposFormulario&id_tipo=${idTipo}`);
                const data = await res.json();

                const contenedor = document.getElementById('campos-contenedor');
                contenedor.innerHTML = '';

                if (!data.campos || data.campos.length === 0) {
                    contenedor.innerHTML = `
                        <div class="md:col-span-2 text-center py-6 text-slate-300">
                            <i class="fas fa-puzzle-piece text-3xl mb-2"></i>
                            <p class="text-xs font-bold">Este tipo no tiene campos configurados.<br>
                            Ve a <a href="parametros_tipos.php" class="text-brand-600 underline">Tipos de Equipo</a> para configurarlos.</p>
                        </div>`;
                } else {
                    data.campos.forEach(campo => {
                        const isFullWidth = ['descripcion','notas','observaciones'].includes(campo.nombre);
                        const div = document.createElement('div');
                        div.className = isFullWidth ? 'md:col-span-2' : '';
                        div.dataset.campoNombre = campo.nombre;
                        div.innerHTML = `
                            <label class="label-ruby">
                                <i class="fas ${campo.icono} mr-1 text-brand-600"></i>
                                ${campo.etiqueta}
                                ${campo.requerido ? '<span class="text-brand-600">*</span>' : ''}
                            </label>
                            <div class="input-group-ruby">
                                ${renderInput(campo)}
                            </div>`;
                        contenedor.appendChild(div);

                        // Sincronizar campos base con los hidden del controller
                        const input = div.querySelector(`[name="campo_din_${campo.id_campo}"]`);
                        if (input) {
                            const mapeoHidden = {
                                'serial':    'input-serial',
                                'hostname':  'input-hostname',
                                'ip_equipo': 'input-ip',
                                'mac_activo':'input-mac',
                                'referencia':'input-referencia',
                            };
                            if (mapeoHidden[campo.nombre]) {
                                input.addEventListener('input', () => {
                                    const hidden = document.getElementById(mapeoHidden[campo.nombre]);
                                    if (hidden) hidden.value = input.value;
                                });
                            }
                        }
                    });
                }

                document.getElementById('campos-loading').classList.add('hidden');
                document.getElementById('campos-grid').classList.remove('hidden');

            } catch(e) {
                console.error('Error cargando campos:', e);
                document.getElementById('campos-loading').innerHTML =
                    '<p class="text-red-500 text-xs font-bold"><i class="fas fa-exclamation-circle mr-1"></i>Error al cargar campos</p>';
            }
        }

        function toggleCampos() {
            var select = document.getElementById("selectTipo");
            var idTipo = select.value;

            // Mostrar/ocultar modelo
            const nombreTipo = (select.options[select.selectedIndex]?.getAttribute("data-nombre") || "").toUpperCase();
            const esPC = ["TABLET","COMPUTADOR","PORTATIL","PC","SERVIDOR","AIO"].some(t => nombreTipo.includes(t));
            document.getElementById("col-modelo").style.display = esPC ? "block" : "none";

            // Mostrar equipo padre si no es tipo principal
            document.getElementById("grupo-padre").style.display = esPC ? "none" : "block";

            // Cargar campos dinámicos
            if (idTipo) cargarCamposTipo(idTipo);
        }

        async function buscarEmpleado() {
            const id = document.getElementById('input-responsable').value.trim();
            if(!id) return;
            document.getElementById('icon-search').className = 'fas fa-spinner fa-spin';
            try {
                const res = await fetch(`../controllers/get_empleado.php?id=${id}`);
                const d = await res.json();
                if(d.success) {
                    document.getElementById('nom_nuevo_empleado').value = d.nombre;
                    document.getElementById('id_area_nuevo').value = d.id_area;
                    document.getElementById('nom_nuevo_empleado').readOnly = true;
                    document.getElementById('alerta-empleado').innerHTML = `<i class='fas fa-check-circle'></i> Verificado: ${d.nombre}`;
                } else {
                    document.getElementById('nom_nuevo_empleado').value = "";
                    document.getElementById('nom_nuevo_empleado').readOnly = false;
                    document.getElementById('alerta-empleado').innerHTML = "Custodio Nuevo";
                }
            } finally { document.getElementById('icon-search').className = 'fas fa-search'; }
        }
        document.addEventListener('DOMContentLoaded', toggleCampos);
    </script>
    <script src="../assets/js/dark_mode.js"></script>
</body>
</html>