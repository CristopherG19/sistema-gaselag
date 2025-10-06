@extends('layouts.app')

@section('title', 'Cargar Remesa DBF')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-upload text-primary me-2"></i>
                        Cargar Archivo de Remesa DBF
                    </h2>
                    <p class="text-muted mb-0">Sube y procesa archivos DBF de remesas de forma eficiente</p>
                </div>
                <a href="{{ route('remesa.lista') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Lista
                </a>
            </div>

            <!-- Alerts -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> Se encontraron los siguientes problemas:
                    <div class="mt-2">
                        @foreach ($errors->all() as $error)
                            <div style="white-space: pre-line;">{{ $error }}</div>
                        @endforeach
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Main Upload Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-upload me-2"></i>
                        Cargar Archivos DBF
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Process Steps -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-1"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Seleccionar Archivos</h6>
                                    <small class="text-muted">Elige uno o más archivos DBF</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-light text-muted rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-2"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-muted">Vista Previa</h6>
                                    <small class="text-muted">Revisa los datos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-light text-muted rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-muted">Procesar</h6>
                                    <small class="text-muted">Carga al sistema</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('remesa.subir.pendiente') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        
                        <!-- Centro de Servicio Selection -->
                        <div class="mb-4">
                            <label for="centro_servicio" class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>
                                Centro de Servicio
                            </label>
                            <select class="form-select @error('centro_servicio') is-invalid @enderror" 
                                    id="centro_servicio" 
                                    name="centro_servicio" 
                                    required>
                                <option value="">Selecciona un centro de servicio</option>
                                @foreach(config('centros_servicio.centros') as $key => $centro)
                                    <option value="{{ $key }}" {{ old('centro_servicio', config('centros_servicio.default')) == $key ? 'selected' : '' }}>
                                        {{ $centro }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Todos los archivos subidos se asignarán a este centro de servicio.
                            </div>
                            @error('centro_servicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="archivos_dbf" class="form-label fw-semibold">
                                <i class="bi bi-file-earmark-binary me-1"></i>
                                Selecciona archivos DBF de remesa
                            </label>
                            <div class="input-group">
                                <input type="file" 
                                       class="form-control @error('archivos_dbf') is-invalid @enderror" 
                                       id="archivos_dbf" 
                                       name="archivos_dbf[]" 
                                       accept=".dbf"
                                       multiple
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('archivos_dbf').click()">
                                    <i class="bi bi-folder2-open me-1"></i>Elegir archivos
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Solo archivos .dbf (máximo 50MB por archivo). Puedes seleccionar múltiples archivos.
                            </div>
                            @error('archivos_dbf')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- File Info Display -->
                        <div id="fileInfo" class="alert alert-info d-none">
                            <h6 class="mb-3">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Archivos seleccionados:
                            </h6>
                            <div id="fileList"></div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('remesa.lista') }}" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-arrow-left me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-upload me-1"></i>Subir Archivos
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Process Information -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Proceso de Carga
                            </h6>
                        </div>
                        <div class="card-body">
                            <ol class="mb-0">
                                <li><strong>Subir archivos:</strong> Se suben y validan uno o más archivos DBF</li>
                                <li><strong>Procesamiento automático:</strong> Cada archivo se procesa individualmente</li>
                                <li><strong>Verificación:</strong> Se detectan duplicados automáticamente</li>
                                <li><strong>Resultado:</strong> Se muestran los archivos procesados y errores</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-check-circle me-2"></i>
                                Ventajas del Sistema
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>Subida múltiple de archivos DBF</li>
                                <li>Procesamiento automático en lote</li>
                                <li>Validación individual de cada archivo</li>
                                <li>Control de duplicados por archivo</li>
                                <li>Reporte detallado de resultados</li>
                                <li>Generación automática de números OC</li>
                            </ul>
                        </div>
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
    const fileInput = document.getElementById('archivos_dbf');
    const fileInfo = document.getElementById('fileInfo');
    const fileList = document.getElementById('fileList');
    const submitBtn = document.getElementById('submitBtn');
    const uploadForm = document.getElementById('uploadForm');

    // File selection handler
    fileInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        if (files.length > 0) {
            displayFileList(files);
            fileInfo.classList.remove('d-none');
        } else {
            fileInfo.classList.add('d-none');
        }
    });

    // Display multiple files
    function displayFileList(files) {
        fileList.innerHTML = '';
        files.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
            fileItem.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-file-earmark-binary text-primary me-2"></i>
                    <div>
                        <div class="fw-semibold">${file.name}</div>
                        <small class="text-muted">${formatFileSize(file.size)}</small>
                    </div>
                </div>
                <span class="badge bg-primary">${index + 1}</span>
            `;
            fileList.appendChild(fileItem);
        });
        
        // Add summary
        const totalSize = files.reduce((sum, file) => sum + file.size, 0);
        const summary = document.createElement('div');
        summary.className = 'mt-3 p-2 bg-light rounded';
        summary.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span><strong>Total: ${files.length} archivo${files.length > 1 ? 's' : ''}</strong></span>
                <span class="text-muted">${formatFileSize(totalSize)}</span>
            </div>
        `;
        fileList.appendChild(summary);
    }

    // Form submission handler
    uploadForm.addEventListener('submit', function(e) {
        const files = fileInput.files;
        if (files.length === 0) {
            e.preventDefault();
            alert('Por favor selecciona al menos un archivo.');
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="bi bi-hourglass-split me-1"></i>Subiendo ${files.length} archivo${files.length > 1 ? 's' : ''}...`;
    });

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endpush