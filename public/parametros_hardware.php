<?php
require_once '../controllers/ParametrosController.php';
$data = ParametrosController::getHardwareData();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Hardware | SIH_QR</title>
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
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Parámetros del <span class="text-brand-600">Sistema</span></h1>
                        <div class="flex gap-6 mt-4">
                            <a href="parametros_hardware.php" class="text-xs font-black uppercase border-b-4 border-brand-600 pb-2 text-slate-900">Hardware</a>
                            <a href="parametros_rrhh.php" class="text-xs font-black uppercase text-slate-400 hover:text-brand-600 transition-all">Recursos Humanos</a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="bg-slate-900 text-white p-4 text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-tags text-brand-500"></i> Tipos de Equipo
                        </div>
                        <div class="p-6">
                           <form action="../controllers/parametrosController.php?view=hw&ent=tipo" method="POST" class="flex gap-2 mb-6">                                <input type="text" name="nom_tipo" class="flex-1 px-4 py-2 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-brand-600" placeholder="Nuevo Tipo" required>
                                <button type="submit" class="bg-brand-600 text-white px-4 rounded-xl hover:bg-brand-700 transition-all shadow-lg shadow-brand-100"><i class="fas fa-plus"></i></button>
                            </form>
                            <table class="w-full text-left">
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach($data['tipos'] as $t): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-2 text-sm font-bold text-slate-700 uppercase"><?= $t['r_nombre'] ?></td>
                                        <td class="py-3 px-2 text-right">
                                            <a href="#" class="text-slate-300 hover:text-brand-600 transition-colors"><i class="fas fa-trash-alt text-xs"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="bg-slate-900 text-white p-4 text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-copyright text-brand-500"></i> Marcas registradas
                        </div>
                        <div class="p-6">
                           <form action="../controllers/parametrosController.php?view=hw&ent=tipo" method="POST" class="flex gap-2 mb-6">                                <input type="text" name="nom_tipo" class="flex-1 px-4 py-2 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-brand-600" placeholder="Nuevo Tipo" required>
                                <input type="text" name="nom_marca" class="flex-1 px-4 py-2 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-brand-600" placeholder="Nueva Marca" required>
                                <button type="submit" class="bg-brand-600 text-white px-4 rounded-xl hover:bg-brand-700 transition-all shadow-lg shadow-brand-100"><i class="fas fa-plus"></i></button>
                            </form>
                            <table class="w-full text-left">
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach($data['marcas'] as $m): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-2 text-sm font-bold text-slate-700 uppercase"><?= $m['r_nombre'] ?></td>
                                        <td class="py-3 px-2 text-right">
                                            <a href="#" class="text-slate-300 hover:text-brand-600 transition-colors"><i class="fas fa-trash-alt text-xs"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="bg-slate-900 text-white p-6 flex flex-col md:flex-row justify-between items-center gap-4">
                        <span class="text-xs font-black uppercase tracking-widest"><i class="fas fa-layer-group text-brand-500 mr-2"></i>Catálogo de Modelos</span>
                        <input type="text" id="buscadorModelo" class="bg-white/10 border-0 rounded-xl py-2 px-6 text-xs font-bold text-white outline-none focus:bg-white/20 transition-all w-full md:w-80" placeholder="Filtrar por modelo o marca...">
                    </div>
                    <div class="overflow-x-auto p-4">
                        <table class="w-full text-left border-collapse" id="tablaModelos">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] font-black uppercase text-slate-500 tracking-wider">
                                    <th class="px-6 py-4">Modelo</th>
                                    <th class="px-6 py-4 text-center">Marca</th>
                                    <th class="px-6 py-4 text-center">Tipo</th>
                                    <th class="px-6 py-4 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-bold">
                                <?php foreach($data['modelos'] as $mod): ?>
                                <tr class="hover:bg-brand-50/20 transition-colors">
                                    <td class="px-6 py-4 text-slate-900 uppercase"><?= $mod['r_modelo'] ?></td>
                                    <td class="px-6 py-4 text-center"><span class="bg-slate-100 text-slate-500 text-[10px] px-3 py-1 rounded-lg uppercase"><?= $mod['r_marca'] ?></span></td>
                                    <td class="px-6 py-4 text-center text-xs text-slate-400 font-mono"><?= $mod['r_tipo'] ?></td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="#" class="text-slate-300 hover:text-brand-600 transition-colors"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="../assets/js/sidebar_logic.js"></script>
</body>
</html>