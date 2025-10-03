@extends('layouts.app')

@section('title', 'Editar Registro')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
<style>
.card {
    transition: all 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
}
.form-control:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.25);
}
.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
}
.badge {
    font-weight: 500;
}
.card-header {
    border-bottom: none !important;
}
.text-muted.small {
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.fw-semibold {
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-primary">
            <i class="bi bi-pencil-square me-2"></i>Editar Registro
        </h2>
        <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Registros
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- CAMPOS EDITABLES - PRIORIDAD MÁXIMA -->
        <div class="col-lg-7">
            <div class="card shadow border-0 h-100">
                <div class="card-header" style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white; border: none;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-pencil-square fs-4 me-3"></i>
                        <div>
                            <h5 class="mb-1">Campos Editables</h5>
                            <small class="opacity-75">Solo puedes modificar las siguientes fechas y horas</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('remesa.actualizar.registro', $registro->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Grupo de Retiro -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3 pb-2 border-bottom">
                                <i class="bi bi-arrow-up-circle text-primary me-2"></i>Información de Retiro
                            </h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="retfech" class="form-label">
                                        <i class="bi bi-calendar-date text-primary me-1"></i>Fecha de Retiro
                                    </label>
                                    <input type="date" class="form-control" id="retfech" name="retfech" 
                                           value="{{ old('retfech', $registro->retfech ? \Carbon\Carbon::parse($registro->retfech)->format('Y-m-d') : '') }}">
                                    @error('retfech')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-sm-6">
                                    <label for="rethor" class="form-label">
                                        <i class="bi bi-clock text-primary me-1"></i>Hora de Retiro
                                    </label>
                                    <input type="time" class="form-control" id="rethor" name="rethor" 
                                           value="{{ old('rethor', $registro->rethor ? sprintf('%02d:%02d', floor($registro->rethor), ($registro->rethor - floor($registro->rethor)) * 60) : '') }}"
                                           step="60">
                                    @error('rethor')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Grupo de Programación -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3 pb-2 border-bottom">
                                <i class="bi bi-calendar-check text-success me-2"></i>Información Programada
                            </h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="fechaprog" class="form-label">
                                        <i class="bi bi-calendar-check text-success me-1"></i>Fecha Programada
                                    </label>
                                    <input type="date" class="form-control" id="fechaprog" name="fechaprog" 
                                           value="{{ old('fechaprog', $registro->fechaprog ? \Carbon\Carbon::parse($registro->fechaprog)->format('Y-m-d') : '') }}">
                                    @error('fechaprog')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-sm-6">
                                    <label for="horaprog" class="form-label">
                                        <i class="bi bi-clock-history text-success me-1"></i>Hora Programada
                                    </label>
                                    <input type="time" class="form-control" id="horaprog" name="horaprog" 
                                           value="{{ old('horaprog', $registro->horaprog ? sprintf('%02d:%02d', floor($registro->horaprog), ($registro->horaprog - floor($registro->horaprog)) * 60) : '') }}"
                                           step="60">
                                    @error('horaprog')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Volver
                            </a>
                            <button type="submit" class="btn btn-warning px-4">
                                <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        
        <!-- SIDEBAR CON INFORMACIÓN CONTEXTUAL -->
        <div class="col-lg-5">
            <div class="row g-3">
                <!-- Información del Sistema -->
                <div class="col-12">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-info text-white border-0">
                            <h6 class="mb-0">
                                <i class="bi bi-database me-2"></i>Información del Sistema
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Nro. Carga</span>
                                    <span class="badge bg-primary fs-6">{{ $registro->nro_carga }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">OC</span>
                                    <span class="fw-semibold">{{ $registro->oc ?? 'No asignada' }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted small d-block">Centro de Servicio</span>
                                <span class="fw-semibold">{{ $registro->centro_servicio ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-0">
                                <span class="text-muted small d-block">Fecha de Carga</span>
                                <span class="fw-semibold">{{ \Carbon\Carbon::parse($registro->fecha_carga)->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado y Información del Cliente -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0 text-muted">
                                <i class="bi bi-person me-2"></i>Información del Cliente
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="text-muted small d-block">NIS</span>
                                <span class="fw-semibold">{{ $registro->nis ?: 'No especificado' }}</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted small d-block">Medidor</span>
                                <span class="fw-semibold">{{ $registro->nromedidor ?: 'No especificado' }}</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted small d-block">Cliente</span>
                                <span class="fw-semibold">{{ $registro->nomclie ?: 'No especificado' }}</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted small d-block">Teléfono</span>
                                <span class="fw-semibold">{{ $registro->tel_clie ?: 'No especificado' }}</span>
                            </div>
                            <div class="mb-0">
                                <span class="text-muted small d-block">Dirección</span>
                                <span class="fw-semibold small">{{ $registro->dir_proc ?: 'No especificada' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado del Registro -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center py-4">
                            @if($registro->editado)
                                <div class="mb-2">
                                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                        <i class="bi bi-pencil-square me-1"></i>Editado
                                    </span>
                                </div>
                                @if($registro->fecha_edicion)
                                    <small class="text-muted">
                                        Última edición:<br>
                                        {{ \Carbon\Carbon::parse($registro->fecha_edicion)->format('d/m/Y H:i:s') }}
                                    </small>
                                @endif
                            @else
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    <i class="bi bi-file-text me-1"></i>Original
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0 text-muted">
                                <i class="bi bi-lightning me-2"></i>Acciones Rápidas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('remesa.ver.detalle', $registro->id) }}" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-eye me-2"></i>Ver Detalle Completo
                                </a>
                                <a href="{{ route('remesa.ver.historial', $registro->id) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-clock-history me-2"></i>Ver Historial
                                </a>
                                <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-list me-2"></i>Todos los Registros
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection