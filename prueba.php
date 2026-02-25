<?php
// sih_qr/public/ver.php
require_once '../controllers/activoVerController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: activos.php");
    exit();
}

$activo = ActivoVerController::obtenerDetalle($_GET['id']);

if (!$activo) {
    header("Location: activos.php?msg=Activo no encontrado&tipo=danger");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoja de Vida | <?= htmlspecialchars($activo['r_qr']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#fff1f2', 100: '#ffe4e6', 600: '#e11d48', 700: '#be123c', 900: '#4c0519' }
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>

    <style>
        body { background: radial-gradient(circle at top left, #fff1f2, #f8fafc); min-height: 100vh; }
        .glass-card { 
            background: rgba(255, 255, 255, 0.75); 
            backdrop-filter: blur(12px); 
            border: 2px solid rgba(225, 29, 72, 0.15); 
            border-radius: 2.5rem;
        }
        .red-gradient { background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%); }
        .timeline-item::after {
            content: ''; position: absolute; left: 3.5px; top: 20px;
            width: 1px; height: calc(100% - 10px); background: #e2e8f0;
        }
        .timeline-item:last-child::after { display: none; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body class="text-slate-800 antialiased font-sans flex">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-10 transition-all duration-300 ml-0 md:ml-64">
        <div class="max-w-6xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6 no-print">
                <div class="flex items-start gap-5">
                    <div class="p-4 bg-white rounded-3xl shadow-md border-2 border-slate-100">
                        <img src="../assets/logo.png" class="h-10 w-auto" alt="Logo">
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">Hoja de <span class="text-brand-600">Vida</span></h1>
                        <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Activo QR: <?= htmlspecialchars($activo['r_qr']) ?></p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="editar.php?id=<?= $activo['r_id'] ?>" class="px-6 py-3 bg-slate-900 text-white rounded-2xl font-black text-[10px] hover:scale-105 transition-all flex items-center gap-2 shadow-xl shadow-slate-900/20 uppercase">
                        <i class="fas fa-edit"></i> Editar Equipo
                    </a>
                    <button onclick="window.print()" class="px-6 py-3 bg-white text-slate-900 border-2 border-slate-200 rounded-2xl font-black text-[10px] hover:border-brand-600 transition-all flex items-center gap-2 uppercase">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-8 space-y-8">
                    
                    <div class="glass-card p-10 shadow-2xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-10 opacity-5">
                            <i class="fas fa-microchip text-9xl text-brand-600"></i>
                        </div>
                        
                        <div class="relative z-10">
                            <span class="px-4 py-1.5 bg-brand-50 text-brand-600 rounded-full text-[9px] font-black tracking-widest uppercase mb-6 inline-block italic border border-brand-100">
                                Hardware verificado
                            </span>
                            
                            <h2 class="text-5xl font-black text-slate-900 tracking-tighter mb-10 uppercase italic">
                                <?= htmlspecialchars($activo['r_tipo']) ?> 
                                <span class="text-brand-600"><?= htmlspecialchars($activo['r_marca']) ?></span>
                            </h2>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-y-10 gap-x-6">
                                <div><p class="text-[9px] font-black text-slate-400 uppercase mb-1">Serial S/N</p><p class="font-bold text-slate-900 uppercase font-mono"><?= htmlspecialchars($activo['r_serial']) ?></p></div>
                                <div><p class="text-[9px] font-black text-slate-400 uppercase mb-1">Referencia</p><p class="font-bold text-slate-900 uppercase italic"><?= htmlspecialchars($activo['r_referencia'] ?: 'N/A') ?></p></div>
                                <div><p class="text-[9px] font-black text-slate-400 uppercase mb-1">Hostname</p><p class="font-bold text-brand-600 uppercase italic"><?= htmlspecialchars($activo['r_hostname'] ?: 'SIN NOMBRE') ?></p></div>
                                <div><p class="text-[9px] font-black text-slate-400 uppercase mb-1">Dirección IP</p><p class="font-bold text-slate-900 font-mono"><?= htmlspecialchars($activo['r_ip'] ?: 'DHCP') ?></p></div>
                                <div><p class="text-[9px] font-black text-slate-400 uppercase mb-1">MAC Address</p><p class="font-bold text-slate-900 font-mono uppercase"><?= htmlspecialchars($activo['r_mac'] ?: 'N/A') ?></p></div>

                                <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
                                <div class="relative">
                                    <p class="text-[9px] font-black text-brand-600 uppercase mb-1 flex items-center gap-1">
                                        <i class="fas fa-key text-[8px]"></i> Password Acceso
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <input type="password" id="pass_field" readonly 
                                            value="<?= htmlspecialchars($activo['r_pass_activo'] ?? '') ?>" 
                                            class="bg-transparent border-none p-0 font-bold text-slate-900 font-mono outline-none w-24 text-sm">
                                        <button type="button" onclick="togglePassword()" class="text-slate-400 hover:text-brand-600 transition-colors text-xs no-print">
                                            <i id="pass_icon" class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white shadow-2xl">
                        <h3 class="text-[11px] font-black uppercase tracking-widest text-brand-500 mb-8 flex items-center gap-3">
                            <i class="fas fa-plug text-xl"></i> Accesorios Vinculados
                        </h3>
                        <?php if (empty($activo['perifericos'])): ?>
                            <p class="text-slate-500 font-bold text-xs italic">No hay componentes adicionales registrados.</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($activo['perifericos'] as $p): ?>
                                <div class="bg-white/5 border border-white/10 p-5 rounded-3xl flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-brand-600/20 flex items-center justify-center text-brand-500 text-lg"><i class="fas fa-link"></i></div>
                                    <div>
                                        <p class="text-[9px] font-black text-brand-500 uppercase italic"><?= htmlspecialchars($p['r_tipo']) ?></p>
                                        <p class="font-bold text-sm text-white"><?= htmlspecialchars($p['r_serial']) ?></p>
                                        <p class="text-[8px] text-slate-400 uppercase tracking-widest"><?= htmlspecialchars($p['r_marca'] . ' · ' . $p['r_modelo']) ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-8">
                    <div class="bg-white rounded-[2.5rem] border-2 border-slate-100 p-10 shadow-xl text-center">
                        <div class="w-24 h-24 bg-slate-50 rounded-full mx-auto mb-6 flex items-center justify-center text-slate-200 text-4xl border-2 border-slate-50 shadow-inner"><i class="fas fa-id-card"></i></div>
                        <h4 class="font-black text-slate-900 uppercase tracking-tighter italic text-lg leading-tight mb-1"><?= htmlspecialchars($activo['r_responsable'] ?? 'SIN CUSTODIO') ?></h4>
                        <p class="text-brand-600 font-black text-[10px] mb-8 uppercase tracking-widest"><?= htmlspecialchars($activo['r_cod_responsable'] ?? 'S/N') ?></p>
                        
                        <div class="space-y-4 pt-8 border-t border-slate-50 text-left">
                            <div class="flex justify-between items-center"><span class="text-[9px] font-black text-slate-400 uppercase">Área</span><span class="text-[10px] font-bold text-slate-700 uppercase italic"><?= htmlspecialchars($activo['r_area'] ?? 'GENERAL') ?></span></div>
                            <div class="flex justify-between items-center"><span class="text-[9px] font-black text-slate-400 uppercase">Estado</span><span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[8px] font-black uppercase"><?= htmlspecialchars($activo['r_estado']) ?></span></div>
                            <div class="flex justify-between items-center"><span class="text-[9px] font-black text-slate-400 uppercase">Creado</span><span class="text-[10px] font-bold text-slate-500 tracking-tighter"><?= date('d/m/Y', strtotime($activo['r_fecha_creacion'])) ?></span></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] border-2 border-slate-100 p-10 shadow-xl">
                        <h3 class="text-[10px] font-black text-slate-900 uppercase mb-8 pb-4 border-b border-slate-50 italic flex items-center gap-2"><i class="fas fa-history text-brand-600"></i> Línea de Tiempo</h3>
                        <div class="space-y-8 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                            <?php if (empty($activo['historial'])): ?>
                                <p class="text-[10px] text-slate-400 font-bold italic text-center">Sin actualizaciones registradas.</p>
                            <?php else: ?>
                                <?php foreach ($activo['historial'] as $h): ?>
                                <div class="relative pl-6 timeline-item">
                                    <div class="absolute left-0 top-1.5 w-2 h-2 bg-brand-600 rounded-full shadow-[0_0_5px_rgba(225,29,72,0.5)]"></div>
                                    <p class="text-[8px] font-black text-slate-400 uppercase mb-1"><?= date('d M, Y', strtotime($h['fecha_actualizacion'])) ?></p>
                                    <p class="text-[10px] font-bold text-slate-800 leading-tight"><?= htmlspecialchars($h['descripcion']) ?></p>
                                    <p class="text-[8px] text-brand-600 font-black uppercase mt-1">Por: <?= htmlspecialchars($h['nom_usuario']) ?></p>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        function togglePassword() {
            const field = document.getElementById('pass_field');
            const icon = document.getElementById('pass_icon');
            if (field.type === "password") {
                field.type = "text"; icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = "password"; icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
    <script src="../assets/js/sidebar_logic.js"></script>
</body>
</html>