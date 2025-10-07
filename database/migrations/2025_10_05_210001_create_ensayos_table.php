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
        Schema::create('ensayos', function (Blueprint $table) {
            $table->id();
            
            // Información del medidor
            $table->string('nro_medidor', 50)->comment('Número del medidor a ensayar');
            $table->string('marca', 100)->nullable()->comment('Marca del medidor');
            $table->string('modelo', 100)->nullable()->comment('Modelo del medidor');
            $table->decimal('calibre', 8, 2)->nullable()->comment('Calibre del medidor en mm');
            $table->string('clase_metrologia', 20)->nullable()->comment('Clase metrológica del medidor');
            $table->year('ano_fabricacion')->nullable()->comment('Año de fabricación');
            
            // Información del ensayo
            $table->foreignId('banco_ensayo_id')->constrained('bancos_ensayo')->onDelete('cascade');
            $table->foreignId('tecnico_id')->constrained('usuarios')->onDelete('cascade')->comment('Técnico que realiza el ensayo');
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'cancelado'])->default('pendiente');
            $table->enum('tipo_ensayo', ['verificacion_inicial', 'verificacion_periodica', 'reparacion'])->default('verificacion_periodica');
            
            // Datos técnicos del ensayo según NMP 005:2018
            $table->decimal('caudal_q1', 10, 6)->nullable()->comment('Caudal de ensayo Q1 (L/h)');
            $table->decimal('caudal_q2', 10, 6)->nullable()->comment('Caudal de ensayo Q2 (L/h)');
            $table->decimal('caudal_q3', 10, 6)->nullable()->comment('Caudal de ensayo Q3 (L/h)');
            
            // Resultados de las mediciones
            $table->decimal('volumen_ensayo_q1', 12, 6)->nullable()->comment('Volumen de ensayo en Q1 (L)');
            $table->decimal('volumen_medidor_q1', 12, 6)->nullable()->comment('Volumen registrado por medidor en Q1 (L)');
            $table->decimal('error_q1', 8, 4)->nullable()->comment('Error en Q1 (%)');
            
            $table->decimal('volumen_ensayo_q2', 12, 6)->nullable()->comment('Volumen de ensayo en Q2 (L)');
            $table->decimal('volumen_medidor_q2', 12, 6)->nullable()->comment('Volumen registrado por medidor en Q2 (L)');
            $table->decimal('error_q2', 8, 4)->nullable()->comment('Error en Q2 (%)');
            
            $table->decimal('volumen_ensayo_q3', 12, 6)->nullable()->comment('Volumen de ensayo en Q3 (L)');
            $table->decimal('volumen_medidor_q3', 12, 6)->nullable()->comment('Volumen registrado por medidor en Q3 (L)');
            $table->decimal('error_q3', 8, 4)->nullable()->comment('Error en Q3 (%)');
            
            // Condiciones ambientales
            $table->decimal('temperatura', 5, 2)->nullable()->comment('Temperatura durante el ensayo (°C)');
            $table->decimal('presion', 8, 2)->nullable()->comment('Presión durante el ensayo (kPa)');
            $table->decimal('humedad', 5, 2)->nullable()->comment('Humedad relativa (%)');
            
            // Resultado final
            $table->enum('resultado_final', ['aprobado', 'rechazado', 'pendiente'])->default('pendiente');
            $table->text('observaciones')->nullable()->comment('Observaciones del técnico');
            $table->text('defectos_encontrados')->nullable()->comment('Defectos o anomalías detectadas');
            
            // Control de tiempos
            $table->timestamp('fecha_inicio')->nullable()->comment('Inicio del ensayo');
            $table->timestamp('fecha_finalizacion')->nullable()->comment('Finalización del ensayo');
            $table->integer('tiempo_ensayo_minutos')->nullable()->comment('Duración total del ensayo en minutos');
            
            // Certificación
            $table->string('nro_certificado', 100)->nullable()->comment('Número de certificado generado');
            $table->timestamp('fecha_certificado')->nullable()->comment('Fecha de emisión del certificado');
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['nro_medidor', 'estado']);
            $table->index(['banco_ensayo_id', 'estado']);
            $table->index(['tecnico_id', 'fecha_inicio']);
            $table->index(['resultado_final', 'tipo_ensayo']);
            $table->index('nro_certificado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ensayos');
    }
};
