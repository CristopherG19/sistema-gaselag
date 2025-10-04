<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo RemesaPendiente
 * 
 * Representa una remesa pendiente de procesar
 * con datos del DBF almacenados como JSON
 */
class RemesaPendiente extends Model
{
    use HasFactory;

    protected $table = 'remesas_pendientes';

    protected $fillable = [
        'usuario_id',
        'nombre_archivo',
        'nro_carga',
        'fecha_carga',
        'datos_dbf',
    ];

    /**
     * Reglas de validación
     */
    public static function rules()
    {
        return [
            'usuario_id' => 'required|exists:usuarios,id',
            'nombre_archivo' => 'required|string|max:255',
            'nro_carga' => 'required|string|max:20',
            'fecha_carga' => 'required|date',
            'datos_dbf' => 'required|array',
        ];
    }

    /**
     * Verificar si existe duplicado por nombre de archivo para un usuario
     */
    public static function existeArchivoPorUsuario(string $nombreArchivo, int $usuarioId): bool
    {
        return static::where('nombre_archivo', $nombreArchivo)
                    ->where('usuario_id', $usuarioId)
                    ->exists();
    }

    /**
     * Verificar si existe duplicado por número de carga para un usuario
     */
    public static function existeNroCargaPorUsuario(string $nroCarga, int $usuarioId): bool
    {
        return static::where('nro_carga', $nroCarga)
                    ->where('usuario_id', $usuarioId)
                    ->exists();
    }

    protected $casts = [
        'fecha_carga' => 'datetime',
        'datos_dbf' => 'array',
    ];

    /**
     * Relación con el usuario propietario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Obtener datos específicos del DBF
     */
    public function getDatoDbf(string $campo): mixed
    {
        return $this->datos_dbf[$campo] ?? null;
    }

    /**
     * Establecer datos específicos del DBF
     */
    public function setDatoDbf(string $campo, mixed $valor): void
    {
        $datos = $this->datos_dbf ?? [];
        $datos[$campo] = $valor;
        $this->datos_dbf = $datos;
    }

    /**
     * Obtener todos los datos del DBF como array
     */
    public function getDatosDbfArray(): array
    {
        return $this->datos_dbf ?? [];
    }

    /**
     * Convertir a formato compatible con Remesa
     */
    public function toRemesaAttributes(): array
    {
        $datos = $this->getDatosDbfArray();
        
        return [
            'usuario_id' => $this->usuario_id,
            'nombre_archivo' => $this->nombre_archivo,
            'nro_carga' => $this->nro_carga,
            'fecha_carga' => $this->fecha_carga,
            'cargado_al_sistema' => true,
            'fecha_carga_sistema' => now(),
            'nis' => $datos['nis'] ?? null,
            'nromedidor' => $datos['nromedidor'] ?? null,
            'diametro' => $datos['diametro'] ?? null,
            'clase' => $datos['clase'] ?? null,
            'retfech' => $datos['retfech'] ?? null,
            'rethor' => $datos['rethor'] ?? null,
            'fechaprog' => $datos['fechaprog'] ?? null,
            'fechaing' => $datos['fechaing'] ?? null,
            'tel_clie' => $datos['tel_clie'] ?? null,
            'horaprog' => $datos['horaprog'] ?? null,
            'cus' => $datos['cus'] ?? null,
            'f_inst' => $datos['f_inst'] ?? null,
            'marcamed' => $datos['marcamed'] ?? null,
            'reclamante' => $datos['reclamante'] ?? null,
            'nomclie' => $datos['nomclie'] ?? null,
            'dir_proc' => $datos['dir_proc'] ?? null,
            'dir_cata' => $datos['dir_cata'] ?? null,
            'tipo_afe' => $datos['tipo_afe'] ?? null,
            'resol' => $datos['resol'] ?? null,
            'itin' => $datos['itin'] ?? null,
            'aol' => $datos['aol'] ?? null,
            'correcarta' => $datos['correcarta'] ?? null,
            'nrocarga_dbf' => $datos['nrocarga_dbf'] ?? null,
            'emisor' => $datos['emisor'] ?? null,
            'especial' => $datos['especial'] ?? null,
            'reconsi' => $datos['reconsi'] ?? null,
            'f_reclamo' => $datos['f_reclamo'] ?? null,
            'hrprom' => $datos['hrprom'] ?? null,
            'hrabas' => $datos['hrabas'] ?? null,
            'regabas' => $datos['regabas'] ?? null,
            'empresa' => $datos['empresa'] ?? null,
            'masivo_bool' => $datos['masivo_bool'] ?? null,
            'ruta_num' => $datos['ruta_num'] ?? null,
            'cgv' => $datos['cgv'] ?? null,
            'db_mode' => $datos['db_mode'] ?? null,
            'db_afab' => $datos['db_afab'] ?? null,
            'dbq_max' => $datos['dbq_max'] ?? null,
            'dbq_min' => $datos['dbq_min'] ?? null,
            'dbq_perm' => $datos['dbq_perm'] ?? null,
            'dbq_tran' => $datos['dbq_tran'] ?? null,
            'ref_dir_ca' => $datos['ref_dir_ca'] ?? null,
            'ref_dir_pr' => $datos['ref_dir_pr'] ?? null,
            'cup' => $datos['cup'] ?? null,
            'tipo_dseg' => $datos['tipo_dseg'] ?? null,
            'cua' => $datos['cua'] ?? null,
            'tarifa' => $datos['tarifa'] ?? null,
            'reclamo' => $datos['reclamo'] ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}