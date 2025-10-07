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
        Schema::table('remesas', function (Blueprint $table) {
            // Agregar columna oc como string para manejar números de orden de control
            $table->string('oc', 20)->nullable()->after('id');
            
            // Agregar índice para mejorar performance en búsquedas
            $table->index('oc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remesas', function (Blueprint $table) {
            // Eliminar índice y columna
            $table->dropIndex(['oc']);
            $table->dropColumn('oc');
        });
    }
};
