<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RemesaPendiente;
use App\Models\Remesa;

class LimpiarPendientesYaProcesados extends Command
{
    protected $signature = 'remesas:limpiar-procesados';
    protected $description = 'Eliminar remesas pendientes que ya fueron procesadas exitosamente';

    public function handle()
    {
        $this->info('🧹 Limpiando remesas pendientes ya procesadas...');
        
        $pendientes = RemesaPendiente::all();
        $eliminados = 0;
        
        foreach ($pendientes as $pendiente) {
            // Obtener centro de servicio de metadatos
            $centroServicio = $pendiente->datos_dbf['metadata']['centro_servicio'] ?? null;
            
            // Verificar si ya existe en la tabla principal
            $existe = Remesa::where('nro_carga', $pendiente->nro_carga)
                           ->where('usuario_id', $pendiente->usuario_id)
                           ->when($centroServicio, function($query, $cs) {
                               return $query->where('centro_servicio', $cs);
                           })
                           ->exists();
            
            if ($existe) {
                $this->warn("🗑️ Eliminando pendiente ya procesado:");
                $this->line("   ID: {$pendiente->id}");
                $this->line("   Carga: {$pendiente->nro_carga}");
                $this->line("   Archivo: {$pendiente->nombre_archivo}");
                $this->line("   Centro: " . ($centroServicio ?: 'No definido'));
                
                $pendiente->delete();
                $eliminados++;
            }
        }
        
        $this->info("✅ Proceso completado");
        $this->line("📊 Remesas pendientes eliminadas: {$eliminados}");

        return 0;
    }
}