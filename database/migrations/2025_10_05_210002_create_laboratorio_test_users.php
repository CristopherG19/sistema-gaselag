<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear usuarios de prueba para el laboratorio
        $tecnicos = [
            [
                'nombre' => 'Carlos',
                'apellidos' => 'Técnico Laboratorio',
                'correo' => 'tecnico.lab@gaselag.com',
                'password' => Hash::make('tecnico123'),
                'rol' => 'operario_campo', // Usar rol válido existente, se actualizará después
                'activo' => true,
                'notas' => 'Usuario de prueba para sistema de laboratorio'
            ],
            [
                'nombre' => 'María',
                'apellidos' => 'Especialista Medidores',
                'correo' => 'maria.especialista@gaselag.com',
                'password' => Hash::make('especialista123'),
                'rol' => 'operario_campo', // Usar rol válido existente, se actualizará después
                'activo' => true,
                'notas' => 'Especialista en ensayos de medidores de agua'
            ]
        ];

        foreach ($tecnicos as $tecnico) {
            // Verificar si el usuario ya existe
            $existeUsuario = Usuario::where('correo', $tecnico['correo'])->first();
            
            if (!$existeUsuario) {
                Usuario::create($tecnico);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar usuarios de prueba del laboratorio
        Usuario::whereIn('correo', [
            'tecnico.lab@gaselag.com',
            'maria.especialista@gaselag.com'
        ])->delete();
    }
};
