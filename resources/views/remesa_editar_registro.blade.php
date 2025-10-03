@extends('layouts.app')

@section('title', 'Editar Registro')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
@endpush

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-primary">
            <i class="bi bi-pencil-square me-2"></i>Editar Registro
        </h2>
        <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Registros
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>Información del Registro
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('remesa.actualizar.registro', $registro->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nis" class="form-label">NIS <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nis" name="nis" 
                                       value="{{ old('nis', $registro->nis) }}" required>
                                @error('nis')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nromedidor" class="form-label">Número de Medidor</label>
                                <input type="text" class="form-control" id="nromedidor" name="nromedidor" 
                                       value="{{ old('nromedidor', $registro->nromedidor) }}">
                                @error('nromedidor')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nomclie" class="form-label">Nombre del Cliente <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nomclie" name="nomclie" 
                                   value="{{ old('nomclie', $registro->nomclie) }}" required>
                            @error('nomclie')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="dir_proc" class="form-label">Dirección de Proceso <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="dir_proc" name="dir_proc" rows="3" required>{{ old('dir_proc', $registro->dir_proc) }}</textarea>
                            @error('dir_proc')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="tel_clie" class="form-label">Teléfono del Cliente</label>
                            <input type="text" class="form-control" id="tel_clie" name="tel_clie" 
                                   value="{{ old('tel_clie', $registro->tel_clie) }}">
                            @error('tel_clie')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="retfech" class="form-label">Fecha de Retiro</label>
                                <input type="date" class="form-control" id="retfech" name="retfech" 
                                       value="{{ old('retfech', $registro->retfech ? \Carbon\Carbon::parse($registro->retfech)->format('Y-m-d') : '') }}">
                                @error('retfech')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="rethor" class="form-label">Hora de Retiro</label>
                                <input type="time" class="form-control" id="rethor" name="rethor" 
                                       value="{{ old('rethor', $registro->rethor ? sprintf('%02d:%02d', floor($registro->rethor), ($registro->rethor - floor($registro->rethor)) * 60) : '') }}"
                                       step="60">
                                @error('rethor')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fechaprog" class="form-label">Fecha Programada</label>
                                <input type="date" class="form-control" id="fechaprog" name="fechaprog" 
                                       value="{{ old('fechaprog', $registro->fechaprog ? \Carbon\Carbon::parse($registro->fechaprog)->format('Y-m-d') : '') }}">
                                @error('fechaprog')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="horaprog" class="form-label">Hora Programada</label>
                                <input type="time" class="form-control" id="horaprog" name="horaprog" 
                                       value="{{ old('horaprog', $registro->horaprog ? sprintf('%02d:%02d', floor($registro->horaprog), ($registro->horaprog - floor($registro->horaprog)) * 60) : '') }}"
                                       step="60">
                                @error('horaprog')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Información Adicional
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Nro. Carga:</strong> {{ $registro->nro_carga }}</p>
                    <p><strong>OC:</strong> {{ $registro->oc ?? 'N/A' }}</p>
                    <p><strong>Centro de Servicio:</strong> {{ $registro->centro_servicio ?? 'N/A' }}</p>
                    <p><strong>Fecha de Carga:</strong> {{ \Carbon\Carbon::parse($registro->fecha_carga)->format('d/m/Y H:i') }}</p>
                    <p><strong>Editado:</strong> 
                        @if($registro->editado)
                            <span class="badge bg-warning">Sí</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection