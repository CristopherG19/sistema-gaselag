<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Remesa;
use App\Models\RemesaPendiente;

class LimpiarTodasRemesas extends Command
{
    protected $signature = 'remesas:limpiar-todas {--force : Forzar eliminación sin confirmación}';
    
    protected $description = 'Elimina TODAS las remesas de la base de datos para realizar pruebas desde cero';

    public function handle()
    {
        // Mostrar advertencia
        $this->warn('⚠️  ADVERTENCIA: Este comando eliminará TODAS las remesas de la base de datos');
        $this->warn('   Esto incluye tanto remesas principales como pendientes');
        
        // Mostrar conteo actual
        $remesasCount = Remesa::count();
        $pendientesCount = RemesaPendiente::count();
        
        $this->info("📊 Estado actual:");
        $this->line("   • Remesas principales: {$remesasCount}");
        $this->line("   • Remesas pendientes: {$pendientesCount}");
        
        if ($remesasCount === 0 && $pendientesCount === 0) {
            $this->info('✅ La base de datos ya está limpia - no hay remesas para eliminar');
            return Command::SUCCESS;
        }
        
        // Confirmar eliminación si no se usa --force
        if (!$this->option('force')) {
            if (!$this->confirm('¿Estás seguro de que quieres eliminar TODAS las remesas?')) {
                $this->info('❌ Operación cancelada');
                return Command::FAILURE;
            }
        }
        
        $this->info('🗑️  Iniciando eliminación...');
        
        try {
            // Deshabilitar restricciones de clave foránea
            $this->line('   • Deshabilitando restricciones de clave foránea...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Eliminar todas las remesas principales
            $this->line('   • Eliminando remesas principales...');
            DB::table('remesas')->truncate();
            
            // Eliminar todas las remesas pendientes
            $this->line('   • Eliminando remesas pendientes...');
            DB::table('remesas_pendientes')->truncate();
            
            // Rehabilitar restricciones de clave foránea
            $this->line('   • Rehabilitando restricciones de clave foránea...');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            // Verificar eliminación
            $remesasRestantes = Remesa::count();
            $pendientesRestantes = RemesaPendiente::count();
            
            if ($remesasRestantes === 0 && $pendientesRestantes === 0) {
                $this->newLine();
                $this->info('✅ Eliminación completada exitosamente');
                $this->line('📊 Estado final:');
                $this->line('   • Remesas principales: 0');
                $this->line('   • Remesas pendientes: 0');
                $this->newLine();
                $this->comment('🧪 La base de datos está lista para pruebas desde cero');
                
                return Command::SUCCESS;
            } else {
                $this->error('❌ Error: Algunos registros no fueron eliminados');
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            // Rehabilitar restricciones en caso de error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->error('❌ Error durante la eliminación: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}