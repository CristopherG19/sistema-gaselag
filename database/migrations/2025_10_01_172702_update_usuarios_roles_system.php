<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Actualizar el enum de roles para incluir los nuevos roles (incluyendo tecnico_laboratorio)
            $table->enum('rol', ['admin', 'usuario', 'operario_campo', 'tecnico_laboratorio'])
                  ->default('usuario')
                  ->change();
            
            // Agregar campos adicionales para gestión de usuarios
            $table->boolean('activo')->default(true)->after('rol');
            $table->timestamp('ultimo_acceso')->nullable()->after('activo');
            $table->text('notas')->nullable()->after('ultimo_acceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Revertir a los roles originales
            $table->enum('rol', ['admin', 'usuario'])
                  ->default('usuario')
                  ->change();
            
            // Eliminar campos adicionales
            $table->dropColumn(['activo', 'ultimo_acceso', 'notas']);
        });
    }
};