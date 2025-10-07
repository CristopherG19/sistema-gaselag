<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BancoEnsayo extends Model
{
    use HasFactory;

    protected $table = 'bancos_ensayo';

    protected $fillable = [
        'nombre',
        'ubicacion',
        'capacidad_maxima',
        'estado',
        'descripcion',
        'especificaciones_tecnicas',
        'responsable_tecnico',
        'ultima_calibracion',
        'proxima_calibracion'
    ];

    protected $casts = [
        'especificaciones_tecnicas' => 'array',
        'ultima_calibracion' => 'datetime',
        'proxima_calibracion' => 'datetime'
    ];

    /**
     * Relación con los ensayos realizados en este banco
     */
    public function ensayos(): HasMany
    {
        return $this->hasMany(Ensayo::class);
    }

    /**
     * Ensayos actualmente en proceso en este banco
     */
    public function ensayosEnProceso(): HasMany
    {
        return $this->hasMany(Ensayo::class)->where('estado', 'en_proceso');
    }

    /**
     * Verificar si el banco está disponible (no está en mantenimiento y tiene capacidad)
     */
    public function estaDisponible(): bool
    {
        if ($this->estado !== 'activo') {
            return false;
        }

        $ensayosActivos = $this->ensayosEnProceso()->count();
        return $ensayosActivos < $this->capacidad_maxima;
    }

    /**
     * Obtener capacidad disponible actual
     */
    public function capacidadDisponible(): int
    {
        $ensayosActivos = $this->ensayosEnProceso()->count();
        return max(0, $this->capacidad_maxima - $ensayosActivos);
    }

    /**
     * Verificar si necesita calibración
     */
    public function necesitaCalibracion(): bool
    {
        if (!$this->proxima_calibracion) {
            return false;
        }
        
        return now()->greaterThan($this->proxima_calibracion);
    }

    /**
     * Scope para bancos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para bancos disponibles
     */
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'activo')
                    ->whereRaw('(SELECT COUNT(*) FROM ensayos WHERE banco_ensayo_id = bancos_ensayo.id AND estado = "en_proceso") < capacidad_maxima');
    }
}
