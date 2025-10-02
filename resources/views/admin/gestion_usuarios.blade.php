@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@push('styles')
<style>
    .user-card {
        transition: transform 0.2s;
    }
    .user-card:hover {
        transform: translateY(-2px);
    }
    .role-badge {
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
    .status-active { background-color: #28a745; }
    .status-inactive { background-color: #dc3545; }
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
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        Gestión de Usuarios
                    </h2>
                    <p class="text-muted mb-0">Administra los usuarios del sistema</p>
                </div>
                <div>
                    <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>
                        Nuevo Usuario
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('usuarios.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="buscar" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="buscar" name="buscar" 
                                   value="{{ request('buscar') }}" placeholder="Nombre, apellidos o correo">
                        </div>
                        <div class="col-md-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol">
                                <option value="">Todos los roles</option>
                                <option value="admin" {{ request('rol') == 'admin' ? 'selected' : '' }}>Administrador</option>
                                <option value="usuario" {{ request('rol') == 'usuario' ? 'selected' : '' }}>Usuario Normal</option>
                                <option value="operario_campo" {{ request('rol') == 'operario_campo' ? 'selected' : '' }}>Operario de Campo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Todos los estados</option>
                                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activos</option>
                                <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
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
    </div>

    <!-- Lista de usuarios -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Usuarios del Sistema</h5>
                        <span class="badge bg-primary">{{ $usuarios->total() }} usuarios</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($usuarios->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Último Acceso</th>
                                        <th>Registrado</th>
                                        <th width="150">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($usuarios as $usuario)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="bi bi-person-fill text-muted"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $usuario->nombre }} {{ $usuario->apellidos }}</h6>
                                                        <small class="text-muted">{{ $usuario->correo }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge role-badge bg-{{ $usuario->rol_color }}">
                                                    {{ $usuario->rol_texto }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-indicator status-{{ $usuario->activo ? 'active' : 'inactive' }}"></span>
                                                <span class="text-{{ $usuario->activo ? 'success' : 'danger' }}">
                                                    {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($usuario->ultimo_acceso)
                                                    <small class="text-muted">
                                                        {{ $usuario->ultimo_acceso->format('d/m/Y H:i') }}
                                                    </small>
                                                @else
                                                    <small class="text-muted">Nunca</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $usuario->created_at->format('d/m/Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('usuarios.show', $usuario) }}" 
                                                       class="btn btn-outline-info" title="Ver detalles">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('usuarios.edit', $usuario) }}" 
                                                       class="btn btn-outline-warning" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    @if($usuario->id !== Auth::id())
                                                        <button type="button" class="btn btn-outline-{{ $usuario->activo ? 'secondary' : 'success' }}" 
                                                                onclick="toggleActivo({{ $usuario->id }})" 
                                                                title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
                                                            <i class="bi bi-{{ $usuario->activo ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No se encontraron usuarios</h5>
                            <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                        </div>
                    @endif
                </div>
                @if($usuarios->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-center">
                            <x-pagination :paginator="$usuarios" />
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar acciones -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Confirmar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleActivo(usuarioId) {
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    document.getElementById('confirmMessage').textContent = '¿Estás seguro de que deseas cambiar el estado de este usuario?';
    document.getElementById('confirmAction').onclick = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/usuarios/${usuarioId}/toggle-activo`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    };
    modal.show();
}
</script>
@endpush
