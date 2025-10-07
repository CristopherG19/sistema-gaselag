@extends('layouts.app')

@section('title', 'Nuevo Ensayo - Laboratorio')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center bg-white rounded p-3 shadow-sm">
                <div>
                    <h4 class="mb-1 text-primary">Nuevo Ensayo de Medidor</h4>
                    <small class="text-muted">Complete la información del medidor y seleccione un banco disponible</small>
                </div>
                <a href="{{ route('laboratorio.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('laboratorio.crear-ensayo') }}" method="POST" id="formNuevoEnsayo">
        @csrf
        
        <div class="row">
            <!-- Información del Medidor -->
            <div class="col-lg-8 col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Información del Medidor</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Número de Medidor -->
                            <div class="col-md-6 mb-3">
                                <label for="nro_medidor" class="form-label fw-bold">Número de Medidor *</label>
                                <input type="text" 
                                       class="form-control form-control-lg @error('nro_medidor') is-invalid @enderror" 
                                       id="nro_medidor" 
                                       name="nro_medidor" 
                                       value="{{ old('nro_medidor') }}" 
                                       required 
                                       autofocus
                                       placeholder="Ej: 12345678">
                                @error('nro_medidor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tipo de Ensayo -->
                            <div class="col-md-6 mb-3">
                                <label for="tipo_ensayo" class="form-label fw-bold">Tipo de Ensayo *</label>
                                <select class="form-select form-select-lg @error('tipo_ensayo') is-invalid @enderror" 
                                        id="tipo_ensayo" 
                                        name="tipo_ensayo" 
                                        required>
                                    <option value="">Seleccione tipo...</option>
                                    <option value="verificacion_inicial" {{ old('tipo_ensayo') == 'verificacion_inicial' ? 'selected' : '' }}>
                                        Verificación Inicial
                                    </option>
                                    <option value="verificacion_periodica" {{ old('tipo_ensayo') == 'verificacion_periodica' ? 'selected' : '' }}>
                                        Verificación Periódica
                                    </option>
                                    <option value="reparacion" {{ old('tipo_ensayo') == 'reparacion' ? 'selected' : '' }}>
                                        Post-Reparación
                                    </option>
                                </select>
                                @error('tipo_ensayo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Marca -->
                            <div class="col-md-4 mb-3">
                                <label for="marca" class="form-label">Marca</label>
                                <input type="text" 
                                       class="form-control @error('marca') is-invalid @enderror" 
                                       id="marca" 
                                       name="marca" 
                                       value="{{ old('marca') }}"
                                       placeholder="Ej: Sensus, Itron">
                                @error('marca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Modelo -->
                            <div class="col-md-4 mb-3">
                                <label for="modelo" class="form-label">Modelo</label>
                                <input type="text" 
                                       class="form-control @error('modelo') is-invalid @enderror" 
                                       id="modelo" 
                                       name="modelo" 
                                       value="{{ old('modelo') }}"
                                       placeholder="Ej: 420PC, WP-Dynamic">
                                @error('modelo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Calibre -->
                            <div class="col-md-4 mb-3">
                                <label for="calibre" class="form-label">Calibre (mm)</label>
                                <select class="form-select @error('calibre') is-invalid @enderror" 
                                        id="calibre" 
                                        name="calibre">
                                    <option value="">Seleccione...</option>
                                    <option value="15" {{ old('calibre') == '15' ? 'selected' : '' }}>15 mm</option>
                                    <option value="20" {{ old('calibre') == '20' ? 'selected' : '' }}>20 mm</option>
                                    <option value="25" {{ old('calibre') == '25' ? 'selected' : '' }}>25 mm</option>
                                    <option value="32" {{ old('calibre') == '32' ? 'selected' : '' }}>32 mm</option>
                                    <option value="40" {{ old('calibre') == '40' ? 'selected' : '' }}>40 mm</option>
                                    <option value="50" {{ old('calibre') == '50' ? 'selected' : '' }}>50 mm</option>
                                </select>
                                @error('calibre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Clase Metrológica -->
                            <div class="col-md-6 mb-3">
                                <label for="clase_metrologia" class="form-label">Clase Metrológica</label>
                                <select class="form-select @error('clase_metrologia') is-invalid @enderror" 
                                        id="clase_metrologia" 
                                        name="clase_metrologia">
                                    <option value="">Seleccione...</option>
                                    <option value="A" {{ old('clase_metrologia') == 'A' ? 'selected' : '' }}>Clase A</option>
                                    <option value="B" {{ old('clase_metrologia') == 'B' ? 'selected' : '' }}>Clase B</option>
                                    <option value="C" {{ old('clase_metrologia') == 'C' ? 'selected' : '' }}>Clase C</option>
                                </select>
                                @error('clase_metrologia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Año de Fabricación -->
                            <div class="col-md-6 mb-3">
                                <label for="ano_fabricacion" class="form-label">Año de Fabricación</label>
                                <input type="number" 
                                       class="form-control @error('ano_fabricacion') is-invalid @enderror" 
                                       id="ano_fabricacion" 
                                       name="ano_fabricacion" 
                                       value="{{ old('ano_fabricacion') }}"
                                       min="1990" 
                                       max="{{ date('Y') }}"
                                       placeholder="{{ date('Y') }}">
                                @error('ano_fabricacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selección de Banco -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-industry me-2"></i>Banco de Ensayo</h5>
                    </div>
                    <div class="card-body">
                        @if($bancosDisponibles->count() > 0)
                            <div class="mb-3">
                                <label for="banco_ensayo_id" class="form-label fw-bold">Seleccionar Banco *</label>
                                <select class="form-select form-select-lg @error('banco_ensayo_id') is-invalid @enderror" 
                                        id="banco_ensayo_id" 
                                        name="banco_ensayo_id" 
                                        required>
                                    <option value="">Seleccione banco...</option>
                                    @foreach($bancosDisponibles as $banco)
                                        <option value="{{ $banco->id }}" 
                                                {{ old('banco_ensayo_id') == $banco->id ? 'selected' : '' }}
                                                data-capacidad="{{ $banco->capacidad_maxima }}"
                                                data-ocupados="{{ $banco->ensayosEnProceso->count() }}">
                                            {{ $banco->nombre }} 
                                            ({{ $banco->capacidadDisponible() }}/{{ $banco->capacidad_maxima }} disponible)
                                        </option>
                                    @endforeach
                                </select>
                                @error('banco_ensayo_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Información del banco seleccionado -->
                            <div id="info-banco" class="d-none">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Información del Banco</h6>
                                    <p class="mb-1"><strong>Ubicación:</strong> <span id="banco-ubicacion"></span></p>
                                    <p class="mb-1"><strong>Capacidad:</strong> <span id="banco-capacidad"></span></p>
                                    <p class="mb-0"><strong>Estado:</strong> <span id="banco-estado"></span></p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">No hay bancos disponibles</h6>
                                <p class="mb-0">Todos los bancos están ocupados o en mantenimiento. Intente más tarde.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Información sobre NMP 005:2018 -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Norma NMP 005:2018</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Límites de Error:</strong><br>
                            • Q1 (Caudal mínimo): ±5%<br>
                            • Q2 (Caudal transitorio): ±2%<br>
                            • Q3 (Caudal nominal): ±2%<br><br>
                            <strong>Condiciones de ensayo:</strong><br>
                            • Temperatura: 20°C ± 5°C<br>
                            • Presión: Atmosférica<br>
                            • Humedad: 45% - 75%
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-success btn-lg me-3" id="btnCrear">
                            <i class="fas fa-plus me-2"></i>Crear Ensayo
                        </button>
                        <a href="{{ route('laboratorio.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    /* Optimización para tablets */
    .form-control-lg, .form-select-lg {
        font-size: 1.1rem;
        min-height: 48px;
    }
    
    .form-control, .form-select {
        min-height: 44px;
        border-radius: 6px;
    }
    
    .btn-lg {
        min-height: 48px;
        font-size: 1.1rem;
        border-radius: 8px;
    }
    
    .card {
        border-radius: 10px;
    }
    
    .card-header {
        border-radius: 10px 10px 0 0 !important;
        font-weight: 600;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
    }
    
    .fw-bold {
        font-weight: 600 !important;
    }
    
    @media (max-width: 768px) {
        .col-md-6, .col-md-4 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectBanco = document.getElementById('banco_ensayo_id');
    const infoBanco = document.getElementById('info-banco');
    
    // Datos de los bancos (en un escenario real vendría del servidor)
    const bancosData = {
        @foreach($bancosDisponibles as $banco)
        {{ $banco->id }}: {
            ubicacion: '{{ $banco->ubicacion }}',
            capacidad: '{{ $banco->ensayosEnProceso->count() }}/{{ $banco->capacidad_maxima }}',
            estado: '{{ $banco->estado }}'
        },
        @endforeach
    };
    
    selectBanco.addEventListener('change', function() {
        const bancoId = this.value;
        
        if (bancoId && bancosData[bancoId]) {
            const banco = bancosData[bancoId];
            document.getElementById('banco-ubicacion').textContent = banco.ubicacion;
            document.getElementById('banco-capacidad').textContent = banco.capacidad;
            document.getElementById('banco-estado').textContent = banco.estado;
            infoBanco.classList.remove('d-none');
        } else {
            infoBanco.classList.add('d-none');
        }
    });
    
    // Validación del formulario
    const form = document.getElementById('formNuevoEnsayo');
    form.addEventListener('submit', function(e) {
        const nroMedidor = document.getElementById('nro_medidor').value.trim();
        const tipoEnsayo = document.getElementById('tipo_ensayo').value;
        const bancoId = document.getElementById('banco_ensayo_id').value;
        
        if (!nroMedidor || !tipoEnsayo || !bancoId) {
            e.preventDefault();
            alert('Por favor complete todos los campos obligatorios.');
            return false;
        }
        
        // Deshabilitar botón para evitar doble envío
        document.getElementById('btnCrear').disabled = true;
        document.getElementById('btnCrear').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
    });
    
    // Auto-completado inteligente para número de medidor
    const nroMedidorInput = document.getElementById('nro_medidor');
    nroMedidorInput.addEventListener('input', function() {
        // Convertir a mayúsculas y eliminar espacios
        this.value = this.value.toUpperCase().replace(/\s/g, '');
    });
});
</script>
@endpush
@endsection
