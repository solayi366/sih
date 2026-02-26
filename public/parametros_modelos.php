<?php
require_once '../controllers/parametrosController.php';
$data = ParametrosController::getHardwareData();
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

                <p class="text-xs font-bold text-slate-400 mb-3"><?= count($data['modelos']) ?> modelo<?= count($data['modelos'])!=1?'s':'' ?> registrado<?= count($data['modelos'])!=1?'s':'' ?></p>

                <!-- Lista -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                    <!-- Cabecera desktop -->
                    <div class="hidden md:grid grid-cols-[60px_1fr_150px_120px_80px] gap-4 px-5 py-3 bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <span># ID</span>
                        <span>Modelo</span>
                        <span>Marca</span>
                        <span>Tipo</span>
                        <span class="text-right">Acción</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <?php foreach ($data['modelos'] as $mod): ?>
                        <div class="flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors">

                            <!-- Icono -->
                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                <i class="fas fa-cube text-slate-500 text-sm"></i>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-800 text-sm truncate"><?= htmlspecialchars($mod['r_modelo']) ?></p>
                                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                    <span class="text-[10px] font-black text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded"><?= htmlspecialchars($mod['r_marca']) ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold"><?= htmlspecialchars($mod['r_tipo']) ?></span>
                                </div>
                            </div>

                            <!-- Acciones -->
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

                        <?php if (empty($data['modelos'])): ?>
                        <div class="py-16 text-center text-slate-400">
                            <i class="fas fa-cube text-3xl mb-3 opacity-30"></i>
                            <p class="text-sm font-bold">No hay modelos registrados</p>
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
                <h3 class="text-white text-xs font-black tracking-widest">Registrar Modelo</h3>
                <button onclick="cerrarModal('modalCrear')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=modelo&action=create" method="POST">
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
    </script>
    <script src="../assets/js/dark_mode.js"></script>
</body>
</html>
