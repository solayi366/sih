/**
 * SIH_QR - Motor de Alertas Universal (Gama Alta)
 */
const Alerts = {
    // Configuración para Toasts (Notificaciones rápidas)
    toast: Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#0f172a', // Slate 900
        color: '#ffffff',
        customClass: {
            popup: 'rounded-2xl shadow-2xl border-l-4',
            timerProgressBar: 'bg-brand-600'
        }
    }),

    /**
     * Muestra notificación de éxito o error automáticamente al cargar la página
     */
    checkURL() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('msg')) {
            const msg = decodeURIComponent(params.get('msg'));
            const tipo = params.get('tipo') === 'success' ? 'success' : 'error';
            
            this.toast.fire({
                icon: tipo,
                title: msg,
                customClass: {
                    popup: `rounded-2xl shadow-2xl border-l-4 ${tipo === 'success' ? 'border-emerald-500' : 'border-rose-600'}`
                }
            });
            // Limpia la URL para que al refrescar no salga la alerta de nuevo
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    },

    /**
     * Diálogo de confirmación universal para eliminaciones
     * @param {string} url - Ruta del controlador que ejecuta el delete
     * @param {string} texto - Mensaje personalizado (opcional)
     */
    confirmDelete(url, texto = "Esta acción desactivará el registro permanentemente.") {
        Swal.fire({
            title: '¿Estás seguro?',
            text: texto,
            icon: 'warning',
            showCancelButton: true,
            background: '#ffffff',
            confirmButtonColor: '#e11d48', // Rojo Ruby
            cancelButtonColor: '#94a3b8',  // Slate 400
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-[2.5rem] p-10 shadow-2xl',
                title: 'text-slate-900 font-black uppercase tracking-tight',
                htmlContainer: 'text-slate-500 font-medium',
                confirmButton: 'rounded-xl px-8 py-3 font-bold uppercase text-xs tracking-widest',
                cancelButton: 'rounded-xl px-8 py-3 font-bold uppercase text-xs tracking-widest'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
};

// Se ejecuta automáticamente al cargar cualquier página que incluya este JS
document.addEventListener('DOMContentLoaded', () => Alerts.checkURL());