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
            $table->string('centro_servicio', 100)->nullable()->after('nro_carga');
            $table->index(['nro_carga', 'centro_servicio', 'usuario_id'], 'idx_remesa_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remesas', function (Blueprint $table) {
            $table->dropIndex('idx_remesa_unique');
            $table->dropColumn('centro_servicio');
        });
    }
};
