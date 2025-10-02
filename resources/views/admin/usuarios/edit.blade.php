@extends('layouts.app')

@section('title', 'Editar Usuario')

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
    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: bold;
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
                        <i class="bi bi-person-gear text-primary me-2"></i>
                        Editar Usuario
                    </h2>
                    <p class="text-muted mb-0">Modifica la información del usuario</p>
                </div>
                <div>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
                @csrf
                @method('PUT')
                
                <!-- Información del Usuario -->
                <div class="form-section">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar me-3">
                            {{ strtoupper(substr($usuario->nombre, 0, 1)) }}{{ strtoupper(substr($usuario->apellidos, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $usuario->nombre }} {{ $usuario->apellidos }}</h5>
                            <small class="text-muted">ID: {{ $usuario->id }} | Registrado: {{ $usuario->created_at->format('d/m/Y') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Información Personal -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-person me-2"></i>
                        Información Personal
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('apellidos') is-invalid @enderror" 
                                       id="apellidos" name="apellidos" value="{{ old('apellidos', $usuario->apellidos) }}" required>
                                @error('apellidos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('correo') is-invalid @enderror" 
                               id="correo" name="correo" value="{{ old('correo', $usuario->correo) }}" required>
                        @error('correo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Información de Acceso -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-shield-lock me-2"></i>
                        Información de Acceso
                    </h5>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Nota:</strong> Deja la contraseña en blanco si no deseas cambiarla.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Mínimo 8 caracteres (opcional)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración del Usuario -->
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-gear me-2"></i>
                        Configuración del Usuario
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rol" class="form-label">Rol <span class="text-danger">*</span></label>
                                <select class="form-select @error('rol') is-invalid @enderror" id="rol" name="rol" required>
                                    <option value="">Seleccionar rol</option>
                                    <option value="admin" {{ old('rol', $usuario->rol) == 'admin' ? 'selected' : '' }}>Administrador</option>
                                    <option value="usuario" {{ old('rol', $usuario->rol) == 'usuario' ? 'selected' : '' }}>Usuario Normal</option>
                                    <option value="operario_campo" {{ old('rol', $usuario->rol) == 'operario_campo' ? 'selected' : '' }}>Operario de Campo</option>
                                </select>
                                @error('rol')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                           value="1" {{ old('activo', $usuario->activo) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="activo">
                                        Usuario Activo
                                    </label>
                                </div>
                                <div class="form-text">Los usuarios inactivos no pueden iniciar sesión</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notas" class="form-label">Notas Adicionales</label>
                        <textarea class="form-control @error('notas') is-invalid @enderror" 
                                  id="notas" name="notas" rows="3" 
                                  placeholder="Información adicional sobre el usuario...">{{ old('notas', $usuario->notas) }}</textarea>
                        @error('notas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>

        <!-- Panel de Información -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Información del Usuario
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Estado Actual:</strong>
                        <span class="badge bg-{{ $usuario->activo ? 'success' : 'danger' }} ms-2">
                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Rol Actual:</strong>
                        <span class="badge bg-{{ $usuario->rol_color }} ms-2">
                            {{ $usuario->rol_texto }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Último Acceso:</strong>
                        <br>
                        <small class="text-muted">
                            @if($usuario->ultimo_acceso)
                                {{ $usuario->ultimo_acceso->format('d/m/Y H:i') }}
                            @else
                                Nunca
                            @endif
                        </small>
                    </div>
                    <div class="mb-0">
                        <strong>Registrado:</strong>
                        <br>
                        <small class="text-muted">{{ $usuario->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
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
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No puedes modificar tu propio estado
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

// Validación de contraseña en tiempo real
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const confirmPassword = document.getElementById('password_confirmation');
    
    if (password.length > 0 && password.length < 8) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
    }
    
    // Verificar coincidencia de contraseñas
    if (confirmPassword.value && password !== confirmPassword.value) {
        confirmPassword.classList.add('is-invalid');
    } else {
        confirmPassword.classList.remove('is-invalid');
    }
});

document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password && password !== confirmPassword) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
    }
});
</script>
@endpush
