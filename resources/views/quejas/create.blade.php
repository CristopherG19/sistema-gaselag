@extends('layouts.app')

@section('title', 'Crear Queja')

@push('styles')
<style>
    .form-section {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .section-title {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #dee2e6;
    }
    .priority-option {
        border: 2px solid transparent;
        border-radius: 0.5rem;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .priority-option:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }
    .priority-option.selected {
        border-color: #007bff;
        background-color: #e3f2fd;
    }
    .tipo-option {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tipo-option:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }
    .tipo-option.selected {
        border-color: #007bff;
        background-color: #e3f2fd;
    }
    .oc-option {
        cursor: pointer;
        transition: all 0.2s;
    }
    .oc-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        Crear Nueva Queja
                    </h2>
                    <p class="text-muted mb-0">Reporta un problema o solicita ayuda</p>
                </div>
                <div>
                    <a href="{{ route('quejas.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('quejas.store') }}">
                @csrf
                
                <!-- Información Básica -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Información Básica
                    </h5>
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título de la Queja <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('titulo') is-invalid @enderror" 
                               id="titulo" name="titulo" value="{{ old('titulo') }}" 
                               placeholder="Describe brevemente el problema" required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción Detallada <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" name="descripcion" rows="5" 
                                  placeholder="Proporciona todos los detalles del problema..." required>{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Máximo 2000 caracteres</div>
                    </div>
                </div>

                <!-- Clasificación -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-tags me-2"></i>
                        Clasificación
                    </h5>
                    
                    <!-- Tipo de Queja -->
                    <div class="mb-4">
                        <label class="form-label">Tipo de Queja <span class="text-danger">*</span></label>
                        <div class="row">
                            @foreach($tipos as $tipo)
                                <div class="col-md-6 mb-2">
                                    <div class="tipo-option {{ old('tipo', 'general') == $tipo ? 'selected' : '' }}" 
                                         onclick="selectTipo('{{ $tipo }}')">
                                        <input type="radio" name="tipo" value="{{ $tipo }}" 
                                               id="tipo_{{ $tipo }}" {{ old('tipo', 'general') == $tipo ? 'checked' : '' }} 
                                               style="display: none;">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-{{ $tipo == 'general' ? 'question-circle' : ($tipo == 'tecnica' ? 'gear' : ($tipo == 'administrativa' ? 'clipboard' : 'bug')) }} me-2"></i>
                                            <div>
                                                <strong>{{ ucfirst($tipo) }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    @if($tipo == 'general')
                                                        Consultas generales y dudas
                                                    @elseif($tipo == 'tecnica')
                                                        Problemas técnicos del sistema
                                                    @elseif($tipo == 'administrativa')
                                                        Asuntos administrativos
                                                    @else
                                                        Errores y bugs del sistema
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('tipo')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Prioridad -->
                    <div class="mb-4">
                        <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                        <div class="row">
                            @foreach($prioridades as $prioridad)
                                <div class="col-md-6 mb-2">
                                    <div class="priority-option {{ old('prioridad', 'media') == $prioridad ? 'selected' : '' }}" 
                                         onclick="selectPrioridad('{{ $prioridad }}')">
                                        <input type="radio" name="prioridad" value="{{ $prioridad }}" 
                                               id="prioridad_{{ $prioridad }}" {{ old('prioridad', 'media') == $prioridad ? 'checked' : '' }} 
                                               style="display: none;">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-{{ $prioridad == 'baja' ? 'arrow-down-circle' : ($prioridad == 'media' ? 'arrow-right-circle' : ($prioridad == 'alta' ? 'arrow-up-circle' : 'exclamation-triangle')) }} me-2 text-{{ $prioridad == 'baja' ? 'success' : ($prioridad == 'media' ? 'warning' : ($prioridad == 'alta' ? 'danger' : 'dark')) }}"></i>
                                            <div>
                                                <strong>{{ ucfirst($prioridad) }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    @if($prioridad == 'baja')
                                                        No urgente, puede esperar
                                                    @elseif($prioridad == 'media')
                                                        Importante, atención normal
                                                    @elseif($prioridad == 'alta')
                                                        Urgente, requiere atención rápida
                                                    @else
                                                        Crítica, atención inmediata
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('prioridad')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-plus-circle me-2"></i>
                        Información Adicional
                    </h5>
                    
                    <!-- Selección de OC -->
                    <div class="mb-4">
                        <label for="oc_id" class="form-label">OC Relacionada (Opcional)</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('oc_id') is-invalid @enderror" 
                                   id="oc_search" placeholder="Buscar por código de OC, operario o zona..." 
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="buscarOC()">
                                <i class="bi bi-search"></i>
                            </button>
                            <input type="hidden" id="oc_id" name="oc_id" value="{{ old('oc_id') }}">
                        </div>
                        @error('oc_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Busca y selecciona una OC si la queja está relacionada con una orden de contrastación</div>
                        
                        <!-- Resultados de búsqueda -->
                        <div id="oc_results" class="mt-3" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Resultados de Búsqueda</h6>
                                </div>
                                <div class="card-body" id="oc_results_body">
                                    <!-- Los resultados se cargarán aquí -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- OC seleccionada -->
                        <div id="oc_selected" class="mt-3" style="display: none;">
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>OC seleccionada:</strong> <span id="oc_selected_info"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="deseleccionarOC()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('quejas.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i>
                        Crear Queja
                    </button>
                </div>
            </form>
        </div>

        <!-- Panel de Ayuda -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Consejos para una Buena Queja
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Título claro:</strong> Describe el problema en pocas palabras
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Descripción detallada:</strong> Incluye pasos para reproducir el problema
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Prioridad correcta:</strong> Asigna la prioridad apropiada
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Información adicional:</strong> Menciona si hay una remesa relacionada
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-clock me-2"></i>
                        Tiempos de Respuesta
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge bg-success me-2">Baja</span>
                        <small>1-3 días hábiles</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-warning me-2">Media</span>
                        <small>4-8 horas</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-danger me-2">Alta</span>
                        <small>1-4 horas</small>
                    </div>
                    <div class="mb-0">
                        <span class="badge bg-dark me-2">Crítica</span>
                        <small>Inmediata</small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>
                        Privacidad
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-0">
                        Tu queja será visible solo para ti y los administradores del sistema. 
                        Si es asignada a alguien, esa persona también podrá verla.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectTipo(tipo) {
    // Remover selección anterior
    document.querySelectorAll('.tipo-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Seleccionar nueva opción
    document.querySelector(`.tipo-option[onclick="selectTipo('${tipo}')"]`).classList.add('selected');
    document.getElementById(`tipo_${tipo}`).checked = true;
}

function selectPrioridad(prioridad) {
    // Remover selección anterior
    document.querySelectorAll('.priority-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Seleccionar nueva opción
    document.querySelector(`.priority-option[onclick="selectPrioridad('${prioridad}')"]`).classList.add('selected');
    document.getElementById(`prioridad_${prioridad}`).checked = true;
}

// Contador de caracteres para descripción
document.getElementById('descripcion').addEventListener('input', function() {
    const maxLength = 2000;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // Crear o actualizar contador
    let counter = document.getElementById('char-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'char-counter';
        counter.className = 'form-text text-end';
        this.parentNode.appendChild(counter);
    }
    
    counter.textContent = `${currentLength}/${maxLength} caracteres`;
    
    if (remaining < 100) {
        counter.className = 'form-text text-end text-warning';
    } else if (remaining < 0) {
        counter.className = 'form-text text-end text-danger';
    } else {
        counter.className = 'form-text text-end';
    }
});

// Funciones para búsqueda de OC
function buscarOC() {
    const termino = document.getElementById('oc_search').value.trim();
    
    if (termino.length < 2) {
        alert('Por favor ingresa al menos 2 caracteres para buscar');
        return;
    }
    
    // Mostrar loading
    const resultsDiv = document.getElementById('oc_results');
    const resultsBody = document.getElementById('oc_results_body');
    resultsDiv.style.display = 'block';
    resultsBody.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Buscando...</span></div><p class="mt-2">Buscando OC...</p></div>';
    
    // Realizar petición AJAX
    fetch('{{ route("quejas.buscar-oc") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            termino: termino
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarResultadosOC(data.ocs);
        } else {
            resultsBody.innerHTML = '<div class="alert alert-warning">No se encontraron OC con ese criterio de búsqueda</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultsBody.innerHTML = '<div class="alert alert-danger">Error al buscar OC</div>';
    });
}

function mostrarResultadosOC(ocs) {
    const resultsBody = document.getElementById('oc_results_body');
    
    if (ocs.length === 0) {
        resultsBody.innerHTML = '<div class="alert alert-warning">No se encontraron OC con ese criterio de búsqueda</div>';
        return;
    }
    
    let html = '<div class="row">';
    ocs.forEach(oc => {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card h-100 oc-option" onclick="seleccionarOC(${oc.id}, '${oc.codigo_entrega}', '${oc.nombre_entrega}', '${oc.operario_nombre}', '${oc.zona_asignada || 'Sin zona'}')">
                    <div class="card-body">
                        <h6 class="card-title">${oc.codigo_entrega}</h6>
                        <p class="card-text">
                            <strong>Nombre:</strong> ${oc.nombre_entrega}<br>
                            <strong>Operario:</strong> ${oc.operario_nombre}<br>
                            <strong>Zona:</strong> ${oc.zona_asignada || 'Sin zona'}<br>
                            <strong>Estado:</strong> <span class="badge bg-${oc.estado_color}">${oc.estado_texto}</span>
                        </p>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    resultsBody.innerHTML = html;
}

function seleccionarOC(ocId, codigo, nombre, operario, zona) {
    // Ocultar resultados
    document.getElementById('oc_results').style.display = 'none';
    
    // Establecer valor en el input hidden
    document.getElementById('oc_id').value = ocId;
    
    // Mostrar OC seleccionada
    const selectedDiv = document.getElementById('oc_selected');
    const selectedInfo = document.getElementById('oc_selected_info');
    selectedInfo.textContent = `${codigo} - ${nombre} (${operario})`;
    selectedDiv.style.display = 'block';
    
    // Limpiar búsqueda
    document.getElementById('oc_search').value = '';
}

function deseleccionarOC() {
    // Limpiar selección
    document.getElementById('oc_id').value = '';
    document.getElementById('oc_selected').style.display = 'none';
    document.getElementById('oc_results').style.display = 'none';
}

// Búsqueda al presionar Enter
document.getElementById('oc_search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        buscarOC();
    }
});
</script>
@endpush
