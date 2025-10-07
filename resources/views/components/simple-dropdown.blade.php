@props(['actions' => [], 'align' => 'end', 'size' => null])

<div class="dropdown">
    <button class="btn @if($size) btn-{{ $size }} @endif btn-outline-secondary dropdown-toggle" 
            type="button" 
            data-bs-toggle="dropdown"
            aria-expanded="false">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    
    <ul class="dropdown-menu @if($align === 'end') dropdown-menu-end @endif">
        @foreach($actions as $action)
            @if($action['type'] === 'divider')
                <li><hr class="dropdown-divider"></li>
            @elseif($action['type'] === 'text')
                <li>
                    <span class="dropdown-item-text text-muted">
                        @if(isset($action['icon']))
                            <i class="bi bi-{{ $action['icon'] }} me-2"></i>
                        @endif
                        {{ $action['text'] }}
                        @if(isset($action['subtitle']))
                            <small class="text-muted d-block">{{ $action['subtitle'] }}</small>
                        @endif
                    </span>
                </li>
            @elseif($action['type'] === 'link')
                <li>
                    <a class="dropdown-item {{ $action['class'] ?? '' }}" 
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
                </li>
            @elseif($action['type'] === 'button')
                <li>
                    <button type="button" 
                            class="dropdown-item {{ $action['class'] ?? '' }}"
                            @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
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
                </li>
            @elseif($action['type'] === 'form')
                <li>
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
                        @if(isset($action['inputs']))
                            @foreach($action['inputs'] as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach
                        @endif
                        <button type="submit" class="dropdown-item {{ $action['class'] ?? '' }}">
                            @if(isset($action['icon']))
                                <i class="bi bi-{{ $action['icon'] }} me-2"></i>
                            @endif
                            {{ $action['text'] }}
                            @if(isset($action['subtitle']))
                                <small class="text-muted d-block">{{ $action['subtitle'] }}</small>
                            @endif
                        </button>
                    </form>
                </li>
            @endif
        @endforeach
    </ul>
</div>
