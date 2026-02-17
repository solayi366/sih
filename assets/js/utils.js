        // Funciones para controlar modales
        function abrirModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        // Cerrar modal al hacer clic en el fondo
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                cerrarModal(e.target.id);
            }
        });
        
        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay:not(.hidden)').forEach(modal => {
                    cerrarModal(modal.id);
                });
            }
        });
        
        function abrirEdicion(id, nombre) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nombre').value = nombre;
            abrirModal('modalEdicion');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('msg')) {
                const toastEl = document.getElementById('liveToast');
                const bg = document.getElementById('toastBg');
                bg.className = 'd-flex ' + (urlParams.get('tipo') === 'success' ? 'bg-emerald-500' : 'bg-rose-600');
                document.getElementById('toastMessage').textContent = urlParams.get('msg');
                new bootstrap.Toast(toastEl).show();
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });


        function addAccessory() {
            const select = document.getElementById('bulkSelect');
            const id = select.value;
            const nombre = select.options[select.selectedIndex].getAttribute('data-nombre');
            if(!id) return;

            const tbody = document.querySelector('#tablaAccesorios tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="py-4 px-2">
                    <input type="hidden" class="acc-id" value="${id}">
                    <span class="text-xs font-black text-brand-500 uppercase">${nombre}</span>
                </td>
                <td class="py-4 px-2 space-y-2">
                    <input type="text" class="acc-serial block w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-[11px] text-white font-mono outline-none focus:border-brand-600" placeholder="Serial (S/N)">
                    <input type="text" class="acc-ref block w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-[11px] text-slate-400 outline-none focus:border-brand-600" placeholder="Referencia/Modelo">
                </td>
                <td class="py-4 text-right">
                    <button type="button" class="text-slate-500 hover:text-brand-600 p-2" onclick="this.closest('tr').remove()"><i class="fas fa-trash-alt text-xs"></i></button>
                </td>
            `;
            tbody.appendChild(row);
            select.value = "";
        }

        function prepararYEnviar() {
            const accesorios = [];
            document.querySelectorAll('#tablaAccesorios tbody tr').forEach(tr => {
                accesorios.push({
                    tipo_id: tr.querySelector('.acc-id').value,
                    tipo_nombre: tr.querySelector('span').innerText,
                    serial: tr.querySelector('.acc-serial').value,
                    referencia: tr.querySelector('.acc-ref').value
                });
            });
            document.getElementById('accesoriosJson').value = JSON.stringify(accesorios);
            document.getElementById('formCrear').submit();
        }