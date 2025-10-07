# Componente Actions Dropdown

Un componente reutilizable para crear menús desplegables de acciones consistentes en toda la aplicación.

## Uso Básico

```blade
<x-actions-dropdown :actions="$actions" />
```

## Propiedades

- `actions` (array, requerido): Array de acciones a mostrar
- `align` (string, opcional): Alineación del menú ('start', 'end'). Default: 'end'  
- `size` (string, opcional): Tamaño del botón ('sm', 'md', 'lg'). Default: 'sm'

## Tipos de Acciones

### 1. Link (Enlace)
```php
[
    'type' => 'link',
    'url' => route('ruta.nombre', $id),
    'icon' => 'eye',           // Icono de Bootstrap Icons (sin el 'bi-')
    'text' => 'Ver Detalles',
    'class' => 'text-primary', // Clases CSS adicionales (opcional)
    'target' => '_blank'       // Target del enlace (opcional)
]
```

### 2. Button (Botón con JavaScript)
```php
[
    'type' => 'button',
    'onclick' => "confirmarAccion($id)",
    'icon' => 'trash',
    'text' => 'Eliminar',
    'class' => 'text-danger',
    'data' => [                // Data attributes (opcional)
        'id' => $id,
        'confirm' => 'true'
    ]
]
```

### 3. Form (Formulario)
```php
[
    'type' => 'form',
    'method' => 'POST',        // GET, POST, PUT, DELETE, etc.
    'url' => route('procesar', $id),
    'icon' => 'play-circle',
    'text' => 'Procesar',
    'class' => 'text-success',
    'confirm' => '¿Estás seguro?', // Confirmación opcional
    'inputs' => [              // Inputs hidden (opcional)
        'campo1' => 'valor1',
        'campo2' => 'valor2'
    ]
]
```

### 4. Text (Solo texto informativo)
```php
[
    'type' => 'text',
    'icon' => 'clock',
    'text' => 'Estado: Pendiente',
    'subtitle' => 'Información adicional' // Opcional
]
```

### 5. Divider (Separador)
```php
['type' => 'divider']
```

## Ejemplos Completos

### Remesas
```php
@php
    $actions = [];
    
    if($remesa->cargado_al_sistema) {
        $actions[] = [
            'type' => 'link',
            'url' => route('remesa.ver.registros', $remesa->nro_carga),
            'icon' => 'eye',
            'text' => 'Ver Registros'
        ];
        
        if(Auth::user()->isAdmin()) {
            $actions[] = [
                'type' => 'link',
                'url' => route('remesa.gestionar.registros', $remesa->nro_carga),
                'icon' => 'gear',
                'text' => 'Gestionar Registros'
            ];
        }
    } else {
        $actions[] = [
            'type' => 'form',
            'method' => 'POST',
            'url' => route('remesa.procesar', $remesa->id),
            'icon' => 'play-circle',
            'text' => 'Procesar',
            'class' => 'text-success'
        ];
        
        $actions[] = ['type' => 'divider'];
        
        $actions[] = [
            'type' => 'button',
            'onclick' => "eliminar($remesa->id)",
            'icon' => 'trash',
            'text' => 'Eliminar',
            'class' => 'text-danger'
        ];
    }
@endphp

<x-actions-dropdown :actions="$actions" />
```

### Registros
```php
@php
    $actions = [
        [
            'type' => 'link',
            'url' => route('registro.edit', $registro->id),
            'icon' => 'pencil',
            'text' => 'Editar',
            'class' => 'text-warning'
        ],
        [
            'type' => 'button',
            'onclick' => "verDetalles($registro->id)",
            'icon' => 'eye',
            'text' => 'Ver Detalles'
        ]
    ];
    
    if($registro->editado) {
        $actions[] = [
            'type' => 'link',
            'url' => route('registro.historial', $registro->id),
            'icon' => 'clock-history',
            'text' => 'Ver Historial'
        ];
    }
@endphp

<x-actions-dropdown :actions="$actions" size="sm" />
```

## Clases de Color Predefinidas

- `text-primary`: Azul
- `text-success`: Verde  
- `text-warning`: Amarillo
- `text-danger`: Rojo
- `text-info`: Cyan
- `text-secondary`: Gris

## Iconos Disponibles

Usa cualquier icono de Bootstrap Icons sin el prefijo `bi-`:
- `eye`, `pencil`, `trash`, `gear`, `plus`, `download`, `upload`
- `check-circle`, `x-circle`, `clock`, `clock-history`
- `play-circle`, `pause-circle`, `stop-circle`
- `etc...`

## Notas

- El componente incluye sus propios estilos CSS
- Compatible con todos los tamaños de pantalla
- Soporta confirmaciones automáticas en formularios
- Los formularios incluyen automáticamente tokens CSRF
- Compatible con métodos HTTP diferentes a POST usando @method
