<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración del Sistema de Remesas
    |--------------------------------------------------------------------------
    |
    | Configuraciones específicas para el procesamiento de archivos DBF
    | y la gestión de remesas del sistema.
    |
    */

    'dbf' => [
        /*
        |--------------------------------------------------------------------------
        | Configuración de Archivos DBF
        |--------------------------------------------------------------------------
        */
        'max_file_size' => env('REMESA_MAX_FILE_SIZE', 51200), // KB (50MB por defecto)
        'allowed_extensions' => ['dbf'],
        'temp_storage_disk' => env('REMESA_TEMP_DISK', 'local'),
        'temp_storage_path' => 'temp_dbf',
        
        /*
        |--------------------------------------------------------------------------
        | Configuración de Procesamiento
        |--------------------------------------------------------------------------
        */
        'processing' => [
            'memory_limit' => env('REMESA_MEMORY_LIMIT', '1024M'),
            'time_limit' => env('REMESA_TIME_LIMIT', 600), // 10 minutos
            'batch_size' => env('REMESA_BATCH_SIZE', 500),
            'log_progress_every' => env('REMESA_LOG_EVERY', 1000), // registros
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Configuración de Codificación
        |--------------------------------------------------------------------------
        */
        'encoding' => [
            'from' => env('REMESA_ENCODING_FROM', 'Windows-1252'),
            'to' => env('REMESA_ENCODING_TO', 'UTF-8'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Paginación
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'preview_per_page' => env('REMESA_PREVIEW_PER_PAGE', 50),
        'list_per_page' => env('REMESA_LIST_PER_PAGE', 20),
        'records_per_page' => env('REMESA_RECORDS_PER_PAGE', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Validación
    |--------------------------------------------------------------------------
    */
    'validation' => [
        /*
        |--------------------------------------------------------------------------
        | Límites de Campos de Texto (Actualizados según estructura DBF)
        |--------------------------------------------------------------------------
        */
        'field_limits' => [
            'nis' => 7,
            'nromedidor' => 10,
            'diametro' => 2,
            'clase' => 1,
            'cus' => 9,
            'marcamed' => 20,
            'reclamante' => 60,
            'nomclie' => 60,
            'dir_proc' => 171,
            'dir_cata' => 171,
            'resol' => 3,
            'itin' => 4,
            'aol' => 4,
            'correcarta' => 6,
            'emisor' => 9,
            'especial' => 1,
            'reconsi' => 2,
            'hrabas' => 15,
            'regabas' => 12,
            'cgv' => 4,
            'db_mode' => 20,
            'ref_dir_ca' => 60,
            'ref_dir_pr' => 60,
            'cup' => 12,
            'tipo_dseg' => 80,
            'cua' => 30,
            'tarifa' => 30,
            'reclamo' => 15,
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Mapeo de Campos DBF a Modelo (Actualizado según nueva estructura)
        |--------------------------------------------------------------------------
        */
        'field_mapping' => [
            'NIS' => 'nis',
            'NROMEDIDOR' => 'nromedidor',
            'DIAMETRO' => 'diametro',
            'CLASE' => 'clase',
            'RETFEC' => 'retfech',      // Cambio: RETFECH -> RETFEC
            'RETHOR' => 'rethor',       // Cambio: RETCHOR -> RETHOR
            'FECHAPROG' => 'fechaprog', // Cambio: FECHARROG -> FECHAPROG
            'FECHAING' => 'fechaing',
            'TEL_CLIE' => 'tel_clie',
            'HORAPROG' => 'horaprog',
            'CUS' => 'cus',             // Cambio: CUJS -> CUS
            'F_INST' => 'f_inst',       // Cambio: JINST -> F_INST
            'MARCAMED' => 'marcamed',
            'RECLAMANTE' => 'reclamante',
            'NOMCLIE' => 'nomclie',     // Cambio: NOMCLI -> NOMCLIE
            'DIR_PROC' => 'dir_proc',   // Cambio: DIR_PRO -> DIR_PROC
            'DIR_CATA' => 'dir_cata',   // Cambio: REF_CATA -> DIR_CATA
            'TIPO_AFE' => 'tipo_afe',   // Nuevo campo
            'RESOL' => 'resol',         // Cambio: RSOL -> RESOL
            'ITIN' => 'itin',           // Cambio: TIN -> ITIN
            'AOL' => 'aol',
            'CORRECARTA' => 'correcarta',
            'NROCARGA' => 'nrocarga_dbf', // Nuevo campo específico del DBF
            'EMISOR' => 'emisor',       // Nuevo campo
            'ESPECIAL' => 'especial',
            'RECONSI' => 'reconsi',
            'F_RECLAMO' => 'f_reclamo', // Cambio: RETFECLAMD -> F_RECLAMO
            'HRPROM' => 'hrprom',       // Cambio: RETCHORROM -> HRPROM
            'HRABAS' => 'hrabas',       // Cambio: HRRABAS -> HRABAS
            'REGABAS' => 'regabas',     // Cambio: REGEBAS -> REGABAS
            'EMPRESA' => 'empresa',
            'MASIVO' => 'masivo_bool',  // Cambio: tipo lógico
            'RUTA' => 'ruta_num',       // Cambio: tipo numérico
            'CGV' => 'cgv',             // Cambio: GCV -> CGV
            'DB_MODE' => 'db_mode',     // Cambio: DBO_MODE -> DB_MODE
            'DB_AFAB' => 'db_afab',     // Cambio: DBO_AFAB -> DB_AFAB
            'DBQ_MAX' => 'dbq_max',     // Cambio: DBO_MAX -> DBQ_MAX
            'DBQ_MIN' => 'dbq_min',     // Cambio: DBO_MIN -> DBQ_MIN
            'DBQ_PERM' => 'dbq_perm',   // Cambio: DBO_PERM -> DBQ_PERM
            'DBQ_TRAN' => 'dbq_tran',   // Cambio: DBO_TRAN -> DBQ_TRAN
            'REF_DIR_CA' => 'ref_dir_ca',
            'REF_DIR_PR' => 'ref_dir_pr',
            'CUP' => 'cup',
            'TIPO_DSEG' => 'tipo_dseg', // Cambio: DBO_DSEG -> TIPO_DSEG
            'CUA' => 'cua',             // Nuevo campo
            'TARIFA' => 'tarifa',
            'RECLAMO' => 'reclamo',
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Tipos de Campos (Actualizados según nueva estructura)
        |--------------------------------------------------------------------------
        */
        'field_types' => [
            'date_fields' => [
                'retfech', 'fechaprog', 'fechaing', 'f_inst', 'f_reclamo'
            ],
            'integer_fields' => [
                'tel_clie', 'tipo_afe', 'nrocarga_dbf', 'empresa', 'hrprom', 'db_afab', 'ruta_num'
            ],
            'decimal_fields' => [
                'rethor', 'horaprog', 'dbq_max', 'dbq_min', 'dbq_perm', 'dbq_tran'
            ],
            'boolean_fields' => [
                'masivo_bool'
            ],
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Campos para Extracción de Número de Carga (Actualizados)
        |--------------------------------------------------------------------------
        */
        'nro_carga_fields' => [
            'NROCARGA', 'EMISOR'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'log_uploads' => env('REMESA_LOG_UPLOADS', true),
        'log_processing' => env('REMESA_LOG_PROCESSING', true),
        'log_errors' => env('REMESA_LOG_ERRORS', true),
        'log_changes' => env('REMESA_LOG_CHANGES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Auto-generación
    |--------------------------------------------------------------------------
    */
    'auto_generation' => [
        'nro_carga_prefix' => env('REMESA_AUTO_PREFIX', 'AUTO'),
        'nro_carga_separator' => '_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Limpieza
    |--------------------------------------------------------------------------
    */
    'cleanup' => [
        'auto_delete_temp_files' => env('REMESA_AUTO_DELETE_TEMP', true),
        'temp_file_lifetime' => env('REMESA_TEMP_LIFETIME', 3600), // 1 hora en segundos
    ],

];