<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellidos', 100);
            $table->string('correo', 100)->unique();
            $table->string('password');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->string('rol', 20)->default('usuario');
            $table->string('reset_token', 255)->nullable();
            $table->dateTime('reset_expira')->nullable();
            $table->integer('cambios_password')->default(0);
            $table->timestamps(); // <-- Esto agrega created_at y updated_at

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
