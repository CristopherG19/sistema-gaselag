@extends('layouts.app')

@section('title', 'Editar Metadatos - Remesa ' . $nroCarga)

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Editar Metadatos - Remesa {{ $nroCarga }}
                    </h2>
                    <p class="text-muted mb-0">Modifica la información general de la remesa</p>
                </div>
                <a href="{{ route('remesa.lista') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Lista
                </a>
            </div>

            <!-- Formulario -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Información de la Remesa
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('remesa.actualizar.metadatos', $nroCarga) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nro_carga" class="form-label">Número de Carga</label>
                                    <input type="text" class="form-control" id="nro_carga" 
                                           value="{{ $nroCarga }}" readonly>
                                    <div class="form-text">Este campo no se puede modificar</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_carga" class="form-label">Fecha de Carga</label>
                                    <input type="text" class="form-control" id="fecha_carga" 
                                           value="{{ \Carbon\Carbon::parse($infoRemesa->fecha_carga)->format('d/m/Y H:i') }}" readonly>
                                    <div class="form-text">Este campo no se puede modificar</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre_archivo" class="form-label">
                                Nombre del Archivo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('nombre_archivo') is-invalid @enderror" 
                                   id="nombre_archivo" name="nombre_archivo" 
                                   value="{{ old('nombre_archivo', $infoRemesa->nombre_archivo) }}" required>
                            @error('nombre_archivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="centro_servicio" class="form-label">
                                Centro de Servicio <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('centro_servicio') is-invalid @enderror" 
                                    id="centro_servicio" name="centro_servicio" required>
                                <option value="">Selecciona un centro</option>
                                @foreach(config('centros_servicio.centros') as $key => $centro)
                                    <option value="{{ $key }}" {{ old('centro_servicio', $infoRemesa->centro_servicio) == $key ? 'selected' : '' }}>
                                        {{ $centro }}
                                    </option>
                                @endforeach
                            </select>
                            @error('centro_servicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Información adicional -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="bi bi-info-circle me-2"></i>Información Importante
                            </h6>
                            <p class="mb-0">
                                Al modificar estos metadatos, se actualizarán <strong>todos los registros</strong> 
                                de esta remesa. Los cambios se aplicarán a los {{ \App\Models\Remesa::where('nro_carga', $nroCarga)->count() }} 
                                registros asociados.
                            </p>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('remesa.lista') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información de la remesa -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        Resumen de la Remesa
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-primary">{{ \App\Models\Remesa::where('nro_carga', $nroCarga)->count() }}</div>
                                <small class="text-muted">Total Registros</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-success">{{ \App\Models\Remesa::where('nro_carga', $nroCarga)->where('editado', false)->count() }}</div>
                                <small class="text-muted">Originales</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-warning">{{ \App\Models\Remesa::where('nro_carga', $nroCarga)->where('editado', true)->count() }}</div>
                                <small class="text-muted">Editados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-info">{{ \App\Models\Remesa::where('nro_carga', $nroCarga)->distinct('centro_servicio')->count('centro_servicio') }}</div>
                                <small class="text-muted">Centros</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
