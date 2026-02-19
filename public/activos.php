<?php
require_once '../controllers/activosController.php';
$res         = ActivosController::listar();
$activos     = $res['activos'];
$page        = $res['page'];
$total_pages = $res['total_pages'];
$filtro      = $res['filtro'];
$filtro_peri = $res['filtro_peri'];
$buscar      = $res['buscar'];

function iconoTipo(string $tipo): string {
    $t = strtolower($tipo);
    if (str_contains($t,'laptop') || str_contains($t,'portátil') || str_contains($t,'portatil'))
        return '<i class="fas fa-laptop text-blue-500"></i>';
    if (str_contains($t,'computador') || str_contains($t,'desktop') || str_contains($t,'pc'))
        return '<i class="fas fa-desktop text-indigo-500"></i>';
    if (str_contains($t,'tablet') || str_contains($t,'ipad'))
        return '<i class="fas fa-tablet-screen-button text-cyan-500"></i>';
    if (str_contains($t,'mouse') || str_contains($t,'ratón') || str_contains($t,'raton'))
        return '<i class="fas fa-computer-mouse text-emerald-500"></i>';
    if (str_contains($t,'teclado') || str_contains($t,'keyboard'))
        return '<i class="fas fa-keyboard text-amber-500"></i>';
    if (str_contains($t,'lector') || str_contains($t,'scanner') || str_contains($t,'escáner'))
        return '<i class="fas fa-barcode text-rose-500"></i>';
    if (str_contains($t,'monitor') || str_contains($t,'pantalla'))
        return '<i class="fas fa-tv text-purple-500"></i>';
    if (str_contains($t,'impresora') || str_contains($t,'printer'))
        return '<i class="fas fa-print text-orange-500"></i>';
    return '<i class="fas fa-microchip text-slate-400"></i>';
}

function badgeEstado(string $estado): string {
    return ($estado === 'OPERATIVO')
        ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600">'.$estado.'</span>'
        : '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600">'.$estado.'</span>';
}

// Construye la query string preservando todos los filtros activos
function qStr(array $over = []): string {
    global $page, $filtro, $filtro_peri, $buscar;
    $base = [
        'page'   => $page,
        'filtro' => $filtro,
        'peri'   => $filtro_peri,
        'buscar' => $buscar,
    ];
    return '?' . http_build_query(array_merge($base, $over));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario | SIH_QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { 50:'#fff1f2', 100:'#ffe4e6', 600:'#e11d48', 700:'#be123c' } },
                    fontFamily: { sans: ['Plus Jakarta Sans','sans-serif'] }
                }
            }
        }
    </script>
    <style>
        .toggle-icon { transition: transform .2s; display:inline-block; }
        .row-expand.open .toggle-icon { transform: rotate(90deg); }
        .tabla-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .fbtn        { transition: all .15s; }
        .fbtn.activo { box-shadow: 0 0 0 2px currentColor; }
        .no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50 w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-3 sm:p-6 md:p-8 w-full">
            <div class="max-w-[1700px] mx-auto">

                <!-- BARRA ÚNICA: título + filtros + buscador + nuevo -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-5 overflow-hidden">

                    <!-- Fila superior: título + buscador + botón nuevo -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 px-4 py-3 border-b border-slate-100">

                        <!-- Título -->
                        <div class="flex-1 min-w-0">
                            <h1 class="text-base font-extrabold text-slate-900 tracking-tight leading-none">
                                Inventario de Activos
                            </h1>
                            <p class="text-slate-400 text-[11px] font-medium mt-0.5 truncate">
                                <?php if ($buscar !== ''): ?>
                                    <span class="text-brand-600 font-bold">
                                        <?= $res['total_registros'] ?> resultado(s) para "<em><?= htmlspecialchars($buscar) ?></em>"
                                    </span>
                                <?php else: ?>
                                    Activos principales y periféricos asociados.
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Buscador + botón: fila completa en móvil, compacto en desktop -->
                        <div class="flex items-center gap-2 w-full sm:w-auto">

                            <!-- Buscador -->
                            <div class="relative flex-1 sm:w-64 sm:flex-none">
                                <form id="formBuscar" method="GET" action="">
                                    <input type="hidden" name="filtro" value="<?= htmlspecialchars($filtro) ?>">
                                    <input type="hidden" name="peri"   value="<?= htmlspecialchars($filtro_peri) ?>">
                                    <input type="hidden" name="page"   value="1">
                                    <input
                                        type="text"
                                        name="buscar"
                                        id="searchInput"
                                        value="<?= htmlspecialchars($buscar) ?>"
                                        placeholder="Buscar serial, QR, marca…"
                                        oninput="debounceBuscar()"
                                        autocomplete="off"
                                        class="w-full pl-8 pr-7 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium outline-none focus:border-brand-600 focus:bg-white transition-all">
                                    <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                                    <?php if ($buscar !== ''): ?>
                                    <a href="<?= qStr(['buscar'=>'', 'page'=>1]) ?>"
                                       class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-[10px]"
                                       title="Limpiar búsqueda">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    <?php endif; ?>
                                </form>
                            </div>

                            <!-- Botón nuevo -->
                            <a href="crear_activo.php"
                               class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-sm transition-all"
                               title="Nuevo Activo">
                                <i class="fas fa-plus text-xs"></i>
                            </a>
                        </div>

                    </div>

                    <!-- Fila inferior: filtros -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-0 sm:items-center sm:divide-x sm:divide-slate-100 px-4 py-3">

                        <!-- Grupo 1: Tipo de activo principal -->
                        <div class="sm:pr-5 flex-shrink-0">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipo de dispositivo</p>
                            <div class="flex gap-1.5 overflow-x-auto py-1 px-1 no-scrollbar">
                                <?php
                                $principales = [
                                    ['v'=>'todos',      'l'=>'Todos',      'i'=>'fa-layer-group',          'c'=>'bg-slate-800 text-white'],
                                    ['v'=>'computador', 'l'=>'Computador', 'i'=>'fa-desktop',              'c'=>'bg-indigo-50 text-indigo-600'],
                                    ['v'=>'laptop',     'l'=>'Laptop',     'i'=>'fa-laptop',               'c'=>'bg-blue-50 text-blue-600'],
                                    ['v'=>'tablet',     'l'=>'Tablet',     'i'=>'fa-tablet-screen-button', 'c'=>'bg-cyan-50 text-cyan-600'],
                                ];
                                foreach ($principales as $b):
                                    $activo = ($filtro === $b['v']) ? 'activo' : '';
                                ?>
                                <a href="<?= qStr(['filtro'=>$b['v'], 'page'=>1]) ?>"
                                   class="fbtn <?= $b['c'] ?> <?= $activo ?> flex-shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold hover:opacity-80"
                                   title="<?= $b['l'] ?>">
                                    <i class="fas <?= $b['i'] ?>"></i>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Grupo 2: Periférico asociado -->
                        <div class="sm:pl-5 flex-1 min-w-0">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Filtrar por periférico</p>
                            <div class="flex gap-1.5 overflow-x-auto py-1 px-1 no-scrollbar">
                                <?php
                                $perifericos_filtros = [
                                    ['v'=>'todos-peri', 'l'=>'Todos',     'i'=>'fa-check-double',   'c'=>'bg-slate-100 text-slate-600'],
                                    ['v'=>'mouse',      'l'=>'Mouse',     'i'=>'fa-computer-mouse', 'c'=>'bg-emerald-50 text-emerald-600'],
                                    ['v'=>'teclado',    'l'=>'Teclado',   'i'=>'fa-keyboard',       'c'=>'bg-amber-50 text-amber-600'],
                                    ['v'=>'lector',     'l'=>'Lector',    'i'=>'fa-barcode',        'c'=>'bg-rose-50 text-rose-600'],
                                    ['v'=>'monitor',    'l'=>'Monitor',   'i'=>'fa-tv',             'c'=>'bg-purple-50 text-purple-600'],
                                    ['v'=>'impresora',  'l'=>'Impresora', 'i'=>'fa-print',          'c'=>'bg-orange-50 text-orange-600'],
                                ];
                                foreach ($perifericos_filtros as $b):
                                    $activo = ($filtro_peri === $b['v']) ? 'activo' : '';
                                ?>
                                <a href="<?= qStr(['peri'=>$b['v'], 'page'=>1]) ?>"
                                   class="fbtn <?= $b['c'] ?> <?= $activo ?> flex-shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold hover:opacity-80"
                                   title="<?= $b['l'] ?>">
                                    <i class="fas <?= $b['i'] ?>"></i>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Botón limpiar todo (solo visible si hay algo activo) -->
                        <?php if ($filtro !== 'todos' || $filtro_peri !== 'todos-peri' || $buscar !== ''): ?>
                        <div class="sm:pl-5 flex-shrink-0">
                            <a href="activos.php"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-red-50 text-red-500 hover:bg-red-100 transition-all">
                                <i class="fas fa-filter-circle-xmark"></i>
                                <span>Limpiar filtros</span>
                            </a>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- TABLA -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm tabla-wrapper">
                    <table class="w-full min-w-[640px] text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                <th class="px-4 py-3 w-8"></th>
                                <th class="px-4 py-3">Equipo / QR</th>
                                <th class="px-4 py-3">Marca / Modelo</th>
                                <th class="px-4 py-3 hidden md:table-cell">Ubicación</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-4 py-3 text-center hidden sm:table-cell">Periféricos</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">

                        <?php if (empty($activos)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                                    <i class="fas fa-magnifying-glass text-4xl mb-3 block opacity-30"></i>
                                    No se encontraron activos con los filtros aplicados.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($activos as $item):
                            $hasPerifericos = !empty($item['perifericos']);
                            $panelId        = 'panel-' . $item['r_id'];
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors <?= $hasPerifericos ? 'cursor-pointer row-expand' : '' ?>"
                                <?= $hasPerifericos ? "onclick=\"togglePanel('$panelId', this)\"" : '' ?>>

                                <td class="px-4 py-4 text-center">
                                    <?php if ($hasPerifericos): ?>
                                    <span class="toggle-icon text-slate-400 text-xs">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-base sm:text-lg flex-shrink-0">
                                            <?= iconoTipo($item['r_tipo']) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 text-sm truncate"><?= htmlspecialchars($item['r_tipo']) ?></div>
                                            <?php if ($item['r_hostname']): ?>
                                            <div class="text-xs text-slate-500 truncate"><?= htmlspecialchars($item['r_hostname']) ?></div>
                                            <?php endif; ?>
                                            <div class="text-[10px] font-black text-brand-600 uppercase tracking-wide">
                                                <?= htmlspecialchars($item['r_qr'] ?? '') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 whitespace-nowrap">
                                        <?= htmlspecialchars($item['r_marca']) ?>
                                    </span>
                                    <?php if ($item['r_modelo']): ?>
                                    <div class="text-xs text-slate-400 mt-1 truncate max-w-[120px]"><?= htmlspecialchars($item['r_modelo']) ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="px-4 py-4 hidden md:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-[10px] font-bold flex-shrink-0">
                                            <?= strtoupper(substr($item['r_responsable'] ?? 'B', 0, 2)) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-800 text-xs truncate"><?= htmlspecialchars($item['r_responsable'] ?? 'Bodega') ?></p>
                                            <p class="text-[10px] text-slate-500 uppercase font-bold truncate"><?= htmlspecialchars($item['r_area'] ?? 'Sin Área') ?></p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <?= badgeEstado($item['r_estado']) ?>
                                </td>

                                <td class="px-4 py-4 text-center hidden sm:table-cell">
                                    <?php if ($hasPerifericos): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full text-[10px] font-bold">
                                        <i class="fas fa-puzzle-piece text-slate-400 text-[9px]"></i>
                                        <?= count($item['perifericos']) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-slate-300 text-xs">—</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-1" onclick="event.stopPropagation()">
                                        <a href="ver.php?id=<?= $item['r_id'] ?>"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 hover:text-brand-600 transition-colors"
                                           title="Ver detalle">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="editar.php?id=<?= $item['r_id'] ?>"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 hover:text-blue-600 transition-colors"
                                           title="Editar">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Panel de periféricos desplegable -->
                            <?php if ($hasPerifericos): ?>
                            <tr>
                                <td colspan="7" class="p-0">
                                    <div id="<?= $panelId ?>" class="hidden bg-slate-50/80 border-t border-slate-100">
                                        <div class="px-4 sm:px-6 py-4">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">
                                                <i class="fas fa-link mr-1"></i> Periféricos asociados
                                            </p>
                                            <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2">
                                            <?php foreach ($item['perifericos'] as $peri): ?>
                                                <div class="bg-white rounded-xl border border-slate-200 p-3 flex items-center gap-2.5 shadow-sm">
                                                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0 text-sm">
                                                        <?= iconoTipo($peri['r_tipo']) ?>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="font-bold text-slate-800 text-xs truncate"><?= htmlspecialchars($peri['r_tipo']) ?></div>
                                                        <div class="text-[10px] text-slate-500 truncate">
                                                            <?= htmlspecialchars($peri['r_marca']) ?>
                                                            <?= $peri['r_modelo'] ? ' · '.$peri['r_modelo'] : '' ?>
                                                        </div>
                                                        <div class="text-[10px] font-black text-brand-600 uppercase"><?= htmlspecialchars($peri['r_qr'] ?? '') ?></div>
                                                    </div>
                                                    <div class="flex-shrink-0"><?= badgeEstado($peri['r_estado']) ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>

                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINACIÓN — preserva todos los filtros en la URL -->
                <div class="mt-4 flex flex-col xs:flex-row items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center xs:text-left">
                        Pág. <span class="text-brand-600"><?= $page ?></span> / <?= $total_pages ?>
                        &nbsp;·&nbsp; <?= $res['total_registros'] ?> activos
                    </div>
                    <div class="flex gap-2">
                        <a href="<?= qStr(['page' => max(1, $page-1)]) ?>"
                           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-black hover:bg-slate-200 transition-colors <?= $page <= 1 ? 'opacity-40 pointer-events-none' : '' ?>">
                            <i class="fas fa-chevron-left mr-1"></i> Anterior
                        </a>
                        <a href="<?= qStr(['page' => min($total_pages, $page+1)]) ?>"
                           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-black hover:bg-slate-200 transition-colors <?= $page >= $total_pages ? 'opacity-40 pointer-events-none' : '' ?>">
                            Siguiente <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script>
    // Desplegar / colapsar periféricos
    function togglePanel(panelId, rowEl) {
        const panel = document.getElementById(panelId);
        if (!panel) return;
        const abierto = !panel.classList.contains('hidden');
        panel.classList.toggle('hidden', abierto);
        rowEl.classList.toggle('open', !abierto);
        rowEl.classList.toggle('bg-brand-50/30', !abierto);
    }

    // Búsqueda con debounce: espera 450ms sin escribir y envía el form al servidor
    let timer = null;
    function debounceBuscar() {
        clearTimeout(timer);
        timer = setTimeout(() => document.getElementById('formBuscar').submit(), 450);
    }
    </script>
</body>
</html>