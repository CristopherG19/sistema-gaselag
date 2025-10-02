@extends('layouts.app')

@section('title', 'Gestión de Quejas')

@push('styles')
<style>
    .queja-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-left: 4px solid transparent;
    }
    .queja-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .queja-pendiente { border-left-color: #ffc107; }
    .queja-en-proceso { border-left-color: #17a2b8; }
    .queja-resuelta { border-left-color: #28a745; }
    .queja-cancelada { border-left-color: #6c757d; }
    
    .priority-badge {
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
    .status-pendiente { background-color: #ffc107; }
    .status-en-proceso { background-color: #17a2b8; }
    .status-resuelta { background-color: #28a745; }
    .status-cancelada { background-color: #6c757d; }
    
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
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        Gestión de Quejas
                    </h2>
                    <p class="text-muted mb-0">Administra las quejas del sistema</p>
                </div>
                <div>
                    <a href="{{ route('quejas.create') }}" class="btn btn-warning">
                        <i class="bi bi-plus-circle me-1"></i>
                        Nueva Queja
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <form method="GET" action="{{ route('quejas.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="buscar" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="buscar" name="buscar" 
                               value="{{ request('buscar') }}" placeholder="Título o descripción">
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
                        <label for="prioridad" class="form-label">Prioridad</label>
                        <select class="form-select" id="prioridad" name="prioridad">
                            <option value="">Todas las prioridades</option>
                            @foreach($prioridades as $prioridad)
                                <option value="{{ $prioridad }}" {{ request('prioridad') == $prioridad ? 'selected' : '' }}>
                                    {{ ucfirst($prioridad) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>
                                    {{ ucfirst($tipo) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search me-1"></i>
                                Filtrar
                            </button>
                            <a href="{{ route('quejas.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lista de quejas -->
    <div class="row">
        @if($quejas->count() > 0)
            @foreach($quejas as $queja)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card queja-card queja-{{ str_replace('_', '-', $queja->estado) }}">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $queja->titulo }}</h6>
                                    <small class="text-muted">#{{ $queja->id }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge priority-badge bg-{{ $queja->prioridad_color }}">
                                        {{ $queja->prioridad_texto }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text text-muted mb-3">
                                {{ Str::limit($queja->descripcion, 100) }}
                            </p>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Estado:</small><br>
                                    <span class="status-indicator status-{{ str_replace('_', '-', $queja->estado) }}"></span>
                                    <span class="text-{{ $queja->estado_color }}">
                                        {{ $queja->estado_texto }}
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Tipo:</small><br>
                                    <span class="badge bg-secondary">{{ ucfirst($queja->tipo) }}</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">Creada por:</small><br>
                                <strong>{{ $queja->usuario->nombre }} {{ $queja->usuario->apellidos }}</strong>
                            </div>

                            @if($queja->asignadoA)
                                <div class="mb-3">
                                    <small class="text-muted">Asignada a:</small><br>
                                    <strong>{{ $queja->asignadoA->nombre }} {{ $queja->asignadoA->apellidos }}</strong>
                                </div>
                            @endif

                            <div class="mb-3">
                                <small class="text-muted">Fecha:</small><br>
                                <span>{{ $queja->fecha_creacion->format('d/m/Y H:i') }}</span>
                            </div>

                            @if($queja->remesa)
                                <div class="mb-3">
                                    <small class="text-muted">Remesa relacionada:</small><br>
                                    <a href="{{ route('remesa.ver.registros', $queja->remesa->nro_carga) }}" 
                                       class="text-decoration-none">
                                        {{ $queja->remesa->nombre_archivo }}
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('quejas.show', $queja) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>
                                    Ver Detalles
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if(Auth::user()->isAdmin())
                                            @if($queja->estado === 'pendiente')
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       onclick="asignarQueja({{ $queja->id }})">
                                                        <i class="bi bi-person-plus me-2"></i>
                                                        Asignar
                                                    </a>
                                                </li>
                                            @endif
                                        @endif
                                        
                                        @if($queja->puedeSerResuelta())
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="resolverQueja({{ $queja->id }})">
                                                    <i class="bi bi-check-circle me-2"></i>
                                                    Resolver
                                                </a>
                                            </li>
                                        @endif
                                        
                                        <li>
                                            <a class="dropdown-item" href="#" 
                                               onclick="cambiarEstado({{ $queja->id }})">
                                                <i class="bi bi-arrow-repeat me-2"></i>
                                                Cambiar Estado
                                            </a>
                                        </li>
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
                    <i class="bi bi-exclamation-triangle text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No se encontraron quejas</h5>
                    <p class="text-muted">Intenta ajustar los filtros de búsqueda o crea una nueva queja</p>
                    <a href="{{ route('quejas.create') }}" class="btn btn-warning">
                        <i class="bi bi-plus-circle me-1"></i>
                        Crear Primera Queja
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Paginación -->
    @if($quejas->hasPages())
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    <x-pagination :paginator="$quejas" />
                </div>
            </div>
        </div>
    @endif
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
                            @foreach($estados as $estado)
                                <option value="{{ $estado }}">{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                            @endforeach
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
