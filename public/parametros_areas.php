<?php
require_once '../controllers/parametrosController.php';
$data = ParametrosController::getRRHHData();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Áreas y Dependencias | SIH_QR</title>
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
            <div class="max-w-3xl mx-auto">

                <!-- Header -->
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">Áreas</h1>
                        <p class="text-slate-500 text-sm mt-0.5">Unidades de negocio del hospital.</p>
                    </div>
                    <button onclick="abrirModal('modalCrear')"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs tracking-widest text-white shadow-lg hover:scale-105 transition-all shrink-0"
                            style="background:linear-gradient(135deg,#e11d48,#9f1239)">
                        <i class="fas fa-plus"></i>
                        <span class="hidden sm:inline">Añadir Área</span>
                        <span class="sm:hidden">Nueva</span>
                    </button>
                </div>

                <!-- Contador -->
                <p class="text-xs font-bold text-slate-400 mb-3"><?= count($data['areas']) ?> área<?= count($data['areas'])!=1?'s':'' ?> registrada<?= count($data['areas'])!=1?'s':'' ?></p>

                <!-- Lista -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                    <!-- Cabecera (solo md+) -->
                    <div class="hidden md:grid grid-cols-[60px_1fr_120px] gap-4 px-5 py-3 bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <span># ID</span>
                        <span>Nombre de la Unidad</span>
                        <span class="text-right">Acción</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <?php foreach ($data['areas'] as $a): ?>
                        <div class="flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors group">
                            <!-- ID (solo md+) -->
                            <span class="hidden md:block font-mono text-[10px] text-brand-600 w-12 shrink-0">#<?= str_pad($a['r_id'],3,'0',STR_PAD_LEFT) ?></span>

                            <!-- Icono + Nombre -->
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center shrink-0">
                                    <i class="fas fa-building text-brand-600 text-xs"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 text-sm truncate"><?= htmlspecialchars($a['r_nombre']) ?></p>
                                    <p class="text-[10px] text-slate-400 font-mono md:hidden">#<?= str_pad($a['r_id'],3,'0',STR_PAD_LEFT) ?></p>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="flex items-center gap-1 shrink-0">
                                <button onclick="abrirEdicion(<?= $a['r_id'] ?>, '<?= addslashes($a['r_nombre']) ?>')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-all">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                                <button onclick="Alerts.confirmDelete('../controllers/parametrosController.php?ent=area&action=delete&id=<?= $a['r_id'] ?>')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (empty($data['areas'])): ?>
                        <div class="py-16 text-center text-slate-400">
                            <i class="fas fa-building text-3xl mb-3 opacity-30"></i>
                            <p class="text-sm font-bold">No hay áreas registradas</p>
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
                <h3 class="text-white text-xs font-black tracking-widest">Nueva Área</h3>
                <button onclick="cerrarModal('modalCrear')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=area&action=create" method="POST">
                <div class="p-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre del Área</label>
                    <input type="text" name="nom_area" placeholder="Ej: Tecnología e Información..." required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
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
                <h3 class="text-white text-xs font-black tracking-widest">Editar Área</h3>
                <button onclick="cerrarModal('modalEdicion')" class="text-white/50 hover:text-white w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=area&action=update" method="POST">
                <input type="hidden" name="id_area" id="edit_id">
                <div class="p-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre del Área</label>
                    <input type="text" name="nom_area" id="edit_nombre" required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
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
    <script src="../assets/js/dark_mode.js"></script>
</body>
</html>
