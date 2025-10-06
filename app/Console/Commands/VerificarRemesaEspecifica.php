<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Remesa;

class VerificarRemesaEspecifica extends Command
{
    protected $signature = 'remesas:verificar {nro_carga} {usuario_id} {centro_servicio?}';
    protected $description = 'Verificar si existe una remesa específica';

    public function handle()
    {
        $nroCarga = $this->argument('nro_carga');
        $usuarioId = $this->argument('usuario_id');
        $centroServicio = $this->argument('centro_servicio');
        
        $query = Remesa::where('nro_carga', $nroCarga)
                      ->where('usuario_id', $usuarioId);
        
        if ($centroServicio) {
            $query->where('centro_servicio', $centroServicio);
        }
        
        $remesas = $query->get(['id', 'nro_carga', 'centro_servicio', 'usuario_id', 'created_at']);
        
        $this->info("🔍 Buscando remesas:");
        $this->line("   Nro Carga: {$nroCarga}");
        $this->line("   Usuario: {$usuarioId}");
        $this->line("   Centro: " . ($centroServicio ?: 'Cualquiera'));
        $this->line("");
        
        $this->info("📊 Resultados encontrados: {$remesas->count()}");
        
        foreach ($remesas as $r) {
            $this->line("ID: {$r->id} | Carga: {$r->nro_carga} | CS: {$r->centro_servicio} | Usuario: {$r->usuario_id} | Fecha: {$r->created_at}");
        }

        return 0;
    }
}