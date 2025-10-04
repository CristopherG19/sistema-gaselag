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
                                @if(Auth::user()->isAdmin() && $estado == 'pendientes' && $remesas->total() > 0)
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-success me-2" onclick="confirmarProcesarTodos()">
                                            <i class="bi bi-play-circle-fill"></i> Procesar Todos los Pendientes
                                        </button>
                                    </div>
                                @endif
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
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="{{ route('remesa.procesar.form', ['id' => $remesa->primer_id ?? $remesa->id]) }}" 
                                                                   class="btn btn-success" 
                                                                   title="Procesar remesa pendiente">
                                                                    <i class="bi bi-play-circle"></i> Procesar
                                                                </a>
                                                                <button type="button" 
                                                                        class="btn btn-danger" 
                                                                        title="Eliminar remesa pendiente"
                                                                        onclick="confirmarEliminacion({{ $remesa->primer_id ?? $remesa->id }}, '{{ $remesa->nro_carga }}')">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
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

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="confirmarEliminacionModal" tabindex="-1" aria-labelledby="confirmarEliminacionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmarEliminacionModalLabel">
                        <i class="bi bi-exclamation-triangle text-warning"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar la remesa pendiente <strong id="nroCargaEliminar"></strong>?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para procesar todos los pendientes -->
    <div class="modal fade" id="confirmarProcesarTodosModal" tabindex="-1" aria-labelledby="confirmarProcesarTodosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="confirmarProcesarTodosModalLabel">
                        <i class="bi bi-play-circle-fill"></i> Procesar Todos los Archivos Pendientes
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Procesamiento Masivo</strong>
                    </div>
                    <p>Esta acción procesará <strong>TODOS</strong> los archivos pendientes y los cargará a la base de datos.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="bi bi-check-circle text-success"></i> Lo que sucederá:</h6>
                            <ul class="text-muted small">
                                <li>Se procesarán todos los archivos DBF pendientes</li>
                                <li>Los registros se insertarán en la base de datos</li>
                                <li>Los archivos se marcarán como procesados</li>
                                <li>Recibirás un resumen completo del proceso</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-clock text-warning"></i> Consideraciones:</h6>
                            <ul class="text-muted small">
                                <li>El proceso puede tomar varios minutos</li>
                                <li>No cierres esta pestaña durante el procesamiento</li>
                                <li>Si hay errores, solo los archivos exitosos se procesarán</li>
                                <li>Podrás resubir los archivos que fallen</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Atención:</strong> Esta acción no se puede deshacer una vez iniciada.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <form id="formProcesarTodos" method="POST" action="{{ route('remesa.procesar.todos.pendientes') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success" id="btnProcesarTodos">
                            <i class="bi bi-play-circle-fill"></i> Procesar Todos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmarEliminacion(remesaId, nroCarga) {
        document.getElementById('nroCargaEliminar').textContent = nroCarga;
        document.getElementById('formEliminar').action = '{{ route("remesa.eliminar.pendiente", ":id") }}'.replace(':id', remesaId);
        
        const modal = new bootstrap.Modal(document.getElementById('confirmarEliminacionModal'));
        modal.show();
    }

    function confirmarProcesarTodos() {
        const modal = new bootstrap.Modal(document.getElementById('confirmarProcesarTodosModal'));
        modal.show();
    }

    // Manejar el envío del formulario de procesamiento masivo
    document.getElementById('formProcesarTodos').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnProcesarTodos');
        const originalText = btn.innerHTML;
        
        // Cambiar el botón para mostrar que está procesando
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
        btn.disabled = true;
        
        // Mostrar mensaje de progreso
        const modalBody = document.querySelector('#confirmarProcesarTodosModal .modal-body');
        const progressAlert = document.createElement('div');
        progressAlert.className = 'alert alert-primary mt-3';
        progressAlert.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Procesando...</span>
                </div>
                <div>
                    <strong>Procesamiento en curso...</strong><br>
                    <small class="text-muted">Por favor, no cierres esta ventana. El proceso puede tomar varios minutos.</small>
                </div>
            </div>
        `;
        modalBody.appendChild(progressAlert);
        
        // Desactivar el botón de cancelar
        document.querySelector('#confirmarProcesarTodosModal .btn-secondary').disabled = true;
    });
    </script>
@endsection