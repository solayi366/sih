<?php
require_once '../controllers/parametrosController.php';
$data = ParametrosController::getRRHHData();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Custodios de Activos | SIH_QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { 50: '#fff1f2', 600: '#e11d48', 700: '#be123c' } }, fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] } } } }
    </script>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">
    <?php include '../includes/sidebar.php'; ?>
    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50 w-full">
        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth w-full">
            <div class="max-w-[1400px] mx-auto">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Custodios de Activos</h1>
                        <p class="text-slate-500 font-medium mt-1">Personal responsable del equipamiento técnico.</p>
                    </div>
                    <button onclick="abrirModal('modalCrear')" class="red-gradient text-white px-8 py-3 rounded-2xl font-black text-xs  tracking-widest shadow-xl hover:scale-105 transition-all flex items-center gap-3">
                        <i class="fas fa-user-plus"></i> Añadir Custodio
                    </button>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-xs font-bold text-slate-500  tracking-wider">
                                <th class="px-6 py-4">Ficha / ID</th>
                                <th class="px-6 py-4">Nombre Completo</th>
                                <th class="px-6 py-4">Área Asignada</th>
                                <th class="px-6 py-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php foreach ($data['empleados'] as $e): ?>
                            <tr class="hover:bg-brand-50/20 transition-colors">
                                <td class="px-6 py-4"><span class="px-2 py-1 bg-slate-900 text-brand-50 font-mono text-[10px] rounded-lg"><?= $e['r_codigo'] ?></span></td>
                                <td class="px-6 py-4 text-slate-700 font-bold "><?= $e['r_nombre'] ?></td>
                                <td class="px-6 py-4"><span class="px-2 py-1 bg-slate-100 text-slate-500 text-[10px] font-black rounded-lg "><?= $e['r_area'] ?></span></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="abrirEdicionEmpleado('<?= $e['r_codigo'] ?>', '<?= $e['r_nombre'] ?>', <?= $e['r_id_area'] ?>)" class="text-slate-400 hover:text-brand-600 transition-colors">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="Alerts.confirmDelete('../controllers/parametrosController.php?ent=empleado&action=delete&id=<?= $e['r_codigo'] ?>')" class="text-slate-400 hover:text-rose-600 transition-colors">
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
                <h3 class="text-white text-xs font-black  tracking-widest">Nuevo Custodio</h3>
                <button onclick="cerrarModal('modalCrear')" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=empleado&action=create" method="POST">
                <div class="p-8 space-y-4">
                    <input type="text" name="cod_nom" placeholder="ID / Ficha..." required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                    <input type="text" name="nom_emple" placeholder="Nombre completo..." required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                    <select name="id_area" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                        <option value="">Seleccione Área...</option>
                        <?php foreach($data['areas'] as $a): ?><option value="<?= $a['r_id'] ?>"><?= $a['r_nombre'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalCrear')" class="flex-1 text-sm font-bold text-slate-500">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-black  rounded-xl">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEdicion" class="modal-overlay hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white text-xs font-black  tracking-widest">Actualizar Custodio</h3>
                <button onclick="cerrarModal('modalEdicion')" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="../controllers/parametrosController.php?ent=empleado&action=update" method="POST">
                <div class="p-8 space-y-4">
                    <label class="block text-[10px] font-black text-slate-400  tracking-widest ml-1">Cédula (No editable)</label>
                    <input type="text" name="cod_nom" id="edit_codigo" readonly class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm font-bold text-slate-400 outline-none cursor-not-allowed">
                    
                    <label class="block text-[10px] font-black text-slate-400  tracking-widest ml-1">Nombre Completo</label>
                    <input type="text" name="nom_emple" id="edit_nombre" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                    
                    <label class="block text-[10px] font-black text-slate-400  tracking-widest ml-1">Nueva Área</label>
                    <select name="id_area" id="edit_area" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-semibold focus:border-brand-600 outline-none">
                        <?php foreach($data['areas'] as $a): ?><option value="<?= $a['r_id'] ?>"><?= $a['r_nombre'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex gap-3">
                    <button type="button" onclick="cerrarModal('modalEdicion')" class="flex-1 text-sm font-bold text-slate-500">Cerrar</button>
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-black  rounded-xl">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/utils.js"></script>
    <script>
        // Función específica para este módulo
        function abrirEdicionEmpleado(codigo, nombre, idArea) {
            document.getElementById('edit_codigo').value = codigo;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_area').value = idArea;
            abrirModal('modalEdicion');
        }
    </script>
</body>
</html>