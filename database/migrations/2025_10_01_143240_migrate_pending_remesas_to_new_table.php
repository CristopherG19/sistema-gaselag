<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrar remesas pendientes a la nueva tabla
        $pendingRemesas = DB::table('remesas')
            ->where('cargado_al_sistema', false)
            ->get();

        foreach ($pendingRemesas as $remesa) {
            // Crear objeto JSON con los datos del DBF
            $datosDbf = [
                'nis' => $remesa->nis,
                'nromedidor' => $remesa->nromedidor,
                'diametro' => $remesa->diametro,
                'clase' => $remesa->clase,
                'retfech' => $remesa->retfech,
                'rethor' => $remesa->rethor,
                'fechaprog' => $remesa->fechaprog,
                'fechaing' => $remesa->fechaing,
                'tel_clie' => $remesa->tel_clie,
                'horaprog' => $remesa->horaprog,
                'cus' => $remesa->cus,
                'f_inst' => $remesa->f_inst,
                'marcamed' => $remesa->marcamed,
                'reclamante' => $remesa->reclamante,
                'nomclie' => $remesa->nomclie,
                'dir_proc' => $remesa->dir_proc,
                'dir_cata' => $remesa->dir_cata,
                'tipo_afe' => $remesa->tipo_afe,
                'resol' => $remesa->resol,
                'itin' => $remesa->itin,
                'aol' => $remesa->aol,
                'correcarta' => $remesa->correcarta,
                'nrocarga_dbf' => $remesa->nrocarga_dbf,
                'emisor' => $remesa->emisor,
                'especial' => $remesa->especial,
                'reconsi' => $remesa->reconsi,
                'f_reclamo' => $remesa->f_reclamo,
                'hrprom' => $remesa->hrprom,
                'hrabas' => $remesa->hrabas,
                'regabas' => $remesa->regabas,
                'empresa' => $remesa->empresa,
                'masivo_bool' => $remesa->masivo_bool,
                'ruta_num' => $remesa->ruta_num,
                'cgv' => $remesa->cgv,
                'db_mode' => $remesa->db_mode,
                'db_afab' => $remesa->db_afab,
                'dbq_max' => $remesa->dbq_max,
                'dbq_min' => $remesa->dbq_min,
                'dbq_perm' => $remesa->dbq_perm,
                'dbq_tran' => $remesa->dbq_tran,
                'ref_dir_ca' => $remesa->ref_dir_ca,
                'ref_dir_pr' => $remesa->ref_dir_pr,
                'cup' => $remesa->cup,
                'tipo_dseg' => $remesa->tipo_dseg,
                'cua' => $remesa->cua,
                'tarifa' => $remesa->tarifa,
                'reclamo' => $remesa->reclamo,
            ];

            // Insertar en la nueva tabla
            DB::table('remesas_pendientes')->insert([
                'id' => $remesa->id,
                'usuario_id' => $remesa->usuario_id,
                'nombre_archivo' => $remesa->nombre_archivo,
                'nro_carga' => $remesa->nro_carga,
                'fecha_carga' => $remesa->fecha_carga,
                'datos_dbf' => json_encode($datosDbf),
                'created_at' => $remesa->created_at,
                'updated_at' => $remesa->updated_at,
            ]);
        }

        // Eliminar remesas pendientes de la tabla original
        DB::table('remesas')->where('cargado_al_sistema', false)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrar de vuelta a la tabla original
        $pendingRemesas = DB::table('remesas_pendientes')->get();

        foreach ($pendingRemesas as $remesa) {
            $datosDbf = json_decode($remesa->datos_dbf, true);
            
            DB::table('remesas')->insert([
                'id' => $remesa->id,
                'usuario_id' => $remesa->usuario_id,
                'nombre_archivo' => $remesa->nombre_archivo,
                'nro_carga' => $remesa->nro_carga,
                'fecha_carga' => $remesa->fecha_carga,
                'cargado_al_sistema' => false,
                'fecha_carga_sistema' => null,
                'nis' => $datosDbf['nis'] ?? null,
                'nromedidor' => $datosDbf['nromedidor'] ?? null,
                'diametro' => $datosDbf['diametro'] ?? null,
                'clase' => $datosDbf['clase'] ?? null,
                'retfech' => $datosDbf['retfech'] ?? null,
                'rethor' => $datosDbf['rethor'] ?? null,
                'fechaprog' => $datosDbf['fechaprog'] ?? null,
                'fechaing' => $datosDbf['fechaing'] ?? null,
                'tel_clie' => $datosDbf['tel_clie'] ?? null,
                'horaprog' => $datosDbf['horaprog'] ?? null,
                'cus' => $datosDbf['cus'] ?? null,
                'f_inst' => $datosDbf['f_inst'] ?? null,
                'marcamed' => $datosDbf['marcamed'] ?? null,
                'reclamante' => $datosDbf['reclamante'] ?? null,
                'nomclie' => $datosDbf['nomclie'] ?? null,
                'dir_proc' => $datosDbf['dir_proc'] ?? null,
                'dir_cata' => $datosDbf['dir_cata'] ?? null,
                'tipo_afe' => $datosDbf['tipo_afe'] ?? null,
                'resol' => $datosDbf['resol'] ?? null,
                'itin' => $datosDbf['itin'] ?? null,
                'aol' => $datosDbf['aol'] ?? null,
                'correcarta' => $datosDbf['correcarta'] ?? null,
                'nrocarga_dbf' => $datosDbf['nrocarga_dbf'] ?? null,
                'emisor' => $datosDbf['emisor'] ?? null,
                'especial' => $datosDbf['especial'] ?? null,
                'reconsi' => $datosDbf['reconsi'] ?? null,
                'f_reclamo' => $datosDbf['f_reclamo'] ?? null,
                'hrprom' => $datosDbf['hrprom'] ?? null,
                'hrabas' => $datosDbf['hrabas'] ?? null,
                'regabas' => $datosDbf['regabas'] ?? null,
                'empresa' => $datosDbf['empresa'] ?? null,
                'masivo_bool' => $datosDbf['masivo_bool'] ?? null,
                'ruta_num' => $datosDbf['ruta_num'] ?? null,
                'cgv' => $datosDbf['cgv'] ?? null,
                'db_mode' => $datosDbf['db_mode'] ?? null,
                'db_afab' => $datosDbf['db_afab'] ?? null,
                'dbq_max' => $datosDbf['dbq_max'] ?? null,
                'dbq_min' => $datosDbf['dbq_min'] ?? null,
                'dbq_perm' => $datosDbf['dbq_perm'] ?? null,
                'dbq_tran' => $datosDbf['dbq_tran'] ?? null,
                'ref_dir_ca' => $datosDbf['ref_dir_ca'] ?? null,
                'ref_dir_pr' => $datosDbf['ref_dir_pr'] ?? null,
                'cup' => $datosDbf['cup'] ?? null,
                'tipo_dseg' => $datosDbf['tipo_dseg'] ?? null,
                'cua' => $datosDbf['cua'] ?? null,
                'tarifa' => $datosDbf['tarifa'] ?? null,
                'reclamo' => $datosDbf['reclamo'] ?? null,
                'created_at' => $remesa->created_at,
                'updated_at' => $remesa->updated_at,
            ]);
        }

        // Eliminar tabla de pendientes
        DB::table('remesas_pendientes')->truncate();
    }
};