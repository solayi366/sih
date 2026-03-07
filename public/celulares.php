<?php
require_once '../controllers/celularesController.php';
$res             = CelularesController::listar();
$celulares       = $res['celulares'];
$page            = $res['page'];
$total_pages     = $res['total_pages'];
$total_registros = $res['total_registros'];
$buscar          = $res['buscar'];
$estado_filtro   = $res['estado'];
$es_admin        = $res['es_admin'];

$estados = [
    ''                           => ['label' => 'Todos',            'color' => 'bg-slate-800 text-white'],
    'ASIGNADO'                   => ['label' => 'Asignado',         'color' => 'bg-emerald-50 text-emerald-700'],
    'EN REPOSICION'              => ['label' => 'En Reposición',    'color' => 'bg-amber-50 text-amber-700'],
    'EN PROCESO DE REASIGNACION' => ['label' => 'Reasignación',     'color' => 'bg-blue-50 text-blue-700'],
    'DE BAJA'                    => ['label' => 'De Baja',          'color' => 'bg-rose-50 text-rose-700'],
];

function badgeCelular(string $estado): string {
    return match($estado) {
        'ASIGNADO'                   => '<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700">ASIGNADO</span>',
        'EN REPOSICION'              => '<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-700">EN REPOSICIÓN</span>',
        'EN PROCESO DE REASIGNACION' => '<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700">REASIGNACIÓN</span>',
        'DE BAJA'                    => '<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-50 text-rose-700">DE BAJA</span>',
        default                      => '<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-500">' . htmlspecialchars($estado) . '</span>',
    };
}

function qStr(array $over = []): string {
    global $page, $buscar, $estado_filtro;
    return '?' . http_build_query(array_merge([
        'page'   => $page,
        'buscar' => $buscar,
        'estado' => $estado_filtro,
    ], $over));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Celulares | SIH_QR</title>
    <script>(function(){var t=localStorage.getItem('sihTheme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark');}})();</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{brand:{50:'#fff1f2',100:'#ffe4e6',600:'#e11d48',700:'#be123c'}},fontFamily:{sans:['Plus Jakarta Sans','sans-serif']}}}}</script>
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
    <style>
        .fbtn.activo { box-shadow: 0 0 0 2px currentColor; }
        .tabla-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }

        /* Dark mode */
        .dark .bg-white.rounded-2xl,
        .dark .bg-white.rounded-3xl { background-color: rgba(16,14,24,0.90) !important; border-color: rgba(255,255,255,0.07) !important; }
        .dark .bg-slate-50\/80 { background-color: rgba(22,18,34,0.85) !important; }
        .dark .hover\:bg-slate-50:hover { background-color: rgba(22,18,34,0.85) !important; }
        .dark .bg-slate-50.border { background-color: rgba(22,18,34,0.85) !important; border-color: rgba(255,255,255,0.07) !important; color: #f1f5f9 !important; }
        .dark .divide-y.divide-slate-100 > * + * { border-color: rgba(255,255,255,0.07) !important; }
        .dark .border-b.border-slate-100 { border-color: rgba(255,255,255,0.07) !important; }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative bg-slate-50/50 w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-3 sm:p-6 md:p-8 w-full">
            <div class="max-w-[1600px] mx-auto">

                <!-- ── BARRA SUPERIOR ─────────────────────────────────── -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-5 overflow-hidden">

                    <!-- Título + buscador + botón nuevo -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 px-4 py-3 border-b border-slate-100">

                        <div class="flex-1 min-w-0">
                            <h1 class="text-base font-extrabold text-slate-900 tracking-tight leading-none">
                                Inventario de Celulares Corporativos
                            </h1>
                            <p class="text-slate-400 text-[11px] font-medium mt-0.5">
                                <?php if ($buscar !== ''): ?>
                                    <span class="text-brand-600 font-bold">
                                        <?= $total_registros ?> resultado(s) para "<em><?= htmlspecialchars($buscar) ?></em>"
                                    </span>
                                <?php else: ?>
                                    <?= $total_registros ?> celular<?= $total_registros !== 1 ? 'es' : '' ?> registrado<?= $total_registros !== 1 ? 's' : '' ?>.
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <!-- Buscador -->
                            <div class="relative flex-1 sm:w-64 sm:flex-none">
                                <form id="formBuscar" method="GET" action="">
                                    <input type="hidden" name="estado" value="<?= htmlspecialchars($estado_filtro) ?>">
                                    <input type="hidden" name="page"   value="1">
                                    <input type="text" name="buscar" id="searchInput"
                                           value="<?= htmlspecialchars($buscar) ?>"
                                           placeholder="Buscar línea, IMEI, responsable…"
                                           oninput="debounceBuscar()" autocomplete="off"
                                           class="w-full pl-8 pr-7 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium outline-none focus:border-brand-600 focus:bg-white transition-all">
                                    <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                                    <?php if ($buscar !== ''): ?>
                                    <a href="<?= qStr(['buscar' => '', 'page' => 1]) ?>"
                                       class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-[10px]">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    <?php endif; ?>
                                </form>
                            </div>

                            <!-- Botón importar Excel -->
                            <a href="celular_importar.php"
                               class="flex-shrink-0 h-8 px-3 flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm transition-all text-xs font-bold"
                               title="Importar desde Excel">
                                <i class="fas fa-file-excel text-xs"></i>
                                <span class="hidden sm:inline">Importar</span>
                            </a>

                            <!-- Botón nuevo -->
                            <a href="celular_crear.php"
                               class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-sm transition-all"
                               title="Nuevo Celular">
                                <i class="fas fa-plus text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Filtros de estado -->
                    <div class="flex items-center gap-1.5 px-4 py-3 overflow-x-auto no-scrollbar">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mr-2 shrink-0">Estado</p>
                        <?php foreach ($estados as $val => $cfg): ?>
                        <a href="<?= qStr(['estado' => $val, 'page' => 1]) ?>"
                           class="fbtn <?= $cfg['color'] ?> <?= $estado_filtro === $val ? 'activo' : '' ?> shrink-0 inline-flex items-center gap-1.5 px-3 h-7 rounded-full text-[10px] font-black hover:opacity-80 transition-all whitespace-nowrap">
                            <?= $cfg['label'] ?>
                        </a>
                        <?php endforeach; ?>

                        <?php if ($estado_filtro !== '' || $buscar !== ''): ?>
                        <a href="celulares.php"
                           class="ml-2 shrink-0 inline-flex items-center gap-1.5 px-3 h-7 rounded-full text-[10px] font-bold bg-red-50 text-red-500 hover:bg-red-100 transition-all">
                            <i class="fas fa-filter-circle-xmark"></i> Limpiar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── TABLA ──────────────────────────────────────────── -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm tabla-wrapper">
                    <table class="w-full min-w-[700px] text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Línea / IMEI</th>
                                <th class="px-4 py-3">Marca / Modelo</th>
                                <th class="px-4 py-3 hidden md:table-cell">Responsable</th>
                                <th class="px-4 py-3 hidden lg:table-cell">Área</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">

                        <?php if (empty($celulares)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                                    <i class="fas fa-mobile-screen-button text-4xl mb-3 block opacity-30"></i>
                                    No se encontraron celulares con los filtros aplicados.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($celulares as $c): ?>
                            <tr class="hover:bg-slate-50 transition-colors">

                                <!-- Línea + IMEI -->
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                                            <i class="fas fa-mobile-screen-button text-brand-600 text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-black text-slate-900 text-sm tracking-wide">
                                                <?= htmlspecialchars($c['r_linea']) ?>
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono truncate max-w-[140px]">
                                                <?= htmlspecialchars($c['r_imei']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Marca + Modelo -->
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-600 whitespace-nowrap">
                                        <?= htmlspecialchars($c['r_marca']) ?>
                                    </span>
                                    <div class="text-xs text-slate-500 mt-0.5 font-semibold">
                                        <?= htmlspecialchars($c['r_modelo']) ?>
                                    </div>
                                </td>

                                <!-- Responsable -->
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-[10px] font-black shrink-0">
                                            <?= strtoupper(substr($c['r_responsable'] ?? 'B', 0, 2)) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-800 text-xs truncate max-w-[150px]">
                                                <?= htmlspecialchars($c['r_responsable'] ?? '—') ?>
                                            </p>
                                            <p class="text-[10px] text-slate-400 font-semibold truncate max-w-[150px]">
                                                <?= htmlspecialchars($c['r_cargo']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Área -->
                                <td class="px-4 py-3.5 hidden lg:table-cell">
                                    <span class="text-xs font-semibold text-slate-500">
                                        <?= htmlspecialchars($c['r_area'] ?? '—') ?>
                                    </span>
                                </td>

                                <!-- Estado -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <?= badgeCelular($c['r_estado']) ?>
                                </td>

                                <!-- Acciones -->
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="celular_ver.php?id=<?= $c['r_id'] ?>"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 hover:text-brand-600 transition-colors"
                                           title="Ver ficha">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="celular_editar.php?id=<?= $c['r_id'] ?>"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 hover:text-blue-600 transition-colors"
                                           title="Editar">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <?php if ($es_admin): ?>
                                        <button onclick="Alerts.confirmDelete('../controllers/celularesController.php?action=delete&id=<?= $c['r_id'] ?>', 'Se dará de baja el celular con línea <?= htmlspecialchars($c['r_linea']) ?>.')"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-colors"
                                                title="Dar de baja">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ── PAGINACIÓN ─────────────────────────────────────── -->
                <div class="mt-4 flex flex-col xs:flex-row items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Pág. <span class="text-brand-600"><?= $page ?></span> / <?= $total_pages ?>
                        &nbsp;·&nbsp; <?= $total_registros ?> celulares
                    </div>
                    <div class="flex gap-2">
                        <a href="<?= qStr(['page' => max(1, $page - 1)]) ?>"
                           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-black hover:bg-slate-200 transition-colors <?= $page <= 1 ? 'opacity-40 pointer-events-none' : '' ?>">
                            <i class="fas fa-chevron-left mr-1"></i> Anterior
                        </a>
                        <a href="<?= qStr(['page' => min($total_pages, $page + 1)]) ?>"
                           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-black hover:bg-slate-200 transition-colors <?= $page >= $total_pages ? 'opacity-40 pointer-events-none' : '' ?>">
                            Siguiente <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/dark_mode.js"></script>
    <script>
    let timer = null;
    function debounceBuscar() {
        clearTimeout(timer);
        timer = setTimeout(() => document.getElementById('formBuscar').submit(), 450);
    }
    </script>
</body>
</html>
