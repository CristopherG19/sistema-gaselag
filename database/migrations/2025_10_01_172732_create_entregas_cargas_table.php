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
        Schema::create('entregas_cargas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_entrega')->unique(); // Código único para la entrega
            $table->string('nombre_entrega'); // Nombre descriptivo de la entrega
            
            // Relaciones
            $table->foreignId('remesa_id')->constrained('remesas')->onDelete('cascade');
            $table->foreignId('operario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('asignado_por')->constrained('usuarios')->onDelete('cascade');
            
            // Datos de la carga asignada
            $table->json('registros_asignados'); // Array de IDs de registros asignados
            $table->integer('total_registros')->default(0);
            $table->string('zona_asignada')->nullable(); // Zona geográfica asignada
            $table->text('instrucciones')->nullable(); // Instrucciones específicas
            
            // Estado de la entrega
            $table->enum('estado', ['asignada', 'en_proceso', 'completada', 'cancelada'])->default('asignada');
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_completado')->nullable();
            
            // Metadatos
            $table->text('observaciones')->nullable();
            $table->decimal('progreso', 5, 2)->default(0.00); // Porcentaje de progreso (0-100)
            
            $table->timestamps();
            
            // Índices
            $table->index(['operario_id', 'estado']);
            $table->index(['remesa_id', 'estado']);
            $table->index(['asignado_por', 'fecha_asignacion']);
            $table->index('codigo_entrega');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas_cargas');
    }
};