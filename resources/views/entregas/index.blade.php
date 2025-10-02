@extends('layouts.app')

@section('title', 'Gestión de Entregas')

@push('styles')
<style>
    .entrega-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-left: 4px solid transparent;
    }
    .entrega-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .entrega-asignada { border-left-color: #ffc107; }
    .entrega-en-ruta { border-left-color: #17a2b8; }
    .entrega-entregada { border-left-color: #28a745; }
    .entrega-incidencia { border-left-color: #dc3545; }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
    }
    .status-asignada { background-color: #ffc107; }
    .status-en-ruta { background-color: #17a2b8; }
    .status-entregada { background-color: #28a745; }
    .status-incidencia { background-color: #dc3545; }
    
    .filter-card {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1.5rem;
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
                        Gestión de Entregas
                    </h2>
                    <p class="text-muted mb-0">Administra las entregas de cargas a operarios de campo</p>
                </div>
                <div>
                    <a href="{{ route('entregas.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>
                        Nueva Entrega
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <form method="GET" action="{{ route('entregas.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="buscar" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="buscar" name="buscar" 
                               value="{{ request('buscar') }}" placeholder="Código, operario o remesa">
                    </div>
                    <div class="col-md-2">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado }}" {{ request('estado') == $estado ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="operario" class="form-label">Operario</label>
                        <select class="form-select" id="operario" name="operario">
                            <option value="">Todos los operarios</option>
                            @foreach(\App\Models\Usuario::where('rol', 'operario_campo')->where('activo', true)->get() as $operario)
                                <option value="{{ $operario->id }}" {{ request('operario') == $operario->id ? 'selected' : '' }}>
                                    {{ $operario->nombre }} {{ $operario->apellidos }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="fecha_desde" class="form-label">Desde</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                               value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="fecha_hasta" class="form-label">Hasta</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                               value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search me-1"></i>
                                Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lista de entregas -->
    <div class="row">
        @if($entregas->count() > 0)
            @foreach($entregas as $entrega)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card entrega-card entrega-{{ str_replace('_', '-', $entrega->estado) }}">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $entrega->codigo_entrega }}</h6>
                                    <small class="text-muted">#{{ $entrega->id }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge status-badge bg-{{ $entrega->estado_color }}">
                                        {{ $entrega->estado_texto }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Remesa:</strong><br>
                                <a href="{{ route('remesa.ver.registros', $entrega->remesa->nro_carga) }}" 
                                   class="text-decoration-none">
                                    {{ $entrega->remesa->nombre_archivo }}
                                </a><br>
                                <small class="text-muted">Carga #{{ $entrega->remesa->nro_carga }}</small>
                            </div>

                            <div class="mb-3">
                                <strong>Operario:</strong><br>
                                {{ $entrega->operario->nombre }} {{ $entrega->operario->apellidos }}<br>
                                <small class="text-muted">{{ $entrega->operario->correo }}</small>
                            </div>

                            <div class="mb-3">
                                <strong>Asignado por:</strong><br>
                                {{ $entrega->asignadoPor->nombre }} {{ $entrega->asignadoPor->apellidos }}
                            </div>

                            <div class="mb-3">
                                <strong>Fecha de asignación:</strong><br>
                                <span>{{ $entrega->fecha_asignacion->format('d/m/Y H:i') }}</span>
                            </div>

                            @if($entrega->fecha_inicio)
                                <div class="mb-3">
                                    <strong>Fecha de inicio:</strong><br>
                                    <span>{{ $entrega->fecha_inicio->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif

                            @if($entrega->fecha_entrega)
                                <div class="mb-3">
                                    <strong>Fecha de entrega:</strong><br>
                                    <span>{{ $entrega->fecha_entrega->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif

                            @if($entrega->observaciones)
                                <div class="mb-3">
                                    <strong>Observaciones:</strong><br>
                                    <small class="text-muted">{{ Str::limit($entrega->observaciones, 100) }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('entregas.show', $entrega) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>
                                    Ver Detalles
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if(Auth::user()->isAdmin() || Auth::user()->isUsuario())
                                            <li>
                                                <a class="dropdown-item" href="{{ route('entregas.edit', $entrega) }}">
                                                    <i class="bi bi-pencil me-2"></i>
                                                    Editar
                                                </a>
                                            </li>
                                        @endif
                                        
                                        @if(Auth::user()->isOperarioCampo() && $entrega->operario_id === Auth::id())
                                            @if($entrega->estado === 'asignada')
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       onclick="iniciarEntrega({{ $entrega->id }})">
                                                        <i class="bi bi-play-circle me-2"></i>
                                                        Iniciar
                                                    </a>
                                                </li>
                                            @elseif($entrega->estado === 'en_ruta')
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       onclick="completarEntrega({{ $entrega->id }})">
                                                        <i class="bi bi-check-circle me-2"></i>
                                                        Completar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       onclick="actualizarProgreso({{ $entrega->id }})">
                                                        <i class="bi bi-arrow-repeat me-2"></i>
                                                        Actualizar Progreso
                                                    </a>
                                                </li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-truck text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No se encontraron entregas</h5>
                    <p class="text-muted">Intenta ajustar los filtros de búsqueda o crea una nueva entrega</p>
                    <a href="{{ route('entregas.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>
                        Crear Primera Entrega
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Paginación -->
    @if($entregas->hasPages())
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    <x-pagination :paginator="$entregas" />
                </div>
            </div>
        </div>
    @endif
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
