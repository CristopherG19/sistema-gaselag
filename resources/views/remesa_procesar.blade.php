@extends('layouts.app')

@section('title', 'Procesar Remesa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Procesar Remesa</h2>
                    <p class="text-muted mb-0">Paso 2: Configurar y procesar la remesa</p>
                </div>
                <div>
                    <a href="{{ route('remesa.lista') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver a Lista
                    </a>
                </div>
            </div>

            <!-- Información de la Remesa -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Información de la Remesa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Número de Carga:</strong><br>
                            <span class="badge bg-info fs-6">{{ $nro_carga }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Archivo:</strong><br>
                            <span class="text-break">{{ $nombre_archivo }}</span>
                        </div>
                        <div class="col-md-2">
                            <strong>Registros:</strong><br>
                            <span class="badge bg-secondary fs-6">{{ number_format($total_registros) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Estado:</strong><br>
                            <span class="badge bg-warning fs-6">
                                <i class="bi bi-clock"></i> Pendiente de Procesar
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Procesamiento -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-gear"></i> Configuración de Procesamiento
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('remesa.procesar') }}" id="procesarForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="centro_servicio" class="form-label">
                                        <i class="bi bi-building"></i> Centro de Servicio <span class="text-danger">*</span>
                                    </label>
                                    <select name="centro_servicio" id="centro_servicio" class="form-select" required>
                                        <option value="">Seleccione un centro de servicio</option>
                                        @foreach($centros_servicio as $centro)
                                            <option value="{{ $centro }}">{{ $centro }}</option>
                                        @endforeach
                                    </select>
                                    @error('centro_servicio')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-info-circle"></i> Información Adicional
                                    </label>
                                    <div class="alert alert-info mb-0">
                                        <small>
                                            <i class="bi bi-lightbulb"></i> 
                                            <strong>Tip:</strong> Seleccione el centro de servicio correspondiente para procesar correctamente la remesa.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview de Datos -->
                        <div class="mb-4">
                            <h6 class="mb-3">
                                <i class="bi bi-eye"></i> Vista Previa de Datos (Primeros 5 registros)
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>NIS</th>
                                            <th>Medidor</th>
                                            <th>Cliente</th>
                                            <th>Dirección</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($preview_data as $row)
                                            <tr>
                                                <td>{{ $row['NIS'] ?? 'N/A' }}</td>
                                                <td>{{ $row['NROMEDIDOR'] ?? 'N/A' }}</td>
                                                <td>{{ $row['NOMCLI'] ?? 'N/A' }}</td>
                                                <td>{{ Str::limit($row['DIR_PROC'] ?? 'N/A', 30) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('remesa.upload.form') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver a Subir
                                </a>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-danger me-2" onclick="cancelarProcesamiento()">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-success btn-lg" id="btnProcesar">
                                    <i class="bi bi-play-circle"></i> Procesar Remesa
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información de Procesamiento -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> ¿Qué sucede al procesar?
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="bi bi-1-circle-fill text-primary fs-1"></i>
                                <h6 class="mt-2">Validación</h6>
                                <small class="text-muted">Se validan todos los datos del archivo DBF</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="bi bi-2-circle-fill text-warning fs-1"></i>
                                <h6 class="mt-2">Generación OC</h6>
                                <small class="text-muted">Se generan números OC únicos para cada registro</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="bi bi-3-circle-fill text-success fs-1"></i>
                                <h6 class="mt-2">Carga Final</h6>
                                <small class="text-muted">Se cargan todos los registros al sistema</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelarProcesamiento() {
    if (confirm('¿Está seguro de que desea cancelar el procesamiento? Se perderán los datos temporales.')) {
        window.location.href = "{{ route('remesa.upload.form') }}";
    }
}

document.getElementById('procesarForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('btnProcesar');
    const centro = document.getElementById('centro_servicio').value;
    
    if (!centro) {
        e.preventDefault();
        alert('Por favor seleccione un centro de servicio');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    // Mostrar mensaje de progreso
    const progressDiv = document.createElement('div');
    progressDiv.className = 'alert alert-info mt-3';
    progressDiv.innerHTML = '<i class="bi bi-info-circle"></i> Procesando remesa... Esto puede tomar varios minutos.';
    this.appendChild(progressDiv);
});
</script>
@endsection
