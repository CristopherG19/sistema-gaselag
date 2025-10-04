@extends('layouts.app')

@section('title', 'Remesa ' . $nroCarga . ' - Registros')

@push('styles')
    <style>
        :root {
            --primary-color: #2563eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-900: #111827;
        }

        body {
            background-color: var(--gray-50);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            padding: 2rem 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            transition: all 0.2s ease;
        }

        .stats-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stats-label {
            color: var(--gray-600);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
        }

        .table-section {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
        }

        .table-header {
            background: var(--gray-100);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .custom-table {
            margin: 0;
        }

        .custom-table th {
            background: var(--gray-50);
            color: var(--gray-900);
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.75rem 0.625rem;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .custom-table td {
            padding: 0.625rem 0.625rem;
            border: none;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
            font-size: 0.875rem;
            line-height: 1.25;
            height: 3rem; /* Altura fija para todas las filas */
            white-space: nowrap; /* Evita salto de línea */
            overflow: hidden; /* Oculta texto que se desborda */
            text-overflow: ellipsis; /* Muestra ... cuando el texto es muy largo */
        }

        .custom-table tbody tr {
            height: 3rem; /* Altura fija para todas las filas */
        }

        .custom-table tbody tr:hover {
            background-color: var(--gray-50);
        }

        .compact-cell {
            padding: 0.625rem 0.625rem !important;
            height: 3rem !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .client-info {
            line-height: 1.25;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .client-name {
            font-weight: 600;
            font-size: 0.875rem;
            margin: 0;
            color: var(--gray-900);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .address-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge-modern {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.7rem;
            line-height: 1;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .btn-action {
            padding: 0.25rem 0.375rem;
            margin: 0 0.125rem;
            border-radius: 4px;
            font-size: 0.8rem;
            border: 1px solid var(--gray-200);
            background: white;
            color: var(--gray-600);
            transition: all 0.15s ease;
        }

        .btn-action:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .form-control-modern {
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            transition: all 0.15s ease;
        }

        .form-control-modern:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .pagination-modern .page-link {
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            padding: 0.5rem 0.75rem;
            margin: 0 0.125rem;
            border-radius: 6px;
        }

        .pagination-modern .page-link:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .pagination-modern .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray-600);
        }

        .empty-state-icon {
            font-size: 4rem;
            color: var(--gray-200);
            margin-bottom: 1rem;
        }
    </style>
@endpush

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('remesa.lista') }}" class="btn btn-outline-light me-3">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="h3 mb-1">Remesa {{ $nroCarga }}</h1>
                            <div class="d-flex align-items-center text-light opacity-75">
                                <i class="bi bi-file-text me-2"></i>
                                <span class="me-3">{{ $infoRemesa->nombre_archivo ?? 'N/A' }}</span>
                                <i class="bi bi-geo-alt me-2"></i>
                                <span>{{ $infoRemesa->centro_servicio ?? 'Sin centro' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-light text-dark fs-6 px-3 py-2">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ $infoRemesa->fecha_carga ? \Carbon\Carbon::parse($infoRemesa->fecha_carga)->format('d/m/Y H:i') : 'N/A' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <!-- Enlaces de navegación -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                <i class="bi bi-house"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('remesa.lista') }}" class="text-decoration-none">
                                <i class="bi bi-list-ul"></i> Lista de Remesas
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('remesas.general') }}" class="text-decoration-none">
                                <i class="bi bi-table"></i> Vista General
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-file-earmark-text"></i> Remesa {{ $nroCarga }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number">{{ number_format($estadisticas['total_registros']) }}</div>
                    <div class="stats-label">Total Registros</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-success">{{ number_format($estadisticas['registros_originales']) }}</div>
                    <div class="stats-label">Originales</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-warning">{{ number_format($estadisticas['registros_editados']) }}</div>
                    <div class="stats-label">Editados</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-info">{{ $estadisticas['total_centros'] }}</div>
                    <div class="stats-label">Centros</div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="bi bi-funnel me-2"></i>Filtros de Búsqueda
                </h5>
                <a href="{{ route('remesa.ver.registros', $nroCarga) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                </a>
            </div>
            
            <form method="GET" action="{{ route('remesa.ver.registros', $nroCarga) }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">NIS</label>
                        <input type="text" class="form-control form-control-modern" name="nis" 
                               value="{{ $filtros['nis'] ?? '' }}" placeholder="Buscar por NIS">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Medidor</label>
                        <input type="text" class="form-control form-control-modern" name="nromedidor" 
                               value="{{ $filtros['nromedidor'] ?? '' }}" placeholder="Número de medidor">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Cliente</label>
                        <input type="text" class="form-control form-control-modern" name="nomclie" 
                               value="{{ $filtros['nomclie'] ?? '' }}" placeholder="Nombre del cliente">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Centro</label>
                        <select class="form-select form-control-modern" name="centro_servicio">
                            <option value="">Todos los centros</option>
                            @foreach($centrosDisponibles as $centro)
                                <option value="{{ $centro }}" {{ ($filtros['centro_servicio'] ?? '') == $centro ? 'selected' : '' }}>
                                    {{ str_replace('SEDAPAL ', '', $centro) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-semibold">Mostrar</label>
                        <select class="form-select form-control-modern" name="per_page">
                            <option value="25" {{ ($filtros['per_page'] ?? '50') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($filtros['per_page'] ?? '50') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($filtros['per_page'] ?? '50') == '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de registros -->
        <div class="table-section">
            <div class="table-header">
                <h5 class="mb-0">
                    <i class="bi bi-table me-2"></i>Registros 
                    <span class="text-muted">
                        ({{ $registros->firstItem() ?? 0 }}-{{ $registros->lastItem() ?? 0 }} de {{ $registros->total() }})
                    </span>
                </h5>
            </div>
            
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th style="width: 100px;">OC</th>
                            <th style="width: 100px;">NIS</th>
                            <th style="width: 120px;">Medidor</th>
                            <th style="width: 200px;">Cliente</th>
                            <th>Dirección</th>
                            <th style="width: 140px;">Centro</th>
                            <th style="width: 120px;">Teléfono</th>
                            <th style="width: 100px;">Estado</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $registro)
                            <tr>
                                <td class="compact-cell fw-semibold text-primary">#{{ $registro->id }}</td>
                                <td class="compact-cell">
                                    <span class="badge bg-primary">{{ $registro->oc ?? 'N/A' }}</span>
                                </td>
                                <td class="compact-cell font-monospace">{{ $registro->nis ?? '-' }}</td>
                                <td class="compact-cell font-monospace">{{ $registro->nromedidor ?? '-' }}</td>
                                <td class="compact-cell">
                                    <div class="client-name" title="{{ $registro->nomclie ?? '-' }}">
                                        {{ $registro->nomclie ?? '-' }}
                                    </div>
                                </td>
                                <td class="compact-cell address-cell" title="{{ $registro->dir_proc ?? '-' }}">
                                    {{ $registro->dir_proc ?? '-' }}
                                </td>
                                <td class="compact-cell">
                                    <span class="badge badge-info badge-modern">
                                        {{ str_replace('SEDAPAL ', '', $registro->centro_servicio ?? 'Sin centro') }}
                                    </span>
                                </td>
                                <td class="compact-cell font-monospace">{{ $registro->tel_clie ?? '-' }}</td>
                                <td class="compact-cell">
                                    @if($registro->editado)
                                        <span class="badge badge-warning badge-modern">
                                            <i class="bi bi-pencil-square me-1"></i>Editado
                                        </span>
                                    @else
                                        <span class="badge badge-success badge-modern">
                                            <i class="bi bi-check-circle me-1"></i>Original
                                        </span>
                                    @endif
                                </td>
                                <td class="compact-cell">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('remesa.editar.registro', $registro->id) }}" 
                                           class="btn btn-outline-warning btn-sm" 
                                           title="Editar registro">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm" 
                                                title="Ver detalles"
                                                onclick="verDetalles({{ $registro->id }})">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @if($registro->editado)
                                            <a href="{{ route('remesa.ver.historial', $registro->id) }}" 
                                               class="btn btn-outline-secondary btn-sm" 
                                               title="Ver historial">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h6 class="text-muted">No se encontraron registros</h6>
                                        <p class="text-muted">No hay registros que coincidan con los filtros aplicados.</p>
                                        <a href="{{ route('remesa.ver.registros', $nroCarga) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Limpiar filtros
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación mejorada --}}
            <x-pagination :paginator="$registros" />
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarRegistro(id) {
            const url = '{{ route("remesa.editar.registro", ":id") }}'.replace(':id', id);
            window.location.href = url;
        }

        function verHistorial(id) {
            const url = '{{ route("remesa.ver.historial", ":id") }}'.replace(':id', id);
            window.location.href = url;
        }

        function verDetalles(id) {
            const url = '{{ route("remesa.ver.detalle", ":id") }}'.replace(':id', id);
            window.location.href = url;
        }
    </script>
@endsection