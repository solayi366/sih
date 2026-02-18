<?php
require_once '../controllers/activosController.php';
$res          = ActivosController::listar();
$activos      = $res['activos'];
$page         = $res['page'];
$total_pages  = $res['total_pages'];
$filtro       = $res['filtro']  ?? 'todos';
$buscar       = $res['buscar']  ?? '';

/* ── Icono por tipo ──────────────────────────────────────── */
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
        .toggle-icon   { transition: transform .2s; display:inline-block; }
        .row-expand.open .toggle-icon { transform: rotate(90deg); }

        /* Tabla scroll horizontal en móvil */
        .tabla-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* Botón de filtro activo */
        .fbtn          { transition: all .15s; cursor: pointer; }
        .fbtn.activo   { box-shadow: 0 0 0 2px currentColor; }
    </style>
</head>

<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50 w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-3 sm:p-6 md:p-8 w-full">
            <div class="max-w-[1700px] mx-auto">

                <!-- ══════════════════════════════════════════
                     CABECERA
                ══════════════════════════════════════════ -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
                            Inventario de Activos
                        </h1>
                        <p class="text-slate-500 text-sm font-medium mt-0.5">
                            Activos principales y periféricos asociados.
                        </p>
                    </div>
                    <!-- Buscador + botón nuevo -->
                    <div class="flex flex-col xs:flex-row items-stretch xs:items-center gap-2 w-full sm:w-auto">
                        <div class="relative flex-1 sm:w-72">
                            <input type="text" id="searchInput" onkeyup="aplicarFiltros()"
                                   value="<?= htmlspecialchars($buscar) ?>"
                                   placeholder="Buscar activo..."
                                   class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm shadow-sm outline-none focus:border-brand-600 transition-all">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        </div>
                        <a href="crear_activo.php"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm transition-all whitespace-nowrap">
                            <i class="fas fa-plus"></i>
                            <span>Nuevo Activo</span>
                        </a>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     BARRA DE FILTROS (dos grupos)
                ══════════════════════════════════════════ -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3 sm:p-4 mb-5 flex flex-col sm:flex-row gap-3 sm:gap-0 sm:items-center sm:divide-x sm:divide-slate-200">

                    <!-- GRUPO 1: Tipo de dispositivo (activos principales) -->
                    <div class="sm:pr-5 flex-shrink-0">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            Tipo de dispositivo
                        </p>
                        <div class="flex flex-wrap gap-1.5" id="grupoPrincipal">
                            <?php
                            $principales = [
                                ['tipo'=>'todos',      'label'=>'Todos',       'icon'=>'fa-layer-group',            'bg'=>'bg-slate-800',  'text'=>'text-white'],
                                ['tipo'=>'computador', 'label'=>'Computador',  'icon'=>'fa-desktop',                'bg'=>'bg-indigo-50',  'text'=>'text-indigo-600'],
                                ['tipo'=>'laptop',     'label'=>'Laptop',      'icon'=>'fa-laptop',                 'bg'=>'bg-blue-50',    'text'=>'text-blue-600'],
                                ['tipo'=>'tablet',     'label'=>'Tablet',      'icon'=>'fa-tablet-screen-button',   'bg'=>'bg-cyan-50',    'text'=>'text-cyan-600'],
                            ];
                            foreach ($principales as $b): ?>
                            <button
                                class="fbtn grupo-principal inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold <?= $b['bg'] ?> <?= $b['text'] ?> hover:opacity-80"
                                data-tipo="<?= $b['tipo'] ?>"
                                data-grupo="principal"
                                data-bg="<?= $b['bg'] ?>"
                                data-tc="<?= $b['text'] ?>"
                                onclick="filtrarGrupo('principal', '<?= $b['tipo'] ?>', this)">
                                <i class="fas <?= $b['icon'] ?>"></i>
                                <span class="hidden xs:inline"><?= $b['label'] ?></span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- GRUPO 2: Filtrar por periférico -->
                    <div class="sm:pl-5 flex-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            Filtrar por periférico
                        </p>
                        <div class="flex flex-wrap gap-1.5" id="grupoPerifericos">
                            <?php
                            $perifericos_filtros = [
                                ['tipo'=>'todos-peri', 'label'=>'Todos',   'icon'=>'fa-check-double',   'bg'=>'bg-slate-100',    'text'=>'text-slate-600'],
                                ['tipo'=>'mouse',      'label'=>'Mouse',   'icon'=>'fa-computer-mouse', 'bg'=>'bg-emerald-50',   'text'=>'text-emerald-600'],
                                ['tipo'=>'teclado',    'label'=>'Teclado', 'icon'=>'fa-keyboard',       'bg'=>'bg-amber-50',     'text'=>'text-amber-600'],
                                ['tipo'=>'lector',     'label'=>'Lector',  'icon'=>'fa-barcode',        'bg'=>'bg-rose-50',      'text'=>'text-rose-600'],
                                ['tipo'=>'monitor',    'label'=>'Monitor', 'icon'=>'fa-tv',             'bg'=>'bg-purple-50',    'text'=>'text-purple-600'],
                                ['tipo'=>'impresora',  'label'=>'Impresora','icon'=>'fa-print',         'bg'=>'bg-orange-50',    'text'=>'text-orange-600'],
                            ];
                            foreach ($perifericos_filtros as $b): ?>
                            <button
                                class="fbtn grupo-perifericos inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold <?= $b['bg'] ?> <?= $b['text'] ?> hover:opacity-80"
                                data-tipo="<?= $b['tipo'] ?>"
                                data-grupo="perifericos"
                                data-bg="<?= $b['bg'] ?>"
                                data-tc="<?= $b['text'] ?>"
                                onclick="filtrarGrupo('perifericos', '<?= $b['tipo'] ?>', this)">
                                <i class="fas <?= $b['icon'] ?>"></i>
                                <span class="hidden xs:inline"><?= $b['label'] ?></span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

                <!-- ══════════════════════════════════════════
                     TABLA
                ══════════════════════════════════════════ -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm tabla-wrapper">
                    <table class="w-full min-w-[640px] text-left border-collapse" id="tablaActivos">
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
                        <tbody class="divide-y divide-slate-100 text-sm" id="tbodyActivos">

                        <?php if (empty($activos)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                                    <i class="fas fa-box-open text-4xl mb-3 block"></i>
                                    No hay activos registrados.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($activos as $item):
                            $hasPerifericos = !empty($item['perifericos']);
                            $panelId        = 'panel-' . $item['r_id'];
                            // Palabras clave del tipo para que el JS filtre
                            $tipoClave = strtolower($item['r_tipo']);
                            // Tipos de periféricos que tiene (para filtro de periféricos)
                            $tiposPeri = [];
                            foreach ($item['perifericos'] as $p) {
                                $tiposPeri[] = strtolower($p['r_tipo']);
                            }
                            $dataPeri = htmlspecialchars(implode(',', $tiposPeri));
                        ?>
                            <!-- ── FILA ACTIVO PRINCIPAL ── -->
                            <tr class="hover:bg-slate-50 transition-colors row-activo <?= $hasPerifericos ? 'cursor-pointer row-expand' : '' ?>"
                                data-tipo="<?= htmlspecialchars($tipoClave) ?>"
                                data-perifericos="<?= $dataPeri ?>"
                                <?= $hasPerifericos ? "onclick=\"togglePerifericos('$panelId', this)\"" : '' ?>>

                                <!-- Chevron -->
                                <td class="px-4 py-4 text-center">
                                    <?php if ($hasPerifericos): ?>
                                    <span class="toggle-icon text-slate-400 text-xs">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Icono + Tipo + QR -->
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

                                <!-- Marca / Modelo -->
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 whitespace-nowrap">
                                        <?= htmlspecialchars($item['r_marca']) ?>
                                    </span>
                                    <?php if ($item['r_modelo']): ?>
                                    <div class="text-xs text-slate-400 mt-1 truncate max-w-[120px]"><?= htmlspecialchars($item['r_modelo']) ?></div>
                                    <?php endif; ?>
                                </td>

                                <!-- Responsable / Área (oculto en móvil pequeño) -->
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

                                <!-- Estado -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <?= badgeEstado($item['r_estado']) ?>
                                </td>

                                <!-- Periféricos (oculto en móvil) -->
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

                                <!-- Acciones -->
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

                            <!-- ── PANEL PERIFÉRICOS ── -->
                            <?php if ($hasPerifericos): ?>
                            <tr class="fila-panel">
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
                                                        <div class="text-[10px] text-slate-500 truncate"><?= htmlspecialchars($peri['r_marca']) ?><?= $peri['r_modelo'] ? ' · '.$peri['r_modelo'] : '' ?></div>
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

                <!-- ══════════════════════════════════════════
                     PAGINACIÓN
                ══════════════════════════════════════════ -->
                <div class="mt-4 flex flex-col xs:flex-row items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center xs:text-left">
                        Pág. <span class="text-brand-600"><?= $page ?></span> / <?= $total_pages ?>
                        &nbsp;·&nbsp; <?= $res['total_registros'] ?> activos
                    </div>
                    <div class="flex gap-2">
                        <a href="?page=<?= max(1, $page-1) ?>"
                           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-black hover:bg-slate-200 transition-colors <?= $page <= 1 ? 'opacity-40 pointer-events-none' : '' ?>">
                            <i class="fas fa-chevron-left mr-1"></i> Anterior
                        </a>
                        <a href="?page=<?= min($total_pages, $page+1) ?>"
                           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-black hover:bg-slate-200 transition-colors <?= $page >= $total_pages ? 'opacity-40 pointer-events-none' : '' ?>">
                            Siguiente <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>

            </div><!-- /max-w -->
        </div><!-- /scroll -->
    </main>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script>
    // ══════════════════════════════════════════════════════
    //  ESTADO GLOBAL
    // ══════════════════════════════════════════════════════
    let filtroPrincipal  = 'todos';       // qué tipo de activo principal
    let filtroPeriferico = 'todos-peri';  // qué periférico debe tener

    // ══════════════════════════════════════════════════════
    //  EXPANDIR / COLAPSAR PERIFÉRICOS
    // ══════════════════════════════════════════════════════
    function togglePerifericos(panelId, rowEl) {
        const panel = document.getElementById(panelId);
        if (!panel) return;
        const abierto = !panel.classList.contains('hidden');
        panel.classList.toggle('hidden', abierto);
        rowEl.classList.toggle('open', !abierto);
        rowEl.classList.toggle('bg-brand-50/30', !abierto);
    }

    // ══════════════════════════════════════════════════════
    //  CLICK EN BOTÓN DE FILTRO
    // ══════════════════════════════════════════════════════
    function filtrarGrupo(grupo, tipo, btnEl) {
        if (grupo === 'principal')  filtroPrincipal  = tipo;
        if (grupo === 'perifericos') filtroPeriferico = tipo;

        // Resetear estilos del grupo
        document.querySelectorAll(`.grupo-${grupo}`).forEach(btn => {
            btn.classList.remove('activo', 'ring-2', 'ring-offset-1');
            // Restaurar bg y color originales
            btn.className = btn.className
                .split(' ')
                .filter(c => !c.startsWith('bg-') && !c.startsWith('text-') && c !== 'activo')
                .join(' ');
            btn.classList.add(btn.dataset.bg, btn.dataset.tc);
        });

        // Marcar activo
        btnEl.classList.add('activo', 'ring-2', 'ring-offset-1');

        aplicarFiltros();
    }

    // ══════════════════════════════════════════════════════
    //  LÓGICA COMBINADA: texto + dispositivo + periférico
    // ══════════════════════════════════════════════════════
    function aplicarFiltros() {
        const texto = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows  = document.querySelectorAll('#tbodyActivos tr.row-activo');

        rows.forEach(row => {
            const rowTipo = (row.dataset.tipo || '').toLowerCase();
            const rowPeri = (row.dataset.perifericos || '').toLowerCase();
            const rowTxt  = row.innerText.toLowerCase();

            // ── Coincidencia dispositivo principal
            let okPrincipal = false;
            if (filtroPrincipal === 'todos') {
                okPrincipal = true;
            } else if (filtroPrincipal === 'computador') {
                okPrincipal = rowTipo.includes('computador') || rowTipo.includes('desktop') || rowTipo.includes('pc');
            } else if (filtroPrincipal === 'laptop') {
                okPrincipal = rowTipo.includes('laptop') || rowTipo.includes('portátil') || rowTipo.includes('portatil');
            } else if (filtroPrincipal === 'tablet') {
                okPrincipal = rowTipo.includes('tablet') || rowTipo.includes('ipad');
            } else {
                okPrincipal = rowTipo.includes(filtroPrincipal);
            }

            // ── Coincidencia periférico
            let okPeri = false;
            if (filtroPeriferico === 'todos-peri') {
                okPeri = true;
            } else if (filtroPeriferico === 'mouse') {
                okPeri = rowPeri.includes('mouse') || rowPeri.includes('ratón') || rowPeri.includes('raton');
            } else if (filtroPeriferico === 'teclado') {
                okPeri = rowPeri.includes('teclado') || rowPeri.includes('keyboard');
            } else if (filtroPeriferico === 'lector') {
                okPeri = rowPeri.includes('lector') || rowPeri.includes('scanner') || rowPeri.includes('escáner');
            } else if (filtroPeriferico === 'monitor') {
                okPeri = rowPeri.includes('monitor') || rowPeri.includes('pantalla');
            } else if (filtroPeriferico === 'impresora') {
                okPeri = rowPeri.includes('impresora') || rowPeri.includes('printer');
            } else {
                okPeri = rowPeri.includes(filtroPeriferico);
            }

            // ── Coincidencia texto libre
            const okTexto = texto === '' || rowTxt.includes(texto);

            const visible = okPrincipal && okPeri && okTexto;
            row.style.display = visible ? '' : 'none';

            // Panel de periféricos asociado
            const panelRow = row.nextElementSibling;
            if (panelRow && panelRow.classList.contains('fila-panel')) {
                panelRow.style.display = visible ? '' : 'none';
                if (!visible) {
                    const panel = panelRow.querySelector('[id^="panel-"]');
                    if (panel) panel.classList.add('hidden');
                    row.classList.remove('open', 'bg-brand-50/30');
                }
            }
        });

        // Mensaje si no hay resultados visibles
        const visibles  = [...rows].filter(r => r.style.display !== 'none');
        let sinRes = document.getElementById('sinResultados');
        if (visibles.length === 0) {
            if (!sinRes) {
                const tr = document.createElement('tr');
                tr.id = 'sinResultados';
                tr.innerHTML = `<td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">
                    <i class="fas fa-search text-3xl mb-3 block opacity-30"></i>
                    No se encontraron activos con los filtros aplicados.
                </td>`;
                document.getElementById('tbodyActivos').appendChild(tr);
            }
        } else {
            if (sinRes) sinRes.remove();
        }
    }

    // ══════════════════════════════════════════════════════
    //  INIT: marcar "Todos" en ambos grupos al cargar
    // ══════════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', () => {
        // Guardar clases originales de cada botón
        document.querySelectorAll('.fbtn').forEach(btn => {
            btn.dataset.bg = [...btn.classList].filter(c => c.startsWith('bg-')).join(' ');
            btn.dataset.tc = [...btn.classList].filter(c => c.startsWith('text-')).join(' ');
        });

        // Marcar activos por defecto
        const btnTodosPrincipal  = document.querySelector('.grupo-principal[data-tipo="todos"]');
        const btnTodosPerifericos = document.querySelector('.grupo-perifericos[data-tipo="todos-peri"]');
        if (btnTodosPrincipal)  btnTodosPrincipal.classList.add('activo','ring-2','ring-offset-1');
        if (btnTodosPerifericos) btnTodosPerifericos.classList.add('activo','ring-2','ring-offset-1');
    });
    </script>
</body>
</html>