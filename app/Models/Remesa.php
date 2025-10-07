<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Modelo Remesa
 * 
 * Representa un registro de remesa del sistema DBF
 * con funcionalidades optimizadas para consultas y manejo de datos
 */
class Remesa extends Model
{
    use HasFactory;

    protected $table = 'remesas';

    protected $fillable = [
        'oc', 'usuario_id', 'nombre_archivo', 'nro_carga', 'centro_servicio', 'fecha_carga', 'cargado_al_sistema', 'fecha_carga_sistema',
        // Campos DBF actualizados según nueva estructura
        'nis', 'nromedidor', 'diametro', 'clase', 'retfech', 'rethor', 'fechaprog', 'fechaing', 'tel_clie', 'horaprog',
        'cus', 'f_inst', 'marcamed', 'reclamante', 'nomclie', 'dir_proc', 'dir_cata', 'tipo_afe', 'resol', 'itin', 'aol',
        'correcarta', 'nrocarga_dbf', 'emisor', 'especial', 'reconsi', 'f_reclamo', 'hrprom', 'hrabas', 'regabas', 'empresa',
        'masivo_bool', 'ruta_num', 'cgv', 'db_mode', 'db_afab', 'dbq_max', 'dbq_min', 'dbq_perm', 'dbq_tran', 'ref_dir_ca',
        'ref_dir_pr', 'cup', 'tipo_dseg', 'cua', 'tarifa', 'reclamo', 'editado', 'fecha_edicion', 'editado_por', 'campos_editados'
    ];

    protected $casts = [
        'fecha_carga' => 'datetime',
        'cargado_al_sistema' => 'boolean',
        'fecha_carga_sistema' => 'datetime',
        'retfech' => 'date',
        'fechaprog' => 'date',
        'fechaing' => 'date',
        'f_inst' => 'date',
        'f_reclamo' => 'date',
        'rethor' => 'decimal:2',
        'horaprog' => 'decimal:2',
        'dbq_max' => 'decimal:2',
        'dbq_min' => 'decimal:2',
        'dbq_perm' => 'decimal:2',
        'dbq_tran' => 'decimal:2',
        'masivo_bool' => 'boolean',
        'editado' => 'boolean',
        'fecha_edicion' => 'datetime',
        'campos_editados' => 'array'
    ];

    protected $appends = [
        'nombre_completo_cliente',
        'telefono_formateado',
        'direccion_completa',
        'estado_carga',
        'tiempo_desde_carga'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    /**
     * Usuario que cargó la remesa
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Usuario que editó la remesa (si aplica)
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'editado_por');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Solo remesas cargadas al sistema
     */
    public function scopeCargadas(Builder $query): Builder
    {
        return $query->where('cargado_al_sistema', true);
    }

    /**
     * Scope: Remesas por usuario
     */
    public function scopeDelUsuario(Builder $query, int $userId): Builder
    {
        return $query->where('usuario_id', $userId);
    }

    /**
     * Scope: Remesas por número de carga
     */
    public function scopePorNroCarga(Builder $query, string $nroCarga): Builder
    {
        return $query->where('nro_carga', $nroCarga);
    }

    /**
     * Scope: Remesas editadas
     */
    public function scopeEditadas(Builder $query): Builder
    {
        return $query->where('editado', true);
    }

    /**
     * Scope: Remesas por rango de fechas
     */
    public function scopeEntreFechas(Builder $query, Carbon $inicio, Carbon $fin): Builder
    {
        return $query->whereBetween('fecha_carga', [$inicio, $fin]);
    }

    /**
     * Scope: Búsqueda por cliente
     */
    public function scopeBuscarCliente(Builder $query, string $termino): Builder
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nomclie', 'like', "%{$termino}%")
              ->orWhere('nis', 'like', "%{$termino}%")
              ->orWhere('nromedidor', 'like', "%{$termino}%");
        });
    }

    /**
     * Scope: Búsqueda por dirección
     */
    public function scopeBuscarDireccion(Builder $query, string $termino): Builder
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('dir_proc', 'like', "%{$termino}%")
              ->orWhere('dir_cata', 'like', "%{$termino}%");
        });
    }

    /**
     * Scope: Remesas recientes (últimos 30 días)
     */
    public function scopeRecientes(Builder $query): Builder
    {
        return $query->where('fecha_carga', '>=', now()->subDays(30));
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Getters)
    |--------------------------------------------------------------------------
    */

    /**
     * Obtener nombre completo del cliente (concatenado)
     */
    public function getNombreCompletoClienteAttribute(): string
    {
        $nombre = trim($this->nomclie);
        $reclamante = trim($this->reclamante);
        
        if (!empty($reclamante) && $reclamante !== $nombre) {
            return "{$nombre} (Reclamante: {$reclamante})";
        }
        
        return $nombre;
    }

    /**
     * Obtener teléfono formateado
     */
    public function getTelefonoFormateadoAttribute(): ?string
    {
        if (!$this->tel_clie) {
            return null;
        }
        
        $telefono = (string) $this->tel_clie;
        
        // Formatear según longitud
        if (strlen($telefono) === 10) {
            return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $telefono);
        }
        
        return $telefono;
    }

    /**
     * Convertir hora de retiro de formato SEDAPAL (HH.MM) a HH:MM
     * SEDAPAL usa punto como separador: 17.30 = 17:30 (no decimal)
     */
    public function getHoraRetFormateadaAttribute(): ?string
    {
        if (!$this->rethor) {
            return null;
        }

        // Siempre usar conversión decimal estándar
        // 16.0 = 16 horas + 0.0 * 60 minutos = 16:00
        $horaFloat = (float) $this->rethor;
        $horas = floor($horaFloat);
        $minutos = round(($horaFloat - $horas) * 60);
        
        // Validar rangos
        $horas = max(0, min(23, $horas));
        $minutos = max(0, min(59, $minutos));
        
        return sprintf('%02d:%02d', $horas, $minutos);
    }

    /**
     * Convertir hora de formato SEDAPAL (HH.MM) a HH:MM
     * SEDAPAL usa punto como separador: 17.30 = 17:30 (no decimal)
     */
    public function getHoraProgFormateadaAttribute(): ?string
    {
        if (!$this->horaprog) {
            return null;
        }

        // Siempre usar conversión decimal estándar
        // 17.5 = 17 horas + 0.5 * 60 minutos = 17:30
        $horaFloat = (float) $this->horaprog;
        $horas = floor($horaFloat);
        $minutos = round(($horaFloat - $horas) * 60);
        
        // Validar rangos
        $horas = max(0, min(23, $horas));
        $minutos = max(0, min(59, $minutos));
        
        return sprintf('%02d:%02d', $horas, $minutos);
    }

    /**
     * Obtener tipo de remesa basado en el emisor
     */
    public function getTipoRemesaAttribute(): string
    {
        if (!$this->emisor) {
            return 'No especificado';
        }

        // Convertir a mayúsculas para comparación
        $emisor = strtoupper(trim($this->emisor));
        
        // Determinar tipo basado en el campo emisor
        switch ($emisor) {
            case 'RECLAMO':
            case 'RECLAMOS':
                return 'Reclamo';
            case 'OFICIO':
            case 'OFICINA':
                return 'Oficio';
            default:
                // Si no coincide con los patrones conocidos, mostrar el valor original
                return ucfirst(strtolower($emisor));
        }
    }

    /**
     * Obtener dirección completa
     */
    public function getDireccionCompletaAttribute(): string
    {
        $direccion = trim($this->dir_proc);
        $referencia = trim($this->dir_cata);
        
        if (!empty($referencia)) {
            $direccion .= " - Ref: {$referencia}";
        }
        
        return $direccion;
    }

    /**
     * Obtener estado de carga descriptivo
     */
    public function getEstadoCargaAttribute(): string
    {
        if ($this->cargado_al_sistema) {
            return $this->editado ? 'Cargado y Editado' : 'Cargado';
        }
        
        return 'Pendiente';
    }

    /**
     * Obtener tiempo transcurrido desde la carga
     */
    public function getTiempoDesdeCargaAttribute(): string
    {
        return $this->fecha_carga->diffForHumans();
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators (Setters)
    |--------------------------------------------------------------------------
    */

    /**
     * Limpiar y normalizar NIS
     */
    public function setNisAttribute($value): void
    {
        $this->attributes['nis'] = $value ? str_pad(trim($value), 7, '0', STR_PAD_LEFT) : null;
    }

    /**
     * Limpiar y normalizar nombre del cliente
     */
    public function setNomclieAttribute($value): void
    {
        $this->attributes['nomclie'] = $value ? ucwords(strtolower(trim($value))) : null;
    }

    /**
     * Normalizar teléfono (solo números)
     */
    public function setTelClieAttribute($value): void
    {
        if ($value) {
            $telefono = preg_replace('/\D/', '', $value);
            $this->attributes['tel_clie'] = $telefono ?: null;
        } else {
            $this->attributes['tel_clie'] = null;
        }
    }

    /**
     * Normalizar dirección
     */
    public function setDirProcAttribute($value): void
    {
        $this->attributes['dir_proc'] = $value ? ucwords(strtolower(trim($value))) : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de Utilidad
    |--------------------------------------------------------------------------
    */

    /**
     * Verificar si existe un número de carga para un usuario
     */
    public static function existeNroCarga(string $nroCarga, int $usuarioId = null): bool
    {
        $query = self::where('nro_carga', $nroCarga);
        
        if ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        }
        
        return $query->exists();
    }

    /**
     * Obtener remesa duplicada si existe
     */
    public static function buscarDuplicado(string $nroCarga, int $usuarioId): ?self
    {
        return self::where('nro_carga', $nroCarga)
                   ->where('usuario_id', $usuarioId)
                   ->first();
    }

    /**
     * Marcar como cargado al sistema
     */
    public function cargarAlSistema(): void
    {
        $this->update([
            'cargado_al_sistema' => true,
            'fecha_carga_sistema' => now()
        ]);
    }

    /**
     * Registrar edición del registro
     */
    public function registrarEdicion(int $editorId, array $camposEditados): void
    {
        $this->update([
            'editado' => true,
            'fecha_edicion' => now(),
            'editado_por' => $editorId,
            'campos_editados' => array_merge($this->campos_editados ?? [], $camposEditados)
        ]);
    }

    /**
     * Obtener estadísticas de una remesa por número de carga
     */
    public static function estadisticasPorNroCarga(string $nroCarga, int $usuarioId): array
    {
        $registros = self::where('nro_carga', $nroCarga)
                        ->where('usuario_id', $usuarioId);

        return [
            'total_registros' => $registros->count(),
            'registros_editados' => $registros->where('editado', true)->count(),
            'fecha_carga' => $registros->first()?->fecha_carga,
            'nombre_archivo' => $registros->first()?->nombre_archivo,
            'ultimo_editado' => $registros->where('editado', true)
                                        ->orderBy('fecha_edicion', 'desc')
                                        ->first()?->fecha_edicion
        ];
    }

    /**
     * Obtener resumen de campos editados
     */
    public function getResumenEdicionesAttribute(): array
    {
        if (!$this->editado || !$this->campos_editados) {
            return [];
        }

        $resumen = [];
        foreach ($this->campos_editados as $campo => $cambio) {
            $resumen[] = [
                'campo' => $campo,
                'valor_anterior' => $cambio['from'] ?? 'N/A',
                'valor_nuevo' => $cambio['to'] ?? 'N/A',
                'fecha' => $this->fecha_edicion
            ];
        }

        return $resumen;
    }

    /**
     * Validar integridad de datos críticos
     */
    public function validarIntegridad(): array
    {
        $errores = [];

        if (empty($this->nis)) {
            $errores[] = 'NIS es requerido';
        }

        if (empty($this->nomclie)) {
            $errores[] = 'Nombre del cliente es requerido';
        }

        if (empty($this->dir_proc)) {
            $errores[] = 'Dirección es requerida';
        }

        if (!empty($this->tel_clie) && strlen((string)$this->tel_clie) < 7) {
            $errores[] = 'Teléfono debe tener al menos 7 dígitos';
        }

        return $errores;
    }

    /*
    |--------------------------------------------------------------------------
    | Query Builders Personalizados
    |--------------------------------------------------------------------------
    */

    /**
     * Obtener remesas agrupadas por número de carga para un usuario
     */
    public static function resumenPorUsuario(int $usuarioId): \Illuminate\Support\Collection
    {
        return self::select([
                'nro_carga',
                'nombre_archivo',
                'fecha_carga',
                DB::raw('COUNT(*) as total_registros'),
                DB::raw('COUNT(CASE WHEN editado = 1 THEN 1 END) as registros_editados'),
                DB::raw('MIN(id) as primer_id')
            ])
            ->where('usuario_id', $usuarioId)
            ->where('cargado_al_sistema', true)
            ->groupBy(['nro_carga', 'nombre_archivo', 'fecha_carga'])
            ->orderBy('fecha_carga', 'desc')
            ->get();
    }

    /**
     * Buscar registros con filtros múltiples
     */
    public static function buscarConFiltros(array $filtros, int $usuarioId): Builder
    {
        $query = self::where('usuario_id', $usuarioId)
                     ->where('cargado_al_sistema', true);

        if (!empty($filtros['nro_carga'])) {
            $query->where('nro_carga', 'like', '%' . $filtros['nro_carga'] . '%');
        }

        if (!empty($filtros['cliente'])) {
            $query->buscarCliente($filtros['cliente']);
        }

        if (!empty($filtros['direccion'])) {
            $query->buscarDireccion($filtros['direccion']);
        }

        if (!empty($filtros['desde'])) {
            $query->where('fecha_carga', '>=', Carbon::parse($filtros['desde']));
        }

        if (!empty($filtros['hasta'])) {
            $query->where('fecha_carga', '<=', Carbon::parse($filtros['hasta']));
        }

        if (isset($filtros['editado'])) {
            $query->where('editado', $filtros['editado']);
        }

        return $query;
    }
}
