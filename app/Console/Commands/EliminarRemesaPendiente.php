<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RemesaPendiente;

class EliminarRemesaPendiente extends Command
{
    protected $signature = 'remesas:eliminar-pendiente {id}';
    protected $description = 'Eliminar una remesa pendiente específica';

    public function handle()
    {
        $id = $this->argument('id');
        
        $pendiente = RemesaPendiente::find($id);
        
        if (!$pendiente) {
            $this->error("❌ No se encontró la remesa pendiente con ID: {$id}");
            return 1;
        }
        
        $this->line("📋 Archivo: {$pendiente->nombre_archivo}");
        $this->line("📋 Nro Carga: {$pendiente->nro_carga}");
        
        if ($this->confirm('¿Eliminar esta remesa pendiente?', false)) {
            $pendiente->delete();
            $this->info("✅ Remesa pendiente eliminada");
        } else {
            $this->info("⏭️ Eliminación cancelada");
        }

        return 0;
    }
}