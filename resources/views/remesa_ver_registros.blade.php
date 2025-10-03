@extends('layouts.app')

@section('title', 'Registros de Remesa ' . $nroCarga)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons-fix.css') }}">
    <style>
        .table-responsive {
            font-size: 0.85rem;
        }
        .campo-largo {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .campo-largo:hover {
            overflow: visible;
            white-space: normal;
            background-color: #f8f9fa;
            z-index: 1000;
            position: relative;
        }
        /* Control de tamaño de iconos - Solución robusta para Tailwind + Bootstrap */
        .bi, i[class*="bi-"] {
            font-size: 16px !important;
            width: 16px !important;
            height: 16px !important;
            line-height: 1 !important;
            display: inline-block !important;
            vertical-align: middle !important;
            font-style: normal !important;
            font-weight: normal !important;
        }

        .btn .bi, .btn i[class*="bi-"] {
            font-size: 14px !important;
            width: 14px !important;
            height: 14px !important;
            margin-right: 4px !important;
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
    </style>
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
        .card-header .bi, .card-header i[class*="bi-"] {
            font-size: 18px !important;
            width: 18px !important;
            height: 18px !important;
            line-height: 1 !important;
        }
        .display-4.bi, .display-4 i[class*="bi-"] {
            font-size: 48px !important;
            width: 48px !important;
            height: 48px !important;
            line-height: 1 !important;
        }
        /* Resetear estilos de Tailwind que pueden interferir */
        .bi::before, i[class*="bi-"]::before {
            vertical-align: baseline !important;
        }
        
        /* Fix específico para paginación */
        .pagination svg {
            width: 16px !important;
            height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            display: inline-block !important;
        }
        
        .pagination .page-link svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
        }
        
        .pagination .page-link {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 38px !important;
        }
        
        .pagination .page-item .page-link {
            padding: 0.5rem 0.75rem !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">
                                    <i class="bi bi-table"></i> Registros de Remesa {{ $nroCarga }}
                                </h4>
                                <small>Archivo: {{ $infoRemesa->nombre_archivo }}</small>
                            </div>
                            <div>
                                <a href="{{ route('remesa.lista') }}" class="btn btn-light">
                                    <i class="bi bi-arrow-left"></i> Volver a Lista
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('info'))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                {{ session('info') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Información general de la remesa -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1">
                                                    <i class="bi bi-file-earmark-text me-2"></i>
                                                    Remesa {{ $nroCarga }}
                                                </h5>
                                                <p class="mb-0 opacity-75">
                                                    <i class="bi bi-file me-1"></i>{{ $infoRemesa->nombre_archivo }} 
                                                    <span class="ms-3">
                                                        <i class="bi bi-calendar me-1"></i>{{ $infoRemesa->fecha_carga->format('d/m/Y H:i') }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="d-flex justify-content-end gap-3">
                                                    <div class="text-center">
                                                        <div class="h4 mb-0">{{ $registros->total() }}</div>
                                                        <small>Total Registros</small>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="h4 mb-0">{{ $infoRemesa->centro_servicio ?? 'N/A' }}</div>
                                                        <small>Centro</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <form method="GET" action="{{ route('remesa.ver.registros', $nroCarga) }}">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <input type="text" name="nis" class="form-control" 
                                                   placeholder="Buscar por NIS" value="{{ $filtros['nis'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="nromedidor" class="form-control" 
                                                   placeholder="Buscar por Medidor" value="{{ $filtros['nromedidor'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="nomcli" class="form-control" 
                                                   placeholder="Buscar por Cliente" value="{{ $filtros['nomcli'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <div class="btn-group w-100">
                                                <button type="submit" class="btn btn-outline-primary">
                                                    <i class="bi bi-search"></i> Buscar
                                                </button>
                                                <a href="{{ route('remesa.ver.registros', $nroCarga) }}" 
                                                   class="btn btn-outline-secondary">
                                                    <i class="bi bi-x-circle"></i> Limpiar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4 text-end">
                                <small class="text-muted">
                                    Mostrando {{ $registros->firstItem() ?? 0 }} - {{ $registros->lastItem() ?? 0 }} 
                                    de {{ $registros->total() }} registros
                                </small>
                            </div>
                        </div>

                        <!-- Tabla de registros -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th style="width: 100px;">NIS</th>
                                        <th style="width: 120px;">Medidor</th>
                                        <th style="width: 250px;">Cliente</th>
                                        <th style="width: 300px;">Dirección</th>
                                        <th style="width: 100px;">Marca</th>
                                        <th style="width: 100px;">Teléfono</th>
                                        <th style="width: 80px;">Estado</th>
                                        <th style="width: 120px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($registros as $registro)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">#{{ $registro->id }}</span>
                                            </td>
                                            <td>
                                                <code class="text-primary fw-bold">{{ $registro->nis }}</code>
                                            </td>
                                            <td>
                                                <span class="font-monospace">{{ $registro->nromedidor }}</span>
                                            </td>
                                            <td title="{{ $registro->nomcli }}">
                                                <div class="text-truncate" style="max-width: 240px;">
                                                    {{ $registro->nomcli }}
                                                </div>
                                            </td>
                                            <td title="{{ $registro->dir_pro }}">
                                                <div class="text-truncate" style="max-width: 290px;">
                                                    {{ $registro->dir_pro }}
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $registro->marcamed ?: '-' }}</small>
                                            </td>
                                            <td>
                                                <span class="font-monospace">{{ $registro->tel_clie ?: '-' }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($registro->editado)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-pencil"></i>
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('remesa.editar.registro', $registro->id) }}" 
                                                       class="btn btn-outline-primary btn-sm" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    @if($registro->editado)
                                                        <a href="{{ route('remesa.ver.historial', $registro->id) }}" 
                                                           class="btn btn-outline-info btn-sm" title="Historial">
                                                            <i class="bi bi-clock-history"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox display-4"></i>
                                                <p class="mt-2">No se encontraron registros</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        @if($registros->hasPages())
                            <div class="mt-3">
                                <x-pagination :paginator="$registros" />
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection