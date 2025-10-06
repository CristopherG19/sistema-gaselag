<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RemesaPendiente;
use App\Services\RemesaService;

class ProcesarRemesaPendiente extends Command
{
    protected $signature = 'remesas:procesar-pendiente {id}';
    protected $description = 'Procesar una remesa pendiente específica';

    public function handle()
    {
        $id = $this->argument('id');
        
        $this->info("🔄 Procesando remesa pendiente ID: {$id}");
        
        $pendiente = RemesaPendiente::find($id);
        
        if (!$pendiente) {
            $this->error("❌ No se encontró la remesa pendiente con ID: {$id}");
            return 1;
        }
        
        $this->line("📋 Archivo: {$pendiente->nombre_archivo}");
        $this->line("📋 Nro Carga: {$pendiente->nro_carga}");
        $this->line("📋 Usuario: {$pendiente->usuario_id}");
        
        $centroServicio = $pendiente->datos_dbf['metadata']['centro_servicio'] ?? 'No definido';
        $this->line("📋 Centro de Servicio: {$centroServicio}");
        
        $totalRegistros = $pendiente->datos_dbf['metadata']['total_records'] ?? 0;
        $this->line("📋 Total Registros: {$totalRegistros}");
        
        if ($this->confirm('¿Procesar esta remesa pendiente?', true)) {
            try {
                $service = new RemesaService();
                $resultado = $service->procesarPendienteCompleto($pendiente);
                
                if ($resultado['success']) {
                    $this->info("✅ Procesamiento exitoso");
                    $this->line("📊 Registros insertados: " . ($resultado['registros_insertados'] ?? 0));
                    $this->info("🗑️ Remesa pendiente eliminada");
                } else {
                    $this->error("❌ Error en procesamiento: " . ($resultado['error'] ?? 'Error desconocido'));
                }
                
            } catch (\Exception $e) {
                $this->error("❌ Excepción durante procesamiento: " . $e->getMessage());
                return 1;
            }
        } else {
            $this->info("⏭️ Procesamiento cancelado");
        }

        return 0;
    }
}