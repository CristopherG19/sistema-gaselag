<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Hacer campos nullable para evitar errores de inserción
     */
    public function up(): void
    {
        Schema::table('remesas', function (Blueprint $table) {
            // Hacer campos nullable que pueden venir vacíos del DBF
            $table->string('nis', 7)->nullable()->change();
            $table->string('nromedidor', 10)->nullable()->change();
            $table->string('diametro', 2)->nullable()->change();
            $table->string('clase', 1)->nullable()->change();
            $table->string('cus', 9)->nullable()->change();
            $table->string('marcamed', 20)->nullable()->change();
            $table->string('reclamante', 60)->nullable()->change();
            $table->string('nomclie', 60)->nullable()->change();
            $table->string('dir_proc', 171)->nullable()->change();
            $table->string('dir_cata', 171)->nullable()->change();
            $table->string('resol', 3)->nullable()->change();
            $table->string('itin', 4)->nullable()->change();
            $table->string('aol', 4)->nullable()->change();
            $table->string('correcarta', 6)->nullable()->change();
            $table->string('emisor', 9)->nullable()->change();
            $table->string('especial', 1)->nullable()->change();
            $table->string('reconsi', 2)->nullable()->change();
            $table->string('hrabas', 15)->nullable()->change();
            $table->string('regabas', 12)->nullable()->change();
            $table->string('cgv', 4)->nullable()->change();
            $table->string('db_mode', 20)->nullable()->change();
            $table->string('ref_dir_ca', 60)->nullable()->change();
            $table->string('ref_dir_pr', 60)->nullable()->change();
            $table->string('cup', 12)->nullable()->change();
            $table->string('tipo_dseg', 80)->nullable()->change();
            $table->string('cua', 30)->nullable()->change();
            $table->string('tarifa', 30)->nullable()->change();
            $table->string('reclamo', 15)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remesas', function (Blueprint $table) {
            // Revertir cambios (hacer campos NOT NULL nuevamente)
            $table->string('nis', 7)->nullable(false)->change();
            $table->string('nromedidor', 10)->nullable(false)->change();
            $table->string('diametro', 2)->nullable(false)->change();
            $table->string('clase', 1)->nullable(false)->change();
            $table->string('cus', 9)->nullable(false)->change();
            $table->string('marcamed', 20)->nullable(false)->change();
            $table->string('reclamante', 60)->nullable(false)->change();
            $table->string('nomclie', 60)->nullable(false)->change();
            $table->string('dir_proc', 171)->nullable(false)->change();
            $table->string('dir_cata', 171)->nullable(false)->change();
            $table->string('resol', 3)->nullable(false)->change();
            $table->string('itin', 4)->nullable(false)->change();
            $table->string('aol', 4)->nullable(false)->change();
            $table->string('correcarta', 6)->nullable(false)->change();
            $table->string('emisor', 9)->nullable(false)->change();
            $table->string('especial', 1)->nullable(false)->change();
            $table->string('reconsi', 2)->nullable(false)->change();
            $table->string('hrabas', 15)->nullable(false)->change();
            $table->string('regabas', 12)->nullable(false)->change();
            $table->string('cgv', 4)->nullable(false)->change();
            $table->string('db_mode', 20)->nullable(false)->change();
            $table->string('ref_dir_ca', 60)->nullable(false)->change();
            $table->string('ref_dir_pr', 60)->nullable(false)->change();
            $table->string('cup', 12)->nullable(false)->change();
            $table->string('tipo_dseg', 80)->nullable(false)->change();
            $table->string('cua', 30)->nullable(false)->change();
            $table->string('tarifa', 30)->nullable(false)->change();
            $table->string('reclamo', 15)->nullable(false)->change();
        });
    }
};
