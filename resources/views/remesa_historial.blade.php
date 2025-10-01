@extends('layouts.app')

@section('title', 'Historial del Registro')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
@endpush

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-primary">
            <i class="bi bi-clock-history me-2"></i>Historial del Registro
        </h2>
        <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Registros
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>Información del Registro
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>NIS:</strong> {{ $registro->nis }}</p>
                            <p><strong>Cliente:</strong> {{ $registro->nomclie }}</p>
                            <p><strong>Dirección:</strong> {{ $registro->dir_proc }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Nro. Carga:</strong> {{ $registro->nro_carga }}</p>
                            <p><strong>OC:</strong> {{ $registro->oc ?? 'N/A' }}</p>
                            <p><strong>Centro:</strong> {{ $registro->centro_servicio ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Estado de Edición
                    </h5>
                </div>
                <div class="card-body">
                    @if($registro->editado)
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Este registro ha sido editado</strong>
                            @if($registro->fecha_edicion)
                                <br><small>Última edición: {{ \Carbon\Carbon::parse($registro->fecha_edicion)->format('d/m/Y H:i:s') }}</small>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Registro original</strong> - No ha sido modificado
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Información Adicional
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>ID del Registro:</strong> {{ $registro->id }}</p>
                    <p><strong>Fecha de Carga:</strong> {{ \Carbon\Carbon::parse($registro->fecha_carga)->format('d/m/Y H:i') }}</p>
                    <p><strong>Teléfono:</strong> {{ $registro->tel_clie ?? 'N/A' }}</p>
                    <p><strong>Medidor:</strong> {{ $registro->nromedidor ?? 'N/A' }}</p>
                    <p><strong>Diametro:</strong> {{ $registro->diametro ?? 'N/A' }}</p>
                    <p><strong>Clase:</strong> {{ $registro->clase ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection