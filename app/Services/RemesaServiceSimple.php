<?php

namespace App\Services;

use App\Models\Remesa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Versión simplificada del servicio de remesas para debugging
 */
class RemesaServiceSimple
{
    /**
     * Método simplificado para cargar datos masivamente
     */
    public function bulkInsertSimple(array $rows, int $userId, string $fileName, string $nroCarga): array
    {
        Log::info("=== INICIO CARGA SIMPLE ===", [
            'usuario_id' => $userId,
            'archivo' => $fileName,
            'total_registros' => count($rows),
            'nro_carga' => $nroCarga,
            'timestamp' => now()
        ]);

        // Aumentar límites
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $savedRecords = 0;
        $errors = 0;

        try {
            // Verificar duplicados
            $existing = DB::table('remesas')->where('nro_carga', $nroCarga)->first();
            if ($existing) {
                throw new \Exception("Ya existe una remesa con el número de carga: {$nroCarga}");
            }

            Log::info("Iniciando procesamiento de registros");

            // Usar una sola transacción
            DB::transaction(function () use ($rows, $userId, $fileName, $nroCarga, &$savedRecords, &$errors) {
                $batchSize = 100;
                $batches = array_chunk($rows, $batchSize);
                
                foreach ($batches as $batchIndex => $batch) {
                    $batchData = [];
                    
                    foreach ($batch as $row) {
                        try {
                            $record = [
                                'usuario_id' => $userId,
                                'nombre_archivo' => $fileName,
                                'nro_carga' => $nroCarga,
                                'fecha_carga' => now(),
                                'cargado_al_sistema' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                                // Campos principales del DBF
                                'nis' => $row['NIS'] ?? null,
                                'nromedidor' => $row['NROMEDIDOR'] ?? null,
                                'diametro' => $row['DIAMETRO'] ?? null,
                                'clase' => $row['CLASE'] ?? null,
                                'nomclie' => $row['NOMCLIE'] ?? null,
                                'dir_proc' => $row['DIR_PROC'] ?? null,
                                'dir_cata' => $row['DIR_CATA'] ?? null,
                                'tel_clie' => $row['TEL_CLIE'] ?? null
                            ];

                            // Sanitizar/truncar campos según límites de la tabla antes de insertar
                            $record = $this->sanitizeRecordForDb($record);

                            $batchData[] = $record;
                            
                        } catch (\Exception $e) {
                            $errors++;
                            Log::warning('Error procesando registro', [
                                'error' => $e->getMessage(),
                                'registro' => array_slice($row, 0, 3)
                            ]);
                        }
                    }
                    
                    if (!empty($batchData)) {
                        // Usar insertOrIgnore para evitar abortar toda la transacción
                        $before = DB::table('remesas')->where('nro_carga', $nroCarga)->count();
                        $inserted = DB::table('remesas')->insertOrIgnore($batchData);
                        $after = DB::table('remesas')->where('nro_carga', $nroCarga)->count();

                        $insertedCount = max(0, $after - $before);
                        $ignoredCount = count($batchData) - $insertedCount;
                        $savedRecords += $insertedCount;

                        Log::info("Batch procesado", [
                            'batch_numero' => $batchIndex + 1,
                            'registros_en_batch' => count($batchData),
                            'inserted_in_batch' => $insertedCount,
                            'ignored_in_batch' => $ignoredCount,
                            'total_guardados' => $savedRecords
                        ]);
                    }
                }
            });

            Log::info("=== CARGA COMPLETADA ===", [
                'registros_guardados' => $savedRecords,
                'errores' => $errors,
                'nro_carga' => $nroCarga
            ]);

            // Verificación final
            $recordsInDb = DB::table('remesas')->where('nro_carga', $nroCarga)->count();
            Log::info("Verificación final", [
                'esperados' => $savedRecords,
                'en_bd' => $recordsInDb
            ]);

            return [
                'success' => true,
                'saved_records' => $savedRecords,
                'errors' => $errors,
                'message' => "Remesa cargada exitosamente. {$savedRecords} registros guardados."
            ];

        } catch (\Exception $e) {
            Log::error("=== ERROR EN CARGA ===", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'nro_carga' => $nroCarga
            ]);

            throw $e;
        }
    }

    /**
     * Sanitizar y truncar campos según los límites de la tabla `remesas`.
     * Devuelve el registro listo para insertar y registra truncados.
     */
    private function sanitizeRecordForDb(array $record): array
    {
        // Definir límites coherentes con la migración
        // Límites actualizados según la migración de estructura real (2025_09_16_220043)
        $limits = [
            'nombre_archivo' => 255,
            'nro_carga' => 20,
            'nis' => 7,
            'nromedidor' => 10,
            // Diametro en la migración actualizada es 2
            'diametro' => 2,
            'clase' => 1,
            'marcamed' => 20,
            'reclamante' => 60,
            'nomclie' => 60,
            // Aceptar ambos nombres (posibles variaciones): dir_pro / dir_proc
            'dir_pro' => 171,
            'dir_proc' => 171,
            // Referencias catastrales / direcciones
            'ref_cata' => 171,
            'dir_cata' => 171,
            'ref_dir_ca' => 60,
            // ref_dir_pr fue actualizado a 60 en la migración
            'ref_dir_pr' => 60,
            // cup reducido a 12 según migración
            'cup' => 12,
            // tipo_dseg (antes dbo_dseg) ahora tiene 80
            'dbo_dseg' => 80,
            'tipo_dseg' => 80,
            'tarifa' => 30,
            'reclamo' => 15,
            'hrrabas' => 15,
            'regebas' => 12,
            // campos renombrados/ajustados
            'ruta' => 16,
            'ruta_num' => 16,
            'cgv' => 4,
            'gcv' => 4,
            // db_mode renamed from dbo_mode
            'dbo_mode' => 20,
            'db_mode' => 20,
            // otros posibles campos nuevos
            'cus' => 9,
            'cua' => 30,
            'resol' => 3,
            'itin' => 4,
            'cup' => 12,
        ];

        foreach ($limits as $field => $max) {
            if (isset($record[$field]) && is_string($record[$field]) && strlen($record[$field]) > $max) {
                $original = $record[$field];
                $record[$field] = mb_substr($original, 0, $max);
                Log::warning('Campo truncado antes de insert', [
                    'campo' => $field,
                    'longitud_original' => strlen($original),
                    'limite' => $max,
                    'nro_carga' => $record['nro_carga'] ?? null,
                    'nombre_archivo' => $record['nombre_archivo'] ?? null
                ]);
            }
        }

        return $record;
    }
}