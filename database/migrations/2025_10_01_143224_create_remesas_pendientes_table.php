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
        Schema::create('remesas_pendientes', function (Blueprint $table) {
            $table->id();
            
            // Metadatos del archivo
            $table->unsignedBigInteger('usuario_id');
            $table->string('nombre_archivo', 255);
            $table->string('nro_carga', 20);
            $table->timestamp('fecha_carga');
            
            // Datos del DBF almacenados como JSON para flexibilidad
            $table->json('datos_dbf');
            
            // Campos de control
            $table->timestamps();
            
            // Índices
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->index('nro_carga');
            $table->index(['usuario_id', 'fecha_carga']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remesas_pendientes');
    }
};
