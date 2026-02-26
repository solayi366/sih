<?php
require_once '../controllers/parametrosController.php';
$data = ParametrosController::getRRHHData();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custodios de Activos | SIH_QR</title>
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
                        <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">Custodios</h1>
                        <p class="text-slate-500 text-sm mt-0.5">Personal responsable del equipamiento.</p>
                    </div>
                    <button onclick="abrirModal('modalCrear')"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs tracking-widest text-white shadow-lg hover:scale-105 transition-all shrink-0"
                            style="background:linear-gradient(135deg,#e11d48,#9f1239)">
                        <i class="fas fa-user-plus"></i>
                        <span class="hidden sm:inline">Añadir Custodio</span>
                        <span class="sm:hidden">Nuevo</span>
                    </button>
                </div>

                <p class="text-xs font-bold text-slate-400 mb-3"><?= count($data['empleados']) ?> custodio<?= count($data['empleados'])!=1?'s':'' ?> registrado<?= count($data['empleados'])!=1?'s':'' ?></p>

                <!-- Lista -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                    <!-- Cabecera desktop -->
                    <div class="hidden md:grid grid-cols-[130px_1fr_180px_80px] gap-4 px-5 py-3 bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <span>Ficha / ID</span>
                        <span>Nombre</span>
                        <span>Área</span>
                        <span class="text-right">Acción</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <?php foreach ($data['empleados'] as $e): ?>
                        <div class="flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors group">

                            <!-- Avatar -->
                            <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-black text-sm shrink-0">
                                <?= strtoupper(substr($e['r_nombre'], 0, 1)) ?>
                            </div>

                            <!-- Info principal -->
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-800 text-sm truncate"><?= htmlspecialchars($e['r_nombre']) ?></p>
                                <div class="flex flex-wrap items-center gap-2 mt-0.5">
                                    <span class="font-mono text-[10px] text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded"><?= htmlspecialchars($e['r_codigo']) ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded truncate max-w-[140px]"><?= htmlspecialchars($e['r_area']) ?></span>
                                </div>
                            </div>

                            <!-- Acciones -->
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

                        <?php if (empty($data['empleados'])): ?>
                        <div class="py-16 text-center text-slate-400">
                            <i class="fas fa-users text-3xl mb-3 opacity-30"></i>
                            <p class="text-sm font-bold">No hay custodios registrados</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Crear -->
    <div id="modalCrear" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-container bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Nuevo Custodio</h3>
                <button onclick="cerrarModal('modalCrear')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=empleado&action=create" method="POST">
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
                            <option value="<?= $a['r_id'] ?>"><?= htmlspecialchars($a['r_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="bg-slate-50 px-5 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalCrear')" class="flex-1 py-2.5 text-sm font-bold text-slate-500 rounded-xl border-2 border-slate-200 hover:bg-slate-100 transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 text-sm font-black text-white rounded-xl transition-all" style="background:linear-gradient(135deg,#e11d48,#9f1239)">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar -->
    <div id="modalEdicion" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-container bg-white w-full sm:max-w-md sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black tracking-widest">Editar Custodio</h3>
                <button onclick="cerrarModal('modalEdicion')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=empleado&action=update" method="POST">
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
                            <option value="<?= $a['r_id'] ?>"><?= htmlspecialchars($a['r_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
        function abrirEdicionEmpleado(codigo, nombre, idArea) {
            document.getElementById('edit_codigo').value = codigo;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_area').value = idArea;
            abrirModal('modalEdicion');
        }
    </script>
    <script src="../assets/js/dark_mode.js"></script>
</body>
</html>
