@extends('layouts.app')

@section('title', 'Gestión de Bancos - Laboratorio')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center bg-white rounded p-3 shadow-sm">
                <div>
                    <h4 class="mb-1 text-primary">Gestión de Bancos de Ensayo</h4>
                    <small class="text-muted">Monitoreo y administración de bancos de ensayo</small>
                </div>
                <a href="{{ route('laboratorio.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Lista de Bancos -->
    <div class="row">
        @foreach($bancos as $banco)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-{{ $banco->estado === 'activo' ? 'success' : ($banco->estado === 'mantenimiento' ? 'warning' : 'danger') }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $banco->nombre }}</h5>
                        <span class="badge bg-light text-dark">
                            {{ ucfirst($banco->estado) }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Información básica -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Información General</h6>
                        <p class="mb-1"><strong>Ubicación:</strong> {{ $banco->ubicacion }}</p>
                        <p class="mb-1"><strong>Responsable:</strong> {{ $banco->responsable_tecnico ?: 'No asignado' }}</p>
                        @if($banco->descripcion)
                            <p class="mb-0"><small class="text-muted">{{ $banco->descripcion }}</small></p>
                        @endif
                    </div>

                    <!-- Capacidad -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Capacidad</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Ocupación actual</span>
                            <span class="fw-bold">{{ $banco->ensayosEnProceso->count() }}/{{ $banco->capacidad_maxima }}</span>
                        </div>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-{{ $banco->ensayosEnProceso->count() >= $banco->capacidad_maxima ? 'danger' : 'success' }}" 
                                 style="width: {{ ($banco->ensayosEnProceso->count() / $banco->capacidad_maxima) * 100 }}%">
                                {{ round(($banco->ensayosEnProceso->count() / $banco->capacidad_maxima) * 100) }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $banco->capacidadDisponible() }} posiciones disponibles
                        </small>
                    </div>

                    <!-- Ensayos activos -->
                    @if($banco->ensayosEnProceso->count() > 0)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Ensayos Activos</h6>
                        <div class="list-group list-group-flush">
                            @foreach($banco->ensayosEnProceso->take(3) as $ensayo)
                            <div class="list-group-item px-0 py-2 border-0 bg-light rounded mb-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="fw-bold">{{ $ensayo->nro_medidor }}</small><br>
                                        <small class="text-muted">{{ $ensayo->tecnico->name }}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">
                                            {{ $ensayo->fecha_inicio ? $ensayo->fecha_inicio->diffForHumans() : 'Sin iniciar' }}
                                        </small><br>
                                        <a href="{{ route('laboratorio.ensayo', $ensayo->id) }}" class="btn btn-sm btn-outline-primary">
                                            Ver
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            
                            @if($banco->ensayosEnProceso->count() > 3)
                            <div class="text-center">
                                <small class="text-muted">
                                    y {{ $banco->ensayosEnProceso->count() - 3 }} más...
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Especificaciones técnicas -->
                    @if($banco->especificaciones_tecnicas)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Especificaciones Técnicas</h6>
                        <div class="row">
                            @foreach($banco->especificaciones_tecnicas as $key => $value)
                            <div class="col-6 mb-1">
                                <small>
                                    <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong><br>
                                    {{ $value }}
                                </small>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Calibración -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Estado de Calibración</h6>
                        @if($banco->ultima_calibracion)
                            <p class="mb-1">
                                <small><strong>Última:</strong> {{ $banco->ultima_calibracion->format('d/m/Y') }}</small>
                            </p>
                        @endif
                        @if($banco->proxima_calibracion)
                            <p class="mb-0">
                                <small><strong>Próxima:</strong> {{ $banco->proxima_calibracion->format('d/m/Y') }}</small>
                                @if($banco->necesitaCalibracion())
                                    <span class="badge bg-danger ms-2">Vencida</span>
                                @endif
                            </p>
                        @endif
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($banco->estaDisponible())
                                <span class="badge bg-success">Disponible</span>
                            @else
                                <span class="badge bg-warning">Ocupado</span>
                            @endif
                            
                            @if($banco->necesitaCalibracion())
                                <span class="badge bg-danger">Necesita Calibración</span>
                            @endif
                        </div>
                        
                        <div class="btn-group btn-group-sm">
                            <!-- Aquí podrías agregar botones para editar, mantenimiento, etc. -->
                            <button class="btn btn-outline-secondary" title="Editar banco" disabled>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-warning" title="Mantenimiento" disabled>
                                <i class="fas fa-tools"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Resumen general -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Resumen General del Laboratorio</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-primary mb-1">{{ $bancos->where('estado', 'activo')->count() }}</h3>
                                <small class="text-muted">Bancos Activos</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-success mb-1">{{ $bancos->sum('capacidad_maxima') }}</h3>
                                <small class="text-muted">Capacidad Total</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-warning mb-1">{{ $bancos->sum(function($banco) { return $banco->ensayosEnProceso->count(); }) }}</h3>
                                <small class="text-muted">Posiciones Ocupadas</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-info mb-1">{{ $bancos->sum(function($banco) { return $banco->capacidadDisponible(); }) }}</h3>
                                <small class="text-muted">Posiciones Disponibles</small>
                            </div>
                        </div>
                    </div>

                    <!-- Indicadores de estado -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">Estado por Banco</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($bancos as $banco)
                                <div class="d-flex align-items-center bg-light rounded px-3 py-2">
                                    <div class="badge bg-{{ $banco->estado === 'activo' ? 'success' : ($banco->estado === 'mantenimiento' ? 'warning' : 'danger') }} me-2">
                                        &nbsp;
                                    </div>
                                    <small>
                                        <strong>{{ $banco->nombre }}:</strong> 
                                        {{ $banco->ensayosEnProceso->count() }}/{{ $banco->capacidad_maxima }}
                                        ({{ round(($banco->ensayosEnProceso->count() / $banco->capacidad_maxima) * 100) }}%)
                                    </small>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .progress {
        border-radius: 10px;
    }
    
    .card {
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .list-group-item {
        border-radius: 6px !important;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    @media (max-width: 768px) {
        .col-lg-6, .col-xl-4 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush
@endsection
