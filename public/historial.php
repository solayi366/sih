<?php
require_once '../controllers/historialController.php';
require_once '../core/HistorialHelper.php';
require_once '../config/config.php';

$res         = HistorialController::ver();
$activo      = $res['activo'];
$eventos     = $res['eventos'];
$page        = $res['page'];
$total_pages = $res['total_pages'];
$total       = $res['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial — <?= htmlspecialchars($activo['r_qr'] ?? '') ?> | SIH_QR</title>
    <script>
        (function(){
            var t = localStorage.getItem('sihTheme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
    <link rel="stylesheet" href="../assets/css/alerts.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#fff1f2', 100:'#ffe4e6', 500:'#f43f5e', 600:'#e11d48', 700:'#be123c', 900:'#881337' }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        /* ── Timeline central ── */
        .timeline-rail {
            position: absolute;
            left: 19px;
            top: 0; bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #e11d48 0%, #e2e8f0 40%, #e2e8f0 100%);
        }
        /* ── Icono del evento ── */
        .evento-dot {
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #e2e8f0;
            flex-shrink: 0;
            position: relative; z-index: 2;
        }
        /* ── Tarjeta de snapshot expandible ── */
        .snap-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease, opacity 0.25s ease;
            opacity: 0;
        }
        .snap-body.open {
            max-height: 2000px;
            opacity: 1;
        }
        /* ── Dark mode ── */
        .dark .evento-dot { border-color: rgba(16,14,24,0.90); box-shadow: 0 0 0 2px rgba(255,255,255,0.07); }
        .dark .timeline-rail { background: linear-gradient(to bottom, #e11d48 0%, rgba(255,255,255,0.07) 40%); }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-10 w-full">
            <div class="max-w-4xl mx-auto">

                <!-- ── CABECERA ──────────────────────────────────────────────── -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-brand-50 border-2 border-brand-100 rounded-2xl flex items-center justify-center text-brand-600">
                            <i class="fas fa-clock-rotate-left text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-brand-600 uppercase tracking-[0.2em] mb-0.5">Auditoría de Cambios</p>
                            <h1 class="text-2xl md:text-3xl font-black text-slate-900 leading-tight">
                                Historial
                                <span class="text-transparent bg-clip-text"
                                      style="background-image: linear-gradient(135deg, #e11d48, #be123c)">
                                    #<?= htmlspecialchars($activo['r_qr'] ?? $activo['r_id'] ?? '') ?>
                                </span>
                            </h1>
                            <p class="text-slate-400 text-sm font-medium mt-0.5">
                                <?= htmlspecialchars($activo['r_tipo'] ?? '') ?>
                                <?= $activo['r_marca'] ? '· ' . htmlspecialchars($activo['r_marca']) : '' ?>
                                <?= $activo['r_referencia'] ? '· ' . htmlspecialchars($activo['r_referencia']) : '' ?>
                                <span class="font-mono text-slate-300 text-xs ml-1">S/N: <?= htmlspecialchars($activo['r_serial'] ?? '—') ?></span>
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <a href="ver.php?id=<?= $activo['r_id'] ?>"
                           class="flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-600 border-2 border-slate-200 rounded-2xl text-sm font-bold transition-all shadow-sm">
                            <i class="fas fa-arrow-left text-slate-400"></i>
                            <span class="hidden sm:inline">Volver a Ficha</span>
                        </a>
                        <span class="flex items-center gap-2 px-5 py-2.5 bg-slate-100 text-slate-500 rounded-2xl text-sm font-bold">
                            <i class="fas fa-list-check text-slate-400"></i>
                            <?= $total ?> evento<?= $total !== 1 ? 's' : '' ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($res['error'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl p-4 mb-6 text-sm font-bold">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($res['error']) ?>
                    <p class="text-xs font-normal mt-1 text-red-500">
                        Asegúrate de haber ejecutado la migración
                        <code class="font-mono">003.fun_historial_detallado.sql</code> en la base de datos.
                    </p>
                </div>
                <?php endif; ?>

                <!-- ── TIMELINE ──────────────────────────────────────────────── -->
                <?php if (!empty($eventos)): ?>
                <div class="relative pl-14">
                    <div class="timeline-rail"></div>

                    <div class="space-y-6">
                    <?php foreach ($eventos as $i => $ev): ?>
                    <?php
                        $tipo = $ev['tipo'];

                        // ── Color e ícono según tipo de evento ──────────────
                        [$dotBg, $dotIcon, $dotText, $cardBorder] = match($tipo) {
                            'CREACION'         => ['bg-emerald-500',  'fa-star',            'text-white',      'border-emerald-200 hover:border-emerald-300'],
                            'EDICION'          => ['bg-blue-500',     'fa-pen',             'text-white',      'border-blue-200 hover:border-blue-300'],
                            'ASIGNACION'       => ['bg-violet-500',   'fa-user-check',      'text-white',      'border-violet-200 hover:border-violet-300'],
                            'CAMBIO_ESTADO'    => ['bg-amber-500',    'fa-circle-dot',      'text-white',      'border-amber-200 hover:border-amber-300'],
                            'NOVEDAD'          => ['bg-red-500',      'fa-triangle-exclamation', 'text-white', 'border-red-200 hover:border-red-300'],
                            'NOVEDAD_RESUELTA' => ['bg-teal-500',     'fa-circle-check',    'text-white',      'border-teal-200 hover:border-teal-300'],
                            'BAJA'             => ['bg-slate-400',    'fa-box-archive',     'text-white',      'border-slate-200 hover:border-slate-300'],
                            default            => ['bg-slate-300',    'fa-circle',          'text-white',      'border-slate-200 hover:border-slate-300'],
                        };

                        [$tipoBadgeBg, $tipoBadgeText] = match($tipo) {
                            'CREACION'         => ['bg-emerald-50 text-emerald-700',  ''],
                            'EDICION'          => ['bg-blue-50 text-blue-700',         ''],
                            'ASIGNACION'       => ['bg-violet-50 text-violet-700',    ''],
                            'CAMBIO_ESTADO'    => ['bg-amber-50 text-amber-700',       ''],
                            'NOVEDAD'          => ['bg-red-50 text-red-700',           ''],
                            'NOVEDAD_RESUELTA' => ['bg-teal-50 text-teal-700',         ''],
                            'BAJA'             => ['bg-slate-100 text-slate-500',      ''],
                            default            => ['bg-slate-100 text-slate-500',      ''],
                        };

                        $tieneSnap = !empty($ev['snapshot']);
                        $snapId    = 'snap-' . $ev['id'];

                        // ── Formatear fecha ──────────────────────────────────
                        $fechaDt = new DateTime($ev['fecha']);
                        $fechaTxt = $fechaDt->format('d M Y');
                        $horaTxt  = $fechaDt->format('H:i');
                    ?>
                    <div class="relative flex gap-4 group">

                        <!-- Dot -->
                        <div class="evento-dot <?= $dotBg ?> absolute -left-14">
                            <i class="fas <?= $dotIcon ?> text-[11px] <?= $dotText ?>"></i>
                        </div>

                        <!-- Tarjeta -->
                        <div class="flex-1 bg-white rounded-2xl border-2 <?= $cardBorder ?> shadow-sm hover:shadow-md transition-all duration-200">

                            <!-- Cabecera tarjeta -->
                            <div class="flex items-start justify-between p-5 gap-3">
                                <div class="flex-1 min-w-0">
                                    <!-- Badge tipo + fecha -->
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg <?= $tipoBadgeBg ?>">
                                            <i class="fas <?= $dotIcon ?> text-[9px]"></i>
                                            <?= htmlspecialchars($tipo) ?>
                                        </span>
                                        <span class="text-[10px] font-mono font-bold text-slate-400">
                                            <?= $fechaTxt ?> &nbsp;<span class="text-slate-300">|</span>&nbsp; <?= $horaTxt ?>
                                        </span>
                                    </div>

                                    <!-- Descripción principal -->
                                    <p class="text-sm font-bold text-slate-700 leading-snug">
                                        <?= nl2br(htmlspecialchars($ev['descripcion'])) ?>
                                    </p>

                                    <!-- Usuario -->
                                    <div class="flex items-center gap-1.5 mt-2">
                                        <div class="w-5 h-5 bg-slate-100 rounded-full flex items-center justify-center shrink-0">
                                            <i class="fas fa-user text-[8px] text-slate-400"></i>
                                        </div>
                                        <span class="text-[11px] font-bold text-slate-400">
                                            <?= htmlspecialchars($ev['usuario'] ?? 'sistema') ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Botón expandir snapshot -->
                                <?php if ($tieneSnap): ?>
                                <button onclick="toggleSnap('<?= $snapId ?>', this)"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-wide rounded-xl border border-slate-200 transition-all shrink-0"
                                        title="Ver estado del activo en este momento">
                                    <i class="fas fa-camera text-[9px]"></i>
                                    <span>Snapshot</span>
                                    <i class="fas fa-chevron-down text-[8px] transition-transform" id="chev-<?= $snapId ?>"></i>
                                </button>
                                <?php endif; ?>
                            </div>

                            <!-- Snapshot expandible -->
                            <?php if ($tieneSnap): ?>
                            <div id="<?= $snapId ?>" class="snap-body px-5 pb-5">
                                <div class="border-t border-slate-100 pt-4">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2 flex items-center gap-1.5">
                                        <i class="fas fa-camera text-[8px]"></i>
                                        Estado del activo en este momento
                                    </p>
                                    <?= HistorialHelper::renderSnapshot($ev['snapshot']) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                </div>

                <!-- ── PAGINACIÓN ─────────────────────────────────────────── -->
                <?php if ($total_pages > 1): ?>
                <div class="flex items-center justify-between mt-10 pt-6 border-t border-slate-200">
                    <span class="text-xs text-slate-400 font-bold">
                        Página <?= $page ?> de <?= $total_pages ?>
                    </span>
                    <div class="flex gap-2">
                        <?php if ($page > 1): ?>
                        <a href="?id=<?= $activo['r_id'] ?>&page=<?= $page - 1 ?>"
                           class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-600 border-2 border-slate-200 rounded-xl text-xs font-bold transition-all">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="?id=<?= $activo['r_id'] ?>&page=<?= $page + 1 ?>"
                           class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <!-- ── EMPTY STATE ──────────────────────────────────────────── -->
                <div class="text-center py-24">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-5 text-slate-300">
                        <i class="fas fa-clock-rotate-left text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-600">Sin historial aún</h3>
                    <p class="text-slate-400 text-sm mt-1">
                        Los eventos se registrarán automáticamente al editar este activo.
                    </p>
                    <?php if (!empty($res['error'])): ?>
                    <p class="text-red-400 text-xs mt-3 font-mono">
                        Ejecuta primero: <code>003.fun_historial_detallado.sql</code>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/dark_mode.js"></script>
    <script>
        function toggleSnap(id, btn) {
            const el   = document.getElementById(id);
            const chev = document.getElementById('chev-' + id);
            const open = el.classList.toggle('open');
            chev.style.transform = open ? 'rotate(180deg)' : '';
        }
    </script>
</body>
</html>
