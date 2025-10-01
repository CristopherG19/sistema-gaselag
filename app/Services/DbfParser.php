<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Servicio para procesar archivos DBF
 * 
 * Maneja la lectura, parsing y conversión de archivos DBF
 * con optimizaciones para archivos grandes
 */
class DbfParser
{
    /**
     * Configuración por defecto para el procesamiento
     */
    private const DEFAULT_CONFIG = [
        'memory_limit' => '1024M',
        'time_limit' => 300,
        'batch_size' => 1000,
        'encoding_from' => 'Windows-1252',
        'encoding_to' => 'UTF-8'
    ];

    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
    }

    /**
     * Procesar archivo DBF y extraer datos
     *
     * @param string $filePath Ruta completa al archivo DBF
     * @return array Array con 'rows' y 'fields'
     * @throws \Exception Si hay error procesando el archivo
     */
    public function parseFile(string $filePath): array
    {
        $this->setProcessingLimits();

        $rows = [];
        $handle = null;
        
        try {
            $handle = $this->openFile($filePath);
            $fileStructure = $this->readFileStructure($handle);
            $fields = $this->readFields($handle, $fileStructure);
            $rows = $this->readRecords($handle, $fileStructure, $fields);

            // Extraer nro_carga del primer registro si existe para mantener
            // consistencia en la estructura de retorno (evita 'Undefined array key')
            $nroCarga = null;
            if (!empty($rows) && is_array($rows[0])) {
                $nroCarga = $this->extractNroCarga($rows[0]);
            }

            return [
                'rows' => $rows,
                'fields' => $fields,
                'nro_carga' => $nroCarga,
            ];

        } catch (\Exception $e) {
            Log::error('Error procesando archivo DBF', [
                'archivo' => $filePath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } finally {
            if ($handle) {
                fclose($handle);
            }
        }
    }

    /**
     * Extraer número de carga del primer registro
     *
     * @param array $firstRow Primera fila de datos
     * @return string|null
     */
    public function extractNroCarga(array $firstRow): ?string
    {
        $possibleFields = config('remesas.validation.nro_carga_fields', ['NROCARGA', 'ENUSORGA']);
        
        foreach ($possibleFields as $field) {
            if (isset($firstRow[$field]) && !empty(trim($firstRow[$field]))) {
                return trim($firstRow[$field]);
            }
        }
        
        return null;
    }

    /**
     * Configurar límites de procesamiento
     */
    private function setProcessingLimits(): void
    {
        ini_set('memory_limit', $this->config['memory_limit']);
        set_time_limit($this->config['time_limit']);
    }

    /**
     * Abrir archivo DBF para lectura
     *
     * @param string $filePath
     * @return resource
     * @throws \Exception
     */
    private function openFile(string $filePath)
    {
        $handle = fopen($filePath, 'rb');
        
        if (!$handle) {
            throw new \Exception("No se pudo abrir el archivo: {$filePath}");
        }

        return $handle;
    }

    /**
     * Leer estructura básica del archivo DBF
     *
     * @param resource $handle
     * @return array
     * @throws \Exception
     */
    private function readFileStructure($handle): array
    {
        $basicHeader = fread($handle, 12);
        
        if (strlen($basicHeader) < 12) {
            throw new \Exception("Archivo DBF corrupto: cabecera insuficiente");
        }

        $headerData = unpack('Cversion/C3date/Vrecords/vheaderSize/vrecordSize', $basicHeader);
        
        return [
            'version' => $headerData['version'],
            'records' => $headerData['records'],
            'headerSize' => $headerData['headerSize'],
            'recordSize' => $headerData['recordSize']
        ];
    }

    /**
     * Leer definición de campos del archivo DBF
     *
     * @param resource $handle
     * @param array $fileStructure
     * @return array
     */
    private function readFields($handle, array $fileStructure): array
    {
        fseek($handle, 32);
        
        $fields = [];
        $currentPos = 32;
        $fieldOffset = 1; // Primer byte es para marca de borrado

        while ($currentPos < $fileStructure['headerSize'] - 1) {
            $fieldData = fread($handle, 32);
            
            if (strlen($fieldData) < 32) {
                break;
            }

            $fieldName = $this->extractFieldName($fieldData);
            
            if (empty($fieldName)) {
                break;
            }

            $fieldType = $fieldData[11];
            $fieldLength = ord($fieldData[16]);
            
            if ($fieldLength == 0) {
                $fieldLength = 10; // Valor por defecto
            }

            $fields[] = [
                'name' => $fieldName,
                'type' => $fieldType,
                'length' => $fieldLength,
                'offset' => $fieldOffset
            ];

            $fieldOffset += $fieldLength;
            $currentPos += 32;
        }

        return $fields;
    }

    /**
     * Extraer nombre de campo de la definición
     *
     * @param string $fieldData
     * @return string
     */
    private function extractFieldName(string $fieldData): string
    {
        $fieldName = '';
        
        for ($i = 0; $i < 11; $i++) {
            if (ord($fieldData[$i]) == 0) {
                break;
            }
            $fieldName .= $fieldData[$i];
        }

        return trim($fieldName);
    }

    /**
     * Leer todos los registros del archivo
     *
     * @param resource $handle
     * @param array $fileStructure
     * @param array $fields
     * @return array
     */
    private function readRecords($handle, array $fileStructure, array $fields): array
    {
        fseek($handle, $fileStructure['headerSize']);
        
        $rows = [];
        $processedRecords = 0;
        $batchSize = $this->config['batch_size'];

        for ($i = 0; $i < $fileStructure['records']; $i++) {
            $record = fread($handle, $fileStructure['recordSize']);
            
            if (strlen($record) < $fileStructure['recordSize']) {
                break;
            }

            // Saltar registros marcados como eliminados
            if (ord($record[0]) == 0x2A) {
                continue;
            }

            $row = $this->parseRecord($record, $fields);

            if ($this->hasValidData($row)) {
                $rows[] = $row;
                $processedRecords++;
                
                // Log de progreso y limpieza de memoria
                if ($processedRecords % $batchSize === 0) {
                    $this->logProgress($processedRecords, $fileStructure['records']);
                    $this->performGarbageCollection();
                }
            }
        }

        Log::info("Procesamiento DBF completado", [
            'registros_procesados' => $processedRecords,
            'total_registros' => $fileStructure['records']
        ]);

        return $rows;
    }

    /**
     * Procesar un registro individual
     *
     * @param string $record
     * @param array $fields
     * @return array
     */
    private function parseRecord(string $record, array $fields): array
    {
        $row = [];

        foreach ($fields as $field) {
            if ($field['offset'] + $field['length'] <= strlen($record)) {
                $value = substr($record, $field['offset'], $field['length']);
                $value = trim($value, " \0");
                $value = $this->convertEncoding($value);
                $row[$field['name']] = $value;
            } else {
                $row[$field['name']] = '';
            }
        }

        return $row;
    }

    /**
     * Convertir codificación de caracteres
     *
     * @param string $value
     * @return string
     */
    private function convertEncoding(string $value): string
    {
        if (!mb_check_encoding($value, $this->config['encoding_to'])) {
            $value = mb_convert_encoding(
                $value, 
                $this->config['encoding_to'], 
                $this->config['encoding_from']
            );
        }
        
        return mb_convert_encoding($value, $this->config['encoding_to'], $this->config['encoding_to']);
    }

    /**
     * Verificar si un registro tiene datos válidos
     *
     * @param array $row
     * @return bool
     */
    private function hasValidData(array $row): bool
    {
        foreach ($row as $value) {
            if (!empty(trim($value))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Log de progreso del procesamiento
     *
     * @param int $processed
     * @param int $total
     */
    private function logProgress(int $processed, int $total): void
    {
        Log::info("Progreso procesamiento DBF", [
            'procesados' => $processed,
            'total' => $total,
            'porcentaje' => round(($processed / $total) * 100, 2)
        ]);
    }

    /**
     * Forzar recolección de basura para liberar memoria
     */
    private function performGarbageCollection(): void
    {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
}