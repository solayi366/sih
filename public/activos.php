<?php
require_once '../controllers/activosController.php';
// Obtenemos los datos procesados
$res = ActivosController::listar();
$activos = $res['activos'];
$page = $res['page'];
$total_pages = $res['total_pages'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario | SIH_QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
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
        <div class="flex-1 overflow-y-auto p-4 md:p-8 w-full">
            <div class="max-w-[1600px] mx-auto">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Inventario de Activos</h1>
                        <p class="text-slate-500 font-medium mt-1">Gestión centralizada de equipos.</p>
                    </div>
                    <div class="relative w-full md:w-96">
                        <input type="text" id="searchInput" onkeyup="filtrarTabla()" placeholder="Buscar..." 
                               class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm outline-none focus:border-brand-600 transition-all">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse" id="tablaActivos">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Equipo / QR</th>
                                <th class="px-6 py-4">Marca/Ref</th>
                                <th class="px-6 py-4">Ubicación</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php foreach ($activos as $item): ?>
                            <tr class="hover:bg-brand-50/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900"><?= $item['r_tipo'] ?></div>
                                    <div class="text-[10px] font-black text-brand-600 uppercase"><?= $item['r_qr'] ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                        <?= $item['r_marca'] ?>
                                    </span>
                                    <div class="text-xs text-slate-400 mt-1"><?= $item['r_modelo'] ?: $item['r_padre_ref'] ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-[10px] font-bold">
                                            <?= substr($item['r_responsable'] ?? 'B', 0, 2) ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 text-xs"><?= $item['r_responsable'] ?? 'Bodega' ?></p>
                                            <p class="text-[10px] text-slate-500 uppercase font-bold"><?= $item['r_area'] ?? 'Sin Área' ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php 
                                        $statusClass = ($item['r_estado'] == 'OPERATIVO') ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600';
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold <?= $statusClass ?>"><?= $item['r_estado'] ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="ver.php?id=<?= $item['r_id'] ?>" class="text-slate-400 hover:text-brand-600"><i class="fas fa-eye"></i></a>
                                        <a href="editar.php?id=<?= $item['r_id'] ?>" class="text-slate-400 hover:text-blue-600"><i class="fas fa-edit"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 flex items-center justify-between bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Página <span class="text-brand-600"><?= $page ?></span> de <?= $total_pages ?>
                    </div>
                    <div class="flex gap-3">
                        <a href="?page=<?= max(1, $page-1) ?>" class="px-6 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-black <?= $page <= 1 ? 'opacity-50 pointer-events-none' : '' ?>">Anterior</a>
                        <a href="?page=<?= min($total_pages, $page+1) ?>" class="px-6 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-black <?= $page >= $total_pages ? 'opacity-50 pointer-events-none' : '' ?>">Siguiente</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/sidebar_logic.js"></script>
</body>
</html>