<?php

namespace App\Console\Commands;

use App\Models\Remesa;
use App\Models\RemesaPendiente;
use Illuminate\Console\Command;

class CheckRemesaExists extends Command
{
    protected $signature = 'remesas:check-exists {nro_carga}';
    protected $description = 'Verificar si una remesa existe en la tabla principal';

    public function handle()
    {
        $nroCarga = $this->argument('nro_carga');
        
        $this->info("Verificando remesa con nro_carga: {$nroCarga}");
        
        // Buscar en tabla principal
        $remesaPrincipal = Remesa::where('nro_carga', $nroCarga)->get();
        
        if ($remesaPrincipal->count() > 0) {
            $this->error("✅ EXISTE en tabla principal ({$remesaPrincipal->count()} registro(s)):");
            foreach ($remesaPrincipal as $remesa) {
                $this->line("  - ID: {$remesa->id}, Archivo: {$remesa->nombre_archivo}, Usuario: {$remesa->usuario_id}, Fecha: {$remesa->created_at}");
            }
        } else {
            $this->info("❌ NO EXISTE en tabla principal");
        }
        
        // Buscar en tabla pendientes
        $remesaPendiente = RemesaPendiente::where('nro_carga', $nroCarga)->get();
        
        if ($remesaPendiente->count() > 0) {
            $this->warn("📋 EXISTE en tabla pendientes ({$remesaPendiente->count()} registro(s)):");
            foreach ($remesaPendiente as $pendiente) {
                $this->line("  - ID: {$pendiente->id}, Archivo: {$pendiente->nombre_archivo}, Usuario: {$pendiente->usuario_id}, Fecha: {$pendiente->created_at}");
            }
        } else {
            $this->info("❌ NO EXISTE en tabla pendientes");
        }
        
        return 0;
    }
}