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
        Schema::create('quejas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->enum('estado', ['pendiente', 'en_proceso', 'resuelta', 'cancelada'])->default('pendiente');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'critica'])->default('media');
            $table->string('tipo')->default('general'); // general, tecnica, administrativa, etc.
            
            // Relaciones
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('asignado_a')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->foreignId('remesa_id')->nullable()->constrained('remesas')->onDelete('set null');
            
            // Metadatos
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->text('solucion')->nullable();
            $table->text('comentarios')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['estado', 'prioridad']);
            $table->index(['usuario_id', 'estado']);
            $table->index(['asignado_a', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quejas');
    }
};