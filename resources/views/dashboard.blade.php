@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mt-5">
    <div class="row">
        <!-- Bienvenida -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3><i class="fas fa-tachometer-alt me-2"></i>Panel de Administración - {{ $usuario->nombre }} {{ $usuario->apellidos }}</h3>
                </div>
                <div class="card-body">
                    <p class="lead">¡Bienvenido al sistema de gestión de remesas GASELAG!</p>
                    <p class="text-muted">Desde aquí puedes gestionar todas las operaciones del sistema de remesas.</p>
                </div>
            </div>
        </div>

        <!-- Panel de Remesas -->
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h4><i class="fas fa-file-upload me-2"></i>Sistema de Remesas</h4>
                </div>
                <div class="card-body">
                    <p>Gestiona la carga y administración de archivos DBF de remesas.</p>
                    
                    <div class="row g-3">
                        <!-- Cargar Nueva Remesa -->
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                                    <h5>Cargar Nueva Remesa</h5>
                                    <p class="text-muted">Subir archivo DBF para procesamiento</p>
                                    <a href="{{ route('remesa.upload.form') }}" class="btn btn-primary">
                                        <i class="fas fa-upload me-1"></i>Cargar Archivo
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Ver Remesas -->
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-list fa-3x text-info mb-3"></i>
                                    <h5>Ver Remesas</h5>
                                    <p class="text-muted">Consultar remesas cargadas</p>
                                    <a href="{{ route('remesa.lista') }}" class="btn btn-info">
                                        <i class="fas fa-eye me-1"></i>Ver Lista
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <!-- Estadísticas -->
                        <div class="col-md-12">
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <h6><i class="fas fa-chart-bar me-2"></i>Estadísticas Rápidas</h6>
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="border-end">
                                                <h4 class="text-primary">{{ $estadisticas['total_remesas'] ?? 0 }}</h4>
                                                <small class="text-muted">Remesas Cargadas</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border-end">
                                                <h4 class="text-success">{{ number_format($estadisticas['total_registros'] ?? 0) }}</h4>
                                                <small class="text-muted">Registros Totales</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h4 class="text-info">{{ $estadisticas['ultima_carga'] ?? 'N/A' }}</h4>
                                            <small class="text-muted">Última Carga</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de Usuario -->
        <div class="col-lg-4 col-md-12">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h4><i class="fas fa-user me-2"></i>Mi Cuenta</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-4x text-muted"></i>
                    </div>
                    
                    <ul class="list-unstyled">
                        <li><strong>Nombre:</strong> {{ $usuario->nombre }}</li>
                        <li><strong>Apellidos:</strong> {{ $usuario->apellidos }}</li>
                        <li><strong>Email:</strong> {{ $usuario->correo }}</li>
                        <li><strong>Rol:</strong> 
                            <span class="badge bg-{{ $usuario->rol === 'admin' ? 'danger' : 'primary' }}">
                                {{ ucfirst($usuario->rol) }}
                            </span>
                        </li>
                    </ul>

                    <hr>

                    <!-- Acciones de Usuario -->
                    @if($usuario->rol === 'admin')
                    <div class="mb-3">
                        <a href="{{ route('admin.historial-passwords') }}" class="btn btn-outline-secondary btn-sm w-100 mb-2">
                            <i class="fas fa-history me-1"></i>Historial de Passwords
                        </a>
                    </div>
                    @endif

                    <!-- Cerrar Sesión -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Enlaces Rápidos -->
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5><i class="fas fa-rocket me-2"></i>Accesos Rápidos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="{{ route('remesa.upload.form') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus me-1"></i>Nueva Remesa
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="{{ route('remesa.lista') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-list me-1"></i>Ver Remesas
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="#" onclick="location.reload()" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-sync me-1"></i>Actualizar
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="#" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#helpModal">
                                <i class="fas fa-question-circle me-1"></i>Ayuda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Ayuda -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-question-circle me-2"></i>Ayuda del Sistema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>¿Cómo usar el sistema de remesas?</h6>
                <ol>
                    <li><strong>Cargar Nueva Remesa:</strong> Haz clic en "Cargar Archivo" para subir un archivo DBF</li>
                    <li><strong>Vista Previa:</strong> Revisa los datos antes de confirmar la carga</li>
                    <li><strong>Confirmar Carga:</strong> Guarda los datos definitivamente en el sistema</li>
                    <li><strong>Gestionar:</strong> Consulta, filtra y edita registros desde "Ver Remesas"</li>
                </ol>
                
                <h6 class="mt-3">Formatos Soportados</h6>
                <ul>
                    <li>Archivos DBF (hasta 50MB)</li>
                    <li>Codificación automática Windows-1252 → UTF-8</li>
                    <li>Procesamiento optimizado para archivos grandes</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection