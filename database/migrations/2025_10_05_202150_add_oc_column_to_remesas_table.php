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
        // Solo agregar la columna si no existe (evita fallos en entornos donde ya se aplicó manualmente)
        if (!Schema::hasColumn('remesas', 'oc')) {
            Schema::table('remesas', function (Blueprint $table) {
                // Agregar columna oc como string para manejar números de orden de control
                $table->string('oc', 20)->nullable()->after('id');

                // Agregar índice para mejorar performance en búsquedas
                $table->index('oc');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Solo intentar eliminar si la columna existe (previene errores en rollback cuando la columna no fue creada por esta migración)
        if (Schema::hasColumn('remesas', 'oc')) {
            Schema::table('remesas', function (Blueprint $table) {
                // Eliminar índice y columna
                // Usar try/catch silencioso por si el índice no existe con el nombre esperado
                try {
                    $table->dropIndex(['oc']);
                } catch (\Exception $e) {
                    // índice puede no existir; ignorar
                }

                try {
                    $table->dropColumn('oc');
                } catch (\Exception $e) {
                    // columna puede haber sido eliminada previamente; ignorar
                }
            });
        }
    }
};
