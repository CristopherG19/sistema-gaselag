<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Actualizar estructura de tabla para reflejar exactamente la estructura DBF
     */
    public function up(): void
    {
        Schema::table('remesas', function (Blueprint $table) {
            // Cambiar nombre de columnas para coincidir exactamente con DBF
            $table->renameColumn('retchor', 'rethor');
            $table->renameColumn('fecharrog', 'fechaprog');
            $table->renameColumn('cujs', 'cus');
            $table->renameColumn('jinst', 'f_inst');
            $table->renameColumn('nomcli', 'nomclie');
            $table->renameColumn('dir_pro', 'dir_proc');
            $table->renameColumn('ref_cata', 'dir_cata');
            $table->renameColumn('retfeclamd', 'f_reclamo');
            $table->renameColumn('retchorrom', 'hrprom');
            $table->renameColumn('hrrabas', 'hrabas');
            $table->renameColumn('regebas', 'regabas');
            $table->renameColumn('gcv', 'cgv');
            $table->renameColumn('dbo_mode', 'db_mode');
            $table->renameColumn('dbo_afab', 'db_afab');
            $table->renameColumn('dbo_max', 'dbq_max');
            $table->renameColumn('dbo_min', 'dbq_min');
            $table->renameColumn('dbo_perm', 'dbq_perm');
            $table->renameColumn('dbo_tran', 'dbq_tran');
            $table->renameColumn('dbo_dseg', 'tipo_dseg');
            
            // Actualizar tamaños de campos para coincidir exactamente con DBF
            $table->string('diametro', 2)->change();
            $table->string('cus', 9)->change();
            $table->string('reclamante', 60)->change();
            $table->string('nomclie', 60)->change();
            $table->bigInteger('tel_clie')->change(); // Numérico 16
            $table->string('rsol', 3)->change();
            $table->string('tin', 4)->change();
            $table->string('aol', 4)->change();
            $table->string('correcarta', 6)->change();
            $table->string('reconsi', 2)->change();
            $table->string('hrabas', 15)->change();
            $table->string('regabas', 12)->change();
            $table->string('cgv', 4)->change();
            $table->string('db_mode', 20)->change();
            $table->string('ref_dir_pr', 60)->change(); // Era 12, ahora 60
            $table->string('cup', 12)->change(); // Era 80, ahora 12
            $table->string('tipo_dseg', 80)->change();
            $table->string('tarifa', 30)->change();
            $table->string('reclamo', 15)->change();
        });
        
        // Agregar nuevos campos que faltan
        Schema::table('remesas', function (Blueprint $table) {
            $table->bigInteger('tipo_afe')->nullable()->after('dir_cata');
            $table->string('resol', 3)->nullable()->after('tipo_afe');
            $table->string('itin', 4)->nullable()->after('resol');
            $table->bigInteger('nrocarga_dbf')->nullable()->after('correcarta'); // Campo NROCARGA del DBF
            $table->string('emisor', 9)->nullable()->after('nrocarga_dbf');
            $table->boolean('masivo_bool')->nullable()->after('empresa'); // Campo lógico MASIVO
            $table->bigInteger('ruta_num')->nullable()->after('masivo_bool'); // RUTA como numérico
            $table->string('cua', 30)->nullable()->after('tipo_dseg');
        });
        
        // Eliminar campos que ya no existen en DBF
        Schema::table('remesas', function (Blueprint $table) {
            $table->dropColumn(['ripolafe', 'enusorga', 'masivo', 'ruta']); // Estos se reemplazan por los nuevos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remesas', function (Blueprint $table) {
            // Revertir cambios de nombres
            $table->renameColumn('rethor', 'retchor');
            $table->renameColumn('fechaprog', 'fecharrog');
            $table->renameColumn('cus', 'cujs');
            $table->renameColumn('f_inst', 'jinst');
            $table->renameColumn('nomclie', 'nomcli');
            $table->renameColumn('dir_proc', 'dir_pro');
            $table->renameColumn('dir_cata', 'ref_cata');
            $table->renameColumn('f_reclamo', 'retfeclamd');
            $table->renameColumn('hrprom', 'retchorrom');
            $table->renameColumn('hrabas', 'hrrabas');
            $table->renameColumn('regabas', 'regebas');
            $table->renameColumn('cgv', 'gcv');
            $table->renameColumn('db_mode', 'dbo_mode');
            $table->renameColumn('db_afab', 'dbo_afab');
            $table->renameColumn('dbq_max', 'dbo_max');
            $table->renameColumn('dbq_min', 'dbo_min');
            $table->renameColumn('dbq_perm', 'dbo_perm');
            $table->renameColumn('dbq_tran', 'dbo_tran');
            $table->renameColumn('tipo_dseg', 'dbo_dseg');
            
            // Eliminar nuevos campos
            $table->dropColumn(['tipo_afe', 'resol', 'itin', 'nrocarga_dbf', 'emisor', 'masivo_bool', 'ruta_num', 'cua']);
            
            // Restaurar campos eliminados
            $table->bigInteger('ripolafe')->nullable();
            $table->bigInteger('enusorga')->nullable();
            $table->string('masivo', 1);
            $table->string('ruta', 16);
        });
    }
};
