<?php

namespace App\Console\Commands;

use App\Models\RemesaPendiente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixPendingRemesasFormat extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'remesas:fix-pending-format {--dry-run : Solo mostrar qué se actualizaría sin hacer cambios}';

    /**
     * The console command description.
     */
    protected $description = 'Actualizar el formato de datos_dbf en remesas pendientes al nuevo formato';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('Iniciando verificación de formato de remesas pendientes...');
        
        $pendientes = RemesaPendiente::all();
        $actualizados = 0;
        $yaCorrectos = 0;
        
        foreach ($pendientes as $pendiente) {
            $datos = $pendiente->getDatosDbfArray();
            
            // Verificar si ya tiene el formato correcto
            if (isset($datos['rows']) && is_array($datos['rows'])) {
                $yaCorrectos++;
                $this->line("✅ {$pendiente->nombre_archivo} - Ya tiene formato correcto");
                continue;
            }
            
            // Si es un array directo de registros (formato anterior)
            if (is_array($datos) && isset($datos[0]) && is_array($datos[0])) {
                $this->warn("🔄 {$pendiente->nombre_archivo} - Necesita actualización (formato anterior)");
                
                if (!$isDryRun) {
                    // Actualizar al nuevo formato
                    $nuevoFormato = [
                        'rows' => $datos,
                        'metadata' => [
                            'total_records' => count($datos),
                            'migrated_at' => now()->toISOString(),
                            'original_format' => 'legacy_direct_array',
                        ]
                    ];
                    
                    $pendiente->datos_dbf = $nuevoFormato;
                    $pendiente->save();
                    
                    $this->info("   ✅ Actualizado correctamente");
                }
                
                $actualizados++;
            } else {
                $this->error("❌ {$pendiente->nombre_archivo} - Formato desconocido");
                $this->line("   Datos: " . json_encode(array_keys($datos), JSON_PRETTY_PRINT));
            }
        }
        
        $this->info("\n=== RESUMEN ===");
        $this->info("Total remesas pendientes: " . $pendientes->count());
        $this->info("Ya con formato correcto: {$yaCorrectos}");
        $this->info("Necesitan actualización: {$actualizados}");
        
        if ($isDryRun) {
            $this->warn("\nEjecución en modo DRY-RUN - No se realizaron cambios");
            $this->info("Para aplicar los cambios ejecuta: php artisan remesas:fix-pending-format");
        } else {
            $this->info("\n✅ Actualización completada");
            Log::info('Comando fix-pending-format ejecutado', [
                'total_pendientes' => $pendientes->count(),
                'ya_correctos' => $yaCorrectos,
                'actualizados' => $actualizados
            ]);
        }
        
        return 0;
    }
}