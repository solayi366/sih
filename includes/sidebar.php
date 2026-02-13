<?php
// Detectamos la página actual para las clases activas
$current_page = basename($_SERVER['PHP_SELF']);
$usuario_nombre = $_SESSION['username'] ?? 'Admin';
$inicial_usuario = strtoupper(substr($usuario_nombre, 0, 1));

// Función auxiliar para la clase activa
function isActive($page, $current_page) {
    return ($page == $current_page) ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-700';
}
?>

<div id="mobile-overlay" onclick="toggleMobileMenu()"
    class="fixed inset-0 bg-black/50 z-30 hidden md:hidden transition-opacity"></div>

<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col h-full shadow-xl transform -translate-x-full md:translate-x-0 md:static transition-transform duration-300">

    <button onclick="toggleSidebarPC()"
        class="hidden md:flex absolute -right-3 top-20 bg-white border border-slate-200 text-slate-400 hover:text-brand-600 rounded-full w-6 h-6 items-center justify-center shadow-sm z-50 hover:scale-110 transition-transform">
        <i id="toggleIcon" class="fas fa-chevron-left text-xs"></i>
    </button>

    <div class="h-16 md:h-20 flex items-center justify-center border-b border-slate-100 shrink-0">
        <a href="dashboard.php" class="flex items-center gap-3 px-4">
            <div class="p-1.5 bg-brand-50 rounded-lg shrink-0">
                <img src="../assets/logo.png" alt="Logo" class="h-8 w-8 object-contain">
            </div>
            <span class="logo-text font-black text-xl tracking-tighter uppercase text-slate-900">
                SIH<span class="text-brand-600">QR</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto no-scrollbar py-4 space-y-1 px-3">
        <p class="section-title px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 mt-2">
            Principal</p>

        <a href="activos.php" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all <?php echo isActive('activos.php', $current_page); ?>">
            <i class="fas fa-layer-group nav-icon w-6 text-center"></i>
            <span class="sidebar-text ml-3">Inventario</span>
        </a>

        <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all <?php echo isActive('dashboard.php', $current_page); ?>">
            <i class="fas fa-chart-pie nav-icon w-6 text-center"></i>
            <span class="sidebar-text ml-3">Métricas</span>
        </a>

        <p class="section-title px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 mt-6">
            Gestión</p>

        <a href="crear_activo.php" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all <?php echo isActive('crear_activo.php', $current_page); ?>">
            <i class="fas fa-plus-circle nav-icon w-6 text-center"></i>
            <span class="sidebar-text ml-3">Nuevo Activo</span>
        </a>

        <a href="parametros_hardware.php" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all <?php echo isActive('parametros.php', $current_page); ?>">
            <i class="fas fa-sliders-h nav-icon w-6 text-center"></i>
            <span class="sidebar-text ml-3">Parámetros</span>
        </a>

        <a href="novedades.php" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all <?php echo isActive('novedades.php', $current_page); ?>">
            <i class="fas fa-headset nav-icon w-6 text-center"></i>
            <span class="sidebar-text ml-3">Mesa de Ayuda</span>
        </a>

        <p class="section-title px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 mt-6">
            Reportes</p>

        <a href="exportar.php" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-all">
            <i class="fas fa-file-excel nav-icon w-6 text-center text-emerald-500"></i>
            <span class="sidebar-text ml-3">Exportar Todo</span>
        </a>
    </nav>

    <div class="p-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-sm shadow-md shrink-0">
                <?php echo $inicial_usuario; ?>
            </div>
            <div class="sidebar-text user-info flex-1 min-w-0">
                <p class="text-xs font-bold text-slate-900 truncate"><?php echo $usuario_nombre; ?></p>
                <a href="../controllers/logout.php" class="text-[10px] text-red-500 hover:text-red-700 font-bold flex items-center gap-1 mt-0.5">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</aside>