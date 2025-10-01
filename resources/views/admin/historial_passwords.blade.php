@extends('layouts.app')

@section('title', 'Historial de Contraseñas')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
@endpush

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-primary">
            <i class="bi bi-shield-lock me-2"></i>Historial de Contraseñas
        </h2>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Dashboard
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-clock-history me-2"></i>Registro de Cambios de Contraseña
            </h5>
        </div>
        <div class="card-body">
            @if($cambios->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Usuario</th>
                                <th>Fecha de Cambio</th>
                                <th>IP</th>
                                <th>User Agent</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cambios as $cambio)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $cambio->usuario->correo ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $cambio->usuario->rol_texto ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($cambio->created_at)->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($cambio->created_at)->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $cambio->ip_address ?? 'N/A' }}</code>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($cambio->user_agent ?? 'N/A', 50) }}</small>
                                    </td>
                                    <td>
                                        @if($cambio->success)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Exitoso
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> Fallido
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="d-flex justify-content-center mt-4">
                    <x-pagination :paginator="$cambios" />
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-shield-lock display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No hay cambios de contraseña registrados</h4>
                    <p class="text-muted">Los cambios de contraseña aparecerán aquí cuando los usuarios las modifiquen.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
