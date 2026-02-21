<?php
require_once '../controllers/novedadesController.php';
require_once '../config/config.php';
$res        = NovedadesController::listar();
$tickets    = $res['tickets'];
$page       = $res['page'];
$total_pages= $res['total_pages'];
$total      = $res['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Ayuda | SIH_QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/css/alerts.css">
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
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-10 w-full">
            <div class="max-w-7xl mx-auto">

                <!-- CABECERA -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-rose-100 rounded-2xl text-rose-600">
                            <i class="fas fa-headset text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Mesa de Ayuda</h1>
                            <p class="text-slate-500 font-medium">
                                <?= $total ?> ticket<?= $total !== 1 ? 's' : '' ?> abierto<?= $total !== 1 ? 's' : '' ?> pendiente<?= $total !== 1 ? 's' : '' ?>
                            </p>
                        </div>
                    </div>
                    <a href="<?= rtrim(APP_URL,'/') ?>/public/portal_reportes.php" target="_blank"
                       class="flex items-center gap-2 px-5 py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-2xl text-sm font-bold transition-all shadow-lg">
                        <i class="fas fa-external-link-alt"></i> Ver Portal Público
                    </a>
                </div>

                <?php if (!empty($res['error'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl p-4 mb-6 text-sm font-bold">
                    <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($res['error']) ?>
                </div>
                <?php endif; ?>

                <!-- TABLA -->
                <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl overflow-hidden">
                    <?php if (!empty($tickets)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="p-5">Ticket / Fecha</th>
                                    <th class="p-5">Reportante</th>
                                    <th class="p-5">Activo Afectado</th>
                                    <th class="p-5">Detalle</th>
                                    <th class="p-5 text-center">Evidencia</th>
                                    <th class="p-5 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($tickets as $t): ?>
                                <tr class="hover:bg-rose-50/30 transition-colors group">

                                    <!-- Ticket + Fecha -->
                                    <td class="p-5 align-top">
                                        <span class="block text-xs font-black text-rose-600 mb-1">#<?= $t['r_id'] ?></span>
                                        <span class="text-xs font-bold text-slate-500"><?= date('Y-m-d', strtotime($t['r_fecha'])) ?></span>
                                        <span class="block text-[10px] text-slate-300"><?= date('H:i', strtotime($t['r_fecha'])) ?></span>
                                        <?php
                                        $estado = $t['r_estado'] ?? 'ABIERTO';
                                        $badgeClass = $estado === 'RESUELTO'
                                            ? 'bg-emerald-50 text-emerald-600'
                                            : 'bg-amber-50 text-amber-600';
                                        ?>
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-black <?= $badgeClass ?>">
                                            <?= htmlspecialchars($estado) ?>
                                        </span>
                                    </td>

                                    <!-- Reportante -->
                                    <td class="p-5 align-top">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-500 font-black text-xs flex-shrink-0">
                                                <?= strtoupper(substr($t['r_nombre_reportante'] ?? '?', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-700"><?= htmlspecialchars($t['r_nombre_reportante'] ?? '—') ?></p>
                                                <p class="text-[10px] font-mono text-slate-400"><?= htmlspecialchars($t['r_cod_nom'] ?? '') ?></p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Activo -->
                                    <td class="p-5 align-top">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($t['r_activo_qr'])): ?>
                                            <div class="p-1.5 bg-white border border-slate-200 rounded-xl shadow-sm flex-shrink-0">
                                                <img src="<?= rtrim(APP_URL,'/') ?>/controllers/qrController.php?codigo=<?= urlencode($t['r_activo_qr']) ?>"
                                                     class="w-10 h-10" alt="QR">
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="text-xs font-black text-slate-700 uppercase"><?= htmlspecialchars($t['r_activo_ref'] ?? '—') ?></p>
                                                <p class="text-[10px] font-mono text-slate-400"><?= htmlspecialchars($t['r_activo_qr'] ?? '') ?></p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Detalle -->
                                    <td class="p-5 align-top max-w-xs">
                                        <span class="inline-block px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-[9px] font-black uppercase mb-2 border border-slate-200">
                                            <?= htmlspecialchars($t['r_tipo_dano'] ?? '') ?>
                                        </span>
                                        <p class="text-sm text-slate-600 leading-snug font-medium">
                                            <?= nl2br(htmlspecialchars(substr($t['r_descripcion'] ?? '', 0, 120))) ?>
                                            <?= strlen($t['r_descripcion'] ?? '') > 120 ? '…' : '' ?>
                                        </p>
                                    </td>

                                    <!-- Evidencia -->
                                    <td class="p-5 align-top text-center">
                                        <?php if (!empty($t['r_foto']) && str_contains($t['r_foto'], 'http')): ?>
                                        <a href="<?= htmlspecialchars($t['r_foto']) ?>" target="_blank" class="relative inline-block group/img">
                                            <img src="<?= htmlspecialchars($t['r_foto']) ?>"
                                                 class="w-14 h-14 object-cover rounded-xl border-2 border-white shadow-md group-hover/img:scale-110 transition-transform">
                                            <div class="absolute inset-0 bg-black/20 rounded-xl flex items-center justify-center opacity-0 group-hover/img:opacity-100 transition-opacity">
                                                <i class="fas fa-search-plus text-white text-xs"></i>
                                            </div>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-xs text-slate-300 italic">Sin foto</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Acción -->
                                    <td class="p-5 align-top text-right">
                                        <?php if (($t['r_estado'] ?? '') !== 'RESUELTO'): ?>
                                        <button onclick="abrirModal('<?= $t['r_id'] ?>', '<?= addslashes(htmlspecialchars($t['r_tipo_dano'] ?? '')) ?>')"
                                                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-500/30 text-xs font-bold transition-all flex items-center gap-2 ml-auto">
                                            <i class="fas fa-check"></i> Resolver
                                        </button>
                                        <?php else: ?>
                                        <span class="text-xs text-emerald-500 font-bold flex items-center gap-1 justify-end">
                                            <i class="fas fa-check-circle"></i> Cerrado
                                        </span>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINACIÓN -->
                    <?php if ($total_pages > 1): ?>
                    <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100">
                        <span class="text-xs text-slate-400 font-bold">Página <?= $page ?> de <?= $total_pages ?></span>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-all">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php else: ?>
                    <!-- EMPTY STATE -->
                    <div class="text-center py-24 px-6">
                        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-clipboard-check text-4xl text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-700">¡Todo al día!</h3>
                        <p class="text-slate-400 text-sm mt-1">No hay tickets de soporte pendientes.</p>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <!-- MODAL RESOLVER -->
    <div id="modalResolver" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center backdrop-blur-sm">
        <div id="modalBox" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md mx-4 p-8 transform scale-95 transition-all duration-200">
            <h3 class="text-xl font-black text-slate-800 mb-1">Cerrar Ticket <span class="text-rose-600">#<span id="lblTicketID"></span></span></h3>
            <p class="text-sm text-slate-500 mb-6 font-medium" id="lblDetalle"></p>

            <form method="POST" action="../controllers/resolver_novedad.php">
                <input type="hidden" name="id_novedad" id="formIdNovedad">

                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Solución aplicada / Comentarios</label>
                <textarea name="solucion" rows="3" required
                          class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-700 outline-none focus:border-emerald-500 transition-all mb-6 resize-none"
                          placeholder="Ej: Se realizó cambio de disco duro..."></textarea>

                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModal()"
                            class="flex-1 py-3 bg-slate-100 text-slate-500 font-bold rounded-xl hover:bg-slate-200 transition-all">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all">
                        Confirmar Cierre
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/sidebar_logic.js"></script>
    <script>
        const modal    = document.getElementById('modalResolver');
        const modalBox = document.getElementById('modalBox');

        function abrirModal(id, detalle) {
            document.getElementById('lblTicketID').innerText  = id;
            document.getElementById('lblDetalle').innerText   = detalle;
            document.getElementById('formIdNovedad').value    = id;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => modalBox.classList.replace('scale-95', 'scale-100'), 10);
        }

        function cerrarModal() {
            modalBox.classList.replace('scale-100', 'scale-95');
            setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 200);
        }

        modal.addEventListener('click', e => { if (e.target === modal) cerrarModal(); });
    </script>
</body>
</html>
