<?php

namespace App\Console\Commands;

use App\Models\RemesaPendiente;
use Illuminate\Console\Command;

class CheckDuplicatePending extends Command
{
    protected $signature = 'remesas:check-duplicates';
    protected $description = 'Verificar duplicados en remesas pendientes';

    public function handle()
    {
        $this->info('Revisando duplicados en remesas pendientes...');
        
        $pendientes = RemesaPendiente::orderBy('created_at')->get();
        $this->info('Total pendientes: ' . $pendientes->count());
        
        $this->table(
            ['ID', 'Archivo', 'Nro. Carga', 'Usuario', 'Fecha Creación'],
            $pendientes->map(function($p) {
                return [
                    $p->id,
                    $p->nombre_archivo,
                    $p->nro_carga,
                    $p->usuario_id,
                    $p->created_at->format('Y-m-d H:i:s')
                ];
            })
        );
        
        // Buscar duplicados por nombre de archivo
        $duplicadosArchivo = $pendientes->groupBy('nombre_archivo')
            ->filter(function($group) {
                return $group->count() > 1;
            });
            
        if ($duplicadosArchivo->count() > 0) {
            $this->error('DUPLICADOS POR NOMBRE DE ARCHIVO:');
            foreach ($duplicadosArchivo as $archivo => $registros) {
                $this->warn("Archivo: {$archivo} ({$registros->count()} veces)");
                foreach ($registros as $reg) {
                    $this->line("  - ID: {$reg->id}, Nro Carga: {$reg->nro_carga}, Fecha: {$reg->created_at}");
                }
            }
        }
        
        // Buscar duplicados por número de carga
        $duplicadosNroCarga = $pendientes->groupBy('nro_carga')
            ->filter(function($group) {
                return $group->count() > 1;
            });
            
        if ($duplicadosNroCarga->count() > 0) {
            $this->error('DUPLICADOS POR NÚMERO DE CARGA:');
            foreach ($duplicadosNroCarga as $nroCarga => $registros) {
                $this->warn("Nro Carga: {$nroCarga} ({$registros->count()} veces)");
                foreach ($registros as $reg) {
                    $this->line("  - ID: {$reg->id}, Archivo: {$reg->nombre_archivo}, Fecha: {$reg->created_at}");
                }
            }
        }
        
        if ($duplicadosArchivo->count() == 0 && $duplicadosNroCarga->count() == 0) {
            $this->info('✅ No se encontraron duplicados');
        }
        
        return 0;
    }
}