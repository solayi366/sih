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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
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
    <link rel="stylesheet" href="../assets/css/dark_mode.css">
</head>

<body class="text-slate-800 antialiased font-sans h-screen flex overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col pt-14 md:pt-0 h-full overflow-hidden relative bg-slate-50/50 w-full">

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
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Elementos Tecnológicos</p>
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

                <!-- ── GRÁFICAS ACTIVOS TEC ─────────────────────────────────────────── -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Estado de los Elementos Tecnológicos</h3>
                        <div class="h-64">
                            <canvas id="chartEstado"></canvas>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Dispositivos por Tipo</h3>
                        <div class="h-64">
                            <canvas id="chartMarca"></canvas>
                        </div>
                    </div>
                </div>

                <!-- ── SECCIÓN CELULARES ────────────────────────────────────────────── -->
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-8 bg-brand-600 rounded-full"></div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Módulo de Celulares</h2>
                </div>

                <!-- KPI Cards Celulares -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8" id="cel-kpis">
                    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Celulares</p>
                        <h2 class="text-3xl font-black text-slate-900 mt-1" id="celTotal">...</h2>
                        <i class="fas fa-mobile-alt absolute bottom-3 right-4 text-slate-50 text-5xl"></i>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden cursor-pointer hover:scale-105 transition-transform"
                         onclick="irCelulares('ASIGNADO')">
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Asignados</p>
                        <h2 class="text-3xl font-black text-emerald-600 mt-1" id="celAsignados">...</h2>
                        <i class="fas fa-check-circle absolute bottom-3 right-4 text-emerald-50 text-5xl"></i>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden cursor-pointer hover:scale-105 transition-transform"
                         onclick="irCelulares('EN PROCESO DE REASIGNACION')">
                        <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">En Reasignación</p>
                        <h2 class="text-3xl font-black text-blue-600 mt-1" id="celReasignacion">...</h2>
                        <i class="fas fa-sync-alt absolute bottom-3 right-4 text-blue-50 text-5xl"></i>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden cursor-pointer hover:scale-105 transition-transform"
                         onclick="irCelulares('DE BAJA')">
                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider">De Baja</p>
                        <h2 class="text-3xl font-black text-rose-600 mt-1" id="celBaja">...</h2>
                        <i class="fas fa-times-circle absolute bottom-3 right-4 text-rose-50 text-5xl"></i>
                    </div>
                </div>

                <!-- Gráficas Celulares -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">

                    <!-- Gráfica de Estados (clickeable → filtra celulares) -->
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col">
                        <div class="flex items-start justify-between mb-1">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Estado Celulares</h3>
                            <span class="text-[10px] text-slate-400 font-semibold mt-0.5 flex items-center gap-1">
                                <i class="fas fa-hand-pointer"></i> Clic para filtrar
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mb-4">Haz clic en un segmento para ver el listado filtrado</p>
                        <div class="h-64 flex-1">
                            <canvas id="chartCelEstado"></canvas>
                        </div>
                    </div>

                    <!-- Gráfica por Área -->
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col lg:col-span-2">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Celulares por Área</h3>
                        <div class="h-64 flex-1">
                            <canvas id="chartCelArea"></canvas>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/sidebar_logic.js"></script>

    <script>
        // ── Helper: navegar a celulares con filtro de estado ───────────────────
        function irCelulares(estado) {
            window.location.href = 'celulares.php?estado=' + encodeURIComponent(estado);
        }

        // ── Colores por estado de celular ──────────────────────────────────────
        const CEL_ESTADO_COLORS = {
            'ASIGNADO':                   '#10b981',
            'EN REPOSICION':              '#f59e0b',
            'EN PROCESO DE REASIGNACION': '#3b82f6',
            'DE BAJA':                    '#ef4444',
        };
        function colorPorEstado(label) {
            return CEL_ESTADO_COLORS[label] ?? '#94a3b8';
        }

        // ── Paleta de colores para áreas ───────────────────────────────────────
        const AREA_PALETTE = [
            '#e11d48','#f59e0b','#3b82f6','#10b981','#8b5cf6',
            '#06b6d4','#f97316','#84cc16','#ec4899','#64748b'
        ];

        const isDark        = () => document.documentElement.classList.contains('dark');
        const chartTextColor= () => isDark() ? '#94a3b8' : '#64748b';
        const chartGridColor= () => isDark() ? 'rgba(16,14,24,0.90)' : '#f1f5f9';

        // ── 1. Activos TEC ─────────────────────────────────────────────────────
        fetch('../controllers/get_stats.php')
            .then(r => r.json())
            .then(data => {
                if (data.error) return console.error(data.error);

                document.getElementById('totalActivos').textContent    = data.total;
                document.getElementById('totalPendientes').textContent = data.pendientes;
                document.getElementById('totalBuenos').textContent     = data.operativos;
                document.getElementById('totalProblemas').textContent  = data.atencion;

                if (data.pendientes > 0) {
                    document.getElementById('pingAnim').classList.remove('hidden');
                }

                new Chart(document.getElementById('chartEstado'), {
                    type: 'doughnut',
                    data: {
                        labels:   data.estados.map(e => e.label),
                        datasets: [{ data: data.estados.map(e => e.count), backgroundColor: ['#10b981','#ef4444','#f59e0b','#64748b'], borderWidth: 0 }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right', labels: { color: chartTextColor(), font: { family: 'Plus Jakarta Sans', weight: '700' } } } }
                    }
                });

                new Chart(document.getElementById('chartMarca'), {
                    type: 'bar',
                    data: {
                        labels:   data.tipos.map(t => t.label),
                        datasets: [{ label: 'Cantidad', data: data.tipos.map(t => t.count), backgroundColor: '#e11d48', borderRadius: 8 }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { color: chartTextColor() }, grid: { color: chartGridColor() } },
                            x: { ticks: { color: chartTextColor() }, grid: { color: 'transparent' } }
                        }
                    }
                });
            });

        // ── 2. Celulares ───────────────────────────────────────────────────────
        fetch('../controllers/get_stats_celulares.php')
            .then(r => r.json())
            .then(data => {
                if (data.error) return console.error('Celulares stats:', data.error);

                // KPI Cards
                document.getElementById('celTotal').textContent       = data.total;
                document.getElementById('celAsignados').textContent   = data.asignados;
                document.getElementById('celReasignacion').textContent = data.en_reasignacion;
                document.getElementById('celBaja').textContent        = data.de_baja;

                // ── Gráfica de Estados — doughnut CLICKEABLE ───────────────────
                const estadosLabels = data.estados.map(e => {
                    // Etiquetas más cortas para la leyenda
                    const map = {
                        'ASIGNADO':                   'Asignado',
                        'EN REPOSICION':              'En Reposición',
                        'EN PROCESO DE REASIGNACION': 'Reasignación',
                        'DE BAJA':                    'De Baja',
                    };
                    return map[e.label] ?? e.label;
                });
                const estadosRaw   = data.estados.map(e => e.label); // valores originales para filtrar

                const chartCelEstado = new Chart(document.getElementById('chartCelEstado'), {
                    type: 'doughnut',
                    data: {
                        labels:   estadosLabels,
                        datasets: [{
                            data:            data.estados.map(e => e.count),
                            backgroundColor: data.estados.map(e => colorPorEstado(e.label)),
                            borderWidth:     3,
                            borderColor:     isDark() ? 'rgba(16,14,24,0.90)' : '#fff',
                            hoverOffset:     8,
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        onClick: function(evt, elements) {
                            if (elements.length > 0) {
                                const idx    = elements[0].index;
                                const estado = estadosRaw[idx];
                                irCelulares(estado);
                            }
                        },
                        onHover: function(evt, elements) {
                            evt.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color:     chartTextColor(),
                                    font:      { family: 'Plus Jakarta Sans', weight: '700', size: 11 },
                                    boxWidth:  12,
                                    padding:   12,
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ` ${ctx.parsed} celulares — clic para filtrar`
                                }
                            }
                        }
                    }
                });

                // ── Gráfica por Área — barras horizontales ─────────────────────
                if (data.areas && data.areas.length > 0) {
                    new Chart(document.getElementById('chartCelArea'), {
                        type: 'bar',
                        data: {
                            labels:   data.areas.map(a => a.label),
                            datasets: [{
                                label:           'Celulares',
                                data:            data.areas.map(a => a.count),
                                backgroundColor: data.areas.map((_, i) => AREA_PALETTE[i % AREA_PALETTE.length]),
                                borderRadius:    6,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: { color: chartTextColor(), precision: 0 },
                                    grid:  { color: chartGridColor() }
                                },
                                y: {
                                    ticks: { color: chartTextColor(), font: { family: 'Plus Jakarta Sans', weight: '600', size: 11 } },
                                    grid:  { color: 'transparent' }
                                }
                            }
                        }
                    });
                } else {
                    document.getElementById('chartCelArea').closest('.bg-white').innerHTML +=
                        '<p class="text-slate-400 text-sm text-center mt-8">Sin datos de área disponibles</p>';
                }
            });
    </script>
    <script src="../assets/js/dark_mode.js"></script>
</body>
</html>