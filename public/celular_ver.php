<?php
require_once '../controllers/celularesController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: celulares.php?msg=' . urlencode('ID inválido.') . '&tipo=danger'); exit(); }

$data = CelularesController::ver($id);

if (isset($data['error'])) {
    header('Location: celulares.php?msg=' . urlencode($data['error']) . '&tipo=danger');
    exit();
}

$cel          = $data['celular'];
$historial    = $data['historial'];
$credenciales = $data['credenciales'];
$es_admin     = $data['es_admin'];

function badgeCelular(string $estado): string {
    return match($estado) {
        'ASIGNADO'                   => '<span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-700">ASIGNADO</span>',
        'EN REPOSICION'              => '<span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-700">EN REPOSICIÓN</span>',
        'EN PROCESO DE REASIGNACION' => '<span class="px-2.5 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-700">REASIGNACIÓN</span>',
        'DE BAJA'                    => '<span class="px-2.5 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-700">DE BAJA</span>',
        default                      => '<span class="px-2.5 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-600">' . htmlspecialchars($estado) . '</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Celular | SIH_QR</title>
    <script>(function(){var t=localStorage.getItem('sihTheme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark');}})();</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{brand:{50:'#fff1f2',100:'#ffe4e6',600:'#e11d48',700:'#be123c'}},fontFamily:{sans:['Plus Jakarta Sans','sans-serif']}}}}</script>
    <link rel="stylesheet" href="../assets/css/custom_sidebar.css">
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
    <style>
        .dark .bg-white { background-color:rgba(16,14,24,0.90)!important; border-color:rgba(255,255,255,0.07)!important; }
        .dark .bg-slate-50 { background-color:rgba(14,12,22,0.90)!important; }
        .dark .divide-y.divide-slate-100>*+* { border-color:rgba(255,255,255,0.07)!important; }
        .dark .border-slate-100,.dark .border-b.border-slate-100 { border-color:rgba(255,255,255,0.07)!important; }
        .dark .text-slate-900 { color:#f1f5f9!important; }
        .dark .text-slate-700 { color:#cbd5e1!important; }
        .dark .text-slate-500 { color:#94a3b8!important; }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative bg-slate-50/50 w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-3 sm:p-6 md:p-8 w-full">
            <div class="max-w-3xl mx-auto">

                <!-- ── HEADER ─────────────────────────────────────────── -->
                <div class="flex items-center justify-between gap-3 mb-6">
                    <div class="flex items-center gap-3">
                        <a href="celulares.php"
                           class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-brand-600 transition-all shadow-sm">
                            <i class="fas fa-arrow-left text-xs"></i>
                        </a>
                        <div>
                            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">
                                Ficha — Línea <?= htmlspecialchars($cel['r_linea']) ?>
                            </h1>
                            <p class="text-slate-400 text-sm mt-0.5">
                                <?= htmlspecialchars($cel['r_marca']) ?> <?= htmlspecialchars($cel['r_modelo']) ?>
                                &nbsp;·&nbsp; <?= badgeCelular($cel['r_estado']) ?>
                            </p>
                        </div>
                    </div>
                    <a href="celular_etiqueta.php?id=<?= $id ?>"
                       class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black text-slate-600 border-2 border-slate-200 hover:border-slate-400 hover:scale-105 transition-all shrink-0 bg-white"
                       title="Imprimir etiqueta sticker">
                        <i class="fas fa-tag"></i>
                        <span class="hidden sm:inline">Etiqueta</span>
                    </a>
                    <a href="celular_editar.php?id=<?= $id ?>"
                       class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black text-white shadow-lg hover:scale-105 transition-all shrink-0"
                       style="background:linear-gradient(135deg,#e11d48,#9f1239)">
                        <i class="fas fa-pen"></i>
                        <span class="hidden sm:inline">Editar</span>
                    </a>
                </div>

                <!-- ── DATOS PRINCIPALES ──────────────────────────────── -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <i class="fas fa-mobile-screen-button mr-1.5 text-brand-600"></i>Datos del Equipo
                        </p>
                        <span class="text-[10px] font-mono text-slate-400">#<?= str_pad($id, 5, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-0 divide-y divide-slate-100 sm:divide-y-0">

                        <?php
                        $campos = [
                            ['Número de Línea', $cel['r_linea'],    'fas fa-phone'],
                            ['IMEI',            $cel['r_imei'],     'fas fa-barcode'],
                            ['Marca',           $cel['r_marca'],    'fas fa-tag'],
                            ['Modelo',          $cel['r_modelo'],   'fas fa-mobile-screen-button'],
                            ['Estado',          null,               'fas fa-circle-check'],
                        ];
                        ?>
                        <?php foreach ($campos as [$label, $valor, $icon]): ?>
                        <div class="p-4 border-r border-slate-100 last:border-r-0">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                <i class="<?= $icon ?> mr-1"></i><?= $label ?>
                            </p>
                            <?php if ($label === 'Estado'): ?>
                                <?= badgeCelular($cel['r_estado']) ?>
                            <?php else: ?>
                                <p class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($valor ?? '—') ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ── RESPONSABLE ────────────────────────────────────── -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <i class="fas fa-user mr-1.5 text-brand-600"></i>Responsable Actual
                        </p>
                    </div>
                    <div class="p-5 flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-brand-100 flex items-center justify-center text-brand-700 text-xl font-black shrink-0">
                            <?= strtoupper(substr($cel['r_responsable'] ?? 'B', 0, 2)) ?>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Nombre</p>
                                <p class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($cel['r_responsable'] ?? '—') ?></p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Cód. Nómina</p>
                                <p class="font-mono font-bold text-brand-600 text-sm"><?= htmlspecialchars($cel['r_cod_nom'] ?? '—') ?></p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Cargo</p>
                                <p class="font-bold text-slate-700 text-sm"><?= htmlspecialchars($cel['r_cargo'] ?? '—') ?></p>
                            </div>
                            <?php if ($cel['r_area']): ?>
                            <div class="sm:col-span-3">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Área</p>
                                <p class="font-bold text-slate-700 text-sm"><?= htmlspecialchars($cel['r_area']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ── CREDENCIALES — solo admin ──────────────────────── -->
                <?php if ($es_admin): ?>
                <div class="bg-white rounded-2xl border border-amber-200 shadow-sm overflow-hidden mb-5">
                    <div class="px-5 py-3.5 border-b border-amber-100 bg-amber-50/60 flex items-center justify-between">
                        <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">
                            <i class="fas fa-lock mr-1.5"></i>Credenciales
                            <span class="ml-2 text-[9px] font-bold text-amber-500">Visible solo para admins</span>
                        </p>
                        <button onclick="toggleCredenciales()" id="btnCredenciales"
                                class="text-[10px] font-black text-amber-600 hover:text-amber-800 flex items-center gap-1">
                            <i class="fas fa-eye" id="iconCredenciales"></i>
                            <span id="textoCredenciales">Mostrar</span>
                        </button>
                    </div>
                    <div id="panelCredenciales" class="hidden p-5 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">PIN</p>
                            <p class="font-mono font-bold text-slate-900 text-lg tracking-widest">
                                <?= $credenciales && $credenciales['r_pin'] ? htmlspecialchars($credenciales['r_pin']) : '<span class="text-slate-300 text-sm">Sin PIN</span>' ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">PUK</p>
                            <p class="font-mono font-bold text-slate-900 text-lg tracking-widest">
                                <?= $credenciales && $credenciales['r_puk'] ? htmlspecialchars($credenciales['r_puk']) : '<span class="text-slate-300 text-sm">Sin PUK</span>' ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── OBSERVACIONES ──────────────────────────────────── -->
                <?php if ($cel['r_observaciones']): ?>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <i class="fas fa-note-sticky mr-1.5"></i>Observaciones
                        </p>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($cel['r_observaciones'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── HISTORIAL DE REASIGNACIONES ────────────────────── -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <i class="fas fa-clock-rotate-left mr-1.5 text-brand-600"></i>Historial de Reasignaciones
                            <span class="ml-2 text-brand-600"><?= count($historial) ?></span>
                        </p>
                    </div>

                    <?php if (empty($historial)): ?>
                    <div class="py-10 text-center text-slate-400">
                        <i class="fas fa-clock-rotate-left text-3xl mb-2 opacity-20 block"></i>
                        <p class="text-sm font-bold">Sin cambios de responsable registrados</p>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-slate-100">
                        <?php foreach ($historial as $h): ?>
                        <div class="px-5 py-4 flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-arrow-right text-slate-400 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mb-1">
                                    <span class="text-xs font-black text-slate-900"><?= htmlspecialchars($h['r_responsable_nuevo'] ?? '—') ?></span>
                                    <span class="text-[10px] font-mono text-brand-600"><?= htmlspecialchars($h['r_cod_nom_nuevo'] ?? '') ?></span>
                                    <?php if ($h['r_estado_nuevo']): ?>
                                    <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-slate-100 text-slate-500"><?= htmlspecialchars($h['r_estado_nuevo']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-[10px] text-slate-400">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Antes: <span class="font-semibold text-slate-500"><?= htmlspecialchars($h['r_responsable_anterior'] ?? '—') ?></span>
                                </p>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($h['r_fecha_cambio'] ?? 'now')) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- ── METADATOS ──────────────────────────────────────── -->
                <div class="flex gap-4 text-[10px] text-slate-400 font-semibold pb-8">
                    <span><i class="fas fa-calendar-plus mr-1"></i>Registrado: <?= date('d/m/Y H:i', strtotime($cel['r_fecha_registro'] ?? 'now')) ?></span>
                    <?php if ($cel['r_fecha_actualizacion']): ?>
                    <span><i class="fas fa-calendar-check mr-1"></i>Actualizado: <?= date('d/m/Y H:i', strtotime($cel['r_fecha_actualizacion'])) ?></span>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/dark_mode.js"></script>
    <script>
    function toggleCredenciales() {
        const panel  = document.getElementById('panelCredenciales');
        const icon   = document.getElementById('iconCredenciales');
        const texto  = document.getElementById('textoCredenciales');
        const visible = !panel.classList.contains('hidden');
        panel.classList.toggle('hidden');
        icon.className  = visible ? 'fas fa-eye' : 'fas fa-eye-slash';
        texto.textContent = visible ? 'Mostrar' : 'Ocultar';
    }
    </script>
</body>
</html>
