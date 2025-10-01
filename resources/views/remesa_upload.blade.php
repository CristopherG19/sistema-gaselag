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
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                        Cargar Archivo DBF
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
                                    <h6 class="mb-1">Seleccionar Archivo</h6>
                                    <small class="text-muted">Elige tu archivo DBF</small>
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
                        
                        <div class="mb-4">
                            <label for="archivo_dbf" class="form-label fw-semibold">
                                <i class="bi bi-file-earmark-binary me-1"></i>
                                Selecciona archivo DBF de remesa
                            </label>
                            <div class="input-group">
                                <input type="file" 
                                       class="form-control @error('archivo_dbf') is-invalid @enderror" 
                                       id="archivo_dbf" 
                                       name="archivo_dbf" 
                                       accept=".dbf"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('archivo_dbf').click()">
                                    <i class="bi bi-folder2-open me-1"></i>Elegir archivo
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Solo archivos .dbf (máximo 50MB)
                            </div>
                            @error('archivo_dbf')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- File Info Display -->
                        <div id="fileInfo" class="alert alert-info d-none">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                <div>
                                    <strong id="fileName"></strong>
                                    <br>
                                    <small id="fileSize" class="text-muted"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('remesa.lista') }}" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-arrow-left me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-upload me-1"></i>Subir Archivo
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
                                <li><strong>Subir archivo:</strong> Se sube y valida el archivo DBF</li>
                                <li><strong>Vista previa:</strong> Revisa los datos antes de procesar</li>
                                <li><strong>Configurar:</strong> Selecciona centro de servicio</li>
                                <li><strong>Procesar:</strong> Se carga al sistema con números OC</li>
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
                                <li>Validación automática de archivos</li>
                                <li>Vista previa antes de procesar</li>
                                <li>Control de duplicados</li>
                                <li>Generación automática de números OC</li>
                                <li>Historial completo de cambios</li>
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
    const fileInput = document.getElementById('archivo_dbf');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');
    const uploadForm = document.getElementById('uploadForm');

    // File selection handler
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.classList.remove('d-none');
        } else {
            fileInfo.classList.add('d-none');
        }
    });

    // Form submission handler
    uploadForm.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Subiendo...';
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