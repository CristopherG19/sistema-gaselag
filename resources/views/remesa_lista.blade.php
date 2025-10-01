@extends('layouts.app')

@section('title', 'Lista de Remesas')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons-fix.css') }}">
    <style>
        /* Control de tamaño de iconos - Solución robusta para Tailwind + Bootstrap */
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
            font-size: inherit !important;
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
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Mis Remesas</h4>
                            <small class="text-muted">
                                <i class="bi bi-person-badge"></i> {{ Auth::user()->rol_texto }}
                            </small>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('remesas.general') }}" class="btn btn-outline-light">
                                <i class="bi bi-table"></i> Vista General
                            </a>
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('remesa.upload.form') }}" class="btn btn-light">
                                    <i class="bi bi-plus-circle"></i> Nueva Remesa
                                </a>
                            @endif
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

                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" action="{{ route('remesa.lista') }}">
                                    <div class="input-group">
                                        <select name="estado" class="form-select">
                                            <option value="todos" {{ $estado == 'todos' ? 'selected' : '' }}>Todas las remesas</option>
                                            <option value="pendientes" {{ $estado == 'pendientes' ? 'selected' : '' }}>Pendientes de cargar</option>
                                            <option value="cargadas" {{ $estado == 'cargadas' ? 'selected' : '' }}>Cargadas al sistema</option>
                                        </select>
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="bi bi-funnel"></i> Filtrar
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    Total: {{ $remesas->total() }} remesas
                                </small>
                            </div>
                        </div>

                        <!-- Tabla de remesas -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                    <th>Archivo</th>
                                    <th>Nro. Carga</th>
                                    <th>Fecha Carga</th>
                                    <th>Estado</th>
                                    <th>Registros</th>
                                    @if(Auth::user()->isAdmin())
                                        <th>Usuario</th>
                                    @endif
                                    <th>Editado</th>
                                    <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($remesas as $remesa)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $remesa->nombre_archivo }}</div>
                                                <small class="text-muted">ID: {{ $remesa->primer_id ?? $remesa->id ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $remesa->nro_carga }}</span>
                                            </td>
                                            <td>
                                                <div>{{ \Carbon\Carbon::parse($remesa->fecha_carga)->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($remesa->fecha_carga)->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                @if($remesa->cargado_al_sistema)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Cargado
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-clock"></i> Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $remesa->total_registros }}</span>
                                            </td>
                                            @if(Auth::user()->isAdmin())
                                                <td>
                                                    <small class="text-muted">{{ $remesa->usuario_nombre ?? 'N/A' }}</small>
                                                </td>
                                            @endif
                                            <td>
                                                @if($remesa->editado)
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-pencil"></i> Sí
                                                    </span>
                                                    <br><small class="text-muted">{{ $remesa->fecha_edicion ? \Carbon\Carbon::parse($remesa->fecha_edicion)->format('d/m/Y') : '' }}</small>
                                                @else
                                                    <span class="text-muted">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if($remesa->cargado_al_sistema)
                                                        <a href="{{ route('remesa.ver.registros', $remesa->nro_carga) }}" 
                                                           class="btn btn-outline-primary" title="Ver registros">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        @if(Auth::user()->isAdmin())
                                                            <a href="{{ route('remesa.gestionar.registros', $remesa->nro_carga) }}" 
                                                               class="btn btn-outline-success" title="Gestionar registros">
                                                                <i class="bi bi-gear"></i>
                                                            </a>
                                                            <a href="{{ route('remesa.editar.metadatos', $remesa->nro_carga) }}" 
                                                               class="btn btn-outline-warning" title="Editar metadatos de remesa">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </a>
                                                        @endif
                                                    @else
                                                        @if(Auth::user()->isAdmin())
                                                            <a href="{{ route('remesa.procesar.form') }}" 
                                                               class="btn btn-success btn-sm" 
                                                               title="Procesar remesa pendiente">
                                                                <i class="bi bi-play-circle"></i> Procesar
                                                            </a>
                                                        @else
                                                            <span class="text-muted">Pendiente</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox display-4"></i>
                                                <p class="mt-2">No tienes remesas cargadas</p>
                                                <a href="{{ route('remesa.upload.form') }}" class="btn btn-primary">
                                                    <i class="bi bi-plus-circle"></i> Cargar Primera Remesa
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación mejorada -->
                        <x-pagination :paginator="$remesas" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection