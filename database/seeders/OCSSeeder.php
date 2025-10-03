<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Remesa;
use App\Models\EntregaCarga;

class OCSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear operario de campo si no existe
        $operario = Usuario::firstOrCreate(
            ['correo' => 'operario.test@gaselag.com'],
            [
                'nombre' => 'Carlos',
                'apellidos' => 'Mendoza',
                'password' => bcrypt('password'),
                'rol' => 'operario_campo',
                'activo' => true
            ]
        );

        // Crear remesa si no existe
        $remesa = Remesa::firstOrCreate(
            ['nro_carga' => 'TEST-001'],
            [
                'nombre_archivo' => 'remesa_test_001.dbf',
                'fecha_carga' => now(),
                'cargado_al_sistema' => true,
                'centro_servicio' => 'CS001',
                'usuario_id' => 1 // Asumiendo que el usuario ID 1 existe
            ]
        );

        // Crear OC de prueba
        $ocs = [
            [
                'codigo_entrega' => 'ENT-20251002-0001',
                'nombre_entrega' => 'Verificación Zona Norte',
                'zona_asignada' => 'Zona Norte',
                'instrucciones' => 'Verificar medidores en zona norte de Lima',
                'estado' => 'asignada'
            ],
            [
                'codigo_entrega' => 'ENT-20251002-0002',
                'nombre_entrega' => 'Verificación Zona Sur',
                'zona_asignada' => 'Zona Sur',
                'instrucciones' => 'Verificar medidores en zona sur de Lima',
                'estado' => 'en_proceso'
            ],
            [
                'codigo_entrega' => 'ENT-20251002-0003',
                'nombre_entrega' => 'Verificación Zona Este',
                'zona_asignada' => 'Zona Este',
                'instrucciones' => 'Verificar medidores en zona este de Lima',
                'estado' => 'completada'
            ],
            [
                'codigo_entrega' => 'ENT-20251002-0004',
                'nombre_entrega' => 'Verificación Zona Oeste',
                'zona_asignada' => 'Zona Oeste',
                'instrucciones' => 'Verificar medidores en zona oeste de Lima',
                'estado' => 'asignada'
            ],
            [
                'codigo_entrega' => 'ENT-20251002-0005',
                'nombre_entrega' => 'Verificación Centro',
                'zona_asignada' => 'Centro de Lima',
                'instrucciones' => 'Verificar medidores en el centro de Lima',
                'estado' => 'en_proceso'
            ]
        ];

        foreach ($ocs as $ocData) {
            EntregaCarga::firstOrCreate(
                ['codigo_entrega' => $ocData['codigo_entrega']],
                array_merge($ocData, [
                    'remesa_id' => $remesa->id,
                    'operario_id' => $operario->id,
                    'asignado_por' => 1, // Asumiendo que el usuario ID 1 es admin
                    'registros_asignados' => [1, 2, 3, 4, 5],
                    'total_registros' => 5,
                    'fecha_asignacion' => now(),
                    'progreso' => $ocData['estado'] === 'completada' ? 100 : ($ocData['estado'] === 'en_proceso' ? 50 : 0)
                ])
            );
        }

        $this->command->info('OC de prueba creadas exitosamente!');
    }
}