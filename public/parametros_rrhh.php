<?php
require_once '../controllers/ParametrosController.php';
$data = ParametrosController::getRRHHData();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración RRHH | SIH_QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { 50: '#fff1f2', 600: '#e11d48', 700: '#be123c' } }, fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] } } } }
    </script>
    <style> body { background-color: #f8fafc; } </style>
</head>

<body class="text-slate-800 antialiased font-sans h-screen flex overflow-hidden">
    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50 w-full">
        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth w-full">
            <div class="max-w-[1400px] mx-auto">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 border-b-2 border-slate-200 pb-4">
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Gestión de <span class="text-brand-600">Recursos Humanos</span></h1>
                        <div class="flex gap-6 mt-4">
                            <a href="parametros_hardware.php" class="text-xs font-black uppercase text-slate-400 hover:text-brand-600 transition-all">Hardware</a>
                            <a href="parametros_rrhh.php" class="text-xs font-black uppercase border-b-4 border-brand-600 pb-2 text-slate-900">Recursos Humanos</a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-4">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="bg-slate-900 text-white p-4 text-[10px] font-black uppercase tracking-widest">Unidades de Negocio</div>
                            <div class="p-6">
<form action="../controllers/parametrosController.php?view=rrhh&ent=empleado" method="POST" class="... ">                                    <input type="text" name="nom_area" class="flex-1 px-4 py-2 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-brand-600" placeholder="Nueva Área" required>
                                    <button type="submit" class="bg-brand-600 text-white px-4 rounded-xl shadow-lg transition-all"><i class="fas fa-plus"></i></button>
                                </form>
                                <table class="w-full">
                                    <tbody class="divide-y divide-slate-100">
                                        <?php foreach($data['areas'] as $a): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-3 px-2 text-sm font-bold text-slate-700 uppercase"><?= $a['r_nombre'] ?></td>
                                            <td class="py-3 px-2 text-right"><a href="#" class="text-slate-300 hover:text-brand-600"><i class="fas fa-trash-alt text-xs"></i></a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-8">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="bg-slate-900 text-white p-6 flex justify-between items-center">
                                <span class="text-xs font-black uppercase tracking-widest"><i class="fas fa-id-card text-brand-500 mr-2"></i>Custodios de Activos</span>
                            </div>
                            <div class="p-8">
<form action="../controllers/parametrosController.php?view=rrhh&ent=empleado" method="POST" class="... ">                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div><label class="text-[9px] font-black text-slate-400 uppercase mb-2 block">Cédula</label>
                                             <input type="text" name="cod_nom" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-brand-600" required></div>
                                        <div><label class="text-[9px] font-black text-slate-400 uppercase mb-2 block">Nombre Completo</label>
                                             <input type="text" name="nom_emple" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-brand-600" required></div>
                                        <div><label class="text-[9px] font-black text-slate-400 uppercase mb-2 block">Área Asignada</label>
                                             <select name="id_area" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-brand-600" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach($data['areas'] as $a): ?><option value="<?= $a['r_id'] ?>"><?= $a['r_nombre'] ?></option><?php endforeach; ?>
                                             </select></div>
                                    </div>
                                    <button type="submit" class="w-full mt-6 bg-slate-900 text-white py-3 rounded-xl text-[10px] font-black tracking-[0.2em] uppercase hover:bg-brand-600 transition-all shadow-xl">Registrar Nuevo Custodio</button>
                                </form>

                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-slate-50 text-[10px] font-black uppercase text-slate-500 tracking-wider">
                                            <th class="px-6 py-4">Ficha / ID</th>
                                            <th class="px-6 py-4">Nombre</th>
                                            <th class="px-6 py-4">Área</th>
                                            <th class="px-6 py-4 text-right">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-sm font-bold">
                                        <?php foreach($data['empleados'] as $e): ?>
                                        <tr class="hover:bg-brand-50/20 transition-colors">
                                            <td class="px-6 py-4"><span class="bg-slate-900 text-brand-500 text-[10px] px-2 py-1 rounded-md font-mono"><?= $e['r_codigo'] ?></span></td>
                                            <td class="px-6 py-4 text-slate-700 uppercase"><?= $e['r_nombre'] ?></td>
                                            <td class="px-6 py-4 text-xs text-slate-400 uppercase"><?= $e['r_area'] ?></td>
                                            <td class="px-6 py-4 text-right"><a href="#" class="text-slate-300 hover:text-brand-600"><i class="fas fa-trash-alt"></i></a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="../assets/js/sidebar_logic.js"></script>
</body>
</html>