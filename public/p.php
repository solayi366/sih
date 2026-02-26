<?php
require_once '../controllers/activoVerController.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: activos.php"); exit(); }

$activo = ActivoVerController::obtenerDetalle($_GET['id']);
if (!$activo) { header("Location: activos.php?msg=No hallado&tipo=danger"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Vida | <?= htmlspecialchars($activo['r_qr']) ?></title>
        <!-- Dark mode: aplicar clase antes del render para evitar flash -->
    <script>
        (function(){
            var t = localStorage.getItem('sihTheme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <style>
        body { background: radial-gradient(circle at top left, #fff1f2, #f8fafc); min-height: 100vh; }
        .glass-card { background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(12px); border: 2px solid rgba(225, 29, 72, 0.15); border-radius: 2.5rem; }
        .red-gradient { background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%); }
        @media print { .no-print { display: none !important; } }
    </style>
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
</head>
<body class="flex">
    <?php include '../includes/sidebar.php'; ?>
    <main class="flex-1 p-10 ml-0 md:ml-64">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-end mb-10 no-print">
                <div class="flex items-start gap-5">
                    <div class="p-4 bg-white rounded-3xl shadow-md border-2 border-slate-100"><img src="../assets/logo.png" class="h-10 w-auto"></div>
                    <div><h1 class="text-3xl font-black text-slate-900 uppercase italic">Hoja de <span class="text-brand-600">Vida</span></h1><p class="text-xs font-bold text-slate-500 uppercase">ID: <?= $activo['r_qr'] ?></p></div>
                </div>
                <div class="flex gap-3">
                    <a href="editar.php?id=<?= $activo['r_id'] ?>" class="px-6 py-3 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase italic">EDITAR</a>
                    <button onclick="window.print()" class="px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl font-black text-[10px] uppercase">IMPRIMIR</button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-8 space-y-8">
                    <div class="glass-card p-10 shadow-2xl relative overflow-hidden">
                        <span class="px-4 py-1.5 bg-brand-50 text-brand-600 rounded-full text-[9px] font-black uppercase italic mb-6 inline-block">Hardware verificado</span>
                        <h2 class="text-5xl font-black text-slate-900 uppercase italic mb-10"><?= $activo['r_tipo'] ?> <span class="text-brand-600"><?= $activo['r_marca'] ?></span></h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-10 relative z-10">
                            <div><p class="text-[9px] font-black text-slate-400 uppercase">Serial</p><p class="font-bold font-mono uppercase"><?= $activo['r_serial'] ?></p></div>
                            <div><p class="text-[9px] font-black text-slate-400 uppercase">Referencia</p><p class="font-bold uppercase italic"><?= $activo['r_referencia'] ?: 'N/A' ?></p></div>
                            <div><p class="text-[9px] font-black text-slate-400 uppercase">Hostname</p><p class="font-bold text-brand-600 italic"><?= $activo['r_hostname'] ?: 'S/N' ?></p></div>
                            
                            <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin']): ?>
                            <div>
                                <p class="text-[9px] font-black text-brand-600 uppercase flex items-center gap-1"><i class="fas fa-lock"></i> Contraseña</p>
                                <div class="flex items-center gap-2">
                                    <input type="password" id="p_field" readonly value="<?= $activo['r_pass_activo'] ?>" class="bg-transparent border-none p-0 font-bold font-mono outline-none w-24">
                                    <button onclick="toggleP()" class="text-slate-400 hover:text-brand-600 no-print"><i id="p_icon" class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        function toggleP() {
            const f = document.getElementById('p_field');
            const i = document.getElementById('p_icon');
            f.type = f.type === "password" ? "text" : "password";
            i.classList.toggle('fa-eye'); i.classList.toggle('fa-eye-slash');
        }
    </script>
    <script src="../assets/js/sidebar_logic.js"></script>
    <script src="../assets/js/dark_mode.js"></script>
</body>
</html>