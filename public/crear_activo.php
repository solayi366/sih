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
        /* ESTILOS EXACTOS DE TU crear.html (SIN CAMBIOS) */
        body { background-color: #f8fafc; }
        .red-gradient { background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%); }
        
        .input-group-ruby {
            display: flex; align-items: stretch; border: 2px solid #cbd5e1; border-radius: 0.75rem;
            overflow: hidden; transition: all 0.3s;
            background: linear-gradient(to bottom, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .input-group-ruby:focus-within {
            border-color: #e11d48;
            box-shadow: 0 0 0 4px rgba(255,241,242,1), 0 4px 12px rgba(225,29,72,0.1);
            background: linear-gradient(to bottom, #ffffff 0%, #fff1f2 100%);
        }
        .input-icon-box {
            display: flex; align-items: center; justify-content: center; width: 3rem; 
            border-right: 2px solid #e2e8f0; color: #94a3b8; transition: all 0.3s;
            background: linear-gradient(to right, #f1f5f9, #f8fafc); cursor: pointer;
        }
        .input-ruby {
            flex: 1; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 700;
            outline: none; background: transparent;
        }
        .label-ruby {
            font-size: 0.6875rem; font-weight: 800; color: #64748b; text-transform: uppercase;
            letter-spacing: 0.1em; margin-bottom: 0.5rem; display: block;
        }
        .modal { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); }
    </style>
</head>

<body class="text-slate-800 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50 w-full">
        
        <header class="md:hidden h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 z-30 shrink-0 sticky top-0">
            <button onclick="toggleMobileMenu()" class="text-slate-500 hover:text-brand-600 p-2">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <span class="font-black text-lg uppercase text-slate-900">SIH<span class="text-brand-600">QR</span></span>
            <div class="w-8"></div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-10 scroll-smooth w-full">
            
            <form action="../controllers/process_activo.php" method="POST" id="formCrear" class="max-w-6xl mx-auto space-y-8">
                
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-10 bg-brand-600 rounded-full shadow-[0_0_10px_#e11d48]"></div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tighter">Registrar <span class="text-brand-600">Nuevo Activo</span></h1>
                    </div>
                    <button type="button" class="px-5 py-2.5 bg-white border-2 border-slate-200 text-brand-600 rounded-xl font-black text-xs hover:border-brand-600 transition-all shadow-sm flex items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCargaExcel">
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

                                <div id="col-modelo">
                                    <label class="label-ruby">Modelo</label>
                                    <div class="input-group-ruby">
                                        <select class="input-ruby cursor-pointer" name="id_modelo" id="selector-modelo">
                                            <option value="">(Genérico / Sin Modelo)</option>
                                            <?php foreach ($data['modelos'] as $mod): ?>
                                                <option value="<?= $mod['id_modelo'] ?>" data-tipo="<?= $mod['id_tipoequi'] ?>"><?= $mod['nom_modelo'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div id="col-referencia">
                                    <label class="label-ruby">Referencia</label>
                                    <div class="input-group-ruby">
                                        <input type="text" name="referencia" class="input-ruby" placeholder="ESCRIBA AQUÍ">
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="label-ruby text-brand-600">Serial del Fabricante (S/N) *</label>
                                    <div class="input-group-ruby border-brand-100">
                                        <input type="text" name="serial" class="input-ruby font-mono uppercase text-base" placeholder="ESCRIBA EL SERIAL AQUÍ" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="grupo-redes" class="bg-white rounded-[2.5rem] border-2 border-slate-200 p-8 shadow-sm" style="display:none;">
                            <h3 class="label-ruby !text-brand-600 mb-6"><i class="fas fa-network-wired"></i> Red y Conectividad</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div id="grupo-hostname">
                                    <label class="label-ruby">Hostname</label>
                                    <input type="text" name="hostname" class="input-group-ruby px-4 py-2.5 text-xs font-bold font-mono outline-none" placeholder="ESCRIBA AQUÍ">
                                </div>
                                <div>
                                    <label class="label-ruby">IP</label>
                                    <input type="text" name="ip_equipo" class="input-group-ruby px-4 py-2.5 text-xs font-bold font-mono outline-none" placeholder="0.0.0.0">
                                </div>
                                <div>
                                    <label class="label-ruby">MAC</label>
                                    <input type="text" name="mac_activo" class="input-group-ruby px-4 py-2.5 text-xs font-bold font-mono outline-none" placeholder="00:00:00...">
                                </div>
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
                                        <button type="button" class="input-icon-box" onclick="buscarEmpleado()" title="Buscar Empleado">
                                            <i class="fas fa-search" id="icon-search"></i>
                                        </button>
                                        <input type="text" name="cod_responsable" id="input-responsable" class="input-ruby" placeholder="ID Empleado">
                                    </div>
                                    <div id="alerta-empleado" class="mt-3 p-3 bg-brand-50 rounded-xl border border-brand-100 text-[10px] font-bold text-brand-700"></div>
                                </div>
                                <input type="text" name="nom_nuevo_empleado" id="nom_nuevo_empleado" class="input-group-ruby w-full px-4 py-3 text-sm font-bold outline-none" placeholder="Nombre completo">
                                <select name="id_area_nuevo" id="id_area_nuevo" class="input-group-ruby w-full px-4 py-3 text-sm font-bold outline-none">
                                    <option value="">Seleccione Área...</option>
                                    <?php foreach ($data['areas'] as $a): ?>
                                        <option value="<?= $a['id_area'] ?>"><?= $a['nom_area'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="grupo-padre" class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl border-2 border-slate-800">
                            <label class="label-ruby !text-brand-500">Equipo Principal (Padre)</label>
                            <select name="id_padre_activo" class="w-full bg-white/5 border-2 border-white/10 rounded-xl py-3 px-4 text-xs font-bold outline-none">
                                <option value="">No, es equipo principal</option>
                                <?php foreach ($data['padres'] as $p): ?>
                                    <option value="<?= $p['id_activo'] ?>" class="text-slate-800"><?= $p['serial'] ?> (<?= $p['referencia'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-5 red-gradient text-white rounded-[2rem] font-black uppercase shadow-xl hover:scale-[1.02] transition-all flex items-center justify-center gap-3">
                            <i class="fas fa-save text-xl"></i> GUARDAR ACTIVO
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
                    <p class="text-xs text-slate-500 font-bold uppercase mb-8">Suba el archivo .xlsx para detectar activos.</p>
                    <input type="file" class="text-xs font-bold">
                </div>
                <div class="p-6 bg-slate-50 flex gap-4">
                    <button type="button" class="flex-1 py-3 text-xs font-bold text-slate-400" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="flex-1 py-3 red-gradient text-white rounded-xl text-xs font-black shadow-lg">ANALIZAR</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script>
        function toggleCampos() {
            var select = document.getElementById("selectTipo");
            var idTipo = select.value;
            var option = select.options[select.selectedIndex];
            var nombreTipo = (option.getAttribute("data-nombre") || "").toUpperCase();

            var colModelo = document.getElementById("col-modelo");
            var selectModelo = document.getElementById("selector-modelo");
            
            var llevaModelo = ["TABLET", "PORTATIL", "COMPUTADOR", "PC", "MONITOR", "CELULAR"].some(t => nombreTipo.includes(t));
            colModelo.style.display = llevaModelo ? "block" : "none";
            
            if (llevaModelo) {
                var opciones = selectModelo.querySelectorAll('option');
                opciones.forEach(opt => {
                    if (opt.value === "") return;
                    opt.style.display = (opt.getAttribute('data-tipo') == idTipo) ? "block" : "none";
                });
            }

            var esPC = ["TABLET", "COMPUTADOR", "PORTATIL", "PC", "SERVIDOR", "AIO"].some(t => nombreTipo.includes(t));
            document.getElementById("grupo-hostname").style.display = esPC ? "block" : "none";
            document.getElementById("grupo-redes").style.display = esPC ? "block" : "none";
            document.getElementById("grupo-padre").style.display = esPC ? "none" : "block";

            if (esPC) document.getElementsByName("id_padre_activo")[0].value = ""; 
        }

        async function buscarEmpleado() {
            const idInput = document.getElementById('input-responsable');
            const id = idInput.value.trim();
            if(!id) return;
            document.getElementById('icon-search').className = 'fas fa-spinner fa-spin';
            try {
                const res = await fetch(`../controllers/get_empleado.php?id=${id}`);
                const data = await res.json();
                if(data.success) {
                    document.getElementById('nom_nuevo_empleado').value = data.nombre;
                    document.getElementById('id_area_nuevo').value = data.id_area;
                    document.getElementById('nom_nuevo_empleado').readOnly = true;
                    document.getElementById('alerta-empleado').innerHTML = `<i class='fas fa-check-circle'></i> Verificado: ${data.nombre}`;
                }
            } finally { document.getElementById('icon-search').className = 'fas fa-search'; }
        }
        document.addEventListener('DOMContentLoaded', toggleCampos);
    </script>
</body>
</html>