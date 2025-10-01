@include('includes.header')

<style>
/* Estilos completamente nuevos y aislados */
.edit-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 20px 0;
}

.edit-header {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgb                <div class="row">
                    <div class="col-md-12">
                        <div class="form-floating-custom">
                            <textarea class="form-control" id="dir_cata" name="dir_cata" style="height: 80px;" 
                                      maxlength="171">{{ old('dir_cata', $registro->dir_cata) }}</textarea>
                            <label for="dir_cata">Referencia Catastral</label>
                        </div>
                    </div>
                </div>, 0.1);
    margin-bottom: 2rem;
}

.form-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border-left: 4px solid #007bff;
}

.form-section.readonly {
    border-left-color: #6c757d;
    background: #f8f9fa;
}

.form-section.editable {
    border-left-color: #28a745;
}

.section-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-icon {
    font-size: 1.2rem;
}

.form-floating-custom {
    position: relative;
    margin-bottom: 1rem;
}

.form-floating-custom .form-control {
    height: calc(3.5rem + 2px);
    padding: 1rem 0.75rem 0.25rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.2s;
}

.form-floating-custom .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-floating-custom .form-control:not(:placeholder-shown) {
    padding-top: 1.625rem;
    padding-bottom: 0.625rem;
}

.form-floating-custom .form-control:focus ~ label,
.form-floating-custom .form-control:not(:placeholder-shown) ~ label {
    opacity: 0.65;
    transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
}

.form-floating-custom label {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    padding: 1rem 0.75rem;
    pointer-events: none;
    border: 1px solid transparent;
    transform-origin: 0 0;
    transition: opacity 0.1s ease-in-out, transform 0.1s ease-in-out;
    color: #6c757d;
}

.readonly-field {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
    color: #6c757d !important;
}

.required-label::after {
    content: " *";
    color: #dc3545;
    font-weight: bold;
}

.btn-group-custom {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.btn-primary-custom {
    background: #007bff;
    border: 1px solid #007bff;
    color: white;
    border-radius: 8px;
    padding: 0.75rem 2rem;
    font-weight: 500;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary-custom:hover {
    background: #0056b3;
    border-color: #0056b3;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
}

.btn-secondary-custom {
    background: #6c757d;
    border: 1px solid #6c757d;
    color: white;
    border-radius: 8px;
    padding: 0.75rem 2rem;
    font-weight: 500;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-secondary-custom:hover {
    background: #545b62;
    border-color: #545b62;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
}

/* Iconos de texto puro */
.icon-person::before { content: "👤 "; }
.icon-meter::before { content: "⚡ "; }
.icon-location::before { content: "📍 "; }
.icon-calendar::before { content: "📅 "; }
.icon-settings::before { content: "⚙️ "; }
.icon-info::before { content: "ℹ️ "; }
.icon-save::before { content: "💾 "; }
.icon-back::before { content: "← "; }

/* Eliminar TODOS los SVG */
svg {
    display: none !important;
}

.bi, i[class*="bi-"] {
    font-family: inherit !important;
}

/* Responsive */
@media (max-width: 768px) {
    .edit-container {
        padding: 10px 0;
    }
    
    .form-section {
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .btn-group-custom {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<div class="edit-container">
    <div class="container">
        <!-- Header -->
        <div class="edit-header">
            <div class="row align-items-center">
                <div class="col-md-10">
                    <h2 class="mb-2">✏️ Editar Registro</h2>
                    <p class="mb-0 opacity-75">NIS: {{ $registro->nis }} | Remesa: {{ $registro->nro_carga }}</p>
                </div>
                <div class="col-md-2 text-end">
                    <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" class="btn btn-secondary-custom">
                        <span class="icon-back"></span>Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Errores -->
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <h6 class="alert-heading">⚠️ Errores de validación:</h6>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('remesa.actualizar.registro', $registro->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- 1. Información del Cliente -->
            <div class="form-section editable">
                <h5 class="section-title">
                    <span class="icon-person section-icon"></span>
                    Información del Cliente
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="nis" 
                                   value="{{ old('nis', $registro->nis) }}" readonly>
                            <label for="nis">NIS</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control" id="nomclie" name="nomclie" 
                                   value="{{ old('nomclie', $registro->nomclie) }}" maxlength="60" required>
                            <label for="nomclie" class="required-label">Nombre del Cliente</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control" id="tel_clie" name="tel_clie" 
                                   value="{{ old('tel_clie', $registro->tel_clie) }}" maxlength="16">
                            <label for="tel_clie">Teléfono</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control" id="reclamante" name="reclamante" 
                                   value="{{ old('reclamante', $registro->reclamante) }}" maxlength="60">
                            <label for="reclamante">Reclamante</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Información del Medidor -->
            <div class="form-section readonly">
                <h5 class="section-title">
                    <span class="icon-meter section-icon"></span>
                    Información del Medidor
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="nromedidor" 
                                   value="{{ $registro->nromedidor }}" readonly>
                            <label for="nromedidor">Número de Medidor</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="marcamed" 
                                   value="{{ $registro->marcamed }}" readonly>
                            <label for="marcamed">Marca</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="diametro" 
                                   value="{{ $registro->diametro }}" readonly>
                            <label for="diametro">Diámetro</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="clase" 
                                   value="{{ $registro->clase }}" readonly>
                            <label for="clase">Clase</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="cus" 
                                   value="{{ $registro->cus }}" readonly>
                            <label for="cus">CUS</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Ubicación -->
            <div class="form-section editable">
                <h5 class="section-title">
                    <span class="icon-location section-icon"></span>
                    Ubicación
                </h5>
                <div class="row">
                    <div class="col-12">
                        <div class="form-floating-custom">
                            <textarea class="form-control" id="dir_proc" name="dir_proc" style="height: 80px;" 
                                      maxlength="171" required>{{ old('dir_proc', $registro->dir_proc) }}</textarea>
                            <label for="dir_proc" class="required-label">Dirección de Propiedad</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control" id="ref_dir_ca" name="ref_dir_ca" 
                                   value="{{ old('ref_dir_ca', $registro->ref_dir_ca) }}" maxlength="60">
                            <label for="ref_dir_ca">Referencia Dirección CA</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control" id="ref_dir_pr" name="ref_dir_pr" 
                                   value="{{ old('ref_dir_pr', $registro->ref_dir_pr) }}" maxlength="60">
                            <label for="ref_dir_pr">Referencia Dirección PR</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-floating-custom">
                            <textarea class="form-control" id="dir_cata" name="dir_cata" style="height: 80px;" 
                                      maxlength="171">{{ old('dir_cata', $registro->dir_cata) }}</textarea>
                            <label for="dir_cata">Dirección Catastral</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Fechas Importantes -->
            <div class="form-section readonly">
                <h5 class="section-title">
                    <span class="icon-calendar section-icon"></span>
                    Fechas del Sistema
                </h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="date" class="form-control readonly-field" id="retfech" 
                                   value="{{ $registro->retfech ? $registro->retfech->format('Y-m-d') : '' }}" readonly>
                            <label for="retfech">Fecha Retiro</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="date" class="form-control readonly-field" id="fechaprog" 
                                   value="{{ $registro->fechaprog ? $registro->fechaprog->format('Y-m-d') : '' }}" readonly>
                            <label for="fechaprog">Fecha Programada</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="date" class="form-control readonly-field" id="fechaing" 
                                   value="{{ $registro->fechaing ? $registro->fechaing->format('Y-m-d') : '' }}" readonly>
                            <label for="fechaing">Fecha Ingreso</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="date" class="form-control readonly-field" id="f_inst" 
                                   value="{{ $registro->f_inst ? $registro->f_inst->format('Y-m-d') : '' }}" readonly>
                            <label for="f_inst">Fecha Instalación</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Parámetros Técnicos -->
            <div class="form-section readonly">
                <h5 class="section-title">
                    <span class="icon-settings section-icon"></span>
                    Parámetros Técnicos
                </h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="ruta_num" 
                                   value="{{ $registro->ruta_num }}" readonly>
                            <label for="ruta_num">Ruta</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="cgv" 
                                   value="{{ $registro->cgv }}" readonly>
                            <label for="cgv">CGV</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="tarifa" 
                                   value="{{ $registro->tarifa }}" readonly>
                            <label for="tarifa">Tarifa</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="cup" 
                                   value="{{ $registro->cup }}" readonly>
                            <label for="cup">CUP</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Información Adicional -->
            <div class="form-section readonly">
                <h5 class="section-title">
                    <span class="icon-info section-icon"></span>
                    Información Adicional
                </h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="resol" 
                                   value="{{ $registro->resol }}" readonly>
                            <label for="resol">RESOL</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="itin" 
                                   value="{{ $registro->itin }}" readonly>
                            <label for="itin">ITIN</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="aol" 
                                   value="{{ $registro->aol }}" readonly>
                            <label for="aol">AOL</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating-custom">
                            <input type="text" class="form-control readonly-field" id="especial" 
                                   value="{{ $registro->especial }}" readonly>
                            <label for="especial">Especial</label>
                            <small class="text-muted">Campo de solo lectura</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="btn-group-custom">
                <button type="submit" class="btn-primary-custom">
                    <span class="icon-save"></span>Guardar Cambios
                </button>
                <a href="{{ route('remesa.ver.registros', $registro->nro_carga) }}" 
                   class="btn-secondary-custom">
                    <span class="icon-back"></span>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>