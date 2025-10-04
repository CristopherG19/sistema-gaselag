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
            // Índice único por usuario_id + nombre_archivo (no puede haber el mismo archivo duplicado por usuario)
            $table->unique(['usuario_id', 'nombre_archivo'], 'unique_user_filename');
            
            // Índice único por usuario_id + nro_carga (no puede haber el mismo nro_carga duplicado por usuario)
            $table->unique(['usuario_id', 'nro_carga'], 'unique_user_nrocarga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remesas_pendientes', function (Blueprint $table) {
            $table->dropUnique('unique_user_filename');
            $table->dropUnique('unique_user_nrocarga');
        });
    }
};