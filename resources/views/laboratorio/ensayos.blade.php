@extends('layouts.app')

@section('title', 'Lista de Ensayos - Laboratorio')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center bg-white rounded p-3 shadow-sm">
                <div>
                    <h4 class="mb-1 text-primary">Lista de Ensayos</h4>
                    <small class="text-muted">Gestión y seguimiento de ensayos de medidores</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('laboratorio.nuevo-ensayo') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Nuevo Ensayo
                    </a>
                    <a href="{{ route('laboratorio.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('laboratorio.ensayos') }}" id="filtrosForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="nro_medidor" class="form-label">Nº Medidor</label>
                                <input type="text" class="form-control" id="nro_medidor" name="nro_medidor" 
                                       value="{{ request('nro_medidor') }}" placeholder="Buscar por número...">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                    <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completado</option>
                                    <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="resultado" class="form-label">Resultado</label>
                                <select class="form-select" id="resultado" name="resultado">
                                    <option value="">Todos los resultados</option>
                                    <option value="aprobado" {{ request('resultado') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="rechazado" {{ request('resultado') == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                                    <option value="pendiente" {{ request('resultado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                </select>
                            </div>
                            
                            @if(Auth::user()->isAdmin())
                            <div class="col-md-3 mb-3">
                                <label for="tecnico_id" class="form-label">Técnico</label>
                                <select class="form-select" id="tecnico_id" name="tecnico_id">
                                    <option value="">Todos los técnicos</option>
                                    @foreach($tecnicos as $tecnico)
                                        <option value="{{ $tecnico->id }}" {{ request('tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                            {{ $tecnico->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                       value="{{ request('fecha_desde') }}">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                       value="{{ request('fecha_hasta') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Buscar
                                    </button>
                                    <a href="{{ route('laboratorio.ensayos') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-eraser me-2"></i>Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Resultados ({{ $ensayos->total() }} ensayos)</h5>
                    <div class="d-flex gap-2">
                        <!-- Aquí podrías agregar botones para exportar, etc. -->
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($ensayos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Medidor</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Resultado</th>
                                        <th>Banco</th>
                                        <th>Técnico</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ensayos as $ensayo)
                                    <tr>
                                        <td class="fw-bold">#{{ $ensayo->id }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $ensayo->nro_medidor }}</div>
                                            @if($ensayo->marca)
                                                <small class="text-muted">{{ $ensayo->marca }} {{ $ensayo->modelo }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ ucwords(str_replace('_', ' ', $ensayo->tipo_ensayo)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $ensayo->estado == 'completado' ? 'success' : ($ensayo->estado == 'en_proceso' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst(str_replace('_', ' ', $ensayo->estado)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($ensayo->resultado_final === 'pendiente')
                                                <span class="badge bg-secondary">Pendiente</span>
                                            @elseif($ensayo->resultado_final === 'aprobado')
                                                <span class="badge bg-success">Aprobado</span>
                                            @else
                                                <span class="badge bg-danger">Rechazado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $ensayo->bancoEnsayo->nombre }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $ensayo->tecnico->name }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $ensayo->created_at->format('d/m/Y') }}</small><br>
                                            <small class="text-muted">{{ $ensayo->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                <a href="{{ route('laboratorio.ensayo', $ensayo->id) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>Ver
                                                </a>
                                                
                                                @if($ensayo->estado === 'completado' && $ensayo->resultado_final === 'aprobado')
                                                    <a href="{{ route('laboratorio.certificado', $ensayo->id) }}" class="btn btn-outline-success btn-sm" target="_blank">
                                                        <i class="fas fa-certificate me-1"></i>Certificado
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $ensayos->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron ensayos</h5>
                            <p class="text-muted">Intente ajustar los filtros de búsqueda o 
                                <a href="{{ route('laboratorio.nuevo-ensayo') }}">crear un nuevo ensayo</a>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    @if($ensayos->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Estadísticas de Resultados Actuales</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-primary mb-1">{{ $ensayos->where('estado', 'pendiente')->count() }}</h4>
                                <small class="text-muted">Pendientes</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-warning mb-1">{{ $ensayos->where('estado', 'en_proceso')->count() }}</h4>
                                <small class="text-muted">En Proceso</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-success mb-1">{{ $ensayos->where('resultado_final', 'aprobado')->count() }}</h4>
                                <small class="text-muted">Aprobados</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-danger mb-1">{{ $ensayos->where('resultado_final', 'rechazado')->count() }}</h4>
                                <small class="text-muted">Rechazados</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        background-color: #f8f9fa;
    }
    
    .table td {
        vertical-align: middle;
        font-size: 0.875rem;
    }
    
    .btn-group-vertical .btn {
        margin-bottom: 2px;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.6rem;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.8rem;
        }
        
        .btn-group-vertical .btn {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit del formulario cuando cambian los selects
    const selectores = ['estado', 'resultado', 'tecnico_id'];
    selectores.forEach(function(selector) {
        const elemento = document.getElementById(selector);
        if (elemento) {
            elemento.addEventListener('change', function() {
                document.getElementById('filtrosForm').submit();
            });
        }
    });
    
    // Validación de fechas
    const fechaDesde = document.getElementById('fecha_desde');
    const fechaHasta = document.getElementById('fecha_hasta');
    
    if (fechaDesde && fechaHasta) {
        fechaDesde.addEventListener('change', function() {
            fechaHasta.min = this.value;
        });
        
        fechaHasta.addEventListener('change', function() {
            fechaDesde.max = this.value;
        });
    }
});
</script>
@endpush
@endsection
