@extends('layouts.app')

@section('title', 'Gestionar Registros - Remesa ' . $nroCarga)

@push('styles')
<style>
    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.375rem;
    }
    .table-responsive {
        font-size: 0.85rem;
    }
    .client-name {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .address-cell {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .compact-cell {
        padding: 0.5rem;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-gear text-success me-2"></i>
                        Gestionar Registros - Remesa {{ $nroCarga }}
                    </h2>
                    <p class="text-muted mb-0">{{ $infoRemesa->nombre_archivo }}</p>
                </div>
                <div>
                    <a href="{{ route('remesa.ver.registros', $nroCarga) }}" class="btn btn-outline-primary me-2">
                        <i class="bi bi-eye me-1"></i>Ver Registros
                    </a>
                    <a href="{{ route('remesa.lista') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Volver a Lista
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-funnel me-2"></i>Filtros de Búsqueda
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" id="filtros-form">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">NIS</label>
                                <input type="text" class="form-control" name="nis" 
                                       value="{{ $filtros['nis'] ?? '' }}" 
                                       placeholder="Buscar por NIS">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Medidor</label>
                                <input type="text" class="form-control" name="nromedidor" 
                                       value="{{ $filtros['nromedidor'] ?? '' }}" 
                                       placeholder="Número de medidor">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cliente</label>
                                <input type="text" class="form-control" name="nomclie" 
                                       value="{{ $filtros['nomclie'] ?? '' }}" 
                                       placeholder="Nombre del cliente">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Centro</label>
                                <select class="form-select" name="centro_servicio">
                                    <option value="">Todos los centros</option>
                                    @foreach($centrosDisponibles as $centro)
                                        <option value="{{ $centro }}" 
                                                {{ ($filtros['centro_servicio'] ?? '') == $centro ? 'selected' : '' }}>
                                            {{ $centro }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Mostrar</label>
                                <select class="form-select" name="per_page">
                                    <option value="25" {{ ($filtros['per_page'] ?? 25) == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ ($filtros['per_page'] ?? 25) == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ ($filtros['per_page'] ?? 25) == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-search me-1"></i>Buscar
                                </button>
                                <a href="{{ route('remesa.gestionar.registros', $nroCarga) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de registros -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-table me-2"></i>Registros ({{ $registros->firstItem() ?? 0 }} - {{ $registros->lastItem() ?? 0 }} de {{ $registros->total() }})
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-success" onclick="seleccionarTodos()">
                            <i class="bi bi-check-square me-1"></i>Seleccionar Todos
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="deseleccionarTodos()">
                            <i class="bi bi-square me-1"></i>Deseleccionar
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">
                                        <input type="checkbox" id="select-all" onchange="toggleAll()">
                                    </th>
                                    <th style="width: 80px;">ID</th>
                                    <th style="width: 100px;">Código</th>
                                    <th style="width: 200px;">Nombre</th>
                                    <th>Dirección</th>
                                    <th style="width: 120px;">Teléfono</th>
                                    <th style="width: 100px;">Estado</th>
                                    <th style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registros as $registro)
                                    <tr>
                                        <td class="compact-cell">
                                            <input type="checkbox" class="registro-checkbox" value="{{ $registro->id }}">
                                        </td>
                                        <td class="compact-cell fw-semibold text-primary">#{{ $registro->id }}</td>
                                        <td class="compact-cell font-monospace">{{ $registro->nis ?? '-' }}</td>
                                        <td class="compact-cell">
                                            <div class="client-name" title="{{ $registro->nomclie ?? '-' }}">
                                                {{ $registro->nomclie ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="compact-cell address-cell" title="{{ $registro->dir_proc ?? '-' }}">
                                            {{ $registro->dir_proc ?? '-' }}
                                        </td>
                                        <td class="compact-cell font-monospace">{{ $registro->tel_clie ?? '-' }}</td>
                                        <td class="compact-cell">
                                            @if($registro->editado)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-pencil-square me-1"></i>Editado
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Original
                                                </span>
                                            @endif
                                        </td>
                                        <td class="compact-cell">
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('remesa.editar.registro', $registro->id) }}" 
                                                   class="btn btn-warning btn-sm" 
                                                   title="Editar registro">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Eliminar registro"
                                                        onclick="eliminarRegistro({{ $registro->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox display-4"></i>
                                            <p class="mt-2">No se encontraron registros</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted">Mostrando {{ $registros->firstItem() ?? 0 }} - {{ $registros->lastItem() ?? 0 }} de {{ $registros->total() }} registros</span>
                        </div>
                        <div>
                            <x-pagination :paginator="$registros" />
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
function verDetalles(id) {
    const url = '{{ route("remesa.ver.detalle", ":id") }}'.replace(':id', id);
    window.location.href = url;
}

function eliminarRegistro(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este registro? Esta acción no se puede deshacer.')) {
        // Implementar eliminación
        alert('Eliminar registro: ' + id + ' (Función por implementar)');
    }
}

function seleccionarTodos() {
    document.querySelectorAll('.registro-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('select-all').checked = true;
}

function deseleccionarTodos() {
    document.querySelectorAll('.registro-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('select-all').checked = false;
}

function toggleAll() {
    const selectAll = document.getElementById('select-all');
    document.querySelectorAll('.registro-checkbox').forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// Auto-submit del formulario de filtros
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filtros-form');
    const inputs = form.querySelectorAll('input, select');
    let timeoutId;
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                form.submit();
            }, 1000);
        });
    });
});
</script>
@endpush
