<?php
session_start();
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
    <title>Dashboard | SIH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="antialiased">

<div class="flex">
    <main class="flex-1 p-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-extrabold text-slate-900">Tablero de Control</h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-slate-600">Hola, <?php echo $_SESSION['username']; ?></span>
                    <a href="../controllers/logout.php" class="text-rose-600 hover:text-rose-800 font-bold text-xs uppercase tracking-widest">Cerrar Sesión</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-rose-600 text-white p-6 rounded-2xl shadow-lg shadow-rose-200 relative overflow-hidden group cursor-pointer transition-transform hover:scale-105" onclick="window.location.href='novedades.php'">
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
                    <i class="fas fa-cubes absolute bottom-4 right-4 text-slate-100 text-6xl"></i>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Operativos</p>
                    <h2 class="text-4xl font-black text-emerald-600 mt-2" id="totalBuenos">...</h2>
                    <i class="fas fa-check-circle absolute bottom-4 right-4 text-emerald-50 text-6xl"></i>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Requieren Atención</p>
                    <h2 class="text-4xl font-black text-amber-500 mt-2" id="totalProblemas">...</h2>
                    <i class="fas fa-exclamation-triangle absolute bottom-4 right-4 text-amber-50 text-6xl"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Estado de la Flota</h3>
                    <div class="h-64"><canvas id="chartEstado"></canvas></div>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Top 5 Marcas</h3>
                    <div class="h-64"><canvas id="chartMarca"></canvas></div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Apuntamos al nuevo controlador PHP
    fetch('../controllers/get_stats.php')
        .then(res => res.json())
        .then(data => {
            document.getElementById('totalActivos').textContent = data.total;
            document.getElementById('totalPendientes').textContent = data.pendientes;
            if (data.pendientes > 0) document.getElementById('pingAnim').classList.remove('hidden');

            document.getElementById('totalBuenos').textContent = data.operativos;
            document.getElementById('totalProblemas').textContent = data.atencion;

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

            new Chart(document.getElementById('chartMarca'), {
                type: 'bar',
                data: {
                    labels: data.marcas.map(m => m.label),
                    datasets: [{ 
                        label: 'Cantidad', 
                        data: data.marcas.map(m => m.count), 
                        backgroundColor: '#e11d48', 
                        borderRadius: 4 
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