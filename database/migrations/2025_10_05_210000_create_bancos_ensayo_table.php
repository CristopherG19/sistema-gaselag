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
        Schema::create('bancos_ensayo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->comment('Nombre identificativo del banco de ensayo');
            $table->string('ubicacion', 200)->comment('Ubicación física del banco');
            $table->integer('capacidad_maxima')->default(15)->comment('Máximo de medidores que puede ensayar simultáneamente');
            $table->enum('estado', ['activo', 'mantenimiento', 'inactivo'])->default('activo');
            $table->text('descripcion')->nullable()->comment('Descripción del banco y sus características');
            $table->json('especificaciones_tecnicas')->nullable()->comment('Datos técnicos del banco en formato JSON');
            $table->string('responsable_tecnico')->nullable()->comment('Técnico responsable asignado');
            $table->timestamp('ultima_calibracion')->nullable()->comment('Fecha de última calibración del banco');
            $table->timestamp('proxima_calibracion')->nullable()->comment('Fecha programada para próxima calibración');
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['estado', 'capacidad_maxima']);
            $table->index('responsable_tecnico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bancos_ensayo');
    }
};
