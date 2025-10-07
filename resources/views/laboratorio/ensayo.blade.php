@extends('layouts.app')

@section('title', 'Ensayo de Medidor - Laboratorio')

@section('content')
<div class="container-fluid px-3">
    <!-- Header del ensayo -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center bg-white rounded p-3 shadow-sm">
                <div>
                    <h4 class="mb-1 text-primary">Ensayo Nº {{ $ensayo->id }}</h4>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-{{ $ensayo->estado == 'completado' ? 'success' : ($ensayo->estado == 'en_proceso' ? 'warning' : 'secondary') }} me-2">
                            {{ ucfirst(str_replace('_', ' ', $ensayo->estado)) }}
                        </span>
                        <small class="text-muted">Medidor: {{ $ensayo->nro_medidor }}</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @if($ensayo->estado === 'pendiente')
                        <form action="{{ route('laboratorio.iniciar-ensayo', $ensayo->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-play me-2"></i>Iniciar Ensayo
                            </button>
                        </form>
                    @endif
                    
                    @if($ensayo->estado === 'completado' && $ensayo->resultado_final === 'aprobado')
                        <a href="{{ route('laboratorio.certificado', $ensayo->id) }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-certificate me-2"></i>Certificado
                        </a>
                    @endif
                    
                    <a href="{{ route('laboratorio.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información del Medidor -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Información del Medidor</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted">Número de Medidor:</label>
                            <p class="h5 text-primary">{{ $ensayo->nro_medidor }}</p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted">Tipo de Ensayo:</label>
                            <p class="h6">{{ ucwords(str_replace('_', ' ', $ensayo->tipo_ensayo)) }}</p>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label class="text-muted">Marca:</label>
                            <p>{{ $ensayo->marca ?: 'No especificada' }}</p>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label class="text-muted">Modelo:</label>
                            <p>{{ $ensayo->modelo ?: 'No especificado' }}</p>
                        </div>
                        <div class="col-sm-4 mb-3">
                            <label class="text-muted">Calibre:</label>
                            <p>{{ $ensayo->calibre ? $ensayo->calibre . ' mm' : 'No especificado' }}</p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted">Clase Metrológica:</label>
                            <p>{{ $ensayo->clase_metrologia ? 'Clase ' . $ensayo->clase_metrologia : 'No especificada' }}</p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted">Año de Fabricación:</label>
                            <p>{{ $ensayo->ano_fabricacion ?: 'No especificado' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Ensayo -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Detalles del Ensayo</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted">Banco de Ensayo:</label>
                            <p class="h6">{{ $ensayo->bancoEnsayo->nombre }}</p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted">Técnico Responsable:</label>
                            <p>{{ $ensayo->tecnico->name }}</p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted">Fecha de Creación:</label>
                            <p>{{ $ensayo->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted">Fecha de Inicio:</label>
                            <p>{{ $ensayo->fecha_inicio ? $ensayo->fecha_inicio->format('d/m/Y H:i') : 'No iniciado' }}</p>
                        </div>
                        @if($ensayo->fecha_finalizacion)
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted">Fecha de Finalización:</label>
                            <p>{{ $ensayo->fecha_finalizacion->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="text-muted">Duración:</label>
                            <p>{{ $ensayo->duracion_formateada }}</p>
                        </div>
                        @endif
                    </div>
                    
                    @if($ensayo->resultado_final !== 'pendiente')
                    <div class="alert alert-{{ $ensayo->resultado_final === 'aprobado' ? 'success' : 'danger' }}">
                        <h6 class="alert-heading">
                            <i class="fas fa-{{ $ensayo->resultado_final === 'aprobado' ? 'check-circle' : 'times-circle' }} me-2"></i>
                            Resultado Final: {{ strtoupper($ensayo->resultado_final) }}
                        </h6>
                        @if($ensayo->nro_certificado)
                        <p class="mb-0">Certificado: <strong>{{ $ensayo->nro_certificado }}</strong></p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($ensayo->estado !== 'completado')
    <!-- Formulario de ensayo -->
    <form action="{{ route('laboratorio.actualizar-ensayo', $ensayo->id) }}" method="POST" id="formEnsayo">
        @csrf
        @method('PUT')
        
        <!-- Datos de Ensayo NMP 005:2018 -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Datos de Ensayo según NMP 005:2018</h5>
                    </div>
                    <div class="card-body">
                        <!-- Ensayo Q1 -->
                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-primary">Ensayo Q1 - Caudal Mínimo (Error máximo permitido: ±5%)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="caudal_q1" class="form-label">Caudal Q1 (L/h)</label>
                                        <input type="number" step="0.000001" class="form-control" id="caudal_q1" name="caudal_q1" 
                                               value="{{ old('caudal_q1', $ensayo->caudal_q1) }}" placeholder="0.000">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="volumen_ensayo_q1" class="form-label">Vol. Ensayo (L)</label>
                                        <input type="number" step="0.000001" class="form-control" id="volumen_ensayo_q1" name="volumen_ensayo_q1" 
                                               value="{{ old('volumen_ensayo_q1', $ensayo->volumen_ensayo_q1) }}" placeholder="0.000000">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="volumen_medidor_q1" class="form-label">Vol. Medidor (L)</label>
                                        <input type="number" step="0.000001" class="form-control" id="volumen_medidor_q1" name="volumen_medidor_q1" 
                                               value="{{ old('volumen_medidor_q1', $ensayo->volumen_medidor_q1) }}" placeholder="0.000000">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Error Q1 (%)</label>
                                        <input type="text" class="form-control bg-light" id="error_q1_display" readonly 
                                               value="{{ $ensayo->error_q1 ? number_format($ensayo->error_q1, 4) . '%' : 'Pendiente' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ensayo Q2 -->
                        <div class="card mb-3 border-warning">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-warning">Ensayo Q2 - Caudal Transitorio (Error máximo permitido: ±2%)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="caudal_q2" class="form-label">Caudal Q2 (L/h)</label>
                                        <input type="number" step="0.000001" class="form-control" id="caudal_q2" name="caudal_q2" 
                                               value="{{ old('caudal_q2', $ensayo->caudal_q2) }}" placeholder="0.000">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="volumen_ensayo_q2" class="form-label">Vol. Ensayo (L)</label>
                                        <input type="number" step="0.000001" class="form-control" id="volumen_ensayo_q2" name="volumen_ensayo_q2" 
                                               value="{{ old('volumen_ensayo_q2', $ensayo->volumen_ensayo_q2) }}" placeholder="0.000000">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="volumen_medidor_q2" class="form-label">Vol. Medidor (L)</label>
                                        <input type="number" step="0.000001" class="form-control" id="volumen_medidor_q2" name="volumen_medidor_q2" 
                                               value="{{ old('volumen_medidor_q2', $ensayo->volumen_medidor_q2) }}" placeholder="0.000000">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Error Q2 (%)</label>
                                        <input type="text" class="form-control bg-light" id="error_q2_display" readonly 
                                               value="{{ $ensayo->error_q2 ? number_format($ensayo->error_q2, 4) . '%' : 'Pendiente' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ensayo Q3 -->
                        <div class="card mb-3 border-danger">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-danger">Ensayo Q3 - Caudal Nominal (Error máximo permitido: ±2%)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="caudal_q3" class="form-label">Caudal Q3 (L/h)</label>
                                        <input type="number" step="0.000001" class="form-control" id="caudal_q3" name="caudal_q3" 
                                               value="{{ old('caudal_q3', $ensayo->caudal_q3) }}" placeholder="0.000">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="volumen_ensayo_q3" class="form-label">Vol. Ensayo (L)</label>
                                        <input type="number" step="0.000001" class="form-control" id="volumen_ensayo_q3" name="volumen_ensayo_q3" 
                                               value="{{ old('volumen_ensayo_q3', $ensayo->volumen_ensayo_q3) }}" placeholder="0.000000">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="volumen_medidor_q3" class="form-label">Vol. Medidor (L)</label>
                                        <input type="number" step="0.000001" class="form-control" id="volumen_medidor_q3" name="volumen_medidor_q3" 
                                               value="{{ old('volumen_medidor_q3', $ensayo->volumen_medidor_q3) }}" placeholder="0.000000">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Error Q3 (%)</label>
                                        <input type="text" class="form-control bg-light" id="error_q3_display" readonly 
                                               value="{{ $ensayo->error_q3 ? number_format($ensayo->error_q3, 4) . '%' : 'Pendiente' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Condiciones Ambientales y Observaciones -->
        <div class="row">
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-thermometer-half me-2"></i>Condiciones Ambientales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4 mb-3">
                                <label for="temperatura" class="form-label">Temperatura (°C)</label>
                                <input type="number" step="0.01" class="form-control" id="temperatura" name="temperatura" 
                                       value="{{ old('temperatura', $ensayo->temperatura) }}" placeholder="20.0">
                                <small class="text-muted">Rango: 15°C - 25°C</small>
                            </div>
                            <div class="col-sm-4 mb-3">
                                <label for="presion" class="form-label">Presión (kPa)</label>
                                <input type="number" step="0.01" class="form-control" id="presion" name="presion" 
                                       value="{{ old('presion', $ensayo->presion) }}" placeholder="101.3">
                                <small class="text-muted">Presión atmosférica</small>
                            </div>
                            <div class="col-sm-4 mb-3">
                                <label for="humedad" class="form-label">Humedad (%)</label>
                                <input type="number" step="0.01" class="form-control" id="humedad" name="humedad" 
                                       value="{{ old('humedad', $ensayo->humedad) }}" placeholder="60.0">
                                <small class="text-muted">Rango: 45% - 75%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Observaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones del Ensayo</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="4" 
                                      placeholder="Anote cualquier observación relevante durante el ensayo...">{{ old('observaciones', $ensayo->observaciones) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Guardar Datos
                        </button>
                        
                        @if($ensayo->estado === 'en_proceso')
                        <button type="button" class="btn btn-success btn-lg me-3" onclick="finalizarEnsayo()">
                            <i class="fas fa-check me-2"></i>Finalizar Ensayo
                        </button>
                        @endif
                        
                        <a href="{{ route('laboratorio.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif
</div>

<!-- Modal para finalizar ensayo -->
<div class="modal fade" id="modalFinalizarEnsayo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('laboratorio.finalizar-ensayo', $ensayo->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Finalizar Ensayo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="defectos_encontrados" class="form-label">Defectos Encontrados</label>
                        <textarea class="form-control" id="defectos_encontrados" name="defectos_encontrados" rows="3" 
                                  placeholder="Describa cualquier defecto o anomalía encontrada...">{{ old('defectos_encontrados', $ensayo->defectos_encontrados) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones_finales" class="form-label">Observaciones Finales</label>
                        <textarea class="form-control" id="observaciones_finales" name="observaciones" rows="3" 
                                  placeholder="Observaciones adicionales al finalizar...">{{ old('observaciones', $ensayo->observaciones) }}</textarea>
                    </div>
                    <div class="alert alert-info">
                        <strong>Nota:</strong> Al finalizar el ensayo se calculará automáticamente el resultado final basado en los errores medidos y los límites de la norma NMP 005:2018.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Finalizar Ensayo</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
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
    
    .badge {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
    
    @media (max-width: 768px) {
        .col-md-3, .col-md-4, .col-sm-4, .col-sm-6 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function calcularError(volumenEnsayo, volumenMedidor) {
    if (volumenEnsayo && volumenEnsayo > 0) {
        return ((volumenMedidor - volumenEnsayo) / volumenEnsayo) * 100;
    }
    return 0;
}

function actualizarErrores() {
    // Calcular Error Q1
    const volEnsayoQ1 = parseFloat(document.getElementById('volumen_ensayo_q1').value) || 0;
    const volMedidorQ1 = parseFloat(document.getElementById('volumen_medidor_q1').value) || 0;
    if (volEnsayoQ1 > 0) {
        const errorQ1 = calcularError(volEnsayoQ1, volMedidorQ1);
        document.getElementById('error_q1_display').value = errorQ1.toFixed(4) + '%';
    }
    
    // Calcular Error Q2
    const volEnsayoQ2 = parseFloat(document.getElementById('volumen_ensayo_q2').value) || 0;
    const volMedidorQ2 = parseFloat(document.getElementById('volumen_medidor_q2').value) || 0;
    if (volEnsayoQ2 > 0) {
        const errorQ2 = calcularError(volEnsayoQ2, volMedidorQ2);
        document.getElementById('error_q2_display').value = errorQ2.toFixed(4) + '%';
    }
    
    // Calcular Error Q3
    const volEnsayoQ3 = parseFloat(document.getElementById('volumen_ensayo_q3').value) || 0;
    const volMedidorQ3 = parseFloat(document.getElementById('volumen_medidor_q3').value) || 0;
    if (volEnsayoQ3 > 0) {
        const errorQ3 = calcularError(volEnsayoQ3, volMedidorQ3);
        document.getElementById('error_q3_display').value = errorQ3.toFixed(4) + '%';
    }
}

function finalizarEnsayo() {
    const modal = new bootstrap.Modal(document.getElementById('modalFinalizarEnsayo'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Agregar event listeners para cálculo automático de errores
    const camposVolumen = ['volumen_ensayo_q1', 'volumen_medidor_q1', 'volumen_ensayo_q2', 'volumen_medidor_q2', 'volumen_ensayo_q3', 'volumen_medidor_q3'];
    
    camposVolumen.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.addEventListener('input', actualizarErrores);
            elemento.addEventListener('change', actualizarErrores);
        }
    });
    
    // Calcular errores iniciales
    actualizarErrores();
});
</script>
@endpush
@endsection
