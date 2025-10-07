<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BancoEnsayo;
use Carbon\Carbon;

class BancoEnsayoSeeder extends Seeder
{
    public function run()
    {
        $bancos = [
            [
                'nombre' => 'Banco Principal A',
                'ubicacion' => 'Laboratorio Principal - Módulo A',
                'capacidad_maxima' => 15,
                'estado' => 'activo',
                'descripcion' => 'Banco de ensayo principal con capacidad para 15 medidores simultáneos',
                'especificaciones_tecnicas' => json_encode([
                    'presion_maxima' => '16 bar',
                    'caudal_maximo' => '100 L/h',
                    'precision' => '±0.1%',
                    'temperatura_operacion' => '5°C - 50°C'
                ]),
                'responsable_tecnico' => 'Técnico Principal',
                'ultima_calibracion' => Carbon::now()->subMonths(6),
                'proxima_calibracion' => Carbon::now()->addMonths(6)
            ],
            [
                'nombre' => 'Banco Secundario B',
                'ubicacion' => 'Laboratorio Principal - Módulo B',
                'capacidad_maxima' => 10,
                'estado' => 'activo',
                'descripcion' => 'Banco de ensayo secundario para medidores de menor calibre',
                'especificaciones_tecnicas' => json_encode([
                    'presion_maxima' => '10 bar',
                    'caudal_maximo' => '50 L/h',
                    'precision' => '±0.2%',
                    'temperatura_operacion' => '10°C - 40°C'
                ]),
                'responsable_tecnico' => 'Técnico Auxiliar',
                'ultima_calibracion' => Carbon::now()->subMonths(3),
                'proxima_calibracion' => Carbon::now()->addMonths(9)
            ],
            [
                'nombre' => 'Banco Calibres Grandes C',
                'ubicacion' => 'Laboratorio Especial - Área C',
                'capacidad_maxima' => 8,
                'estado' => 'activo',
                'descripcion' => 'Banco especializado para medidores de calibres grandes (≥40mm)',
                'especificaciones_tecnicas' => json_encode([
                    'presion_maxima' => '25 bar',
                    'caudal_maximo' => '500 L/h',
                    'precision' => '±0.1%',
                    'temperatura_operacion' => '5°C - 60°C'
                ]),
                'responsable_tecnico' => 'Especialista Calibres Grandes',
                'ultima_calibracion' => Carbon::now()->subMonths(4),
                'proxima_calibracion' => Carbon::now()->addMonths(8)
            ]
        ];

        foreach ($bancos as $banco) {
            BancoEnsayo::create($banco);
        }
    }
}
