@extends('layouts.app')

@section('title', 'Vista Previa - Remesa ' . $nombre_archivo)

@push('styles')
    <style>
        .table-container {
            max-height: 70vh;
            overflow-y: auto;
        }
        .table th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
        }
        .alert-warning {
            border-left: 5px solid #ffc107;
        }
        .alert-success {
            border-left: 5px solid #198754;
        }

        /* FIX ESPECÍFICO PARA PAGINACIÓN - SUPER IMPORTANTE */
        .pagination svg {
            width: 16px !important;
            height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            font-size: 16px !important;
        }

        .pagination .page-link svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
            font-size: 14px !important;
        }

        .pagination .page-link {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0.375rem 0.75rem !important;
        }

        /* Resetear cualquier herencia de tamaño */
        * svg {
            max-width: none !important;
        }

        .pagination * svg {
            max-width: 16px !important;
        }

        /* Control de iconos Bootstrap en general */
        .bi, i[class*="bi-"] {
            font-size: 16px !important;
            width: 16px !important;
            height: 16px !important;
            line-height: 1 !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Vista Previa: {{ $nombre_archivo }}</h4>
                            <small>Total de registros: {{ $totalRecords }} | NROCARGA: {{ $nro_carga ?? 'No detectado' }}</small>
                        </div>
                        <div>
                            <a href="{{ route('remesa.cancelar') }}" class="btn btn-outline-light me-2">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Selector de Centro de Servicio -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="bi bi-geo-alt"></i> Seleccionar Centro de Servicio</h6>
                            <p class="mb-3">Por favor, selecciona el centro de servicio SEDAPAL correspondiente a esta remesa:</p>
                            <form id="centroForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-select" id="centro_servicio" name="centro_servicio" required>
                                            <option value="">-- Selecciona un Centro de Servicio --</option>
                                            @foreach($centros_disponibles as $codigo => $nombre)
                                                <option value="{{ $codigo }}">{{ $nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-primary" id="verificarBtn" disabled>
                                            <i class="bi bi-search"></i> Verificar Duplicados
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div id="resultadoVerificacion" class="mt-3" style="display: none;"></div>
                        </div>

                        <!-- Tabla de datos -->
                        <div class="table-container">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        @foreach($columns as $column)
                                            <th>{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rows as $index => $row)
                                        <tr>
                                            <td>{{ (($currentPage - 1) * 50) + $index + 1 }}</td>
                                            @foreach($columns as $column)
                                                <td>{{ $row[$column] ?? '' }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($columns) + 1 }}" class="text-center text-muted">
                                                No hay datos para mostrar
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        @if($totalPages > 1)
                            <div class="mt-3">
                                <nav aria-label="Navegación de páginas">
                                    <ul class="pagination justify-content-center">
                                        {{-- Página anterior --}}
                                        @if($currentPage > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}">
                                                    <i class="bi bi-chevron-left"></i> Anterior
                                                </a>
                                            </li>
                                        @endif

                                        {{-- Páginas --}}
                                        @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                            </li>
                                        @endfor

                                        {{-- Página siguiente --}}
                                        @if($currentPage < $totalPages)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}">
                                                    Siguiente <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                                <p class="text-center text-muted">
                                    Página {{ $currentPage }} de {{ $totalPages }} 
                                    ({{ $totalRecords }} registros total)
                                </p>
                            </div>
                        @endif

                        <!-- Botones de acción -->
                        <div class="mt-4 d-flex justify-content-between">
                            <div>
                                <a href="{{ route('remesa.upload.form') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Cargar Otro Archivo
                                </a>
                            </div>
                            <div>
                                <form action="{{ route('remesa.cargar.sistema') }}" method="POST" class="d-inline" id="cargarForm">
                                    @csrf
                                    <input type="hidden" name="centro_servicio" id="centro_servicio_hidden" value="">
                                    <button type="submit" class="btn btn-success btn-lg" id="cargarBtn" disabled
                                            onclick="return confirm('¿Estás seguro de cargar estos {{ $totalRecords }} registros al sistema?')">
                                        <i class="bi bi-database-add"></i> Cargar al Sistema DIRECTO
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons-fix.css') }}">
    
    <script>
    // Debug info simple
    console.log('Preview cargado - SISTEMA CON SELECTOR DE CENTRO');
    console.log('Archivo:', '{{ $nombre_archivo ?? "" }}');
    console.log('Registros:', {{ $totalRecords ?? 0 }});
    console.log('Nro carga:', '{{ $nro_carga ?? "" }}');
    
    // Manejar selección de centro de servicio
    document.getElementById('centro_servicio').addEventListener('change', function() {
        const centroSelect = this;
        const verificarBtn = document.getElementById('verificarBtn');
        const resultadoDiv = document.getElementById('resultadoVerificacion');
        
        if (centroSelect.value) {
            verificarBtn.disabled = false;
            resultadoDiv.style.display = 'none';
        } else {
            verificarBtn.disabled = true;
            resultadoDiv.style.display = 'none';
            document.getElementById('cargarBtn').disabled = true;
        }
    });
    
    // Verificar duplicados
    document.getElementById('verificarBtn').addEventListener('click', function() {
        const centro = document.getElementById('centro_servicio').value;
        const nroCarga = '{{ $nro_carga }}';
        
        if (!centro) {
            alert('Por favor selecciona un centro de servicio');
            return;
        }
        
        // Mostrar loading
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Verificando...';
        
        // Hacer petición AJAX para verificar duplicados
        fetch('{{ route("remesa.verificar.duplicado") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                nro_carga: nroCarga,
                centro_servicio: centro
            })
        })
        .then(response => response.json())
        .then(data => {
            const resultadoDiv = document.getElementById('resultadoVerificacion');
            const cargarBtn = document.getElementById('cargarBtn');
            const centroHidden = document.getElementById('centro_servicio_hidden');
            
            if (data.duplicado) {
                // Hay duplicado
                resultadoDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h6><i class="bi bi-exclamation-triangle"></i> Duplicado Encontrado</h6>
                        <p>Ya existe una remesa con el número de carga <strong>${nroCarga}</strong> en el centro <strong>${centro}</strong>.</p>
                        <p class="mb-0"><strong>No se puede cargar este archivo.</strong></p>
                    </div>
                `;
                cargarBtn.disabled = true;
            } else {
                // No hay duplicado
                resultadoDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h6><i class="bi bi-check-circle"></i> Verificación Exitosa</h6>
                        <p class="mb-0">No se encontraron duplicados. El archivo está listo para cargar al sistema.</p>
                    </div>
                `;
                cargarBtn.disabled = false;
                centroHidden.value = centro;
            }
            
            resultadoDiv.style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al verificar duplicados. Inténtalo de nuevo.');
        })
        .finally(() => {
            // Restaurar botón
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-search"></i> Verificar Duplicados';
        });
    });
    </script>
@endsection