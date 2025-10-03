@extends('layouts.app')

@section('title', 'Detalle Completo del Registro')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
<style>
.detail-card {
    transition: all 0.3s ease;
}
.detail-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.field-label {
    font-weight: 600;
    color: #495057;
}
.field-value {
    color: #212529;
    word-break: break-word;
}
.section-header {
    border-left: 4px solid #007bff;
    padding-left: 15px;
    margin-bottom: 20px;
}
.empty-field {
    color: #6c757d;
    font-style: italic;
}
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-primary">
            <i class="bi bi-file-text me-2"></i>Detalle Completo del Registro
        </h2>
        <div>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('remesa.editar.registro', $registro->id) }}" class="btn btn-warning me-2">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>
            @endif
            <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Registros
            </a>
        </div>
    </div>

    <!-- Información Básica -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card detail-card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 section-header" style="border-left-color: white; color: white;">
                        <i class="bi bi-info-circle me-2"></i>Información Básica del Suministro
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="field-label">NIS</div>
                            <div class="field-value">{{ $registro->nis ?: 'No especificado' }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="field-label">Número de Medidor</div>
                            <div class="field-value">{{ $registro->nromedidor ?: 'No especificado' }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="field-label">Número de Carga</div>
                            <div class="field-value">{{ $registro->nro_carga }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="field-label">Orden de Compra (OC)</div>
                            <div class="field-value">{{ $registro->oc ?: 'No asignada' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card detail-card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 section-header" style="border-left-color: white; color: white;">
                        <i class="bi bi-person me-2"></i>Información del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="field-label">Nombre del Cliente</div>
                            <div class="field-value">{{ $registro->nomclie ?: 'No especificado' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="field-label">Teléfono</div>
                            <div class="field-value">{{ $registro->tel_clie ?: 'No especificado' }}</div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="field-label">Dirección de Proceso</div>
                            <div class="field-value">{{ $registro->dir_proc ?: 'No especificada' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fechas y Horarios -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card detail-card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0 section-header" style="border-left-color: #ffc107;">
                        <i class="bi bi-calendar me-2"></i>Fechas y Horarios
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="field-label">Fecha de Retiro</div>
                            <div class="field-value">
                                @if($registro->retfech)
                                    {{ \Carbon\Carbon::parse($registro->retfech)->format('d/m/Y') }}
                                @else
                                    <span class="empty-field">No especificada</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="field-label">Hora de Retiro</div>
                            <div class="field-value">
                                @if($registro->rethor)
                                    {{ sprintf('%02d:%02d', floor($registro->rethor), ($registro->rethor - floor($registro->rethor)) * 60) }}
                                @else
                                    <span class="empty-field">No especificada</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="field-label">Fecha Programada</div>
                            <div class="field-value">
                                @if($registro->fechaprog)
                                    {{ \Carbon\Carbon::parse($registro->fechaprog)->format('d/m/Y') }}
                                @else
                                    <span class="empty-field">No especificada</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="field-label">Hora Programada</div>
                            <div class="field-value">
                                @if($registro->horaprog)
                                    {{ sprintf('%02d:%02d', floor($registro->horaprog), ($registro->horaprog - floor($registro->horaprog)) * 60) }}
                                @else
                                    <span class="empty-field">No especificada</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información Técnica Adicional -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card detail-card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0 section-header" style="border-left-color: white; color: white;">
                        <i class="bi bi-gear me-2"></i>Información Técnica y del Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="field-label">Centro de Servicio</div>
                            <div class="field-value">{{ $registro->centro_servicio ?: 'No asignado' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="field-label">Fecha de Carga</div>
                            <div class="field-value">
                                @if($registro->fecha_carga)
                                    {{ \Carbon\Carbon::parse($registro->fecha_carga)->format('d/m/Y H:i:s') }}
                                @else
                                    <span class="empty-field">No disponible</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="field-label">Estado de Edición</div>
                            <div class="field-value">
                                @if($registro->editado)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-pencil-square me-1"></i>Editado
                                    </span>
                                    @if($registro->fecha_edicion)
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($registro->fecha_edicion)->format('d/m/Y H:i:s') }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-file-text me-1"></i>Original
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campos Adicionales del DBF (si existen) -->
    @php
        $camposAdicionales = [];
        $camposExcluidos = ['id', 'nis', 'nromedidor', 'nro_carga', 'oc', 'nomclie', 'tel_clie', 'dir_proc', 
                           'retfech', 'rethor', 'fechaprog', 'horaprog', 'centro_servicio', 'fecha_carga', 
                           'editado', 'fecha_edicion', 'usuario_id', 'created_at', 'updated_at'];
        
        foreach ($registro->getAttributes() as $campo => $valor) {
            if (!in_array($campo, $camposExcluidos) && !empty($valor)) {
                $camposAdicionales[$campo] = $valor;
            }
        }
    @endphp

    @if(!empty($camposAdicionales))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card detail-card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0 section-header" style="border-left-color: white; color: white;">
                        <i class="bi bi-database me-2"></i>Campos Adicionales del Archivo DBF
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($camposAdicionales as $campo => $valor)
                        <div class="col-md-4 mb-3">
                            <div class="field-label">{{ strtoupper($campo) }}</div>
                            <div class="field-value">{{ $valor }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Acciones -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="btn-group" role="group">
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('remesa.editar.registro', $registro->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil-square"></i> Editar Registro
                            </a>
                        @endif
                        <a href="{{ route('remesa.ver.historial', $registro->id) }}" class="btn btn-info">
                            <i class="bi bi-clock-history"></i> Ver Historial
                        </a>
                        <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" class="btn btn-secondary">
                            <i class="bi bi-list"></i> Volver a Registros
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Agregar animaciones suaves a las tarjetas
    const cards = document.querySelectorAll('.detail-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        }, index * 100);
    });
});
</script>
@endpush