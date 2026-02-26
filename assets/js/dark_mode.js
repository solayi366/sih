/**
 * SIH — Dark Mode Controller
 * Estrategia : class="dark" en <html>
 * Persistencia: localStorage key "sihTheme"
 * Botones     : #darkToggleBtn (móvil, en header) + #darkToggleBtnDesktop (sidebar desktop)
 */
(function () {
    'use strict';

    const STORAGE_KEY = 'sihTheme';
    const html = document.documentElement;

    function applyTheme(theme) {
        if (theme === 'dark') html.classList.add('dark');
        else html.classList.remove('dark');
    }

    function getStoredTheme()  { return localStorage.getItem(STORAGE_KEY); }
    function getSystemTheme()  { return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
    function getActiveTheme()  { return getStoredTheme() || getSystemTheme(); }

    // Aplicar inmediatamente (anti-flash)
    applyTheme(getActiveTheme());

    function updateIcons(theme) {
        // Actualiza todos los botones toggle que existan en la página
        document.querySelectorAll('#darkToggleBtn, #darkToggleBtnDesktop').forEach(btn => {
            const icon = btn.querySelector('i');
            if (!icon) return;
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
                btn.title = 'Cambiar a modo claro';
            } else {
                icon.className = 'fas fa-moon';
                btn.title = 'Cambiar a modo oscuro';
            }
        });
    }

    function toggleTheme() {
        const next = html.classList.contains('dark') ? 'light' : 'dark';
        localStorage.setItem(STORAGE_KEY, next);
        applyTheme(next);
        updateIcons(next);
    }

    // Respetar cambios del sistema si el usuario no eligió manualmente
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (!getStoredTheme()) {
            const theme = e.matches ? 'dark' : 'light';
            applyTheme(theme);
            updateIcons(theme);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        updateIcons(getActiveTheme());
        // Vincular click a ambos botones
        document.querySelectorAll('#darkToggleBtn, #darkToggleBtnDesktop').forEach(btn => {
            btn.addEventListener('click', toggleTheme);
        });
    });

    window.sihDarkMode = { toggle: toggleTheme, getTheme: getActiveTheme };
})();
