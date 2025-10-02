@extends('layouts.app')

@section('title', 'Detalles de la Queja')

@push('styles')
<style>
    .queja-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    .status-badge {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    .priority-badge {
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
        background: #007bff;
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
    <!-- Header de la Queja -->
    <div class="queja-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2">{{ $queja->titulo }}</h2>
                <p class="mb-0 opacity-75">Queja #{{ $queja->id }}</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex flex-column gap-2">
                    <span class="badge status-badge bg-{{ $queja->estado_color }}">
                        {{ $queja->estado_texto }}
                    </span>
                    <span class="badge priority-badge bg-{{ $queja->prioridad_color }}">
                        {{ $queja->prioridad_texto }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Contenido Principal -->
        <div class="col-lg-8">
            <!-- Descripción -->
            <div class="info-card">
                <h5 class="mb-3">
                    <i class="bi bi-file-text me-2"></i>
                    Descripción
                </h5>
                <p class="mb-0">{{ $queja->descripcion }}</p>
            </div>

            <!-- Solución (si existe) -->
            @if($queja->solucion)
                <div class="info-card">
                    <h5 class="mb-3 text-success">
                        <i class="bi bi-check-circle me-2"></i>
                        Solución
                    </h5>
                    <p class="mb-0">{{ $queja->solucion }}</p>
                    @if($queja->comentarios)
                        <hr>
                        <h6 class="text-muted">Comentarios Adicionales:</h6>
                        <p class="mb-0 text-muted">{{ $queja->comentarios }}</p>
                    @endif
                </div>
            @endif

            <!-- Timeline de la Queja -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Historial de la Queja
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline-item">
                        <h6 class="text-primary">Queja Creada</h6>
                        <p class="text-muted mb-1">Por: {{ $queja->usuario->nombre }} {{ $queja->usuario->apellidos }}</p>
                        <small class="text-muted">{{ $queja->fecha_creacion->format('d/m/Y H:i') }}</small>
                    </div>

                    @if($queja->fecha_asignacion)
                        <div class="timeline-item">
                            <h6 class="text-info">Queja Asignada</h6>
                            <p class="text-muted mb-1">A: {{ $queja->asignadoA->nombre }} {{ $queja->asignadoA->apellidos }}</p>
                            <small class="text-muted">{{ $queja->fecha_asignacion->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif

                    @if($queja->fecha_resolucion)
                        <div class="timeline-item">
                            <h6 class="text-success">Queja Resuelta</h6>
                            <p class="text-muted mb-1">Por: {{ $queja->asignadoA->nombre }} {{ $queja->asignadoA->apellidos }}</p>
                            <small class="text-muted">{{ $queja->fecha_resolucion->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Información de la Queja -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Información
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Tipo:</strong><br>
                        <span class="badge bg-secondary">{{ ucfirst($queja->tipo) }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Creada por:</strong><br>
                        {{ $queja->usuario->nombre }} {{ $queja->usuario->apellidos }}<br>
                        <small class="text-muted">{{ $queja->usuario->correo }}</small>
                    </div>

                    @if($queja->asignadoA)
                        <div class="mb-3">
                            <strong>Asignada a:</strong><br>
                            {{ $queja->asignadoA->nombre }} {{ $queja->asignadoA->apellidos }}<br>
                            <small class="text-muted">{{ $queja->asignadoA->correo }}</small>
                        </div>
                    @endif

                    @if($queja->remesa)
                        <div class="mb-3">
                            <strong>Remesa relacionada:</strong><br>
                            <a href="{{ route('remesa.ver.registros', $queja->remesa->nro_carga) }}" 
                               class="text-decoration-none">
                                {{ $queja->remesa->nombre_archivo }}
                            </a><br>
                            <small class="text-muted">Carga #{{ $queja->remesa->nro_carga }}</small>
                        </div>
                    @endif

                    <div class="mb-0">
                        <strong>Fecha de creación:</strong><br>
                        <small class="text-muted">{{ $queja->fecha_creacion->format('d/m/Y H:i') }}</small>
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
                        @if(Auth::user()->isAdmin())
                            @if($queja->estado === 'pendiente')
                                <button type="button" class="btn btn-primary" 
                                        onclick="asignarQueja({{ $queja->id }})">
                                    <i class="bi bi-person-plus me-1"></i>
                                    Asignar Queja
                                </button>
                            @endif
                        @endif
                        
                        @if($queja->puedeSerResuelta())
                            <button type="button" class="btn btn-success" 
                                    onclick="resolverQueja({{ $queja->id }})">
                                <i class="bi bi-check-circle me-1"></i>
                                Resolver Queja
                            </button>
                        @endif
                        
                        <button type="button" class="btn btn-warning" 
                                onclick="cambiarEstado({{ $queja->id }})">
                            <i class="bi bi-arrow-repeat me-1"></i>
                            Cambiar Estado
                        </button>

                        <a href="{{ route('quejas.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Volver a Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar queja -->
<div class="modal fade" id="asignarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Queja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="asignarForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="asignado_a" class="form-label">Asignar a</label>
                        <select class="form-select" id="asignado_a" name="asignado_a" required>
                            <option value="">Seleccionar usuario</option>
                            @foreach(\App\Models\Usuario::where('activo', true)->get() as $usuario)
                                <option value="{{ $usuario->id }}">{{ $usuario->nombre }} {{ $usuario->apellidos }} ({{ $usuario->rol_texto }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="comentarios" class="form-label">Comentarios</label>
                        <textarea class="form-control" id="comentarios" name="comentarios" rows="3" 
                                  placeholder="Comentarios adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para resolver queja -->
<div class="modal fade" id="resolverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resolver Queja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resolverForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="solucion" class="form-label">Solución <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="solucion" name="solucion" rows="4" 
                                  placeholder="Describe la solución aplicada..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="comentarios_resolver" class="form-label">Comentarios Adicionales</label>
                        <textarea class="form-control" id="comentarios_resolver" name="comentarios" rows="3" 
                                  placeholder="Comentarios adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Resolver</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div class="modal fade" id="cambiarEstadoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cambiarEstadoForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="estado_nuevo" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="estado_nuevo" name="estado" required>
                            <option value="pendiente" {{ $queja->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en_proceso" {{ $queja->estado == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                            <option value="resuelta" {{ $queja->estado == 'resuelta' ? 'selected' : '' }}>Resuelta</option>
                            <option value="cancelada" {{ $queja->estado == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Cambiar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function asignarQueja(quejaId) {
    const modal = new bootstrap.Modal(document.getElementById('asignarModal'));
    document.getElementById('asignarForm').action = `/quejas/${quejaId}/asignar`;
    modal.show();
}

function resolverQueja(quejaId) {
    const modal = new bootstrap.Modal(document.getElementById('resolverModal'));
    document.getElementById('resolverForm').action = `/quejas/${quejaId}/resolver`;
    modal.show();
}

function cambiarEstado(quejaId) {
    const modal = new bootstrap.Modal(document.getElementById('cambiarEstadoModal'));
    document.getElementById('cambiarEstadoForm').action = `/quejas/${quejaId}/cambiar-estado`;
    modal.show();
}
</script>
@endpush
