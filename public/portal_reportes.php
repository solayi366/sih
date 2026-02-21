<?php
/**
 * portal_reportes.php — Portal público para reportar fallas tecnológicas.
 * No requiere login. Accesible desde QR o enlace directo.
 */
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Daño | Mesa de Ayuda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { 200:'#fecdd3', 600:'#e11d48', 900:'#881337' } } } }
        }
    </script>
    <style>
        .label-form { display:block; font-size:10px; font-weight:800; color:#94a3b8; text-transform:uppercase; margin-bottom:5px; }
        .input-form  { width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:12px; font-weight:bold; color:#334155; outline:none; transition:all .3s; background:#fff; }
        .input-form:focus { border-color:#e11d48; background:#fff1f2; }
        @keyframes slide-up { from { transform:translateY(30px); opacity:0; } to { transform:translateY(0); opacity:1; } }
        .animate-slide-up { animation: slide-up .3s ease forwards; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen font-sans" style="font-family:'Plus Jakarta Sans',sans-serif">

    <header class="bg-brand-900 text-white py-5 px-4 shadow-lg sticky top-0 z-50">
        <div class="max-w-md mx-auto flex items-center gap-3">
            <div class="bg-white/10 p-2 rounded-lg"><i class="fas fa-headset text-2xl"></i></div>
            <div>
                <h1 class="text-lg font-black uppercase tracking-wider">Mesa de Ayuda</h1>
                <p class="text-[10px] text-brand-200">Reporte de Fallas Tecnológicas</p>
            </div>
        </div>
    </header>

    <main class="max-w-md mx-auto p-4 pb-20">

        <!-- PASO 1: Ingresar cédula/código -->
        <div id="step-1" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 mt-4">
            <p class="text-xs font-bold text-slate-400 uppercase text-center mb-1">¿Quién reporta?</p>
            <p class="text-center text-sm text-slate-500 mb-4">Ingresa tu código de nómina</p>
            <div class="flex gap-2">
                <input type="text" id="cedulaInput" placeholder="Ej: E0123..."
                    class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-center font-black text-lg outline-none focus:border-brand-600 transition-all"
                    onkeydown="if(event.key==='Enter') buscarActivos()">
                <button onclick="buscarActivos()"
                    class="bg-brand-600 text-white px-5 rounded-xl shadow-lg hover:bg-brand-900 transition-all flex-shrink-0">
                    <i id="iconBuscar" class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- PASO 2: Lista de activos -->
        <div id="step-2" class="hidden mt-6 space-y-4 animate-slide-up">
            <div class="flex items-center justify-between px-1">
                <h3 class="font-bold text-slate-700">Hola, <span id="nombreEmpleado" class="text-brand-600">...</span></h3>
                <span class="text-xs text-slate-400">Selecciona el equipo dañado:</span>
            </div>
            <div id="listaActivosContainer" class="space-y-3"></div>

            <!-- Sin activos -->
            <div id="sinActivos" class="hidden bg-amber-50 border border-amber-200 rounded-2xl p-5 text-center">
                <i class="fas fa-box-open text-amber-400 text-2xl mb-2"></i>
                <p class="text-sm font-bold text-amber-700">No tienes equipos asignados</p>
                <p class="text-xs text-amber-500 mt-1">Contacta al área de TI directamente.</p>
            </div>
        </div>

    </main>

    <!-- MODAL REPORTE -->
    <div id="modalReporte" class="fixed inset-0 bg-black/80 z-[60] hidden flex items-end sm:items-center justify-center backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl p-6 max-h-[90vh] overflow-y-auto relative animate-slide-up">

            <button onclick="cerrarModal()" class="absolute top-4 right-4 text-slate-300 hover:text-slate-600 bg-slate-100 rounded-full w-8 h-8 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>

            <h2 class="text-xl font-black text-slate-800 mb-1">Reportar Falla</h2>
            <p class="text-xs text-slate-500 mb-5 font-bold" id="labelActivoSeleccionado">...</p>

            <div class="space-y-5">
                <input type="hidden" id="formCedula">
                <input type="hidden" id="formIdActivo">

                <div>
                    <label class="label-form">Tipo de Daño</label>
                    <select id="tipoDano" class="input-form">
                        <option value="Hardware">Daño Físico (Golpe, Pantalla, Teclado)</option>
                        <option value="Software">Lentitud / Virus / Programas</option>
                        <option value="Red">Internet / Wifi</option>
                        <option value="Periferico">Mouse / Cargador / Cables</option>
                    </select>
                </div>

                <div>
                    <label class="label-form">Descripción Detallada</label>
                    <textarea id="descripcion" rows="4" class="input-form resize-none"
                        placeholder="Explique qué sucede con el equipo..."></textarea>
                </div>

                <div>
                    <label class="label-form">Evidencia (Opcional)</label>
                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 text-slate-400 cursor-pointer hover:border-brand-600 hover:text-brand-600 transition-all">
                        <i class="fas fa-camera text-xl mb-1"></i>
                        <span class="text-[10px] font-bold">Tomar o subir foto</span>
                        <input type="file" id="inputFoto" accept="image/*" capture="environment" class="hidden" onchange="procesarImagen(this)">
                    </label>
                    <p id="fotoStatus" class="text-xs text-center font-bold mt-2 text-slate-400"></p>
                </div>

                <button type="button" onclick="enviarReporte()"
                    class="w-full py-4 bg-brand-600 text-white rounded-xl font-black uppercase tracking-widest shadow-xl shadow-brand-600/30 hover:bg-brand-900 transition-all">
                    ENVIAR REPORTE
                </button>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '<?= rtrim(APP_URL, '/') ?>/controllers';
        let imagenComprimida = null;

        // ── Buscar empleado y sus activos ─────────────────────────────────────
        async function buscarActivos() {
            const cedula = document.getElementById('cedulaInput').value.trim();
            if (cedula.length < 2) return Swal.fire('Atención', 'Ingresa un código de nómina válido', 'warning');

            const icon = document.getElementById('iconBuscar');
            icon.className = 'fas fa-spinner fa-spin';

            try {
                const res  = await fetch(`${API_BASE}/api_activos.php?cedula=${encodeURIComponent(cedula)}`);
                const data = await res.json();

                if (!data.encontrado) {
                    Swal.fire('No encontrado', data.mensaje, 'info');
                    document.getElementById('step-2').classList.add('hidden');
                } else {
                    document.getElementById('nombreEmpleado').innerText = data.empleado.split(' ')[0];
                    document.getElementById('formCedula').value = cedula;

                    const container = document.getElementById('listaActivosContainer');
                    const sinActivos = document.getElementById('sinActivos');
                    container.innerHTML = '';

                    if (data.activos.length === 0) {
                        sinActivos.classList.remove('hidden');
                    } else {
                        sinActivos.classList.add('hidden');
                        data.activos.forEach(act => {
                            container.innerHTML += `
                            <div onclick="abrirReporte('${act.id}', '${act.tipo} ${act.marca}')"
                                 class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4 cursor-pointer hover:border-brand-600 active:scale-95 transition-all">
                                <div class="bg-slate-100 p-2 rounded-xl flex-shrink-0">
                                    <img src="${act.foto_qr}" class="w-12 h-12 opacity-80" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2248%22><rect width=%2248%22 height=%2248%22 fill=%22%23f1f5f9%22/></svg>'">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-black text-slate-800 text-sm">${act.tipo}</h4>
                                    <p class="text-xs text-slate-500 font-bold">${act.marca} ${act.modelo}</p>
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">S/N: ${act.serial}</p>
                                </div>
                                <i class="fas fa-chevron-right text-slate-300 flex-shrink-0"></i>
                            </div>`;
                        });
                    }

                    document.getElementById('step-2').classList.remove('hidden');
                }
            } catch (e) {
                console.error(e);
                Swal.fire('Error', 'No se pudo conectar al servidor', 'error');
            } finally {
                icon.className = 'fas fa-search';
            }
        }

        // ── Modal ─────────────────────────────────────────────────────────────
        function abrirReporte(id, nombre) {
            document.getElementById('formIdActivo').value = id;
            document.getElementById('labelActivoSeleccionado').innerText = nombre;
            document.getElementById('modalReporte').classList.remove('hidden');
            document.getElementById('modalReporte').classList.add('flex');
        }

        function cerrarModal() {
            document.getElementById('modalReporte').classList.add('hidden');
            document.getElementById('modalReporte').classList.remove('flex');
        }

        // ── Comprimir imagen ──────────────────────────────────────────────────
        async function procesarImagen(input) {
            const file = input.files[0];
            if (!file) return;
            document.getElementById('fotoStatus').innerText = '⏳ Procesando imagen...';
            try {
                imagenComprimida = await imageCompression(file, { maxSizeMB: 0.5, maxWidthOrHeight: 1280, useWebWorker: true });
                document.getElementById('fotoStatus').innerText = '✅ Lista (' + (imagenComprimida.size/1024/1024).toFixed(2) + ' MB)';
            } catch (e) {
                document.getElementById('fotoStatus').innerText = '❌ Error al procesar';
            }
        }

        // ── Enviar reporte ────────────────────────────────────────────────────
        async function enviarReporte() {
            const descripcion = document.getElementById('descripcion').value.trim();
            if (!descripcion) return Swal.fire('Atención', 'Describe el problema antes de enviar', 'warning');

            const fd = new FormData();
            fd.append('cedula',      document.getElementById('formCedula').value);
            fd.append('id_activo',   document.getElementById('formIdActivo').value);
            fd.append('tipo_dano',   document.getElementById('tipoDano').value);
            fd.append('descripcion', descripcion);
            if (imagenComprimida) fd.append('foto', imagenComprimida, imagenComprimida.name);

            Swal.fire({ title: 'Enviando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const res  = await fetch(`${API_BASE}/crear_ticket.php`, { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    Swal.close();
                    window.location.href = `portal_exito.php?ticket=${data.id_ticket}`;
                } else {
                    Swal.fire('Error', data.msg || 'No se pudo crear el ticket', 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Fallo de conexión con el servidor', 'error');
            }
        }
    </script>
</body>
</html>
