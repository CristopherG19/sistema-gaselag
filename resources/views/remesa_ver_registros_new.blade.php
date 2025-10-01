@include('includes.header')

<style>
/* Estilos completamente nuevos y aislados */
.remesa-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 20px 0;
}

.remesa-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.remesa-stats {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stat-item {
    text-align: center;
    padding: 0.5rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
    display: block;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.filters-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.table-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.custom-table {
    border: none;
    font-size: 0.875rem;
}

.custom-table thead th {
    background: #f8f9fa;
    border: none;
    color: #495057;
    font-weight: 600;
    padding: 1rem 0.75rem;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.custom-table tbody td {
    padding: 0.875rem 0.75rem;
    border-top: 1px solid #e9ecef;
    vertical-align: middle;
}

.custom-table tbody tr:hover {
    background: #f8f9fa;
}

.nis-link {
    color: #dc3545;
    text-decoration: none;
    font-weight: 600;
}

.nis-link:hover {
    color: #a71e2a;
    text-decoration: underline;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #28a745;
    color: white;
}

.edit-btn {
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.2s;
}

.edit-btn:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
}

.btn-primary-custom {
    background: #007bff;
    border: 1px solid #007bff;
    color: white;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    transition: all 0.2s;
}

.btn-primary-custom:hover {
    background: #0056b3;
    border-color: #0056b3;
    transform: translateY(-1px);
}

.btn-secondary-custom {
    background: #6c757d;
    border: 1px solid #6c757d;
    color: white;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    transition: all 0.2s;
}

.btn-secondary-custom:hover {
    background: #545b62;
    border-color: #545b62;
    transform: translateY(-1px);
}

/* PAGINATION COMPLETAMENTE NUEVA SIN SVG */
.custom-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 2rem;
    gap: 0.5rem;
}

.pagination-btn {
    background: white;
    border: 1px solid #dee2e6;
    color: #007bff;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
    min-width: 40px;
    text-align: center;
}

.pagination-btn:hover {
    background: #007bff;
    color: white;
    text-decoration: none;
    border-color: #007bff;
}

.pagination-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.pagination-btn.disabled {
    background: #f8f9fa;
    color: #6c757d;
    border-color: #dee2e6;
    cursor: not-allowed;
}

.pagination-btn.disabled:hover {
    background: #f8f9fa;
    color: #6c757d;
    border-color: #dee2e6;
}

/* ICONOS COMPLETAMENTE SIN SVG - SOLO TEXTO */
.icon-left::before { content: "‹"; }
.icon-right::before { content: "›"; }
.icon-first::before { content: "«"; }
.icon-last::before { content: "»"; }
.icon-edit::before { content: "✎"; }
.icon-search::before { content: "🔍"; }
.icon-clear::before { content: "✕"; }
.icon-back::before { content: "← "; }

/* Resetear TODOS los SVG para asegurar que no aparezcan */
svg {
    display: none !important;
}

.bi {
    font-family: inherit !important;
}

i[class*="bi-"] {
    font-family: inherit !important;
}
</style>

<div class="remesa-container">
    <div class="container">
        <!-- Header -->
        <div class="remesa-header">
            <div class="row align-items-center">
                <div class="col-md-10">
                    <h2 class="mb-2">Registros de Remesa {{ $nroCarga }}</h2>
                    <p class="mb-0 opacity-75">Archivo: {{ $infoRemesa->nombre_archivo }}</p>
                </div>
                <div class="col-md-2 text-end">
                    <a href="{{ route('remesa.lista') }}" class="btn btn-light">
                        <span class="icon-back"></span>Volver a Lista
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="remesa-stats">
            <div class="row">
                <div class="col-3">
                    <div class="stat-item">
                        <span class="stat-value">{{ $nroCarga }}</span>
                        <div class="stat-label">Número de Carga</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-item">
                        <span class="stat-value">{{ $infoRemesa->nombre_archivo }}</span>
                        <div class="stat-label">Archivo</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-item">
                        <span class="stat-value">{{ $infoRemesa->fecha_carga->format('d/m/Y H:i') }}</span>
                        <div class="stat-label">Fecha de Carga</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-item">
                        <span class="stat-value">{{ $registros->total() }}</span>
                        <div class="stat-label">Total de Registros</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <h5 class="mb-3">Filtros de Búsqueda</h5>
            <form method="GET" action="{{ route('remesa.ver.registros', $nroCarga) }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">NIS</label>
                        <input type="text" name="nis" class="form-control" 
                               placeholder="Buscar por NIS" value="{{ $filtros['nis'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Medidor</label>
                        <input type="text" name="nromedidor" class="form-control" 
                               placeholder="Buscar por Medidor" value="{{ $filtros['nromedidor'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cliente</label>
                        <input type="text" name="nomcli" class="form-control" 
                               placeholder="Buscar por Cliente" value="{{ $filtros['nomcli'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary-custom">
                                <span class="icon-search"></span> Buscar
                            </button>
                            <a href="{{ route('remesa.ver.registros', $nroCarga) }}" 
                               class="btn btn-secondary-custom">
                                <span class="icon-clear"></span> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla -->
        <div class="table-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Registros</h5>
                <small class="text-muted">
                    Mostrando {{ $registros->firstItem() }} - {{ $registros->lastItem() }} 
                    de {{ $registros->total() }} registros
                </small>
            </div>

            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NIS</th>
                            <th>Medidor</th>
                            <th>Cliente</th>
                            <th>Dirección</th>
                            <th>Marca</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $registro)
                            <tr>
                                <td>{{ $registro->id }}</td>
                                <td>
                                    <a href="{{ route('remesa.editar.registro', $registro->id) }}" 
                                       class="nis-link">{{ $registro->nis }}</a>
                                </td>
                                <td>
                                    <strong>{{ $registro->nromedidor }}</strong>
                                </td>
                                <td>
                                    <div class="campo-largo" title="{{ $registro->nomcli }}">
                                        {{ $registro->nomcli }}
                                    </div>
                                </td>
                                <td>
                                    <div class="campo-largo" title="{{ $registro->dir_pro }}">
                                        {{ $registro->dir_pro }}
                                    </div>
                                </td>
                                <td>{{ $registro->marcamed }}</td>
                                <td>{{ $registro->tel_clie ?: '-' }}</td>
                                <td>
                                    <span class="status-badge">Original</span>
                                </td>
                                <td>
                                    <a href="{{ route('remesa.editar.registro', $registro->id) }}" 
                                       class="edit-btn">
                                        <span class="icon-edit"></span> Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <h5>No se encontraron registros</h5>
                                        <p>No hay registros que coincidan con los filtros aplicados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación Personalizada -->
            @if($registros->hasPages())
                <div class="custom-pagination">
                    <!-- Primera página -->
                    @if($registros->onFirstPage())
                        <span class="pagination-btn disabled">
                            <span class="icon-first"></span>
                        </span>
                    @else
                        <a href="{{ $registros->url(1) }}" class="pagination-btn">
                            <span class="icon-first"></span>
                        </a>
                    @endif

                    <!-- Página anterior -->
                    @if($registros->onFirstPage())
                        <span class="pagination-btn disabled">
                            <span class="icon-left"></span>
                        </span>
                    @else
                        <a href="{{ $registros->previousPageUrl() }}" class="pagination-btn">
                            <span class="icon-left"></span>
                        </a>
                    @endif

                    <!-- Páginas numeradas -->
                    @foreach($registros->getUrlRange(max(1, $registros->currentPage() - 2), min($registros->lastPage(), $registros->currentPage() + 2)) as $page => $url)
                        @if($page == $registros->currentPage())
                            <span class="pagination-btn active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                        @endif
                    @endforeach

                    <!-- Página siguiente -->
                    @if($registros->hasMorePages())
                        <a href="{{ $registros->nextPageUrl() }}" class="pagination-btn">
                            <span class="icon-right"></span>
                        </a>
                    @else
                        <span class="pagination-btn disabled">
                            <span class="icon-right"></span>
                        </span>
                    @endif

                    <!-- Última página -->
                    @if($registros->hasMorePages())
                        <a href="{{ $registros->url($registros->lastPage()) }}" class="pagination-btn">
                            <span class="icon-last"></span>
                        </a>
                    @else
                        <span class="pagination-btn disabled">
                            <span class="icon-last"></span>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>