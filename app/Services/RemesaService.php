<?php

namespace App\Services;

use App\Models\Remesa;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Servicio para manejar la lógica de negocio de Remesas
 * 
 * Centraliza operaciones como guardado masivo, validaciones,
 * conversión de datos y manejo de duplicados
 */
class RemesaService
{
    private DbfParser $dbfParser;

    /**
     * Configuración por defecto para procesamiento masivo
     */
    private function getProcessingConfig(): array
    {
        return [
            'memory_limit' => config('remesas.dbf.processing.memory_limit', '1024M'),
            'time_limit' => config('remesas.dbf.processing.time_limit', 600),
            'batch_size' => config('remesas.dbf.processing.batch_size', 500),
        ];
    }

    /**
     * Mapeo de campos DBF a campos del modelo Remesa
     */
    private function getFieldMapping(): array
    {
        return config('remesas.validation.field_mapping', []);
    }

    /**
     * Límites de caracteres por campo del modelo
     */
    private function getFieldLimits(): array
    {
        return config('remesas.validation.field_limits', []);
    }

    /**
     * Campos que son fechas en formato YYYYMMDD
     */
    private function getDateFields(): array
    {
        return config('remesas.validation.field_types.date_fields', []);
    }

    /**
     * Campos que son números enteros
     */
    private function getIntegerFields(): array
    {
        return config('remesas.validation.field_types.integer_fields', []);
    }

    /**
     * Campos que son números decimales
     */
    private function getDecimalFields(): array
    {
        return config('remesas.validation.field_types.decimal_fields', []);
    }

    /**
     * Campos que son booleanos (lógicos)
     */
    private function getBooleanFields(): array
    {
        return config('remesas.validation.field_types.boolean_fields', []);
    }

    public function __construct(DbfParser $dbfParser = null)
    {
        $this->dbfParser = $dbfParser ?: new DbfParser();
    }

    /**
     * Procesar archivo DBF temporal y extraer datos
     *
     * @param string $filePath Ruta del archivo temporal
     * @return array Datos procesados con información adicional
     * @throws \Exception
     */
    public function processTemporaryFile(string $filePath): array
    {
        $result = $this->dbfParser->parseFile($filePath);

        if (empty($result['rows'])) {
            throw new \Exception('El archivo DBF no contiene datos válidos.');
        }

        $nroCarga = $this->dbfParser->extractNroCarga($result['rows'][0]);

        return [
            'rows' => $result['rows'],
            'fields' => $result['fields'],
            'nro_carga' => $nroCarga,
            'total_records' => count($result['rows'])
        ];
    }

    /**
     * Verificar si existe un número de carga duplicado para un usuario
     *
     * @param string|null $nroCarga
     * @param int $userId
     * @param int|null $excludeRemesaId ID de remesa a excluir de la verificación
     * @return Remesa|null
     */
    public function checkDuplicateNroCarga(?string $nroCarga, int $userId, ?int $excludeRemesaId = null): ?Remesa
    {
        if (!$nroCarga) {
            return null;
        }

        $query = Remesa::where('nro_carga', $nroCarga)
                      ->where('usuario_id', $userId);
        
        if ($excludeRemesaId) {
            $query->where('id', '!=', $excludeRemesaId);
        }
        
        return $query->first();
    }

    /**
     * Cargar datos al sistema de forma masiva
     *
     * @param array $rows Filas de datos a guardar
     * @param int $userId ID del usuario
     * @param string $fileName Nombre del archivo original
     * @param string|null $nroCarga Número de carga (opcional)
     * @param string|null $centroServicio Centro de servicio seleccionado
     * @return array Resultado del procesamiento
     * @throws \Exception
     */
    public function bulkInsert(array $rows, int $userId, string $fileName, ?string $nroCarga = null, ?string $centroServicio = null, ?int $excludeRemesaId = null): array
    {
        Log::info("INICIO: Proceso de carga masiva", [
            'usuario_id' => $userId,
            'archivo' => $fileName,
            'total_registros' => count($rows),
            'centro_servicio' => $centroServicio,
            'timestamp' => now()
        ]);

        $this->setProcessingLimits();

        // Generar número de carga único UNA SOLA VEZ para todo el proceso
        if (!$nroCarga) {
            $nroCarga = $this->generateAutoNroCarga();
        }

        // Generar bloque de OCs secuenciales para todos los registros
        $startingOC = $this->getNextOCRange(count($rows));

        Log::info("Números generados", [
            'nro_carga' => $nroCarga,
            'starting_oc' => $startingOC,
            'total_ocs_needed' => count($rows),
            'archivo' => $fileName
        ]);

        // Verificar duplicados antes de procesar - RECHAZAR INMEDIATAMENTE
        $existingRemesa = $this->checkDuplicateNroCarga($nroCarga, $userId, $excludeRemesaId);
        if ($existingRemesa) {
            Log::warning('Intento de carga duplicada rechazado', [
                'nro_carga' => $nroCarga,
                'usuario_id' => $userId,
                'archivo' => $fileName,
                'existing_file' => $existingRemesa->nombre_archivo,
                'existing_date' => $existingRemesa->fecha_carga
            ]);
            
            throw new \Exception("Ya existe una remesa con el número de carga: {$nroCarga}. Archivo original: {$existingRemesa->nombre_archivo} cargado el {$existingRemesa->fecha_carga}");
        }

        $savedRecords = 0;
        $errors = 0;
        $batchSize = 100; // Volver a lotes más grandes
        $allRecords = []; // Recopilar todos los registros primero
        $currentOC = $startingOC;
        
        // Timestamp único para toda la carga
        $fechaCarga = now();

        Log::info("Iniciando conversión de registros DBF", [
            'total_a_procesar' => count($rows),
            'batch_size' => $batchSize,
            'fecha_carga_unificada' => $fechaCarga
        ]);

        // Primera fase: Convertir todos los registros
        foreach ($rows as $index => $row) {
            try {
                $attributes = $this->convertDbfRowToAttributes($row, $userId, $fileName, $nroCarga, $centroServicio, $currentOC, $fechaCarga);
                $allRecords[] = $attributes;
                $currentOC++; // Incrementar OC para el siguiente registro
                
                if (($index + 1) % 500 == 0) {
                    Log::info("Progreso conversión", [
                        'procesados' => $index + 1,
                        'total' => count($rows),
                        'porcentaje' => round((($index + 1) / count($rows)) * 100, 2)
                    ]);
                }
                
            } catch (\Exception $e) {
                $errors++;
                $currentOC++; // Incrementar OC incluso si hay error para mantener secuencia
                Log::warning('Error procesando registro DBF', [
                    'error' => $e->getMessage(),
                    'fila_index' => $index,
                    'usuario_id' => $userId
                ]);
                
                if ($errors > 100) {
                    throw new \Exception("Demasiados errores durante la conversión. Abortando proceso.");
                }
            }
        }

        Log::info("Conversión completada, iniciando inserción masiva", [
            'registros_convertidos' => count($allRecords),
            'errores_conversion' => $errors
        ]);

        // Segunda fase: Inserción masiva usando DB::transaction
        DB::transaction(function () use ($allRecords, $batchSize, &$savedRecords) {
            $chunks = array_chunk($allRecords, $batchSize);
            
            foreach ($chunks as $chunkIndex => $chunk) {
                try {
                    DB::table('remesas')->insert($chunk);
                    $savedRecords += count($chunk);
                    
                    Log::info("Chunk insertado", [
                        'chunk_numero' => $chunkIndex + 1,
                        'registros_en_chunk' => count($chunk),
                        'total_guardados' => $savedRecords
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('Error insertando chunk', [
                        'chunk_numero' => $chunkIndex + 1,
                        'error' => $e->getMessage(),
                        'registros_en_chunk' => count($chunk)
                    ]);
                    throw $e;
                }
            }
        });

        Log::info('Remesa cargada exitosamente', [
            'usuario_id' => $userId,
            'archivo' => $fileName,
            'nro_carga' => $nroCarga,
            'registros_guardados' => $savedRecords,
            'errores' => $errors
        ]);

        // Verificar que los datos realmente se guardaron
        $recordsInDb = DB::table('remesas')->where('nro_carga', $nroCarga)->count();
        Log::info("Verificación final", [
            'registros_esperados' => $savedRecords,
            'registros_en_bd' => $recordsInDb,
            'nro_carga' => $nroCarga
        ]);

        return [
            'success' => true,
            'saved_records' => $savedRecords,
            'errors' => $errors,
            'message' => $this->generateSuccessMessage($savedRecords, $errors)
        ];
    }

    /**
     * Actualizar un registro específico de remesa con tracking de cambios
     *
     * @param int $id ID del registro
     * @param array $newData Nuevos datos
     * @param int $userId ID del usuario que hace el cambio
     * @return bool
     */
    public function updateRecord(int $id, array $newData, int $userId): bool
    {
        $remesa = Remesa::findOrFail($id);
        $originalData = $remesa->getOriginal();

        // Filtrar solo campos que realmente cambiaron
        $changes = [];
        foreach ($newData as $field => $newValue) {
            if (isset($originalData[$field]) && $originalData[$field] != $newValue) {
                $changes[$field] = [
                    'from' => $originalData[$field],
                    'to' => $newValue
                ];
            }
        }

        if (empty($changes)) {
            return false; // No hay cambios
        }

        // Procesar y validar los nuevos datos
        $processedData = $this->processFieldsForUpdate($newData);

        // Actualizar el registro
        $remesa->update($processedData);

        // Log del cambio
        Log::info('Registro de remesa actualizado', [
            'remesa_id' => $id,
            'usuario_id' => $userId,
            'cambios' => $changes
        ]);

        return true;
    }

    /**
     * Convertir fila de DBF a atributos del modelo Remesa
     *
     * @param array $dbfRow Fila de datos del DBF
     * @param int $userId ID del usuario
     * @param string $fileName Nombre del archivo
     * @param string|null $nroCarga Número de carga
     * @param string|null $centroServicio Centro de servicio seleccionado
     * @param int $ocNumber Número OC específico para este registro
     * @param \Illuminate\Support\Carbon $fechaCarga Timestamp unificado para toda la carga
     * @return array
     */
    private function convertDbfRowToAttributes(array $dbfRow, int $userId, string $fileName, string $nroCarga, ?string $centroServicio = null, int $ocNumber = null, $fechaCarga = null): array
    {
        if (!$fechaCarga) {
            $fechaCarga = now();
        }
        
        $attributes = [
            'usuario_id' => $userId,
            'nombre_archivo' => $fileName,
            'fecha_carga' => $fechaCarga,
            'cargado_al_sistema' => true, // Se marca como cargada porque este método se llama DESPUÉS del procesamiento completo
            'fecha_carga_sistema' => $fechaCarga, // Agregar fecha de carga al sistema
            'oc' => (string)$ocNumber, // Usar el OC específico pasado como parámetro
            'created_at' => $fechaCarga,
            'updated_at' => $fechaCarga,
        ];

        // Mapear campos según la configuración
        $fieldMapping = $this->getFieldMapping();
        
        Log::debug("Mapeando campos DBF", [
            'campos_dbf_disponibles' => array_keys($dbfRow),
            'mapeo_configurado' => count($fieldMapping) . ' campos'
        ]);

        foreach ($fieldMapping as $dbfField => $modelField) {
            $value = $dbfRow[$dbfField] ?? '';
            
            // Limpiar y normalizar el valor antes del procesamiento
            if (is_string($value)) {
                // Convertir a UTF-8 si es necesario
                if (!mb_check_encoding($value, 'UTF-8')) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252,ISO-8859-1,UTF-8');
                }
                
                // Limpiar caracteres de control y problemáticos
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
                
                // Reemplazar caracteres problemáticos específicos
                $value = str_replace(['ñ', 'Ñ', 'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], 
                                   ['n', 'N', 'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], $value);
                
                // Limpiar cualquier carácter no ASCII para evitar problemas
                $value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
                
                $value = trim($value);
                
                // Convertir cadenas vacías a null para evitar errores
                if ($value === '') {
                    $value = null;
                }
            }
            
            $processedValue = $this->processFieldValue($value, $modelField);
            $attributes[$modelField] = $processedValue;
        }

        // Establecer número de carga - SIEMPRE usar el que se pasa como parámetro
        $attributes['nro_carga'] = $nroCarga;

        // Establecer centro de servicio si se proporciona
        if ($centroServicio) {
            $attributes['centro_servicio'] = $centroServicio;
        }

        Log::debug("Registro DBF convertido", [
            'nis' => $attributes['nis'] ?? 'NO_DEFINIDO',
            'nro_carga' => $attributes['nro_carga'],
            'oc' => $attributes['oc'],
            'centro_servicio' => $attributes['centro_servicio'] ?? 'NO_DEFINIDO',
            'total_campos' => count($attributes)
        ]);

        return $attributes;
    }

    /**
     * Procesar valor de un campo según su tipo
     *
     * @param mixed $value Valor original
     * @param string $fieldName Nombre del campo
     * @return mixed Valor procesado
     */
    private function processFieldValue($value, string $fieldName)
    {
        // Si el valor es null, retornar null directamente
        if ($value === null) {
            return null;
        }
        
        // Procesar fechas YYYYMMDD
        if (in_array($fieldName, $this->getDateFields())) {
            return $this->parseDate($value);
        }
        
        // Procesar números enteros
        if (in_array($fieldName, $this->getIntegerFields())) {
            return !empty($value) ? (int) $value : null;
        }
        
        // Procesar números decimales
        if (in_array($fieldName, $this->getDecimalFields())) {
            return !empty($value) ? (float) $value : null;
        }
        
        // Procesar campos booleanos (lógicos)
        if (in_array($fieldName, $this->getBooleanFields())) {
            return $this->parseBoolean($value);
        }
        
        // Procesar campos de texto
        return $this->processTextField($value, $fieldName);
    }

    /**
     * Procesar fecha en formato YYYYMMDD
     *
     * @param string|null $value
     * @return string|null
     */
    private function parseDate(?string $value): ?string
    {
        if (empty($value) || strlen($value) !== 8) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Ymd', $value);
            return $date->toDateString();
        } catch (\Exception $e) {
            Log::warning('Error parseando fecha', [
                'valor' => $value,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Parsear valor booleano desde formato DBF lógico
     *
     * @param mixed $value
     * @return bool|null
     */
    private function parseBoolean($value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Convertir a string para evaluar
        $value = strtolower(trim((string) $value));

        // Valores que se consideran verdaderos
        if (in_array($value, ['t', 'true', '1', 'y', 'yes', 'si', 'sí'])) {
            return true;
        }

        // Valores que se consideran falsos
        if (in_array($value, ['f', 'false', '0', 'n', 'no'])) {
            return false;
        }

        // Si no se puede determinar, retornar null
        return null;
    }

    /**
     * Procesar campo de texto con límites
     *
     * @param string|null $value
     * @param string $fieldName
     * @return string|null
     */
    private function processTextField(?string $value, string $fieldName): ?string
    {
        // Si el valor es null, retornar null
        if ($value === null) {
            return null;
        }
        
        $cleanValue = trim($value);
        
        // Si después del trim queda vacío, retornar null
        if ($cleanValue === '') {
            return null;
        }
        
        // Aplicar límite si existe
        $fieldLimits = $this->getFieldLimits();
        if (isset($fieldLimits[$fieldName])) {
            $limit = $fieldLimits[$fieldName];
            
            if (strlen($cleanValue) > $limit) {
                Log::warning("Campo '{$fieldName}' truncado", [
                    'valor_original' => $cleanValue,
                    'limite' => $limit
                ]);
                $cleanValue = substr($cleanValue, 0, $limit);
            }
        }
        
        return $cleanValue;
    }

    /**
     * Procesar campos para actualización
     *
     * @param array $data
     * @return array
     */
    private function processFieldsForUpdate(array $data): array
    {
        $processed = [];
        
        foreach ($data as $field => $value) {
            $processed[$field] = $this->processFieldValue($value, $field);
        }
        
        return $processed;
    }

    /**
     * Generar número de carga automático
     *
     * @return string
     */
    private function generateAutoNroCarga(): string
    {
        return 'AUTO_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Generar próximo OC único secuencial
     *
     * @return string
     */
    private function generateNextOC(): string
    {
        // Obtener el último OC de la base de datos
        $lastOC = DB::table('remesas')
                   ->whereNotNull('oc')
                   ->where('oc', 'REGEXP', '^[0-9]+$') // Solo OCs numéricos
                   ->orderByRaw('CAST(oc AS UNSIGNED) DESC')
                   ->value('oc');

        // Si no hay OCs previos, empezar desde 100001
        if (!$lastOC) {
            return '100001';
        }

        // Incrementar el último OC
        $nextNumber = (int)$lastOC + 1;
        
        return (string)$nextNumber;
    }

    /**
     * Obtener el próximo OC para un rango de registros
     * Reserva un bloque de OCs secuenciales para evitar conflictos
     *
     * @param int $quantity Cantidad de OCs que se necesitan
     * @return int Primer OC del rango reservado
     */
    private function getNextOCRange(int $quantity): int
    {
        // Usar transacción para evitar condiciones de carrera
        return DB::transaction(function () use ($quantity) {
            // Obtener el último OC de la base de datos
            $lastOC = DB::table('remesas')
                       ->whereNotNull('oc')
                       ->where('oc', 'REGEXP', '^[0-9]+$') // Solo OCs numéricos
                       ->lockForUpdate() // Bloquear para evitar conflictos
                       ->orderByRaw('CAST(oc AS UNSIGNED) DESC')
                       ->value('oc');

            // Si no hay OCs previos, empezar desde 100001
            if (!$lastOC) {
                return 100001;
            }

            // Retornar el siguiente OC después del último
            return (int)$lastOC + 1;
        });
    }

    /**
     * Insertar lote de registros
     *
     * @param array $batch
     */
    private function insertBatch(array $batch): void
    {
        Log::debug('Iniciando inserción de lote', [
            'cantidad_registros' => count($batch),
            'memoria_usada' => memory_get_usage(true) / 1024 / 1024 . ' MB'
        ]);

        try {
            // Insertar usando insert directo para mejor rendimiento
            $result = DB::table('remesas')->insert($batch);
            
            Log::debug('Lote insertado exitosamente', [
                'cantidad_registros' => count($batch),
                'resultado' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en inserción de lote', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'cantidad_registros' => count($batch),
                'primer_registro' => !empty($batch) ? array_slice($batch[0], 0, 5) : 'vacio'
            ]);
            
            // Intentar insertar uno por uno para identificar registros problemáticos
            $this->insertBatchOneByOne($batch);
        }
    }

    /**
     * Insertar registros uno por uno cuando falla el batch
     */
    private function insertBatchOneByOne(array $batch): void
    {
        Log::warning('Insertando lote uno por uno debido a error en batch');
        
        foreach ($batch as $index => $record) {
            try {
                DB::table('remesas')->insert($record);
            } catch (\Exception $e) {
                Log::error('Error insertando registro individual', [
                    'error' => $e->getMessage(),
                    'index' => $index,
                    'nis' => $record['nis'] ?? 'desconocido',
                    'nromedidor' => $record['nromedidor'] ?? 'desconocido'
                ]);
            }
        }
    }

    /**
     * Configurar límites de procesamiento
     */
    private function setProcessingLimits(): void
    {
        $config = $this->getProcessingConfig();
        ini_set('memory_limit', $config['memory_limit']);
        set_time_limit($config['time_limit']);
        
        // Configuraciones adicionales para archivos grandes
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('mysql.connect_timeout', 60);
        
        // Si es posible, ajustar también timeouts de conexión de base de datos
        try {
            DB::statement('SET SESSION wait_timeout = 300');
            DB::statement('SET SESSION interactive_timeout = 300');
        } catch (\Exception $e) {
            Log::warning('No se pudieron ajustar timeouts de MySQL', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Log de progreso de lotes
     *
     * @param int $saved
     * @param int $total
     */
    private function logBatchProgress(int $saved, int $total): void
    {
        Log::info("Lote de remesa procesado", [
            'registros_guardados' => $saved,
            'total' => $total,
            'porcentaje' => round(($saved / $total) * 100, 2)
        ]);
    }

    /**
     * Forzar recolección de basura
     */
    private function performGarbageCollection(): void
    {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    /**
     * Generar mensaje de éxito
     *
     * @param int $saved
     * @param int $errors
     * @return string
     */
    private function generateSuccessMessage(int $saved, int $errors): string
    {
        $message = "Remesa cargada exitosamente. {$saved} registros procesados.";
        
        if ($errors > 0) {
            $message .= " ({$errors} registros omitidos por errores)";
        }
        
        return $message;
    }
}