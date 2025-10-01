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
        Schema::create('remesas', function (Blueprint $table) {
            $table->id();
            
            // Metadatos del archivo
            $table->unsignedBigInteger('usuario_id');
            $table->string('nombre_archivo', 255);
            $table->string('nro_carga', 20)->unique(); // Campo clave para validar duplicados
            $table->timestamp('fecha_carga');
            $table->boolean('cargado_al_sistema')->default(false);
            $table->timestamp('fecha_carga_sistema')->nullable();
            
            // Campos específicos del DBF según especificación
            $table->string('nis', 7);
            $table->string('nromedidor', 10);
            $table->string('diametro', 3);
            $table->string('clase', 1);
            $table->date('retfech')->nullable();
            $table->decimal('retchor', 17, 2)->nullable();
            $table->date('fecharrog')->nullable();
            $table->date('fechaing')->nullable();
            $table->bigInteger('tel_clie')->nullable();
            $table->decimal('horaprog', 17, 2)->nullable();
            $table->string('cujs', 9);
            $table->date('jinst')->nullable();
            $table->string('marcamed', 20);
            $table->string('reclamante', 60);
            $table->string('nomcli', 60);
            $table->string('dir_pro', 171);
            $table->string('ref_cata', 171);
            $table->bigInteger('ripolafe')->nullable();
            $table->string('rsol', 3);
            $table->string('tin', 4);
            $table->string('aol', 4);
            $table->string('correcarta', 6);
            $table->bigInteger('enusorga')->nullable();
            $table->string('especial', 1);
            $table->string('reconsi', 2);
            $table->date('retfeclamd')->nullable();
            $table->bigInteger('retchorrom')->nullable();
            $table->string('hrrabas', 15);
            $table->string('regebas', 12);
            $table->bigInteger('empresa')->nullable();
            $table->string('masivo', 1);
            $table->string('ruta', 16);
            $table->string('gcv', 4);
            $table->string('dbo_mode', 20);
            $table->bigInteger('dbo_afab')->nullable();
            $table->decimal('dbo_max', 17, 2)->nullable();
            $table->decimal('dbo_min', 17, 2)->nullable();
            $table->decimal('dbo_perm', 17, 2)->nullable();
            $table->decimal('dbo_tran', 17, 2)->nullable();
            $table->string('ref_dir_ca', 60);
            $table->string('ref_dir_pr', 12);
            $table->string('cup', 80);
            $table->string('dbo_dseg', 30);
            $table->string('tarifa', 30);
            $table->string('reclamo', 15);
            
            // Campos de control
            $table->boolean('editado')->default(false);
            $table->timestamp('fecha_edicion')->nullable();
            $table->unsignedBigInteger('editado_por')->nullable();
            $table->json('campos_editados')->nullable(); // Para rastrear qué campos fueron editados
            
            $table->timestamps();
            
            // Índices
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('editado_por')->references('id')->on('usuarios');
            $table->index('nro_carga');
            $table->index('nis');
            $table->index('nromedidor');
            $table->index(['usuario_id', 'cargado_al_sistema']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remesas');
    }
};
