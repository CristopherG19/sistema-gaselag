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
            // Eliminar el constraint único del campo nro_carga
            $table->dropUnique(['nro_carga']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remesas', function (Blueprint $table) {
            // Restaurar el constraint único si se revierte la migración
            $table->unique('nro_carga');
        });
    }
};
