@extends('layouts.app')

@section('title', 'Editar Entrega')

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
                        <i class="bi bi-pencil text-warning me-2"></i>
                        Editar Entrega
                    </h2>
                    <p class="text-muted mb-0">Modifica los datos de la entrega</p>
                </div>
                <div>
                    <a href="{{ route('entregas.show', $entrega) }}" class="btn btn-outline-info me-2">
                        <i class="bi bi-eye me-1"></i>
                        Ver Detalles
                    </a>
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
            <form method="POST" action="{{ route('entregas.update', $entrega) }}">
                @csrf
                @method('PUT')
                
                <!-- Información de la Remesa (Solo lectura) -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-file-earmark me-2"></i>
                        Información de la Remesa
                    </h5>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Remesa:</strong> {{ $entrega->remesa->nombre_archivo }} (Carga #{{ $entrega->remesa->nro_carga }})
                        <br>
                        <small>La remesa no puede ser modificada una vez creada la entrega.</small>
                    </div>
                </div>

                <!-- Selección de Operario -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-person-badge me-2"></i>
                        Operario de Campo
                    </h5>
                    <div class="row">
                        @foreach($operarios as $operario)
                            <div class="col-md-6 mb-3">
                                <div class="operario-card {{ $entrega->operario_id == $operario->id ? 'selected' : '' }}" 
                                     onclick="selectOperario({{ $operario->id }})">
                                    <input type="radio" name="operario_id" value="{{ $operario->id }}" 
                                           id="operario_{{ $operario->id }}" 
                                           {{ $entrega->operario_id == $operario->id ? 'checked' : '' }} 
                                           style="display: none;">
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

                <!-- Estado de la Entrega -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-flag me-2"></i>
                        Estado de la Entrega
                    </h5>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado">
                            @foreach($estados as $estado)
                                <option value="{{ $estado }}" {{ $entrega->estado == $estado ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('estado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
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
                                  placeholder="Instrucciones especiales, ubicación, contacto, etc...">{{ old('observaciones', $entrega->observaciones) }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Información adicional que ayudará al operario en la entrega</div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('entregas.show', $entrega) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i>
                        Actualizar Entrega
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
                        Consejos para Editar
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Operario correcto:</strong> Verifica que sea el operario adecuado
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Estado apropiado:</strong> Cambia el estado según el progreso
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Observaciones claras:</strong> Mantén la información actualizada
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Verificación:</strong> Revisa todos los cambios antes de guardar
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
                        Solo los administradores y usuarios normales pueden editar entregas. 
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
    const operarioSeleccionado = document.querySelector('input[name="operario_id"]:checked');
    
    if (!operarioSeleccionado) {
        e.preventDefault();
        alert('Por favor selecciona un operario de campo');
        return;
    }
});
</script>
@endpush
