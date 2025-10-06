<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RemesaPendiente;
use App\Http\Controllers\RemesaController;
use Illuminate\Support\Facades\Log;

class ProcesarPendientesCommand extends Command
{
    protected $signature = 'remesas:procesar-pendientes {--usuario_id= : ID del usuario específico a procesar}';
    
    protected $description = 'Procesa todas las remesas pendientes usando procesamiento por lotes para evitar problemas de memoria';

    public function handle()
    {
        $usuarioId = $this->option('usuario_id');
        
        // Contar pendientes (sin ordenar para evitar problemas de memoria)
        $query = RemesaPendiente::query();
        if ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        }
        
        $totalPendientes = $query->count();
        
        if ($totalPendientes === 0) {
            $this->info('✅ No hay archivos pendientes para procesar');
            return Command::SUCCESS;
        }
        
        $this->info("🔄 Procesando {$totalPendientes} archivos pendientes...");
        
        $procesados = [];
        $errores = [];
        $totalRegistros = 0;
        $procesadosCount = 0;
        
        // Crear barra de progreso
        $bar = $this->output->createProgressBar($totalPendientes);
        $bar->start();
        
        try {
            // Usar RemesaService directamente para evitar problemas con el controlador
            $service = new \App\Services\RemesaService();
            
            // Crear nueva query sin ordenar para el chunk
            $chunkQuery = RemesaPendiente::query();
            if ($usuarioId) {
                $chunkQuery->where('usuario_id', $usuarioId);
            }
            
            $chunkQuery->chunk(5, function ($archivosPendientes) use (&$procesados, &$errores, &$totalRegistros, &$procesadosCount, $totalPendientes, $bar, $service) {
                foreach ($archivosPendientes as $pendiente) {
                    $procesadosCount++;
                    
                    try {
                        $resultado = $service->procesarPendienteCompleto($pendiente);
                        
                        if ($resultado['success']) {
                            $procesados[] = [
                                'archivo' => $pendiente->nombre_archivo,
                                'nro_carga' => $pendiente->nro_carga,
                                'registros' => $resultado['registros_insertados']
                            ];
                            $totalRegistros += $resultado['registros_insertados'];
                            
                            // Eliminar el pendiente después del procesamiento exitoso
                            $pendiente->delete();
                        } else {
                            $errores[] = "Error en {$pendiente->nombre_archivo}: {$resultado['error']}";
                        }
                    } catch (\Exception $e) {
                        $errores[] = "Error crítico en {$pendiente->nombre_archivo}: " . $e->getMessage();
                        Log::error('Error crítico procesando pendiente desde comando', [
                            'archivo' => $pendiente->nombre_archivo,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    $bar->advance();
                }
            });
            
            $bar->finish();
            $this->newLine(2);
            
            // Mostrar resultados
            $this->info("✅ Procesamiento completado:");
            $this->line("   • Archivos procesados: " . count($procesados));
            $this->line("   • Registros insertados: {$totalRegistros}");
            
            if (!empty($errores)) {
                $this->warn("⚠️  Errores encontrados: " . count($errores));
                foreach ($errores as $error) {
                    $this->error("   • {$error}");
                }
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Error general: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}