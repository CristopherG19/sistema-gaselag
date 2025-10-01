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
            // Remover la restricción unique del campo nro_carga
            // Ya que múltiples registros pueden tener el mismo número de remesa
            $table->dropUnique(['nro_carga']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remesas', function (Blueprint $table) {
            // Restaurar la restricción unique si es necesario revertir
            $table->unique('nro_carga');
        });
    }
};
