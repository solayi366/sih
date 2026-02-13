const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('mobile-overlay');
const icon = document.getElementById('toggleIcon');
const STORAGE_KEY = 'sidebarState';

// Lógica PC (Minimizar/Maximizar)
function toggleSidebarPC() {
    if (window.innerWidth >= 768) {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        localStorage.setItem(STORAGE_KEY, isCollapsed ? 'collapsed' : 'expanded');
        if (icon) {
            icon.className = isCollapsed ? 'fas fa-chevron-right text-xs' : 'fas fa-chevron-left text-xs';
        }
    }
}

// Lógica Móvil (Abrir/Cerrar Menu)
function toggleMobileMenu() {
    const isClosed = sidebar.classList.contains('-translate-x-full');
    if (isClosed) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    }
}

// Restaurar estado al cargar
document.addEventListener('DOMContentLoaded', () => {
    if (window.innerWidth >= 768) {
        const state = localStorage.getItem(STORAGE_KEY);
        if (state === 'collapsed') {
            sidebar.classList.add('collapsed');
            if (icon) icon.className = 'fas fa-chevron-right text-xs';
        }
    }
});