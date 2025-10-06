<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RemesaPendiente;

class DebugRemesasPendientes extends Command
{
    protected $signature = 'debug:remesas-pendientes';
    protected $description = 'Verificar el estado de las remesas pendientes';

    public function handle()
    {
        $this->info('🔍 Estado actual de remesas pendientes');
        
        $pendientes = RemesaPendiente::orderBy('created_at', 'desc')->get();
        
        $this->line("📊 Total de remesas pendientes: {$pendientes->count()}");
        $this->line("");
        
        foreach ($pendientes as $p) {
            $this->line("📋 ID: {$p->id}");
            $this->line("   Carga: {$p->nro_carga}");
            $this->line("   Archivo: {$p->nombre_archivo}");
            $this->line("   Usuario: {$p->usuario_id}");
            $this->line("   Fecha: {$p->created_at}");
            
            if (isset($p->datos_dbf['metadata'])) {
                $metadata = $p->datos_dbf['metadata'];
                $this->line("   Centro: " . ($metadata['centro_servicio'] ?? 'No definido'));
                $this->line("   Registros: " . ($metadata['total_records'] ?? 0));
            } else {
                $this->line("   ⚠️ Sin metadatos");
            }
            
            if (isset($p->datos_dbf['rows'])) {
                $this->line("   Filas disponibles: " . count($p->datos_dbf['rows']));
            } else {
                $this->line("   ⚠️ Sin filas de datos");
            }
            
            $this->line("---");
        }

        return 0;
    }
}