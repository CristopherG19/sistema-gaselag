@props(['paginator'])

@if($paginator->hasPages())
    <div class="pagination-container">
        <div class="pagination-info">
            <span class="text-muted">
                Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }} 
                ({{ number_format($paginator->total()) }} registros total)
            </span>
        </div>
        
        <nav class="pagination-nav">
            <ul class="pagination-list">
                {{-- Botón Anterior --}}
                @if($paginator->onFirstPage())
                    <li class="pagination-item disabled">
                        <span class="pagination-btn disabled">
                            <i class="bi bi-chevron-left"></i> Anterior
                        </span>
                    </li>
                @else
                    <li class="pagination-item">
                        <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn">
                            <i class="bi bi-chevron-left"></i> Anterior
                        </a>
                    </li>
                @endif

                {{-- Páginas numeradas (máximo 5 páginas visibles) --}}
                @php
                    $start = max(1, $paginator->currentPage() - 2);
                    $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
                    
                    // Ajustar si estamos cerca del inicio o final
                    if ($end - $start < 4) {
                        if ($start == 1) {
                            $end = min($paginator->lastPage(), $start + 4);
                        } else {
                            $start = max(1, $end - 4);
                        }
                    }
                @endphp

                {{-- Mostrar "..." al inicio si es necesario --}}
                @if($start > 1)
                    <li class="pagination-item">
                        <a href="{{ $paginator->url(1) }}" class="pagination-btn">1</a>
                    </li>
                    @if($start > 2)
                        <li class="pagination-item disabled">
                            <span class="pagination-btn disabled">...</span>
                        </li>
                    @endif
                @endif

                {{-- Páginas del rango --}}
                @for($i = $start; $i <= $end; $i++)
                    <li class="pagination-item {{ $i == $paginator->currentPage() ? 'active' : '' }}">
                        @if($i == $paginator->currentPage())
                            <span class="pagination-btn active">{{ $i }}</span>
                        @else
                            <a href="{{ $paginator->url($i) }}" class="pagination-btn">{{ $i }}</a>
                        @endif
                    </li>
                @endfor

                {{-- Mostrar "..." al final si es necesario --}}
                @if($end < $paginator->lastPage())
                    @if($end < $paginator->lastPage() - 1)
                        <li class="pagination-item disabled">
                            <span class="pagination-btn disabled">...</span>
                        </li>
                    @endif
                    <li class="pagination-item">
                        <a href="{{ $paginator->url($paginator->lastPage()) }}" class="pagination-btn">{{ $paginator->lastPage() }}</a>
                    </li>
                @endif

                {{-- Botón Siguiente --}}
                @if($paginator->hasMorePages())
                    <li class="pagination-item">
                        <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn">
                            Siguiente <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="pagination-item disabled">
                        <span class="pagination-btn disabled">
                            Siguiente <i class="bi bi-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>

    <style>
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-top: 1px solid #e9ecef;
        margin-top: 1rem;
    }

    .pagination-info {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .pagination-nav {
        display: flex;
        align-items: center;
    }

    .pagination-list {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 0.25rem;
    }

    .pagination-item {
        display: flex;
    }

    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.25;
        color: #495057;
        text-decoration: none;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        min-width: 2.5rem;
        height: 2.5rem;
        transition: all 0.15s ease-in-out;
    }

    .pagination-btn:hover:not(.disabled) {
        color: #0d6efd;
        background-color: #e9ecef;
        border-color: #dee2e6;
        text-decoration: none;
    }

    .pagination-btn.active {
        color: #fff;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .pagination-btn.disabled {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
        cursor: not-allowed;
        opacity: 0.65;
    }

    .pagination-btn i {
        font-size: 0.75rem;
        width: 0.75rem;
        height: 0.75rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .pagination-container {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .pagination-list {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .pagination-btn {
            min-width: 2rem;
            height: 2rem;
            padding: 0.375rem 0.5rem;
            font-size: 0.8rem;
        }
    }
    </style>
@endif

