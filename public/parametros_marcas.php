<?php
require_once '../controllers/parametrosController.php';
$data = ParametrosController::getHardwareData(); // Contiene marcas y tipos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Marcas de Equipo | SIH_QR</title>
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
                    colors: { brand: { 50: '#fff1f2', 600: '#e11d48', 700: '#be123c' } },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">
    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50 w-full">
        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth w-full">
            <div class="max-w-[1200px] mx-auto">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Marcas de Equipo</h1>
                        <p class="text-slate-500 font-medium mt-1">Gestión de fabricantes por categoría.</p>
                    </div>
                    <button onclick="abrirModal('modalCrear')" 
                            class="red-gradient text-white px-8 py-3 rounded-2xl font-black text-xs  tracking-widest shadow-xl hover:scale-105 transition-all flex items-center gap-3">
                        <i class="fas fa-plus"></i> Añadir Marca
                    </button>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-xs font-bold text-slate-500  tracking-wider">
                                <th class="px-6 py-4"># ID</th>
                                <th class="px-6 py-4">Marca</th>
                                <th class="px-6 py-4">Tipo Asociado</th>
                                <th class="px-6 py-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php foreach ($data['marcas'] as $m): ?>
                            <tr class="hover:bg-brand-50/20 transition-colors">
                                <td class="px-6 py-4 font-mono text-[10px] text-brand-600">#<?= str_pad($m['r_id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-6 py-4 text-slate-700 font-bold "><?= $m['r_nombre'] ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-slate-100 text-slate-500 text-[10px] font-black rounded-lg "><?= $m['r_tipo'] ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="abrirEdicion(<?= $m['r_id'] ?>, '<?= $m['r_nombre'] ?>')" 
                                                class="text-slate-400 hover:text-brand-600 transition-colors">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="Alerts.confirmDelete('../controllers/parametrosController.php?ent=marca&action=delete&id=<?= $m['r_id'] ?>')" 
                                                class="text-slate-400 hover:text-rose-600 transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div id="modalCrear" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black  tracking-widest">Registrar Marca</h3>
                <button type="button" onclick="cerrarModal('modalCrear')" class="text-white/50 hover:text-white transition-colors"><i class="fas fa-times"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=marca&action=create" method="POST">
                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600  tracking-wide mb-2">Tipo de Equipo</label>
                        <select name="id_tipoequi" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                            <option value="">Seleccione tipo...</option>
                            <?php foreach ($data['tipos'] as $t): ?>
                                <option value="<?= $t['r_id'] ?>"><?= $t['r_nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600  tracking-wide mb-2">Nombre de la Marca</label>
                        <input type="text" name="nom_marca" placeholder="Ej: Dell, HP, Lenovo..." required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalCrear')" class="flex-1 text-sm font-bold text-slate-500">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-black  rounded-xl">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEdicion" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black  tracking-widest">Actualizar Marca</h3>
                <button type="button" onclick="cerrarModal('modalEdicion')" class="text-white/50 hover:text-white transition-colors"><i class="fas fa-times"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=marca&action=update" method="POST">
                <input type="hidden" name="id_marca" id="edit_id">
                <div class="p-8">
                    <label class="block text-xs font-bold text-slate-600  tracking-wide mb-2">Nombre de Marca</label>
                    <input type="text" name="nom_marca" id="edit_nombre" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none transition-all">
                </div>
                <div class="bg-slate-50 px-6 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalEdicion')" class="flex-1 text-sm font-bold text-slate-500">Cerrar</button>
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-black  rounded-xl">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/utils.js"></script>
</body>
</html>