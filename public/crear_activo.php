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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        .input-ruby { flex: 1; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 700; outline: none; }
        .label-ruby { font-size: 0.6875rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 0.5rem; display: block; }
    </style>
</head>

<body class="text-slate-800 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50 w-full">
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
                                <div id="col-referencia"><label class="label-ruby">Referencia</label><div class="input-group-ruby"><input type="text" name="referencia" class="input-ruby" placeholder="Referencia técnica"></div></div>
                                <div class="md:col-span-2">
                                    <label class="label-ruby text-brand-600">Serial del Fabricante (S/N) *</label>
                                    <div class="input-group-ruby border-brand-100">
                                        <input type="text" name="serial" class="input-ruby font-mono uppercase text-base" placeholder="serial..." required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="grupo-redes" class="bg-white rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-sm" style="display:none;">
                            <h3 class="label-ruby !text-brand-600 mb-6"><i class="fas fa-network-wired"></i> Red y Conectividad</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div id="grupo-hostname"><label class="label-ruby">Hostname</label><input type="text" name="hostname" class="input-group-ruby px-4 py-2.5 text-xs font-bold font-mono outline-none"></div>
                                <div><label class="label-ruby">IP</label><input type="text" name="ip_equipo" class="input-group-ruby px-4 py-2.5 text-xs font-bold font-mono outline-none" placeholder="0.0.0.0"></div>
                                <div><label class="label-ruby">MAC</label><input type="text" name="mac_activo" class="input-group-ruby px-4 py-2.5 text-xs font-bold font-mono outline-none" placeholder="00:00:00..."></div>
                            </div>
                        </div>

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
                    // Llenar campos principales
                    document.getElementsByName('serial')[0].value = p.serial;
                    document.getElementsByName('referencia')[0].value = p.referencia;
                    document.getElementsByName('hostname')[0].value = p.hostname || '';
                    document.getElementsByName('ip_equipo')[0].value = p.ip;
                    document.getElementsByName('mac_activo')[0].value = p.mac;
                    document.getElementById('input-responsable').value = p.responsable;

                    // Mapear Tipo y Marca por texto inteligente
                    seleccionarMatch('id_tipoequi', p.tipo);
                    seleccionarMatch('id_marca', p.marca);

                    // Inyectar Accesorios detectados (Filtrados por el controlador)
                    document.querySelector('#tablaAccesorios tbody').innerHTML = "";
                    data.accesorios.forEach(acc => {
                        const quickAdd = document.getElementById('quickAdd');
                        for(let opt of quickAdd.options) {
                            if(opt.text.toUpperCase().includes(acc.tipo.toUpperCase())) {
                                quickAdd.value = opt.value;
                                addAccessory();
                                const lastRow = document.querySelector('#tablaAccesorios tbody tr:last-child');
                                lastRow.querySelector('.acc-serial').value = acc.serial;
                                lastRow.querySelector('.acc-ref').value = acc.ref;
                                break;
                            }
                        }
                    });

                    bootstrap.Modal.getInstance(document.getElementById('modalCargaExcel')).hide();
                    buscarEmpleado(); // Verificar responsable extraído
                    toggleCampos();
                }
            } finally { 
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // Helper para encontrar ID por coincidencia de texto
        function seleccionarMatch(name, texto) {
            const select = document.getElementsByName(name)[0];
            if (!select || !texto) return;
            const search = texto.toUpperCase();
            for (let opt of select.options) {
                if (opt.text.toUpperCase().includes(search) || search.includes(opt.text.toUpperCase())) {
                    select.value = opt.value;
                    break;
                }
            }
        }

        function prepararYEnviar() {
            const accesorios = [];
            document.querySelectorAll('#tablaAccesorios tbody tr').forEach(tr => {
                accesorios.push({
                    tipo_id: tr.querySelector('.acc-id').value,
                    serial: tr.querySelector('.acc-serial').value,
                    referencia: tr.querySelector('.acc-ref').value
                });
            });
            document.getElementById('accesoriosJson').value = JSON.stringify(accesorios);
            document.getElementById('formCrear').submit();
        }

        function toggleCampos() {
            var select = document.getElementById("selectTipo");
            var nombreTipo = (select.options[select.selectedIndex]?.getAttribute("data-nombre") || "").toUpperCase();
            var esPC = ["TABLET", "COMPUTADOR", "PORTATIL", "PC", "SERVIDOR", "AIO"].some(t => nombreTipo.includes(t));
            document.getElementById("grupo-redes").style.display = esPC ? "block" : "none";
            document.getElementById("grupo-padre").style.display = esPC ? "none" : "block";
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
</body>
</html>