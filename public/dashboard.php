<?php
session_start();
// Control de acceso: Si no hay sesión, al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SIH_QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        body { background-color: #f8fafc; }
        #sidebar { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-text { transition: opacity 0.2s; white-space: nowrap; }
        
        /* Lógica de colapso exacta de tu base.html */
        @media (min-width: 768px) {
            #sidebar.collapsed { width: 5rem; }
            #sidebar.collapsed .sidebar-text,
            #sidebar.collapsed .logo-text,
            #sidebar.collapsed .section-title,
            #sidebar.collapsed .user-info { display: none; opacity: 0; }
            #sidebar.collapsed .nav-icon { margin-right: 0; font-size: 1.25rem; }
        }
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body class="text-slate-800 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50 w-full">

        <header class="md:hidden h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 z-30 shrink-0 sticky top-0">
            <button onclick="toggleMobileMenu()" class="text-slate-500 hover:text-brand-600 p-2">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <span class="font-black text-lg uppercase text-slate-900">SIH<span class="text-brand-600">QR</span></span>
            <div class="w-8"></div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth w-full">
            
            <div class="max-w-6xl mx-auto">
                <h1 class="text-3xl font-extrabold text-slate-900 mb-8 tracking-tight">Tablero de Control</h1>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    
                    <div class="bg-brand-600 text-white p-6 rounded-2xl shadow-lg shadow-rose-200 relative overflow-hidden group cursor-pointer transition-transform hover:scale-105" onclick="window.location.href='novedades.php'">
                        <p class="text-xs font-bold text-rose-100 uppercase tracking-wider">Mesa de Ayuda</p>
                        <div class="flex items-end gap-2">
                            <h2 class="text-4xl font-black mt-2" id="totalPendientes">0</h2>
                            <span class="text-sm font-bold mb-1 opacity-80">Pendientes</span>
                        </div>
                        <i class="fas fa-bell absolute bottom-4 right-4 text-rose-500 text-6xl -z-0 group-hover:scale-110 transition-transform"></i>
                        <div id="pingAnim" class="hidden absolute top-4 right-4 w-3 h-3 bg-white rounded-full animate-ping"></div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Activos</p>
                        <h2 class="text-4xl font-black text-slate-900 mt-2" id="totalActivos">...</h2>
                        <i class="fas fa-cubes absolute bottom-4 right-4 text-slate-50 text-6xl -z-0"></i>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Operativos</p>
                        <h2 class="text-4xl font-black text-emerald-600 mt-2" id="totalBuenos">...</h2>
                        <i class="fas fa-check-circle absolute bottom-4 right-4 text-emerald-50 text-6xl -z-0"></i>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Requieren Atención</p>
                        <h2 class="text-4xl font-black text-amber-500 mt-2" id="totalProblemas">...</h2>
                        <i class="fas fa-exclamation-triangle absolute bottom-4 right-4 text-amber-50 text-6xl -z-0"></i>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Estado de la Flota</h3>
                        <div class="h-64">
                            <canvas id="chartEstado"></canvas>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Top 5 Marcas</h3>
                        <div class="h-64">
                            <canvas id="chartMarca"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/sidebar_logic.js"></script>

    <script>
        fetch('../controllers/get_stats.php')
            .then(res => res.json())
            .then(data => {
                if(data.error) return console.error(data.error);

                document.getElementById('totalActivos').textContent = data.total;
                document.getElementById('totalPendientes').textContent = data.pendientes;
                
                if (data.pendientes > 0) {
                    document.getElementById('pingAnim').classList.remove('hidden');
                }

                document.getElementById('totalBuenos').textContent = data.operativos;
                document.getElementById('totalProblemas').textContent = data.atencion;

                // Gráfica de Rosca (Estados)
                new Chart(document.getElementById('chartEstado'), {
                    type: 'doughnut',
                    data: {
                        labels: data.estados.map(e => e.label),
                        datasets: [{ 
                            data: data.estados.map(e => e.count), 
                            backgroundColor: ['#10b981', '#ef4444', '#f59e0b', '#64748b'], 
                            borderWidth: 0 
                        }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                });

                // Gráfica de Barras (Marcas)
                new Chart(document.getElementById('chartMarca'), {
                    type: 'bar',
                    data: {
                        labels: data.marcas.map(m => m.label),
                        datasets: [{ 
                            label: 'Cantidad', 
                            data: data.marcas.map(m => m.count), 
                            backgroundColor: '#e11d48', 
                            borderRadius: 8 
                        }]
                    },
                    options: { 
                        maintainAspectRatio: false, 
                        plugins: { legend: { display: false } }, 
                        scales: { y: { beginAtZero: true } } 
                    }
                });
            });
    </script>
</body>
</html>