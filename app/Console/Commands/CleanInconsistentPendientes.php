<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Remesa;
use App\Models\RemesaPendiente;
use Illuminate\Support\Facades\DB;

class CleanInconsistentPendientes extends Command
{
    protected $signature = 'remesas:clean-inconsistent';
    protected $description = 'Elimina remesas pendientes que ya existen en la tabla principal';

    public function handle()
    {
        $this->info('🔍 Buscando remesas pendientes inconsistentes...');

        // Obtener todas las remesas pendientes
        $pendientes = RemesaPendiente::all();
        $inconsistencias = 0;
        $eliminadas = 0;

        foreach ($pendientes as $pendiente) {
            // Obtener centro de servicio del pendiente
            $centroServicioPendiente = null;
            if (isset($pendiente->datos_dbf['metadata']['centro_servicio'])) {
                $centroServicioPendiente = $pendiente->datos_dbf['metadata']['centro_servicio'];
            }
            
            // Verificar si existe en la tabla principal considerando centro de servicio
            $query = Remesa::where('nro_carga', $pendiente->nro_carga)
                          ->where('usuario_id', $pendiente->usuario_id);
            
            if ($centroServicioPendiente) {
                $query->where('centro_servicio', $centroServicioPendiente);
            }
            
            $existeEnPrincipal = $query->exists();

            if ($existeEnPrincipal) {
                $inconsistencias++;
                $this->warn("⚠️  Inconsistencia encontrada:");
                $this->line("   - Pendiente ID: {$pendiente->id}");
                $this->line("   - Nro Carga: {$pendiente->nro_carga}");
                $this->line("   - Archivo: {$pendiente->nombre_archivo}");
                $this->line("   - Usuario: {$pendiente->usuario_id}");
                
                if ($this->confirm("¿Eliminar esta remesa pendiente?", true)) {
                    $pendiente->delete();
                    $eliminadas++;
                    $this->info("   ✅ Eliminada");
                } else {
                    $this->info("   ⏭️  Omitida");
                }
                $this->line("");
            }
        }

        if ($inconsistencias === 0) {
            $this->info('✅ No se encontraron inconsistencias.');
        } else {
            $this->info("📊 Resumen:");
            $this->line("   - Inconsistencias encontradas: {$inconsistencias}");
            $this->line("   - Remesas pendientes eliminadas: {$eliminadas}");
            $this->line("   - Remesas pendientes conservadas: " . ($inconsistencias - $eliminadas));
        }

        return 0;
    }
}