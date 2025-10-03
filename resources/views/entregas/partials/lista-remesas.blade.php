@if($remesas->count() > 0)
    @php
        $remesasAgrupadas = $remesas->groupBy('nro_remesa');
    @endphp
    
    @foreach($remesasAgrupadas as $nroRemesa => $remesasGrupo)
        <div class="remesa-group mb-4">
            <h6 class="text-primary mb-3">
                <i class="bi bi-collection me-2"></i>
                Remesa #{{ $nroRemesa }} 
                <span class="badge bg-primary">{{ $remesasGrupo->count() }} archivo(s)</span>
            </h6>
            
            <div class="row">
                @foreach($remesasGrupo as $remesa)
                    <div class="col-md-6 mb-3">
                        <div class="remesa-card" onclick="selectRemesa({{ $remesa->id }})">
                            <input type="radio" name="remesa_id" value="{{ $remesa->id }}" 
                                   id="remesa_{{ $remesa->id }}" style="display: none;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-text me-3 text-primary" style="font-size: 2rem;"></i>
                                <div>
                                    <h6 class="mb-1">{{ $remesa->nombre_archivo }}</h6>
                                    <small class="text-muted">Carga #{{ $remesa->nro_carga }}</small><br>
                                    <small class="text-muted">CS: {{ $remesa->centro_servicio ?? 'N/A' }}</small><br>
                                    <small class="text-muted">{{ $remesa->fecha_carga->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@else
    <div class="text-center py-4">
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
        <h5 class="text-muted mt-3">No hay remesas disponibles</h5>
        <p class="text-muted">No se encontraron remesas con los filtros aplicados.</p>
    </div>
@endif

