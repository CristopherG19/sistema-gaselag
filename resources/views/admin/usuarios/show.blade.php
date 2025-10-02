@extends('layouts.app')

@section('title', 'Detalles del Usuario')

@push('styles')
<style>
    .user-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
        font-weight: bold;
        margin: 0 auto;
    }
    .info-card {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #495057;
    }
    .stat-label {
        color: #6c757d;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        Detalles del Usuario
                    </h2>
                    <p class="text-muted mb-0">Información completa del usuario</p>
                </div>
                <div>
                    <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-warning me-2">
                        <i class="bi bi-pencil me-1"></i>
                        Editar
                    </a>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="user-avatar mb-3">
                        {{ strtoupper(substr($usuario->nombre, 0, 1)) }}{{ strtoupper(substr($usuario->apellidos, 0, 1)) }}
                    </div>
                    <h4 class="mb-1">{{ $usuario->nombre }} {{ $usuario->apellidos }}</h4>
                    <p class="text-muted mb-3">{{ $usuario->correo }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-{{ $usuario->rol_color }} fs-6">
                            {{ $usuario->rol_texto }}
                        </span>
                        <span class="badge bg-{{ $usuario->activo ? 'success' : 'danger' }} fs-6">
                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    @if($usuario->id !== Auth::id())
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-warning btn-sm" 
                                    onclick="resetPassword({{ $usuario->id }})">
                                <i class="bi bi-key me-1"></i>
                                Resetear Contraseña
                            </button>
                            <button type="button" class="btn btn-outline-{{ $usuario->activo ? 'secondary' : 'success' }} btn-sm" 
                                    onclick="toggleActivo({{ $usuario->id }})">
                                <i class="bi bi-{{ $usuario->activo ? 'pause' : 'play' }} me-1"></i>
                                {{ $usuario->activo ? 'Desactivar' : 'Activar' }} Usuario
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Información de Acceso -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Información de Acceso
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Último Acceso:</strong>
                        <br>
                        @if($usuario->ultimo_acceso)
                            <span class="text-success">
                                {{ $usuario->ultimo_acceso->format('d/m/Y H:i') }}
                            </span>
                            <br>
                            <small class="text-muted">
                                {{ $usuario->ultimo_acceso->diffForHumans() }}
                            </small>
                        @else
                            <span class="text-muted">Nunca ha iniciado sesión</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Registrado:</strong>
                        <br>
                        <span class="text-info">
                            {{ $usuario->created_at->format('d/m/Y H:i') }}
                        </span>
                        <br>
                        <small class="text-muted">
                            {{ $usuario->created_at->diffForHumans() }}
                        </small>
                    </div>
                    <div class="mb-0">
                        <strong>Última Actualización:</strong>
                        <br>
                        <span class="text-warning">
                            {{ $usuario->updated_at->format('d/m/Y H:i') }}
                        </span>
                        <br>
                        <small class="text-muted">
                            {{ $usuario->updated_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Detallada -->
        <div class="col-lg-8">
            <!-- Notas -->
            @if($usuario->notas)
                <div class="info-card">
                    <h6 class="mb-3">
                        <i class="bi bi-sticky me-2"></i>
                        Notas Adicionales
                    </h6>
                    <p class="mb-0">{{ $usuario->notas }}</p>
                </div>
            @endif

            <!-- Estadísticas del Usuario -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-primary">{{ $usuario->remesas->count() }}</div>
                        <div class="stat-label">Remesas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-success">{{ $usuario->quejas->count() }}</div>
                        <div class="stat-label">Quejas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-warning">{{ $usuario->entregasAsignadas->count() }}</div>
                        <div class="stat-label">Entregas Asignadas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-info">{{ $usuario->entregasCreadas->count() }}</div>
                        <div class="stat-label">Entregas Creadas</div>
                    </div>
                </div>
            </div>

            <!-- Actividad Reciente -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-activity me-2"></i>
                        Actividad Reciente
                    </h6>
                </div>
                <div class="card-body">
                    @if($usuario->remesas->count() > 0 || $usuario->quejas->count() > 0)
                        <div class="timeline">
                            @foreach($usuario->remesas->take(5) as $remesa)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-marker bg-primary me-3"></div>
                                        <div>
                                            <h6 class="mb-1">Remesa Cargada</h6>
                                            <p class="text-muted mb-1">{{ $remesa->nombre_archivo }}</p>
                                            <small class="text-muted">{{ $remesa->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @foreach($usuario->quejas->take(3) as $queja)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-marker bg-warning me-3"></div>
                                        <div>
                                            <h6 class="mb-1">Queja Creada</h6>
                                            <p class="text-muted mb-1">{{ $queja->titulo }}</p>
                                            <small class="text-muted">{{ $queja->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clock-history text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No hay actividad reciente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para resetear contraseña -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resetear Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetPasswordForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Resetear Contraseña</button>
                </div>
            </form>
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

@push('styles')
<style>
    .timeline-marker {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-top: 0.5rem;
    }
    .timeline-item:not(:last-child) .timeline-marker::after {
        content: '';
        position: absolute;
        width: 2px;
        height: 30px;
        background: #dee2e6;
        left: 5px;
        top: 12px;
    }
</style>
@endpush

@push('scripts')
<script>
function resetPassword(usuarioId) {
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    document.getElementById('resetPasswordForm').action = `/admin/usuarios/${usuarioId}/reset-password`;
    modal.show();
}

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
