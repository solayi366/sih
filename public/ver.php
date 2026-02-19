<?php
require_once '../controllers/activoVerController.php';
$res    = ActivoVerController::ver();
$activo = $res['activo'];
$hijos  = $res['hijos'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Técnica #<?= $activo['r_id'] ?> | SIH_QR</title>
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
        .glass-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(12px);
            border: 2px solid rgba(225, 29, 72, 0.08);
        }
        .red-gradient {
            background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%);
        }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans h-screen flex overflow-hidden"
      style="background: radial-gradient(circle at top left, #fff1f2, #f8fafc);">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative w-full min-w-0">
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-10 w-full">
            <div class="max-w-6xl mx-auto">

                <!-- CABECERA ── título + acciones -->
                <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6">
                    <div class="flex items-start gap-4">
                        <div>
                            <span class="text-brand-600 font-extrabold text-xs uppercase tracking-[0.2em]">
                                Ficha Técnica
                            </span>
                            <h1 class="text-3xl md:text-4xl font-black text-slate-900 leading-none mt-1">
                                Activo
                                <span class="text-transparent bg-clip-text red-gradient">
                                    #<?= $activo['r_id'] ?>
                                </span>
                            </h1>
                        </div>
                    </div>

                    <div class="flex gap-3 w-full md:w-auto">
                        <a href="activos.php"
                           class="flex-1 md:flex-none flex items-center justify-center gap-2 px-5 py-3 bg-white hover:bg-slate-50 text-slate-600 border-2 border-slate-200 rounded-2xl transition-all shadow-sm font-bold text-sm"
                           title="Volver al inventario">
                            <i class="fas fa-arrow-left text-slate-400"></i>
                        </a>
                        <a href="editar.php?id=<?= $activo['r_id'] ?>"
                           class="flex-1 md:flex-none flex items-center justify-center gap-2 px-5 py-3 bg-white hover:bg-slate-50 text-slate-700 border-2 border-slate-200 rounded-2xl transition-all shadow-sm font-bold text-sm"
                           title="Editar activo">
                            <i class="fas fa-pen-nib text-brand-500"></i>
                        </a>
                        <button onclick="window.print()"
                                class="flex-1 md:flex-none flex items-center justify-center gap-2 px-5 py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-2xl transition-all shadow-lg font-bold text-sm"
                                title="Imprimir ficha">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>

                <!-- GRID PRINCIPAL ── columna lateral + columna de contenido -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                    <!-- ════ COLUMNA IZQUIERDA (4 cols) ════ -->
                    <div class="lg:col-span-4 space-y-6">

                        <!-- Tarjeta QR + estado -->
                        <div class="glass-card rounded-[2.5rem] p-10 shadow-2xl shadow-brand-900/5 text-center relative overflow-hidden group border-2 border-brand-100">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-brand-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110 opacity-50"></div>

                            <div class="relative z-10">
                                <!-- Icono del tipo de equipo -->
                                <div class="inline-block p-6 bg-white rounded-[2rem] shadow-xl border-2 border-brand-50 mb-6">
                                    <div class="w-28 h-28 flex items-center justify-center">
                                        <?= iconoTipoGrande($activo['r_tipo']) ?>
                                    </div>
                                </div>

                                <p class="text-xs font-black text-brand-300 uppercase tracking-widest mb-1">
                                    Código Único
                                </p>
                                <h2 class="text-3xl font-black text-slate-800 mb-6 tracking-tighter font-mono">
                                    <?= htmlspecialchars($activo['r_qr'] ?? '—') ?>
                                </h2>

                                <!-- Badge de estado -->
                                <div class="inline-flex items-center">
                                    <?php if ($activo['r_estado'] === 'Bueno'): ?>
                                    <span class="px-8 py-2.5 bg-emerald-500 text-white rounded-full text-xs font-black shadow-lg shadow-emerald-200 uppercase tracking-wider border-2 border-emerald-400">
                                        <i class="fas fa-check-circle mr-2"></i> Operativo
                                    </span>
                                    <?php elseif ($activo['r_estado'] === 'Malo'): ?>
                                    <span class="px-8 py-2.5 bg-brand-600 text-white rounded-full text-xs font-black shadow-lg shadow-brand-200 uppercase tracking-wider border-2 border-brand-500">
                                        <i class="fas fa-times-circle mr-2"></i> Dañado
                                    </span>
                                    <?php else: ?>
                                    <span class="px-8 py-2.5 bg-amber-500 text-white rounded-full text-xs font-black shadow-lg shadow-amber-200 uppercase tracking-wider border-2 border-amber-400">
                                        <i class="fas fa-tools mr-2"></i>
                                        <?= htmlspecialchars($activo['r_estado']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta Custodio -->
                        <div class="red-gradient rounded-[2.5rem] p-8 text-white shadow-xl shadow-brand-900/20 relative overflow-hidden border-2 border-brand-700">
                            <div class="absolute bottom-0 right-0 opacity-10">
                                <i class="fas fa-user-shield text-9xl -mb-8 -mr-8"></i>
                            </div>
                            <h3 class="text-brand-100/60 text-xs font-black uppercase tracking-widest mb-6">
                                Custodio del Activo
                            </h3>

                            <?php if ($activo['r_responsable']): ?>
                            <div class="flex items-center gap-5 relative z-10">
                                <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center text-3xl border-2 border-white/20">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-black text-xl leading-tight truncate">
                                        <?= htmlspecialchars($activo['r_responsable']) ?>
                                    </p>
                                    <p class="text-brand-100/80 text-sm font-medium mt-1 truncate">
                                        <?= htmlspecialchars($activo['r_area'] ?? 'Sin área') ?>
                                    </p>
                                    <p class="text-brand-100/50 text-xs font-bold mt-0.5 font-mono">
                                        <?= htmlspecialchars($activo['r_cod_responsable'] ?? '') ?>
                                    </p>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="flex items-center gap-4 py-2 relative z-10">
                                <i class="fas fa-warehouse text-3xl text-brand-200/50"></i>
                                <span class="font-bold text-lg text-brand-50">Disponible en Bodega</span>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- ════ COLUMNA DERECHA (8 cols) ════ -->
                    <div class="lg:col-span-8 space-y-6">

                        <!-- Especificaciones Técnicas -->
                        <div class="glass-card rounded-[2.5rem] p-10 shadow-sm border-2 border-brand-50">
                            <div class="flex items-center gap-3 mb-10">
                                <div class="w-10 h-1 bg-brand-600 rounded-full"></div>
                                <h3 class="text-xl font-black text-slate-900">Especificaciones Técnicas</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-x-12 gap-y-8">
                                <!-- Categoría -->
                                <div class="space-y-1">
                                    <span class="text-[10px] font-black text-brand-600 uppercase tracking-[0.15em]">
                                        Categoría
                                    </span>
                                    <p class="text-slate-800 font-bold text-lg leading-tight">
                                        <?= htmlspecialchars($activo['r_tipo']) ?>
                                    </p>
                                </div>

                                <!-- Marca / Modelo -->
                                <div class="space-y-1">
                                    <span class="text-[10px] font-black text-brand-600 uppercase tracking-[0.15em]">
                                        Marca / Modelo
                                    </span>
                                    <p class="text-slate-800 font-bold text-lg leading-tight">
                                        <?= htmlspecialchars($activo['r_marca']) ?>
                                        <?php if ($activo['r_modelo']): ?>
                                        <span class="text-slate-300 font-normal mx-1">|</span>
                                        <?= htmlspecialchars($activo['r_modelo']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <!-- Serial -->
                                <div class="md:col-span-2 bg-slate-50 p-6 rounded-3xl border-2 border-slate-100 flex justify-between items-center group">
                                    <div class="space-y-1">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">
                                            Serial del Fabricante
                                        </span>
                                        <p class="font-mono text-brand-700 font-black text-2xl tracking-tighter">
                                            <?= htmlspecialchars($activo['r_serial'] ?? 'S/N') ?>
                                        </p>
                                    </div>
                                    <i class="fas fa-barcode text-4xl text-slate-200 group-hover:text-brand-100 transition-colors"></i>
                                </div>
                            </div>

                            <!-- Datos de red (solo si existen) -->
                            <?php if ($activo['r_hostname'] || $activo['r_ip'] || $activo['r_mac']): ?>
                            <div class="mt-12 pt-10 border-t-2 border-slate-100">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                    <?php if ($activo['r_hostname']): ?>
                                    <div class="p-4 bg-white rounded-2xl border-2 border-brand-50 border-b-4 border-b-brand-100 shadow-sm">
                                        <span class="block text-[9px] font-black text-slate-400 uppercase mb-1">
                                            Hostname
                                        </span>
                                        <span class="font-mono text-lg font-bold text-slate-700 truncate block">
                                            <?= htmlspecialchars($activo['r_hostname']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($activo['r_ip']): ?>
                                    <div class="p-4 bg-white rounded-2xl border-2 border-brand-50 border-b-4 border-b-brand-300 shadow-sm">
                                        <span class="block text-[9px] font-black text-slate-400 uppercase mb-1">
                                            Dirección IP
                                        </span>
                                        <span class="font-mono text-lg font-bold text-slate-700 block">
                                            <?= htmlspecialchars($activo['r_ip']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($activo['r_mac']): ?>
                                    <div class="p-4 bg-white rounded-2xl border-2 border-brand-50 border-b-4 border-b-brand-300 shadow-sm">
                                        <span class="block text-[9px] font-black text-slate-400 uppercase mb-1">
                                            MAC Address
                                        </span>
                                        <span class="font-mono text-base font-bold text-slate-700 truncate block">
                                            <?= htmlspecialchars($activo['r_mac']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Referencia (si existe) -->
                            <?php if ($activo['r_referencia']): ?>
                            <div class="mt-8 pt-8 border-t-2 border-slate-100">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">
                                    Referencia / Descripción
                                </span>
                                <p class="text-slate-700 font-semibold text-sm mt-1">
                                    <?= htmlspecialchars($activo['r_referencia']) ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Periféricos vinculados (solo activos principales) -->
                        <?php if (!empty($hijos)): ?>
                        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border-2 border-slate-100 overflow-hidden">
                            <div class="px-10 py-6 bg-slate-900 text-white flex justify-between items-center">
                                <h3 class="font-black text-sm uppercase tracking-widest flex items-center gap-3">
                                    <span class="w-2 h-2 bg-brand-500 rounded-full animate-pulse"></span>
                                    Periféricos Vinculados
                                    <span class="ml-1 px-2 py-0.5 bg-white/10 rounded-full text-xs">
                                        <?= count($hijos) ?>
                                    </span>
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 border-b-2 border-slate-100">
                                        <tr>
                                            <th class="px-10 py-4 text-[10px] font-black text-slate-400 uppercase">
                                                Identificador
                                            </th>
                                            <th class="px-10 py-4 text-[10px] font-black text-slate-400 uppercase">
                                                Descripción
                                            </th>
                                            <th class="px-10 py-4 text-[10px] font-black text-slate-400 uppercase text-right">
                                                Acción
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y-2 divide-slate-50">
                                        <?php foreach ($hijos as $hijo): ?>
                                        <tr class="hover:bg-brand-50/20 transition-colors group">
                                            <td class="px-10 py-5">
                                                <div class="font-mono font-black text-brand-600 leading-tight">
                                                    <?= htmlspecialchars($hijo['r_qr'] ?? '—') ?>
                                                </div>
                                                <div class="text-[10px] text-slate-400 font-bold mt-0.5">
                                                    <?= htmlspecialchars($hijo['r_tipo']) ?>
                                                </div>
                                            </td>
                                            <td class="px-10 py-5">
                                                <div class="text-sm font-bold text-slate-700 truncate max-w-[200px]">
                                                    <?= htmlspecialchars($hijo['r_referencia'] ?? $hijo['r_marca']) ?>
                                                </div>
                                                <div class="flex items-center gap-1 mt-1">
                                                    <span class="w-1.5 h-1.5 rounded-full <?= $hijo['r_estado'] === 'Bueno' ? 'bg-emerald-500' : 'bg-brand-500' ?>"></span>
                                                    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-tighter">
                                                        <?= htmlspecialchars($hijo['r_estado']) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-10 py-5 text-right">
                                                <a href="ver.php?id=<?= $hijo['r_id'] ?>"
                                                   class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 text-slate-400 group-hover:bg-brand-600 group-hover:text-white transition-all border-2 border-transparent"
                                                   title="Ver ficha del periférico">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Banner: es periférico de otro activo (tiene padre) -->
                        <?php if ($activo['r_id_padre']): ?>
                        <div class="relative p-8 rounded-[2.5rem] bg-slate-900 text-white overflow-hidden shadow-2xl border-2 border-slate-800">
                            <div class="absolute top-0 right-0 w-32 h-32 red-gradient rounded-full blur-3xl opacity-20 -mr-16 -mt-16"></div>
                            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                                <div class="flex items-center gap-5">
                                    <div class="w-14 h-14 rounded-2xl bg-brand-600 flex items-center justify-center text-2xl border-2 border-brand-500 shadow-lg">
                                        <i class="fas fa-link"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-brand-400 text-[10px] font-black uppercase tracking-widest">
                                            Dependencia jerárquica
                                        </h4>
                                        <p class="text-xl font-bold leading-tight">
                                            Accesorio de:
                                            <span class="text-brand-100">
                                                <?= htmlspecialchars($activo['r_tipo_padre'] ?? 'Activo Principal') ?>
                                            </span>
                                        </p>
                                        <p class="text-slate-500 text-xs font-mono mt-1">
                                            <?= htmlspecialchars($activo['r_qr_padre'] ?? '') ?>
                                        </p>
                                    </div>
                                </div>
                                <a href="ver.php?id=<?= $activo['r_id_padre'] ?>"
                                   class="w-full md:w-auto px-8 py-3 bg-white text-slate-900 rounded-2xl font-black text-sm hover:bg-brand-50 transition-colors shadow-lg shadow-white/5 border-2 border-white">
                                    IR AL PADRE
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Toast de notificaciones -->
    <div id="liveToast" class="fixed bottom-8 right-8 translate-y-24 opacity-0 transition-all duration-700 z-[100]">
        <div class="bg-slate-900 text-white rounded-3xl p-5 shadow-2xl flex items-center gap-4 min-w-[320px] border-2 border-white/10">
            <div id="toastIcon" class="w-12 h-12 rounded-2xl red-gradient flex items-center justify-center text-xl shadow-lg shadow-brand-500/20"></div>
            <div>
                <p id="toastMessage" class="font-bold text-sm tracking-tight"></p>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar_logic.js"></script>
    <script>
        window.addEventListener('load', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.has('msg')) {
                const toast   = document.getElementById('liveToast');
                const message = document.getElementById('toastMessage');
                const icon    = document.getElementById('toastIcon');
                const tipo    = params.get('tipo');

                message.textContent = params.get('msg');
                icon.innerHTML = tipo === 'success'
                    ? '<i class="fas fa-check"></i>'
                    : '<i class="fas fa-exclamation text-brand-200"></i>';

                toast.classList.remove('translate-y-24', 'opacity-0');
                setTimeout(() => toast.classList.add('translate-y-24', 'opacity-0'), 6000);
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>

<?php
// ─────────────────────────────────────────────────────────────────────────────
// HELPERS DE VISTA
// ─────────────────────────────────────────────────────────────────────────────

function iconoTipoGrande(string $tipo): string {
    $t = strtolower($tipo);
    if (str_contains($t,'laptop') || str_contains($t,'portátil') || str_contains($t,'portatil'))
        return '<i class="fas fa-laptop text-blue-400 text-6xl"></i>';
    if (str_contains($t,'computador') || str_contains($t,'desktop') || str_contains($t,'pc'))
        return '<i class="fas fa-desktop text-indigo-400 text-6xl"></i>';
    if (str_contains($t,'tablet') || str_contains($t,'ipad'))
        return '<i class="fas fa-tablet-screen-button text-cyan-400 text-6xl"></i>';
    if (str_contains($t,'mouse') || str_contains($t,'ratón') || str_contains($t,'raton'))
        return '<i class="fas fa-computer-mouse text-emerald-400 text-6xl"></i>';
    if (str_contains($t,'teclado') || str_contains($t,'keyboard'))
        return '<i class="fas fa-keyboard text-amber-400 text-6xl"></i>';
    if (str_contains($t,'lector') || str_contains($t,'scanner') || str_contains($t,'escáner'))
        return '<i class="fas fa-barcode text-rose-400 text-6xl"></i>';
    if (str_contains($t,'monitor') || str_contains($t,'pantalla'))
        return '<i class="fas fa-tv text-purple-400 text-6xl"></i>';
    if (str_contains($t,'impresora') || str_contains($t,'printer'))
        return '<i class="fas fa-print text-orange-400 text-6xl"></i>';
    if (str_contains($t,'servidor') || str_contains($t,'server'))
        return '<i class="fas fa-server text-slate-400 text-6xl"></i>';
    if (str_contains($t,'celular') || str_contains($t,'telefono') || str_contains($t,'teléfono'))
        return '<i class="fas fa-mobile-screen text-teal-400 text-6xl"></i>';
    if (str_contains($t,'ups'))
        return '<i class="fas fa-bolt text-yellow-400 text-6xl"></i>';
    return '<i class="fas fa-microchip text-slate-300 text-6xl"></i>';
}
?>