<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Centros de Servicio SEDAPAL
    |--------------------------------------------------------------------------
    |
    | Lista de centros de servicio disponibles para asignación de remesas.
    | Estos valores son utilizados en los formularios y validaciones.
    |
    */
    
    'centros' => [
        'SEDAPAL ATE' => 'SEDAPAL ATE',
        'SEDAPAL BREÑA' => 'SEDAPAL BREÑA',
        'SEDAPAL CALLAO' => 'SEDAPAL CALLAO',
        'SEDAPAL CLIENTES ESPECIALES' => 'SEDAPAL CLIENTES ESPECIALES',
        'SEDAPAL COMAS' => 'SEDAPAL COMAS',
        'SEDAPAL SAN JUAN DE LURIGANCHO' => 'SEDAPAL SAN JUAN DE LURIGANCHO',
        'SEDAPAL SURQUILLO' => 'SEDAPAL SURQUILLO',
        'SEDAPAL VILLA EL SALVADOR' => 'SEDAPAL VILLA EL SALVADOR',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Centro por Defecto
    |--------------------------------------------------------------------------
    |
    | Centro de servicio que se selecciona por defecto en los formularios.
    | Si es null, no habrá selección predeterminada.
    |
    */
    
    'default' => null,
];