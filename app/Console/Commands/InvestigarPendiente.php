<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RemesaPendiente;

class InvestigarPendiente extends Command
{
    protected $signature = 'remesas:investigar-pendiente';
    protected $description = 'Investigar la última remesa pendiente para diagnosticar problemas';

    public function handle()
    {
        $pendientes = RemesaPendiente::all();
        
        if ($pendientes->isEmpty()) {
            $this->info('✅ No hay remesas pendientes');
            return Command::SUCCESS;
        }
        
        $this->info("🔍 Investigando {$pendientes->count()} remesa(s) pendiente(s):");
        
        foreach ($pendientes as $index => $pendiente) {
            $this->newLine();
            $this->info("📄 Remesa pendiente #" . ($index + 1));
            $this->line("   • ID: {$pendiente->id}");
            $this->line("   • Archivo: {$pendiente->nombre_archivo}");
            $this->line("   • Nro Carga: {$pendiente->nro_carga}");
            $this->line("   • Usuario ID: {$pendiente->usuario_id}");
            $this->line("   • Fecha: {$pendiente->created_at}");
            
            // Analizar datos JSON
            $datosDbf = $pendiente->getDatosDbfArray();
            
            if (empty($datosDbf)) {
                $this->error("   ❌ datos_dbf está vacío o es inválido");
                $this->line("   • Contenido raw: " . substr($pendiente->datos_dbf, 0, 200) . "...");
            } else {
                $this->info("   ✅ datos_dbf contiene datos");
                
                // Verificar formato
                if (isset($datosDbf['rows']) && is_array($datosDbf['rows'])) {
                    $this->line("   • Formato: Nuevo (con metadata)");
                    $this->line("   • Registros: " . count($datosDbf['rows']));
                    
                    if (isset($datosDbf['metadata'])) {
                        $metadata = $datosDbf['metadata'];
                        $this->line("   • Centro Servicio: " . ($metadata['centro_servicio'] ?? 'N/A'));
                        $this->line("   • Otras metadata: " . json_encode($metadata));
                    }
                } elseif (is_array($datosDbf) && isset($datosDbf[0])) {
                    $this->line("   • Formato: Anterior (directo)");
                    $this->line("   • Registros: " . count($datosDbf));
                } else {
                    $this->warn("   ⚠️  Formato no reconocido");
                    $this->line("   • Tipo: " . gettype($datosDbf));
                    if (is_array($datosDbf)) {
                        $this->line("   • Keys: " . json_encode(array_keys($datosDbf)));
                    } else {
                        $this->line("   • Valor: " . json_encode($datosDbf));
                    }
                }
                
                // Mostrar muestra de datos
                if (isset($datosDbf['rows'][0]) && is_array($datosDbf['rows'][0])) {
                    $primerRegistro = $datosDbf['rows'][0];
                    $this->line("   • Primer registro keys: " . json_encode(array_keys($primerRegistro)));
                } elseif (isset($datosDbf[0]) && is_array($datosDbf[0])) {
                    $primerRegistro = $datosDbf[0];
                    $this->line("   • Primer registro keys: " . json_encode(array_keys($primerRegistro)));
                }
            }
        }
        
        return Command::SUCCESS;
    }
}
