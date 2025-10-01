@extends('layouts.app')

@section('title', 'Vista General de Remesas')

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

        /* Control de tamaño de iconos - Solución robusta */
        .bi, i[class*="bi-"] {
            font-size: 16px !important;
            width: 16px !important;
            height: 16px !important;
            line-height: 1 !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
        
        .btn .bi, .btn i[class*="bi-"] {
            font-size: 14px !important;
            width: 14px !important;
            height: 14px !important;
            line-height: 1 !important;
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
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: var(--gray-600);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .filters-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            margin-bottom: 2rem;
        }

        .form-control-modern {
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-control-modern:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-modern {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .btn-primary-modern {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .btn-primary-modern:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        .results-card {
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
            justify-content: space-between;
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
            padding: 0.75rem 0.625rem;
            border-bottom: 1px solid var(--gray-200);
            font-size: 0.875rem;
            vertical-align: middle;
            height: 3rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }

        .custom-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .badge-oc {
            background-color: var(--primary-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-center {
            background-color: var(--success-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
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
            color: white;
        }

        .breadcrumb {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            height: 1.5rem;
            line-height: 1.5rem;
        }

        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .table-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
        }
    </style>
@endpush

@section('content')

<div class="page-header">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a href="{{ route('remesa.lista') }}" class="btn btn-outline-light me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-table me-2"></i>Vista General de Remesas
                </h1>
                <p class="mb-0 opacity-75">Consulta todos tus registros con filtros avanzados</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-4">
    <!-- Breadcrumbs -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
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
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="bi bi-table"></i> Vista General
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
                <div class="stats-number">{{ $estadisticas['total_remesas'] }}</div>
                <div class="stats-label">Remesas Cargadas</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number">{{ $estadisticas['total_centros'] }}</div>
                <div class="stats-label">Centros de Servicio</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number">{{ number_format($estadisticas['registros_editados']) }}</div>
                <div class="stats-label">Registros Editados</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
        <h5 class="mb-3">
            <i class="bi bi-funnel me-2"></i>Filtros de Búsqueda
        </h5>

        <form method="GET" action="{{ route('remesas.general') }}" id="filtros-form">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="centro_servicio" class="form-label">Centro de Servicio</label>
                    <select name="centro_servicio" id="centro_servicio" class="form-control form-control-modern">
                        <option value="">Todos los centros</option>
                        @foreach($centrosDisponibles as $centro)
                            <option value="{{ $centro }}" {{ ($filtros['centro_servicio'] ?? '') == $centro ? 'selected' : '' }}>
                                {{ $centro }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="nro_carga" class="form-label">Nro. de Remesa</label>
                    <input type="text" 
                           name="nro_carga" 
                           id="nro_carga" 
                           class="form-control form-control-modern" 
                           value="{{ $filtros['nro_carga'] ?? '' }}" 
                           placeholder="Buscar por número...">
                </div>

                <div class="form-group">
                    <label for="oc" class="form-label">OC</label>
                    <input type="text" 
                           name="oc" 
                           id="oc" 
                           class="form-control form-control-modern" 
                           value="{{ $filtros['oc'] ?? '' }}" 
                           placeholder="Buscar por OC...">
                </div>

                <div class="form-group">
                    <label for="nis" class="form-label">NIS (Suministro)</label>
                    <input type="text" 
                           name="nis" 
                           id="nis" 
                           class="form-control form-control-modern" 
                           value="{{ $filtros['nis'] ?? '' }}" 
                           placeholder="Buscar por NIS...">
                </div>

                <div class="form-group">
                    <label for="nromedidor" class="form-label">Nro. Medidor</label>
                    <input type="text" 
                           name="nromedidor" 
                           id="nromedidor" 
                           class="form-control form-control-modern" 
                           value="{{ $filtros['nromedidor'] ?? '' }}" 
                           placeholder="Buscar por medidor...">
                </div>

                <div class="form-group">
                    <label for="nomclie" class="form-label">Nombre Cliente</label>
                    <input type="text" 
                           name="nomclie" 
                           id="nomclie" 
                           class="form-control form-control-modern" 
                           value="{{ $filtros['nomclie'] ?? '' }}" 
                           placeholder="Buscar por cliente...">
                </div>

                <div class="form-group">
                    <label for="fecha_desde" class="form-label">Fecha Desde</label>
                    <input type="date" 
                           name="fecha_desde" 
                           id="fecha_desde" 
                           class="form-control form-control-modern" 
                           value="{{ $filtros['fecha_desde'] ?? '' }}">
                </div>

                <div class="form-group">
                    <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                    <input type="date" 
                           name="fecha_hasta" 
                           id="fecha_hasta" 
                           class="form-control form-control-modern" 
                           value="{{ $filtros['fecha_hasta'] ?? '' }}">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-modern btn-primary-modern">
                    <i class="bi bi-search me-1"></i>Buscar
                </button>
                <a href="{{ route('remesas.general') }}" class="btn btn-modern btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Limpiar Filtros
                </a>
            </div>
        </form>
    </div>

    <!-- Resultados -->
    <div class="results-card">
        <div class="table-header">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>Registros 
                <span class="badge bg-primary ms-2">{{ number_format($registros->total()) }}</span>
            </h5>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">
                    Mostrando {{ $registros->firstItem() ?? 0 }} - {{ $registros->lastItem() ?? 0 }} 
                    de {{ number_format($registros->total()) }} registros
                </span>
                <select name="per_page" onchange="changePerPage(this.value)" class="form-select form-select-sm" style="width: auto;">
                    <option value="25" {{ ($filtros['per_page'] ?? 25) == 25 ? 'selected' : '' }}>25 por página</option>
                    <option value="50" {{ ($filtros['per_page'] ?? 25) == 50 ? 'selected' : '' }}>50 por página</option>
                    <option value="100" {{ ($filtros['per_page'] ?? 25) == 100 ? 'selected' : '' }}>100 por página</option>
                </select>
            </div>
        </div>

        @if($registros->count() > 0)
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">OC</th>
                            <th style="width: 100px;">NIS</th>
                            <th style="width: 120px;">Medidor</th>
                            <th style="width: 200px;">Cliente</th>
                            <th style="width: 140px;">Centro</th>
                            <th style="width: 120px;">Remesa</th>
                            <th style="width: 140px;">Fecha Carga</th>
                            <th style="width: 80px;">Estado</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registros as $registro)
                            <tr>
                                <td>
                                    <span class="badge-oc">{{ $registro->oc }}</span>
                                </td>
                                <td class="font-monospace">{{ $registro->nis ?? 'N/A' }}</td>
                                <td class="font-monospace">{{ $registro->nromedidor ?? 'N/A' }}</td>
                                <td title="{{ $registro->nomclie }}">
                                    {{ Str::limit($registro->nomclie ?? 'N/A', 25) }}
                                </td>
                                <td>
                                    <span class="badge-center">
                                        {{ Str::limit($registro->centro_servicio ?? 'N/A', 15) }}
                                    </span>
                                </td>
                                <td class="font-monospace">{{ $registro->nro_carga }}</td>
                                <td>{{ $registro->fecha_carga->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    @if($registro->editado)
                                        <i class="bi bi-pencil-square text-warning" title="Editado"></i>
                                    @else
                                        <i class="bi bi-check-circle text-success" title="Original"></i>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" 
                                       class="btn btn-primary btn-sm" 
                                       title="Ver remesa completa">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación mejorada -->
            <x-pagination :paginator="$registros" />
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h5 class="mt-3 text-muted">No se encontraron registros</h5>
                <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                <a href="{{ route('remesas.general') }}" class="btn btn-modern btn-primary-modern">
                    <i class="bi bi-arrow-clockwise me-1"></i>Limpiar filtros
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

// Auto-submit del formulario después de un delay
let timeoutId;
document.querySelectorAll('#filtros-form input, #filtros-form select').forEach(element => {
    element.addEventListener('input', function() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            document.getElementById('filtros-form').submit();
        }, 1000);
    });
});
</script>
@endsection