<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Recrear tabla remesas con estructura exacta del DBF
     */
    public function up(): void
    {
        // Eliminar tabla existente completamente
        Schema::dropIfExists('remesas');
        
        // Crear tabla con estructura nueva
        Schema::create('remesas', function (Blueprint $table) {
            $table->id();
            
            // Metadatos del archivo
            $table->unsignedBigInteger('usuario_id');
            $table->string('nombre_archivo', 255);
            $table->string('nro_carga', 20)->unique(); // Campo clave para validar duplicados
            $table->timestamp('fecha_carga');
            $table->boolean('cargado_al_sistema')->default(false);
            $table->timestamp('fecha_carga_sistema')->nullable();
            
            // Campos DBF según estructura exacta proporcionada
            $table->string('nis', 7);                      // NIS Carácter 7
            $table->string('nromedidor', 10);              // NROMEDIDOR Carácter 10
            $table->string('diametro', 2);                 // DIAMETRO Carácter 2
            $table->string('clase', 1);                    // CLASE Carácter 1
            $table->date('retfech')->nullable();           // RETFEC Fecha 8 (renombrado para evitar confusión)
            $table->decimal('rethor', 17, 2)->nullable();  // RETHOR Numérico 17,2
            $table->date('fechaprog')->nullable();         // FECHAPROG Fecha 8
            $table->date('fechaing')->nullable();          // FECHAING Fecha 8
            $table->bigInteger('tel_clie')->nullable();    // TEL_CLIE Numérico 16
            $table->decimal('horaprog', 17, 2)->nullable(); // HORAPROG Numérico 17,2
            $table->string('cus', 9);                      // CUS Carácter 9
            $table->date('f_inst')->nullable();            // F_INST Fecha 8
            $table->string('marcamed', 20);                // MARCAMED Carácter 20
            $table->string('reclamante', 60);              // RECLAMANTE Carácter 60
            $table->string('nomclie', 60);                 // NOMCLIE Carácter 60
            $table->string('dir_proc', 171);               // DIR_PROC Carácter 171
            $table->string('dir_cata', 171);               // DIR_CATA Carácter 171
            $table->bigInteger('tipo_afe')->nullable();    // TIPO_AFE Numérico 16
            $table->string('resol', 3);                    // RESOL Carácter 3
            $table->string('itin', 4);                     // ITIN Carácter 4
            $table->string('aol', 4);                      // AOL Carácter 4
            $table->string('correcarta', 6);               // CORRECARTA Carácter 6
            $table->bigInteger('nrocarga_dbf')->nullable(); // NROCARGA Numérico 16
            $table->string('emisor', 9);                   // EMISOR Carácter 9
            $table->string('especial', 1);                 // ESPECIAL Carácter 1
            $table->string('reconsi', 2);                  // RECONSI Carácter 2
            $table->date('f_reclamo')->nullable();         // F_RECLAMO Fecha 8
            $table->bigInteger('hrprom')->nullable();      // HRPROM Numérico 16
            $table->string('hrabas', 15);                  // HRABAS Carácter 15
            $table->string('regabas', 12);                 // REGABAS Carácter 12
            $table->bigInteger('empresa')->nullable();     // EMPRESA Numérico 16
            $table->boolean('masivo_bool')->nullable();    // MASIVO Lógico 1
            $table->bigInteger('ruta_num')->nullable();    // RUTA Numérico 16
            $table->string('cgv', 4);                      // CGV Carácter 4
            $table->string('db_mode', 20);                 // DB_MODE Carácter 20
            $table->bigInteger('db_afab')->nullable();     // DB_AFAB Numérico 16
            $table->decimal('dbq_max', 17, 2)->nullable(); // DBQ_MAX Numérico 17,2
            $table->decimal('dbq_min', 17, 2)->nullable(); // DBQ_MIN Numérico 17,2
            $table->decimal('dbq_perm', 17, 2)->nullable(); // DBQ_PERM Numérico 17,2
            $table->decimal('dbq_tran', 17, 2)->nullable(); // DBQ_TRAN Numérico 17,2
            $table->string('ref_dir_ca', 60);              // REF_DIR_CA Carácter 60
            $table->string('ref_dir_pr', 60);              // REF_DIR_PR Carácter 60
            $table->string('cup', 12);                     // CUP Carácter 12
            $table->string('tipo_dseg', 80);               // TIPO_DSEG Carácter 80
            $table->string('cua', 30);                     // CUA Carácter 30
            $table->string('tarifa', 30);                  // TARIFA Carácter 30
            $table->string('reclamo', 15);                 // RECLAMO Carácter 15
            
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
