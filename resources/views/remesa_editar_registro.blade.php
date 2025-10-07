@extends('layouts.app')

@section('title', 'Editar Registro')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
<style>
.card {
    transition: all 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
}
.form-control:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.25);
}
.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
}
.badge {
    font-weight: 500;
}
.card-header {
    border-bottom: none !important;
}
.text-muted.small {
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.fw-semibold {
    font-weight: 600;
}
.badge-tipo-remesa {
    font-size: 0.85rem;
    padding: 0.4rem 0.8rem;
    border-radius: 0.5rem;
}
.validation-message {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}
.modal-confirmacion .table th {
    background-color: #f8f9fa;
    font-size: 0.875rem;
    font-weight: 600;
}
.modal-confirmacion .table td {
    font-size: 0.875rem;
    vertical-align: middle;
}
.cambio-detectado {
    background-color: #d1ecf1;
    border-left: 4px solid #0ea5e9;
}
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-primary">
            <i class="bi bi-pencil-square me-2"></i>Editar Registro
        </h2>
        <a href="{{ route('remesa.gestionar.registros', $registro->nro_carga) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Gestión
        </a>
    </div>

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>¡Error!</strong> Se encontraron los siguientes problemas:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- CAMPOS EDITABLES - PRIORIDAD MÁXIMA -->
        <div class="col-lg-7">
            <div class="card shadow border-0 h-100">
                <div class="card-header" style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white; border: none;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-pencil-square fs-4 me-3"></i>
                        <div>
                            <h5 class="mb-1">Campos Editables</h5>
                            <small class="opacity-75">Solo puedes modificar las siguientes fechas y horas</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('remesa.actualizar.registro', $registro->id) }}" id="formActualizarRegistro">
                        @csrf
                        @method('PUT')
                        
                        <!-- Grupo de Retiro -->
                        <!-- Mensaje informativo sobre validaciones -->
                        <div class="validation-message">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle text-warning me-2 mt-1"></i>
                                <div>
                                    <strong>Reglas de validación:</strong>
                                    <ul class="mb-0 mt-1">
                                        <li>Los horarios deben estar entre las 8:00 AM y 6:00 PM</li>
                                        <li>La fecha programada no puede ser anterior a la fecha de retiro</li>
                                        <li>El tipo de remesa se determina automáticamente según el emisor</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted mb-3 pb-2 border-bottom">
                                <i class="bi bi-arrow-up-circle text-primary me-2"></i>Información de Retiro
                            </h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="retfech" class="form-label">
                                        <i class="bi bi-calendar-date text-primary me-1"></i>Fecha de Retiro
                                    </label>
                                    <input type="date" class="form-control" id="retfech" name="retfech" 
                                           value="{{ old('retfech', $registro->retfech ? \Carbon\Carbon::parse($registro->retfech)->format('Y-m-d') : '') }}">
                                    @error('retfech')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-sm-6">
                                    <label for="rethor" class="form-label">
                                        <i class="bi bi-clock text-primary me-1"></i>Hora de Retiro
                                    </label>
                                    <input type="time" class="form-control" id="rethor" name="rethor" 
                                           value="{{ old('rethor', $registro->hora_ret_formateada ?? '') }}"
                                           step="60">
                                    @error('rethor')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Grupo de Programación -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3 pb-2 border-bottom">
                                <i class="bi bi-calendar-check text-success me-2"></i>Información Programada
                            </h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="fechaprog" class="form-label">
                                        <i class="bi bi-calendar-check text-success me-1"></i>Fecha Programada
                                    </label>
                                    <input type="date" class="form-control" id="fechaprog" name="fechaprog" 
                                           value="{{ old('fechaprog', $registro->fechaprog ? \Carbon\Carbon::parse($registro->fechaprog)->format('Y-m-d') : '') }}">
                                    @error('fechaprog')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-sm-6">
                                    <label for="horaprog" class="form-label">
                                        <i class="bi bi-clock-history text-success me-1"></i>Hora Programada
                                    </label>
                                    <input type="time" class="form-control" id="horaprog" name="horaprog" 
                                           value="{{ old('horaprog', $registro->hora_prog_formateada ?? '') }}"
                                           step="60">
                                    @error('horaprog')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('remesa.gestionar.registros', $registro->nro_carga) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Volver
                            </a>
                            <button type="button" class="btn btn-warning px-4" id="btnGuardarCambios">
                                <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                            </button>
                            <button type="button" class="btn btn-info px-3 ms-2" id="btnTestUpdate">
                                <i class="bi bi-bug me-2"></i>Test Simple
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        
        <!-- SIDEBAR CON INFORMACIÓN CONTEXTUAL -->
        <div class="col-lg-5">
            <div class="row g-3">
                <!-- Información del Sistema -->
                <div class="col-12">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-info text-white border-0">
                            <h6 class="mb-0">
                                <i class="bi bi-database me-2"></i>Información del Sistema
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Nro. Carga</span>
                                    <span class="badge bg-primary fs-6">{{ $registro->nro_carga }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">OC</span>
                                    <span class="fw-semibold">{{ $registro->oc ?? 'No asignada' }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted small d-block">Centro de Servicio</span>
                                <span class="fw-semibold">{{ $registro->centro_servicio ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted small d-block">Tipo de Remesa</span>
                                <span class="badge badge-tipo-remesa {{ $registro->tipo_remesa == 'Reclamo' ? 'bg-warning' : 'bg-info' }} text-dark">
                                    <i class="bi bi-{{ $registro->tipo_remesa == 'Reclamo' ? 'exclamation-triangle' : 'briefcase' }} me-1"></i>
                                    {{ $registro->tipo_remesa }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted small d-block">Emisor</span>
                                <span class="fw-semibold">{{ $registro->emisor ?? 'No especificado' }}</span>
                            </div>
                            <div class="mb-0">
                                <span class="text-muted small d-block">Fecha de Carga</span>
                                <span class="fw-semibold">{{ \Carbon\Carbon::parse($registro->fecha_carga)->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado y Información del Cliente -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0 text-muted">
                                <i class="bi bi-person me-2"></i>Información del Cliente
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="text-muted small d-block">NIS</span>
                                <span class="fw-semibold">{{ $registro->nis ?: 'No especificado' }}</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted small d-block">Medidor</span>
                                <span class="fw-semibold">{{ $registro->nromedidor ?: 'No especificado' }}</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted small d-block">Cliente</span>
                                <span class="fw-semibold">{{ $registro->nomclie ?: 'No especificado' }}</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted small d-block">Teléfono</span>
                                <span class="fw-semibold">{{ $registro->tel_clie ?: 'No especificado' }}</span>
                            </div>
                            <div class="mb-0">
                                <span class="text-muted small d-block">Dirección</span>
                                <span class="fw-semibold small">{{ $registro->dir_proc ?: 'No especificada' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado del Registro -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center py-4">
                            @if($registro->editado)
                                <div class="mb-2">
                                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                        <i class="bi bi-pencil-square me-1"></i>Editado
                                    </span>
                                </div>
                                @if($registro->fecha_edicion)
                                    <small class="text-muted">
                                        Última edición:<br>
                                        {{ \Carbon\Carbon::parse($registro->fecha_edicion)->format('d/m/Y H:i:s') }}
                                    </small>
                                @endif
                            @else
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    <i class="bi bi-file-text me-1"></i>Original
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0 text-muted">
                                <i class="bi bi-lightning me-2"></i>Acciones Rápidas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('remesa.ver.detalle', $registro->id) }}" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-eye me-2"></i>Ver Detalle Completo
                                </a>
                                <a href="{{ route('remesa.ver.historial', $registro->id) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-clock-history me-2"></i>Ver Historial
                                </a>
                                <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-list me-2"></i>Todos los Registros
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-confirmacion">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalConfirmacionLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Actualización
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3"><strong>¿Está seguro que desea actualizar este registro?</strong></p>
                
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Resumen de cambios:</h6>
                    <div id="resumenCambios">
                        <!-- Se llenará dinámicamente con JavaScript -->
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Información del registro:</h6>
                        <ul class="list-unstyled small">
                            <li><strong>NIS:</strong> {{ $registro->nis ?? 'N/A' }}</li>
                            <li><strong>Cliente:</strong> {{ $registro->nomclie ?? 'N/A' }}</li>
                            <li><strong>Medidor:</strong> {{ $registro->nromedidor ?? 'N/A' }}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Detalles adicionales:</h6>
                        <ul class="list-unstyled small">
                            <li><strong>Nro. Carga:</strong> {{ $registro->nro_carga }}</li>
                            <li><strong>Centro:</strong> {{ $registro->centro_servicio ?? 'N/A' }}</li>
                            <li><strong>Tipo:</strong> 
                                <span class="badge {{ $registro->tipo_remesa == 'Reclamo' ? 'bg-warning' : 'bg-info' }} text-dark">
                                    {{ $registro->tipo_remesa }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="confirmarActualizacion">
                    <i class="bi bi-check-lg me-2"></i>Sí, Actualizar Registro
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rethorInput = document.getElementById('rethor');
    const horaprogInput = document.getElementById('horaprog');
    const retfechInput = document.getElementById('retfech');
    const fechaprogInput = document.getElementById('fechaprog');

    // Validar horarios (8:00 AM - 6:00 PM)
    function validarHorario(input, nombre) {
        input.addEventListener('change', function() {
            if (this.value) {
                const [hours, minutes] = this.value.split(':').map(Number);
                const totalMinutes = (hours * 60) + minutes;
                
                if (totalMinutes < 480 || totalMinutes > 1080) { // 8:00 AM - 6:00 PM
                    alert(`La ${nombre} debe estar entre las 8:00 AM y 6:00 PM.`);
                    this.value = '';
                    this.focus();
                    return false;
                }
            }
        });
    }

    // Validar antes de mostrar modal
    function validarFormulario() {
        const rethor = document.getElementById('rethor').value;
        const horaprog = document.getElementById('horaprog').value;
        const retfech = document.getElementById('retfech').value;
        const fechaprog = document.getElementById('fechaprog').value;

        // Validar horarios
        const validarHora = (hora, nombre) => {
            if (hora) {
                const [hours, minutes] = hora.split(':').map(Number);
                const totalMinutes = (hours * 60) + minutes;
                if (totalMinutes < 480 || totalMinutes > 1080) {
                    alert(`La ${nombre} debe estar entre las 8:00 AM y 6:00 PM.`);
                    return false;
                }
            }
            return true;
        };

        if (!validarHora(rethor, 'hora de retiro')) return false;
        if (!validarHora(horaprog, 'hora programada')) return false;

        // Validar fechas
        if (fechaprog && retfech) {
            if (new Date(fechaprog) < new Date(retfech)) {
                alert('La fecha programada no puede ser anterior a la fecha de retiro.');
                return false;
            }
        }

        return true;
    }

    // Validar fechas
    function validarFechas() {
        if (retfechInput.value && fechaprogInput.value) {
            const fechaRetiro = new Date(retfechInput.value);
            const fechaProg = new Date(fechaprogInput.value);
            
            if (fechaProg < fechaRetiro) {
                alert('La fecha programada no puede ser anterior a la fecha de retiro.');
                fechaprogInput.value = '';
                fechaprogInput.focus();
            }
        }
    }

    // Aplicar validaciones
    if (rethorInput) validarHorario(rethorInput, 'hora de retiro');
    if (horaprogInput) validarHorario(horaprogInput, 'hora programada');
    
    if (fechaprogInput) {
        fechaprogInput.addEventListener('change', validarFechas);
    }
    if (retfechInput) {
        retfechInput.addEventListener('change', validarFechas);
    }

    // Establecer límites en los inputs de tiempo
    if (rethorInput) {
        rethorInput.setAttribute('min', '08:00');
        rethorInput.setAttribute('max', '18:00');
    }
    if (horaprogInput) {
        horaprogInput.setAttribute('min', '08:00');
        horaprogInput.setAttribute('max', '18:00');
    }

    // Manejar confirmación de cambios
    const btnGuardarCambios = document.getElementById('btnGuardarCambios');
    const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
    const confirmarBtn = document.getElementById('confirmarActualizacion');
    const form = document.getElementById('formActualizarRegistro'); // ✅ CORREGIDO: usar ID específico

    // Obtener datos originales directamente de los inputs al cargar la página
    const datosOriginales = {
        retfech: document.getElementById('retfech').value,
        rethor: document.getElementById('rethor').value,
        fechaprog: document.getElementById('fechaprog').value,
        horaprog: document.getElementById('horaprog').value
    };

    if (btnGuardarCambios) {
        btnGuardarCambios.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validar formulario antes de continuar
            if (!validarFormulario()) {
                return;
            }
            
            // Obtener valores actuales del formulario
            const valoresActuales = {
                retfech: document.getElementById('retfech').value || '',
                rethor: document.getElementById('rethor').value || '',
                fechaprog: document.getElementById('fechaprog').value || '',
                horaprog: document.getElementById('horaprog').value || ''
            };

            // Detectar cambios
            const cambios = [];
            
            // Función para formatear valores para mostrar
            const formatearValor = (valor, tipo) => {
                if (!valor || valor === '') {
                    return 'No establecida';
                }
                if (tipo === 'fecha') {
                    // Convertir Y-m-d a d/m/Y
                    const fecha = new Date(valor);
                    return fecha.toLocaleDateString('es-ES');
                }
                if (tipo === 'hora') {
                    // Ya viene en formato HH:MM
                    return valor;
                }
                return valor;
            };

            if (valoresActuales.retfech !== datosOriginales.retfech) {
                cambios.push({
                    campo: 'Fecha de Retiro',
                    original: formatearValor(datosOriginales.retfech, 'fecha'),
                    nuevo: formatearValor(valoresActuales.retfech, 'fecha')
                });
            }
            
            if (valoresActuales.rethor !== datosOriginales.rethor) {
                cambios.push({
                    campo: 'Hora de Retiro',
                    original: formatearValor(datosOriginales.rethor, 'hora'),
                    nuevo: formatearValor(valoresActuales.rethor, 'hora')
                });
            }
            
            if (valoresActuales.fechaprog !== datosOriginales.fechaprog) {
                cambios.push({
                    campo: 'Fecha Programada',
                    original: formatearValor(datosOriginales.fechaprog, 'fecha'),
                    nuevo: formatearValor(valoresActuales.fechaprog, 'fecha')
                });
            }
            
            if (valoresActuales.horaprog !== datosOriginales.horaprog) {
                cambios.push({
                    campo: 'Hora Programada',
                    original: formatearValor(datosOriginales.horaprog, 'hora'),
                    nuevo: formatearValor(valoresActuales.horaprog, 'hora')
                });
            }

            // Mostrar resumen de cambios
            const resumenDiv = document.getElementById('resumenCambios');
            if (cambios.length === 0) {
                resumenDiv.innerHTML = '<p class="text-muted mb-0"><i class="bi bi-info-circle me-2"></i>No se detectaron cambios en los campos.</p>';
            } else {
                let html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
                html += '<thead class="table-light"><tr><th>Campo</th><th>Valor Actual</th><th>Nuevo Valor</th></tr></thead><tbody>';
                
                cambios.forEach(cambio => {
                    html += `<tr>
                        <td><strong>${cambio.campo}</strong></td>
                        <td class="text-muted">${cambio.original}</td>
                        <td class="text-success"><strong>${cambio.nuevo}</strong></td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                resumenDiv.innerHTML = html;
            }

            // Mostrar el modal
            modalConfirmacion.show();
        });
    }

    // Confirmar actualización
    if (confirmarBtn) {
        confirmarBtn.addEventListener('click', function() {
            const debugInfo = {
                timestamp: new Date().toISOString(),
                action: form.action,
                method: form.method,
                csrf: document.querySelector('[name="_token"]').value,
                formData: new FormData(form)
            };
            
            // Guardar en localStorage para debug persistente
            localStorage.setItem('debug_form_submit', JSON.stringify(debugInfo));
            console.log('🚀 ENVIANDO FORMULARIO - DEBUG INFO:', debugInfo);
            
            modalConfirmacion.hide();
            
            // ✅ SOLUCIÓN: Crear un formulario temporal POST para asegurar method spoofing
            console.log('📤 Enviando formulario como POST con method spoofing...');
            
            // ✅ VALIDACIÓN DE SEGURIDAD
            if (!form) {
                console.error('❌ NO SE ENCONTRÓ EL FORMULARIO');
                alert('Error: No se encontró el formulario para enviar');
                return;
            }
            
            console.log('🔍 FORMULARIO ENCONTRADO:', {
                id: form.id,
                action: form.action,
                method: form.method
            });
            
            // Crear formulario POST temporal
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = form.action;
            
            // Agregar CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('[name="_token"]').value;
            tempForm.appendChild(csrfInput);
            
            // Agregar method spoofing para PUT
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            tempForm.appendChild(methodInput);
            
            // Copiar todos los campos del formulario original
            const formData = new FormData(form);
            for (let [name, value] of formData.entries()) {
                if (name !== '_token' && name !== '_method') {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    tempForm.appendChild(input);
                }
            }
            
            // Agregar al DOM y enviar
            document.body.appendChild(tempForm);
            tempForm.submit();
        });
    }
    
    // Al cargar la página, mostrar debug info anterior si existe
    const previousDebug = localStorage.getItem('debug_form_submit');
    if (previousDebug) {
        console.log('🔍 DEBUG INFO ANTERIOR:', JSON.parse(previousDebug));
        // Limpiar después de mostrar
        localStorage.removeItem('debug_form_submit');
    }

    // Test simple de actualización
    const btnTestUpdate = document.getElementById('btnTestUpdate');
    if (btnTestUpdate) {
        btnTestUpdate.addEventListener('click', function() {
            console.log('🧪 Iniciando test simple...');
            
            const registroId = {{ $registro->id }};
            
            fetch(`/test-update/${registroId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('🧪 Test resultado:', data);
                alert('Test exitoso: ' + JSON.stringify(data));
            })
            .catch(error => {
                console.error('🧪 Test falló:', error);
                alert('Test falló: ' + error);
            });
        });
    }
});
</script>
@endpush