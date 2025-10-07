@props(['actions' => [], 'align' => 'end', 'size' => null])

@php
    $modalId = 'actionsModal-' . uniqid();
@endphp

<button class="btn @if($size) btn-{{ $size }} @endif btn-outline-secondary" 
        type="button" 
        data-bs-toggle="modal" 
        data-bs-target="#{{ $modalId }}">
    <i class="bi bi-three-dots-vertical"></i>
</button>

<!-- Modal de acciones -->
<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 300px;">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Acciones</h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                @foreach($actions as $action)
                    @if($action['type'] === 'divider')
                        <hr class="my-2">
                    @elseif($action['type'] === 'text')
                        <div class="text-muted small p-2">
                            @if(isset($action['icon']))
                                <i class="bi bi-{{ $action['icon'] }} me-2"></i>
                            @endif
                            {{ $action['text'] }}
                            @if(isset($action['subtitle']))
                                <small class="text-muted d-block">{{ $action['subtitle'] }}</small>
                            @endif
                        </div>
                    @elseif($action['type'] === 'link')
                        <a class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start {{ $action['class'] ?? '' }}" 
                           href="{{ $action['url'] }}"
                           @if(isset($action['target'])) target="{{ $action['target'] }}" @endif>
                            @if(isset($action['icon']))
                                <i class="bi bi-{{ $action['icon'] }} me-2"></i>
                            @endif
                            {{ $action['text'] }}
                            @if(isset($action['subtitle']))
                                <small class="text-muted d-block">{{ $action['subtitle'] }}</small>
                            @endif
                        </a>
                    @elseif($action['type'] === 'button')
                        <button type="button" 
                                class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start {{ $action['class'] ?? '' }}"
                                @if(isset($action['onclick'])) 
                                    onclick="{{ $action['onclick'] }}; 
                                    var modal = bootstrap.Modal.getInstance(this.closest('.modal')); 
                                    if(modal) modal.hide();" 
                                @endif
                                @if(isset($action['data'])) 
                                    @foreach($action['data'] as $key => $value)
                                        data-{{ $key }}="{{ $value }}"
                                    @endforeach
                                @endif>
                            @if(isset($action['icon']))
                                <i class="bi bi-{{ $action['icon'] }} me-2"></i>
                            @endif
                            {{ $action['text'] }}
                            @if(isset($action['subtitle']))
                                <small class="text-muted d-block">{{ $action['subtitle'] }}</small>
                            @endif
                        </button>
                    @elseif($action['type'] === 'form')
                        <form method="{{ $action['method'] ?? 'POST' }}" 
                              action="{{ $action['url'] }}" 
                              class="m-0"
                              @if(isset($action['confirm'])) onsubmit="return confirm('{{ $action['confirm'] }}')" @endif>
                            @if(strtoupper($action['method'] ?? 'POST') !== 'GET')
                                @csrf
                            @endif
                            @if(isset($action['method']) && strtoupper($action['method']) !== 'POST' && strtoupper($action['method']) !== 'GET')
                                @method($action['method'])
                            @endif
                            <button type="submit" 
                                    class="btn btn-outline-secondary btn-sm w-100 mb-1 text-start {{ $action['class'] ?? '' }}">
                                @if(isset($action['icon']))
                                    <i class="bi bi-{{ $action['icon'] }} me-2"></i>
                                @endif
                                {{ $action['text'] }}
                                @if(isset($action['subtitle']))
                                    <small class="text-muted d-block">{{ $action['subtitle'] }}</small>
                                @endif
                            </button>
                        </form>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>


