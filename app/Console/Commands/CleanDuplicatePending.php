<?php

namespace App\Console\Commands;

use App\Models\RemesaPendiente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanDuplicatePending extends Command
{
    protected $signature = 'remesas:clean-duplicates {--dry-run : Solo mostrar qué se eliminaría sin hacer cambios}';
    protected $description = 'Eliminar duplicados en remesas pendientes (mantiene el más antiguo)';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('Iniciando limpieza de duplicados en remesas pendientes...');
        
        $eliminados = 0;
        
        // Buscar duplicados por nombre de archivo
        $this->info('Buscando duplicados por nombre de archivo...');
        $duplicadosArchivo = RemesaPendiente::select('nombre_archivo', 'usuario_id')
                                          ->groupBy('nombre_archivo', 'usuario_id')
                                          ->havingRaw('COUNT(*) > 1')
                                          ->get();
        
        foreach ($duplicadosArchivo as $grupo) {
            $registros = RemesaPendiente::where('nombre_archivo', $grupo->nombre_archivo)
                                       ->where('usuario_id', $grupo->usuario_id)
                                       ->orderBy('created_at', 'asc')
                                       ->get();
            
            $mantener = $registros->first(); // Mantener el más antiguo
            $aEliminar = $registros->skip(1); // Eliminar el resto
            
            $this->warn("Archivo duplicado: {$grupo->nombre_archivo} (Usuario: {$grupo->usuario_id})");
            $this->line("  ✅ Mantener: ID {$mantener->id} (creado: {$mantener->created_at})");
            
            foreach ($aEliminar as $registro) {
                $this->line("  ❌ Eliminar: ID {$registro->id} (creado: {$registro->created_at})");
                
                if (!$isDryRun) {
                    $registro->delete();
                }
                $eliminados++;
            }
        }
        
        // Buscar duplicados por número de carga
        $this->info('Buscando duplicados por número de carga...');
        $duplicadosNroCarga = RemesaPendiente::select('nro_carga', 'usuario_id')
                                           ->groupBy('nro_carga', 'usuario_id')
                                           ->havingRaw('COUNT(*) > 1')
                                           ->get();
        
        foreach ($duplicadosNroCarga as $grupo) {
            $registros = RemesaPendiente::where('nro_carga', $grupo->nro_carga)
                                       ->where('usuario_id', $grupo->usuario_id)
                                       ->orderBy('created_at', 'asc')
                                       ->get();
            
            $mantener = $registros->first(); // Mantener el más antiguo
            $aEliminar = $registros->skip(1); // Eliminar el resto
            
            $this->warn("Nro. Carga duplicado: {$grupo->nro_carga} (Usuario: {$grupo->usuario_id})");
            $this->line("  ✅ Mantener: ID {$mantener->id} - {$mantener->nombre_archivo}");
            
            foreach ($aEliminar as $registro) {
                $this->line("  ❌ Eliminar: ID {$registro->id} - {$registro->nombre_archivo}");
                
                if (!$isDryRun) {
                    // Solo eliminar si no se eliminó ya por archivo duplicado
                    if (RemesaPendiente::where('id', $registro->id)->exists()) {
                        $registro->delete();
                        $eliminados++;
                    }
                }
            }
        }
        
        $this->info("\n=== RESUMEN ===");
        $this->info("Registros a eliminar: {$eliminados}");
        
        if ($isDryRun) {
            $this->warn("Ejecución en modo DRY-RUN - No se realizaron cambios");
            $this->info("Para aplicar los cambios ejecuta: php artisan remesas:clean-duplicates");
        } else {
            $this->info("✅ Limpieza de duplicados completada");
            Log::info('Comando clean-duplicates ejecutado', [
                'eliminados' => $eliminados
            ]);
        }
        
        return 0;
    }
}