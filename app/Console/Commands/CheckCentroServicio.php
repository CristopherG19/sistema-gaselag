<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Remesa;
use App\Models\RemesaPendiente;

class CheckCentroServicio extends Command
{
    protected $signature = 'remesas:check-cs {nro_carga}';
    protected $description = 'Verifica los centros de servicio de una remesa específica';

    public function handle()
    {
        $nroCarga = $this->argument('nro_carga');
        
        $this->info("🔍 Verificando centros de servicio para remesa: {$nroCarga}");
        
        // Verificar remesas principales
        $principales = Remesa::where('nro_carga', $nroCarga)->get(['id', 'nro_carga', 'centro_servicio', 'nombre_archivo', 'usuario_id', 'created_at']);
        
        $this->line("📋 REMESAS PRINCIPALES ({$nroCarga}):");
        if ($principales->count() > 0) {
            foreach ($principales->take(5) as $r) {
                $this->line("  - ID: {$r->id} | CS: '{$r->centro_servicio}' | Archivo: {$r->nombre_archivo} | Usuario: {$r->usuario_id} | Fecha: {$r->created_at}");
            }
            if ($principales->count() > 5) {
                $this->line("  ... y " . ($principales->count() - 5) . " registros más");
            }
            
            // Mostrar centros de servicio únicos
            $csUnicos = $principales->pluck('centro_servicio')->unique()->filter();
            $this->line("  📍 Centros de Servicio únicos: " . $csUnicos->implode(', '));
        } else {
            $this->line("  ❌ No existe en tabla principal");
        }
        
        $this->line("");
        
        // Verificar remesas pendientes (extraer CS de datos JSON)
        $pendientes = RemesaPendiente::where('nro_carga', $nroCarga)->get();
        
        $this->line("📋 REMESAS PENDIENTES ({$nroCarga}):");
        if ($pendientes->count() > 0) {
            foreach ($pendientes as $p) {
                // Los datos ya están decodificados por el cast en el modelo
                $datosDbf = $p->datos_dbf;
                $centroServicio = 'N/A';
                
                if (isset($datosDbf['rows']) && count($datosDbf['rows']) > 0) {
                    $firstRow = $datosDbf['rows'][0];
                    // Buscar posibles campos de centro de servicio
                    $centroServicio = $firstRow['CENTRO_SERVICIO'] ?? 
                                    $firstRow['CS'] ?? 
                                    $firstRow['CENTRO'] ?? 
                                    'No encontrado en JSON';
                }
                
                $this->line("  - ID: {$p->id} | CS: '{$centroServicio}' | Archivo: {$p->nombre_archivo} | Usuario: {$p->usuario_id} | Fecha: {$p->created_at}");
                
                // Mostrar estructura del primer registro para debug
                if (isset($datosDbf['rows'][0])) {
                    $this->line("    � Campos disponibles en JSON: " . implode(', ', array_keys($datosDbf['rows'][0])));
                }
            }
        } else {
            $this->line("  ❌ No existe en tabla pendientes");
        }

        return 0;
    }
}