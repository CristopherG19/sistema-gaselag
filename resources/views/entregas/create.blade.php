@extends('layouts.app')

@section('title', 'Crear Entrega')

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
    .remesa-card {
        border: 2px solid transparent;
        border-radius: 0.5rem;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .remesa-card:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }
    .remesa-card.selected {
        border-color: #007bff;
        background-color: #e3f2fd;
    }
    .operario-card {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .operario-card:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }
    .operario-card.selected {
        border-color: #007bff;
        background-color: #e3f2fd;
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
                        <i class="bi bi-truck text-success me-2"></i>
                        Crear Nueva Entrega
                    </h2>
                    <p class="text-muted mb-0">Asigna una remesa a un operario de campo</p>
                </div>
                <div>
                    <a href="{{ route('entregas.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('entregas.store') }}">
                @csrf
                
                <!-- Selección de Remesa -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-file-earmark me-2"></i>
                        Seleccionar Remesa
                    </h5>
                    <div class="row">
                        @foreach($remesas as $remesa)
                            <div class="col-md-6 mb-3">
                                <div class="remesa-card" onclick="selectRemesa({{ $remesa->id }})">
                                    <input type="radio" name="remesa_id" value="{{ $remesa->id }}" 
                                           id="remesa_{{ $remesa->id }}" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-text me-3 text-primary" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1">{{ $remesa->nombre_archivo }}</h6>
                                            <small class="text-muted">Carga #{{ $remesa->nro_carga }}</small><br>
                                            <small class="text-muted">{{ $remesa->fecha_carga->format('d/m/Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('remesa_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Selección de Operario -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-person-badge me-2"></i>
                        Seleccionar Operario de Campo
                    </h5>
                    <div class="row">
                        @foreach($operarios as $operario)
                            <div class="col-md-6 mb-3">
                                <div class="operario-card" onclick="selectOperario({{ $operario->id }})">
                                    <input type="radio" name="operario_id" value="{{ $operario->id }}" 
                                           id="operario_{{ $operario->id }}" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle me-3 text-success" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1">{{ $operario->nombre }} {{ $operario->apellidos }}</h6>
                                            <small class="text-muted">{{ $operario->correo }}</small><br>
                                            <span class="badge bg-success">{{ $operario->rol_texto }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('operario_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Información Adicional -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-plus-circle me-2"></i>
                        Información Adicional
                    </h5>
                    
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                  id="observaciones" name="observaciones" rows="4" 
                                  placeholder="Instrucciones especiales, ubicación, contacto, etc...">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Información adicional que ayudará al operario en la entrega</div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('entregas.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>
                        Crear Entrega
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
                        Consejos para una Buena Asignación
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Remesa correcta:</strong> Verifica que sea la remesa adecuada
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Operario disponible:</strong> Asegúrate de que el operario esté activo
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Observaciones claras:</strong> Proporciona información útil
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Verificación:</strong> Revisa todos los datos antes de crear
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Estados de Entrega
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge bg-warning me-2">Asignada</span>
                        <small>Recién creada, esperando inicio</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-info me-2">En Ruta</span>
                        <small>Operario en camino a la entrega</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-success me-2">Entregada</span>
                        <small>Entrega completada exitosamente</small>
                    </div>
                    <div class="mb-0">
                        <span class="badge bg-danger me-2">Incidencia</span>
                        <small>Problema durante la entrega</small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>
                        Permisos
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-0">
                        Solo los administradores y usuarios normales pueden crear entregas. 
                        Los operarios de campo solo pueden ver y gestionar sus entregas asignadas.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectRemesa(remesaId) {
    // Remover selección anterior
    document.querySelectorAll('.remesa-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Seleccionar nueva opción
    document.querySelector(`.remesa-card[onclick="selectRemesa(${remesaId})"]`).classList.add('selected');
    document.getElementById(`remesa_${remesaId}`).checked = true;
}

function selectOperario(operarioId) {
    // Remover selección anterior
    document.querySelectorAll('.operario-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Seleccionar nueva opción
    document.querySelector(`.operario-card[onclick="selectOperario(${operarioId})"]`).classList.add('selected');
    document.getElementById(`operario_${operarioId}`).checked = true;
}

// Validación del formulario
document.querySelector('form').addEventListener('submit', function(e) {
    const remesaSeleccionada = document.querySelector('input[name="remesa_id"]:checked');
    const operarioSeleccionado = document.querySelector('input[name="operario_id"]:checked');
    
    if (!remesaSeleccionada) {
        e.preventDefault();
        alert('Por favor selecciona una remesa');
        return;
    }
    
    if (!operarioSeleccionado) {
        e.preventDefault();
        alert('Por favor selecciona un operario de campo');
        return;
    }
});
</script>
@endpush
