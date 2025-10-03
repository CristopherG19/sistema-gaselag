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
        Schema::table('remesas_pendientes', function (Blueprint $table) {
            // Índice compuesto para consultas frecuentes
            $table->index(['usuario_id', 'fecha_carga'], 'idx_usuario_fecha');
            
            // Índice para ordenamiento por fecha
            $table->index('fecha_carga', 'idx_fecha_carga');
            
            // Índice para búsquedas por número de carga
            $table->index('nro_carga', 'idx_nro_carga');
            
            // Índice para búsquedas por nombre de archivo
            $table->index('nombre_archivo', 'idx_nombre_archivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remesas_pendientes', function (Blueprint $table) {
            $table->dropIndex('idx_usuario_fecha');
            $table->dropIndex('idx_fecha_carga');
            $table->dropIndex('idx_nro_carga');
            $table->dropIndex('idx_nombre_archivo');
        });
    }
};