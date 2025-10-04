<?php

namespace App\Console\Commands;

use App\Models\RemesaPendiente;
use Illuminate\Console\Command;

class DebugPendingRemesa extends Command
{
    protected $signature = 'remesas:debug-pending {id}';
    protected $description = 'Depurar datos de una remesa pendiente específica';

    public function handle()
    {
        $id = $this->argument('id');
        $remesa = RemesaPendiente::find($id);
        
        if (!$remesa) {
            $this->error("Remesa con ID {$id} no encontrada");
            return 1;
        }
        
        $this->info("=== DEBUG REMESA PENDIENTE ID: {$id} ===");
        $this->info("Archivo: {$remesa->nombre_archivo}");
        $this->info("Nro Carga: {$remesa->nro_carga}");
        $this->info("Usuario ID: {$remesa->usuario_id}");
        $this->info("Fecha: {$remesa->created_at}");
        
        $datos = $remesa->getDatosDbfArray();
        
        $this->info("\n=== ANÁLISIS DE DATOS ===");
        $this->info("Datos vacíos: " . (empty($datos) ? 'SÍ' : 'NO'));
        
        if (empty($datos)) {
            $this->error("❌ PROBLEMA: datos_dbf está vacío");
            return 1;
        }
        
        $this->info("Tipo de datos: " . gettype($datos));
        
        if (is_array($datos)) {
            $this->info("Claves principales: " . implode(', ', array_keys($datos)));
            
            // Verificar formato nuevo
            if (isset($datos['rows'])) {
                $this->info("✅ Formato NUEVO detectado");
                $this->info("Cantidad de rows: " . count($datos['rows']));
                
                if (empty($datos['rows'])) {
                    $this->error("❌ PROBLEMA: rows está vacío");
                } else {
                    $this->info("✅ Primer registro existe");
                    $primerRegistro = $datos['rows'][0];
                    $this->info("Campos del primer registro: " . implode(', ', array_keys($primerRegistro)));
                }
            }
            // Verificar formato anterior
            elseif (isset($datos[0]) && is_array($datos[0])) {
                $this->info("✅ Formato ANTERIOR detectado");
                $this->info("Cantidad de registros: " . count($datos));
                $primerRegistro = $datos[0];
                $this->info("Campos del primer registro: " . implode(', ', array_keys($primerRegistro)));
            } else {
                $this->error("❌ PROBLEMA: Formato de datos no reconocido");
                $this->info("Estructura actual:");
                $this->line(json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
        
        return 0;
    }
}