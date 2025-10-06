<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ShowTableStructure extends Command
{
    protected $signature = 'db:show-structure {table}';
    protected $description = 'Muestra la estructura de una tabla';

    public function handle()
    {
        $table = $this->argument('table');
        
        $this->info("📋 Estructura de la tabla: {$table}");
        
        try {
            $columns = Schema::getColumnListing($table);
            
            foreach ($columns as $column) {
                $this->line("  - {$column}");
            }
            
            // También mostrar algunos registros de ejemplo
            if (in_array($table, ['remesas', 'remesas_pendientes'])) {
                $this->line("");
                $this->info("📊 Ejemplo de registros:");
                $records = DB::table($table)->limit(2)->get();
                
                foreach ($records as $record) {
                    $this->line("  Registro ID {$record->id}:");
                    foreach ($record as $key => $value) {
                        $this->line("    {$key}: " . (is_null($value) ? 'NULL' : $value));
                    }
                    $this->line("");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }

        return 0;
    }
}