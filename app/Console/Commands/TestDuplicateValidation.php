<?php

namespace App\Console\Commands;

use App\Models\RemesaPendiente;
use App\Models\Remesa;
use Illuminate\Console\Command;

class TestDuplicateValidation extends Command
{
    protected $signature = 'remesas:test-duplicates';
    protected $description = 'Probar validaciones de duplicados';

    public function handle()
    {
        $this->info('Probando validaciones de duplicados...');
        
        // Obtener un archivo existente para probar
        $existente = RemesaPendiente::first();
        
        if (!$existente) {
            $this->error('No hay archivos pendientes para probar');
            return 1;
        }
        
        $this->info("Usando archivo de prueba: {$existente->nombre_archivo}");
        $this->info("Número de carga: {$existente->nro_carga}");
        $this->info("Usuario ID: {$existente->usuario_id}");
        
        // Probar duplicado por nombre de archivo
        $this->info("\n1. Probando duplicado por nombre de archivo...");
        $resultadoArchivo = RemesaPendiente::existeArchivoPorUsuario(
            $existente->nombre_archivo, 
            $existente->usuario_id
        );
        $this->line($resultadoArchivo ? "✅ Detectado duplicado por archivo" : "❌ No detectó duplicado");
        
        // Probar duplicado por número de carga
        $this->info("\n2. Probando duplicado por número de carga...");
        $resultadoNroCarga = RemesaPendiente::existeNroCargaPorUsuario(
            $existente->nro_carga, 
            $existente->usuario_id
        );
        $this->line($resultadoNroCarga ? "✅ Detectado duplicado por nro. carga" : "❌ No detectó duplicado");
        
        // Probar con archivo inexistente
        $this->info("\n3. Probando con archivo inexistente...");
        $resultadoInexistente = RemesaPendiente::existeArchivoPorUsuario(
            'archivo_que_no_existe.dbf', 
            $existente->usuario_id
        );
        $this->line($resultadoInexistente ? "❌ Falso positivo" : "✅ Correctamente no detectó duplicado");
        
        // Probar con usuario diferente
        $this->info("\n4. Probando con usuario diferente...");
        $resultadoOtroUsuario = RemesaPendiente::existeArchivoPorUsuario(
            $existente->nombre_archivo, 
            999 // Usuario inexistente
        );
        $this->line($resultadoOtroUsuario ? "❌ Falso positivo" : "✅ Correctamente no detectó duplicado");
        
        $this->info("\n✅ Pruebas de validación completadas");
        
        return 0;
    }
}