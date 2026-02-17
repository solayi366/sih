<div class="toast-container position-fixed top-0 end-0 p-4" style="z-index: 9999;">
    <div id="rubyToast" class="toast hide ruby-toast border-0 shadow-2xl" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex p-4">
            <div id="toastIconContainer" class="toast-icon mr-4">
                <i id="toastIcon"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1" id="toastTitle">Sistema</p>
                <p class="toast-body p-0 font-bold text-sm text-white" id="toastMsg"></p>
            </div>
            <button type="button" class="ml-4 text-white/30 hover:text-white transition-colors" data-bs-dismiss="toast">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('msg')) {
        const msg = params.get('msg');
        const tipo = params.get('tipo'); // 'success' o 'danger'
        
        const toastEl = document.getElementById('rubyToast');
        const iconContainer = document.getElementById('toastIconContainer');
        const icon = document.getElementById('toastIcon');
        
        // Configuración según tipo
        if (tipo === 'success') {
            toastEl.classList.add('success');
            iconContainer.className = 'toast-icon mr-4 toast-success-icon';
            icon.className = 'fas fa-check-circle';
            document.getElementById('toastTitle').innerText = 'Operación Exitosa';
        } else {
            toastEl.classList.add('danger');
            iconContainer.className = 'toast-icon mr-4 toast-danger-icon';
            icon.className = 'fas fa-exclamation-triangle';
            document.getElementById('toastTitle').innerText = 'Atención';
        }

        document.getElementById('toastMsg').innerText = msg;
        
        const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
        bsToast.show();
        
        // Limpiar URL sin recargar
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>