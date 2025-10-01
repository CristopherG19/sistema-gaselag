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
            // Aumentar tamaños de campos que pueden contener datos más largos
            $table->string('ref_dir_pr', 100)->change();  // De 12 a 100 caracteres
            $table->string('ref_dir_ca', 100)->change();  // De 60 a 100 caracteres
            $table->string('nomcli', 100)->change();      // De 60 a 100 caracteres
            $table->string('reclamante', 100)->change();  // De 60 a 100 caracteres
            $table->string('dir_pro', 255)->change();     // De 171 a 255 caracteres
            $table->string('ref_cata', 255)->change();    // De 171 a 255 caracteres
            $table->string('marcamed', 50)->change();     // De 20 a 50 caracteres
            $table->string('dbo_mode', 50)->change();     // De 20 a 50 caracteres
            $table->string('dbo_dseg', 100)->change();    // De 30 a 100 caracteres
            $table->string('hrrabas', 50)->change();      // De 15 a 50 caracteres
            $table->string('regebas', 50)->change();      // De 12 a 50 caracteres
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remesas', function (Blueprint $table) {
            // Revertir los cambios
            $table->string('ref_dir_pr', 12)->change();
            $table->string('ref_dir_ca', 60)->change();
            $table->string('nomcli', 60)->change();
            $table->string('reclamante', 60)->change();
            $table->string('dir_pro', 171)->change();
            $table->string('ref_cata', 171)->change();
            $table->string('marcamed', 20)->change();
            $table->string('dbo_mode', 20)->change();
            $table->string('dbo_dseg', 30)->change();
            $table->string('hrrabas', 15)->change();
            $table->string('regebas', 12)->change();
        });
    }
};
