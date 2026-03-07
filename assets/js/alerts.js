/**
 * SIH_QR - Motor de Alertas Universal (Gama Alta)
 * Todos los diálogos y notificaciones del sistema pasan por este objeto.
 */
const Alerts = {

    // ── Estilo base compartido para todos los diálogos modales ──────────────
    _base: {
        background: '#ffffff',
        showCancelButton: true,
        reverseButtons: true,
        customClass: {
            popup:         'rounded-[2.5rem] shadow-2xl',
            title:         'text-slate-900 font-black tracking-tight',
            htmlContainer: 'text-slate-500 font-semibold',
            confirmButton: 'rounded-xl px-8 py-3 font-black uppercase text-xs tracking-widest',
            cancelButton:  'rounded-xl px-8 py-3 font-black uppercase text-xs tracking-widest'
        }
    },

    // ── Toast (notificación esquina superior derecha) ────────────────────────
    toast: Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#0f172a',
        color: '#ffffff',
        customClass: {
            popup: 'rounded-2xl shadow-2xl border-l-4',
            timerProgressBar: 'bg-rose-600'
        }
    }),

    // ── Éxito modal ──────────────────────────────────────────────────────────
    success(titulo, texto) {
        return Swal.fire({
            ...this._base,
            icon: 'success',
            title: titulo,
            text: texto || undefined,
            showCancelButton: false,
            timer: texto ? undefined : 1800,
            showConfirmButton: !!texto,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Aceptar'
        });
    },

    // ── Error ────────────────────────────────────────────────────────────────
    error(titulo, texto) {
        return Swal.fire({
            ...this._base,
            icon: 'error',
            title: titulo,
            text: texto || undefined,
            showCancelButton: false,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Entendido'
        });
    },

    // ── Error con HTML ───────────────────────────────────────────────────────
    errorHtml(titulo, html) {
        return Swal.fire({
            ...this._base,
            icon: 'error',
            title: titulo,
            html: html || undefined,
            showCancelButton: false,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Entendido'
        });
    },

    // ── Advertencia ──────────────────────────────────────────────────────────
    warning(titulo, texto) {
        return Swal.fire({
            ...this._base,
            icon: 'warning',
            title: titulo,
            text: texto || undefined,
            showCancelButton: false,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Entendido'
        });
    },

    // ── Advertencia con HTML ─────────────────────────────────────────────────
    warningHtml(titulo, html) {
        return Swal.fire({
            ...this._base,
            icon: 'warning',
            title: titulo,
            html: html || undefined,
            showCancelButton: false,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Entendido'
        });
    },

    // ── Info ─────────────────────────────────────────────────────────────────
    info(titulo, texto) {
        return Swal.fire({
            ...this._base,
            icon: 'info',
            title: titulo,
            text: texto || undefined,
            showCancelButton: false,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Entendido'
        });
    },

    // ── Loading ──────────────────────────────────────────────────────────────
    loading(titulo) {
        Swal.fire({
            ...this._base,
            title: titulo || 'Procesando...',
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    },

    // ── Cerrar ───────────────────────────────────────────────────────────────
    close() {
        Swal.close();
    },

    // ── Toast de éxito ───────────────────────────────────────────────────────
    toastSuccess(titulo) {
        this.toast.fire({
            icon: 'success',
            title: titulo,
            customClass: { popup: 'rounded-2xl shadow-2xl border-l-4 border-emerald-500' }
        });
    },

    // ── Toast de error ───────────────────────────────────────────────────────
    toastError(titulo) {
        this.toast.fire({
            icon: 'error',
            title: titulo,
            customClass: { popup: 'rounded-2xl shadow-2xl border-l-4 border-rose-600' }
        });
    },

    // ── Confirmación de eliminación (redirige a URL) ─────────────────────────
    confirmDelete(url, texto) {
        return Swal.fire({
            ...this._base,
            title: '¿Estás seguro?',
            text: texto || 'Esta acción desactivará el registro permanentemente.',
            icon: 'warning',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = url;
        });
    },

    // ── Confirmación genérica → devuelve Promise<boolean> ───────────────────
    confirm(titulo, texto, confirmText, icon) {
        return Swal.fire({
            ...this._base,
            title: titulo,
            text: texto || undefined,
            icon: icon || 'question',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: confirmText || 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then(r => r.isConfirmed);
    },

    // ── Confirmación con HTML → devuelve Promise<boolean> ───────────────────
    confirmHtml(titulo, html, confirmText, confirmColor, icon) {
        return Swal.fire({
            ...this._base,
            title: titulo,
            html: html || undefined,
            icon: icon || 'question',
            confirmButtonColor: confirmColor || '#10b981',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: confirmText || 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then(r => r.isConfirmed);
    },

    /**
     * Lee ?msg=...&tipo=success|error de la URL y muestra el toast.
     */
    checkURL() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('msg')) {
            const msg  = decodeURIComponent(params.get('msg'));
            const tipo = params.get('tipo') === 'success' ? 'success' : 'error';
            this.toast.fire({
                icon: tipo,
                title: msg,
                customClass: {
                    popup: 'rounded-2xl shadow-2xl border-l-4 ' + (tipo === 'success' ? 'border-emerald-500' : 'border-rose-600')
                }
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => Alerts.checkURL());
