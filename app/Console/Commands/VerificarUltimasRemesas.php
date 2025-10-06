<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Remesa;

class VerificarUltimasRemesas extends Command
{
    protected $signature = 'remesas:verificar-ultimas';
    protected $description = 'Verificar las últimas remesas insertadas';

    public function handle()
    {
        $this->info('📋 Últimas remesas insertadas en la tabla principal');
        
        $remesas = Remesa::orderBy('created_at', 'desc')->limit(10)->get();
        
        foreach ($remesas as $r) {
            $this->line("ID: {$r->id} | Carga: {$r->nro_carga} | CS: {$r->centro_servicio} | Usuario: {$r->usuario_id} | Fecha: {$r->created_at}");
        }

        return 0;
    }
}