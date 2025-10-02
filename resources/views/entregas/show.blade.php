@extends('layouts.app')

@section('title', 'Detalles de la Entrega')

@push('styles')
<style>
    .entrega-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    .status-badge {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    .timeline-item {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 1.5rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #28a745;
    }
    .timeline-item::after {
        content: '';
        position: absolute;
        left: 5px;
        top: 1.2rem;
        width: 2px;
        height: calc(100% + 0.5rem);
        background: #dee2e6;
    }
    .timeline-item:last-child::after {
        display: none;
    }
    .info-card {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .action-buttons {
        position: sticky;
        top: 2rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header de la Entrega -->
    <div class="entrega-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2">{{ $entrega->codigo_entrega }}</h2>
                <p class="mb-0">Entrega #{{ $entrega->id }}</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex flex-column gap-2">
                    <span class="badge status-badge bg-{{ $entrega->estado_color }}">
                        {{ $entrega->estado_texto }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Contenido Principal -->
        <div class="col-lg-8">
            <!-- Información de la Remesa -->
            <div class="info-card">
                <h5 class="mb-3">
                    <i class="bi bi-file-earmark me-2"></i>
                    Información de la Remesa
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Archivo:</strong><br>
                        <a href="{{ route('remesa.ver.registros', $entrega->remesa->nro_carga) }}" 
                           class="text-decoration-none">
                            {{ $entrega->remesa->nombre_archivo }}
                        </a>
                    </div>
                    <div class="col-md-6">
                        <strong>Número de Carga:</strong><br>
                        {{ $entrega->remesa->nro_carga }}
                    </div>
                </div>
            </div>

            <!-- Información del Operario -->
            <div class="info-card">
                <h5 class="mb-3">
                    <i class="bi bi-person-badge me-2"></i>
                    Operario Asignado
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nombre:</strong><br>
                        {{ $entrega->operario->nombre }} {{ $entrega->operario->apellidos }}
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        {{ $entrega->operario->correo }}
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            @if($entrega->observaciones)
                <div class="info-card">
                    <h5 class="mb-3">
                        <i class="bi bi-chat-text me-2"></i>
                        Observaciones
                    </h5>
                    <p class="mb-0">{{ $entrega->observaciones }}</p>
                </div>
            @endif

            <!-- Timeline de la Entrega -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Historial de la Entrega
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline-item">
                        <h6 class="text-primary">Entrega Creada</h6>
                        <p class="text-muted mb-1">Por: {{ $entrega->asignadoPor->nombre }} {{ $entrega->asignadoPor->apellidos }}</p>
                        <small class="text-muted">{{ $entrega->fecha_asignacion->format('d/m/Y H:i') }}</small>
                    </div>

                    @if($entrega->fecha_inicio)
                        <div class="timeline-item">
                            <h6 class="text-info">Entrega Iniciada</h6>
                            <p class="text-muted mb-1">Por: {{ $entrega->operario->nombre }} {{ $entrega->operario->apellidos }}</p>
                            <small class="text-muted">{{ $entrega->fecha_inicio->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif

                    @if($entrega->fecha_entrega)
                        <div class="timeline-item">
                            <h6 class="text-success">Entrega Completada</h6>
                            <p class="text-muted mb-1">Por: {{ $entrega->operario->nombre }} {{ $entrega->operario->apellidos }}</p>
                            <small class="text-muted">{{ $entrega->fecha_entrega->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Información de la Entrega -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Información
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Estado:</strong><br>
                        <span class="badge bg-{{ $entrega->estado_color }}">{{ $entrega->estado_texto }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Asignado por:</strong><br>
                        {{ $entrega->asignadoPor->nombre }} {{ $entrega->asignadoPor->apellidos }}<br>
                        <small class="text-muted">{{ $entrega->asignadoPor->correo }}</small>
                    </div>

                    <div class="mb-3">
                        <strong>Fecha de asignación:</strong><br>
                        <small class="text-muted">{{ $entrega->fecha_asignacion->format('d/m/Y H:i') }}</small>
                    </div>

                    @if($entrega->fecha_inicio)
                        <div class="mb-3">
                            <strong>Fecha de inicio:</strong><br>
                            <small class="text-muted">{{ $entrega->fecha_inicio->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif

                    @if($entrega->fecha_entrega)
                        <div class="mb-3">
                            <strong>Fecha de entrega:</strong><br>
                            <small class="text-muted">{{ $entrega->fecha_entrega->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif

                    <div class="mb-0">
                        <strong>Última actualización:</strong><br>
                        <small class="text-muted">{{ $entrega->updated_at->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card action-buttons">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Acciones
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(Auth::user()->isAdmin() || Auth::user()->isUsuario())
                            <a href="{{ route('entregas.edit', $entrega) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-1"></i>
                                Editar Entrega
                            </a>
                        @endif
                        
                        @if(Auth::user()->isOperarioCampo() && $entrega->operario_id === Auth::id())
                            @if($entrega->estado === 'asignada')
                                <button type="button" class="btn btn-primary" 
                                        onclick="iniciarEntrega({{ $entrega->id }})">
                                    <i class="bi bi-play-circle me-1"></i>
                                    Iniciar Entrega
                                </button>
                            @elseif($entrega->estado === 'en_ruta')
                                <button type="button" class="btn btn-success" 
                                        onclick="completarEntrega({{ $entrega->id }})">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Completar Entrega
                                </button>
                                <button type="button" class="btn btn-info" 
                                        onclick="actualizarProgreso({{ $entrega->id }})">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    Actualizar Progreso
                                </button>
                            @endif
                        @endif

                        <a href="{{ route('entregas.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Volver a Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para iniciar entrega -->
<div class="modal fade" id="iniciarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Iniciar Entrega</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="iniciarForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres iniciar esta entrega?</p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Al iniciar la entrega, cambiará el estado a "En Ruta" y se registrará la hora de inicio.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Iniciar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para completar entrega -->
<div class="modal fade" id="completarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Completar Entrega</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="completarForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres marcar esta entrega como completada?</p>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        Al completar la entrega, cambiará el estado a "Entregada" y se registrará la hora de finalización.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Completar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para actualizar progreso -->
<div class="modal fade" id="progresoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Actualizar Progreso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="progresoForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="observaciones_progreso" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones_progreso" name="observaciones" rows="4" 
                                  placeholder="Describe el progreso actual de la entrega..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function iniciarEntrega(entregaId) {
    const modal = new bootstrap.Modal(document.getElementById('iniciarModal'));
    document.getElementById('iniciarForm').action = `/entregas/${entregaId}/iniciar`;
    modal.show();
}

function completarEntrega(entregaId) {
    const modal = new bootstrap.Modal(document.getElementById('completarModal'));
    document.getElementById('completarForm').action = `/entregas/${entregaId}/completar`;
    modal.show();
}

function actualizarProgreso(entregaId) {
    const modal = new bootstrap.Modal(document.getElementById('progresoModal'));
    document.getElementById('progresoForm').action = `/entregas/${entregaId}/actualizar-progreso`;
    modal.show();
}
</script>
@endpush
