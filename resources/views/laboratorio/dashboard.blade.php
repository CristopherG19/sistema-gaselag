@extends('layouts.app')

@section('title', 'Laboratorio - Dashboard')

@section('content')
<div class="container-fluid px-3">
    <!-- Header compacto para tablet -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center bg-white rounded p-3 shadow-sm">
                <div>
                    <h4 class="mb-1 text-primary">Laboratorio de Ensayos</h4>
                    <small class="text-muted">{{ now()->format('d/m/Y H:i') }} - {{ Auth::user()->name }}</small>
                </div>
                <a href="{{ route('laboratorio.nuevo-ensayo') }}" class="btn btn-success btn-lg">
                    <i class="fas fa-plus me-2"></i>Nuevo Ensayo
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <h2 class="mb-1">{{ $estadisticas['ensayos_en_proceso'] }}</h2>
                    <p class="mb-0">En Proceso</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <h2 class="mb-1">{{ $estadisticas['ensayos_completados_hoy'] }}</h2>
                    <p class="mb-0">Completados Hoy</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <h2 class="mb-1">{{ $estadisticas['ensayos_aprobados_hoy'] }}</h2>
                    <p class="mb-0">Aprobados Hoy</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <h2 class="mb-1">{{ $estadisticas['capacidad_ocupada'] }}/{{ $estadisticas['capacidad_total'] }}</h2>
                    <p class="mb-0">Capacidad</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado de Bancos de Ensayo -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Estado de Bancos de Ensayo</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="actualizarEstadoBancos()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="row no-gutters" id="bancos-container">
                        @foreach($bancos as $banco)
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card border-left-primary h-100 mx-2">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-primary mb-0">{{ $banco->nombre }}</h6>
                                        <span class="badge badge-{{ $banco->estaDisponible() ? 'success' : 'warning' }}">
                                            {{ $banco->estaDisponible() ? 'Disponible' : 'Ocupado' }}
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Capacidad</small>
                                        <div class="progress mb-1" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $banco->ensayosEnProceso->count() >= $banco->capacidad_maxima ? 'danger' : 'success' }}" 
                                                 style="width: {{ ($banco->ensayosEnProceso->count() / $banco->capacidad_maxima) * 100 }}%">
                                                {{ $banco->ensayosEnProceso->count() }}/{{ $banco->capacidad_maxima }}
                                            </div>
                                        </div>
                                    </div>

                                    @if($banco->ensayosEnProceso->count() > 0)
                                    <div class="ensayos-activos">
                                        <small class="text-muted mb-2 d-block">Ensayos Activos:</small>
                                        @foreach($banco->ensayosEnProceso as $ensayo)
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                            <div>
                                                <strong>{{ $ensayo->nro_medidor }}</strong><br>
                                                <small class="text-muted">{{ $ensayo->tecnico->name }}</small>
                                            </div>
                                            <div class="text-right">
                                                <small class="text-muted">
                                                    {{ $ensayo->fecha_inicio ? $ensayo->fecha_inicio->diffForHumans() : 'Sin iniciar' }}
                                                </small><br>
                                                <a href="{{ route('laboratorio.ensayo', $ensayo->id) }}" class="btn btn-sm btn-outline-primary">
                                                    Ver
                                                </a>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                        <small>Banco disponible</small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Accesos Rápidos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('laboratorio.ensayos') }}" class="btn btn-outline-primary btn-lg btn-block">
                                <i class="fas fa-list-alt mb-2"></i><br>
                                Ver Ensayos
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('laboratorio.ensayos', ['estado' => 'en_proceso']) }}" class="btn btn-outline-warning btn-lg btn-block">
                                <i class="fas fa-clock mb-2"></i><br>
                                En Proceso
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('laboratorio.ensayos', ['resultado' => 'aprobado', 'fecha_desde' => today()->format('Y-m-d')]) }}" class="btn btn-outline-success btn-lg btn-block">
                                <i class="fas fa-check-circle mb-2"></i><br>
                                Aprobados Hoy
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('laboratorio.bancos') }}" class="btn btn-outline-info btn-lg btn-block">
                                <i class="fas fa-cogs mb-2"></i><br>
                                Gestión Bancos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Optimización para tablets */
    @media (min-width: 768px) and (max-width: 1024px) {
        .card-body {
            padding: 1rem 0.75rem;
        }
        
        .btn-lg {
            font-size: 1rem;
            padding: 0.75rem;
        }
        
        h4 {
            font-size: 1.5rem;
        }
        
        h2 {
            font-size: 2rem;
        }
    }
    
    /* Interfaz táctil mejorada */
    .btn {
        min-height: 44px;
        font-weight: 500;
    }
    
    .card {
        border-radius: 8px;
    }
    
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    
    .progress {
        border-radius: 10px;
    }
    
    .badge {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }
    
    /* Animaciones suaves */
    .card {
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .btn {
        transition: all 0.2s ease-in-out;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
</style>
@endpush

@push('scripts')
<script>
function actualizarEstadoBancos() {
    fetch('{{ route("laboratorio.estado-bancos") }}')
        .then(response => response.json())
        .then(data => {
            // Actualizar la información de los bancos
            console.log('Estado actualizado:', data);
            // Aquí podrías actualizar dinámicamente el contenido
            location.reload(); // Por simplicidad, recargamos la página
        })
        .catch(error => {
            console.error('Error al actualizar estado:', error);
        });
}

// Auto-actualización cada 30 segundos
setInterval(actualizarEstadoBancos, 30000);

// Notificación de ensayos completados
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        // Mostrar notificación de éxito
        const toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
    @endif
});
</script>
@endpush
@endsection
