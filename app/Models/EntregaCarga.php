<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaCarga extends Model
{
    use HasFactory;

    protected $table = 'entregas_cargas';

    protected $fillable = [
        'codigo_entrega',
        'nombre_entrega',
        'remesa_id',
        'operario_id',
        'asignado_por',
        'registros_asignados',
        'total_registros',
        'zona_asignada',
        'instrucciones',
        'estado',
        'fecha_asignacion',
        'fecha_inicio',
        'fecha_completado',
        'observaciones',
        'progreso',
    ];

    protected $casts = [
        'registros_asignados' => 'array',
        'fecha_asignacion' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_completado' => 'datetime',
        'progreso' => 'decimal:2',
    ];

    // Relaciones
    public function remesa(): BelongsTo
    {
        return $this->belongsTo(Remesa::class);
    }

    public function operario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'operario_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'asignado_por');
    }

    // Scopes
    public function scopeAsignadas($query)
    {
        return $query->where('estado', 'asignada');
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    public function scopeDelOperario($query, $operarioId)
    {
        return $query->where('operario_id', $operarioId);
    }

    public function scopeDeLaRemesa($query, $remesaId)
    {
        return $query->where('remesa_id', $remesaId);
    }

    // Accessors
    public function getEstadoTextoAttribute(): string
    {
        return match($this->estado) {
            'asignada' => 'Asignada',
            'en_proceso' => 'En Proceso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
            default => 'Desconocido'
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'asignada' => 'warning',
            'en_proceso' => 'info',
            'completada' => 'success',
            'cancelada' => 'secondary',
            default => 'light'
        };
    }

    public function getProgresoTextoAttribute(): string
    {
        return number_format($this->progreso, 1) . '%';
    }

    // Métodos de utilidad
    public function puedeIniciar(): bool
    {
        return $this->estado === 'asignada';
    }

    public function puedeCompletar(): bool
    {
        return in_array($this->estado, ['asignada', 'en_proceso']);
    }

    public function iniciar(): void
    {
        if ($this->estado === 'asignada') {
            $this->update([
                'estado' => 'en_proceso',
                'fecha_inicio' => now()
            ]);
        }
    }

    public function completar(string $observaciones = null): void
    {
        if ($this->puedeCompletar()) {
            $this->update([
                'estado' => 'completada',
                'fecha_completado' => now(),
                'progreso' => 100.00,
                'observaciones' => $observaciones
            ]);
        }
    }

    public function actualizarProgreso(float $progreso): void
    {
        $progreso = max(0, min(100, $progreso));
        $this->update(['progreso' => $progreso]);
    }

    public function cancelar(string $motivo = null): void
    {
        $this->update([
            'estado' => 'cancelada',
            'observaciones' => $motivo
        ]);
    }

    // Generar código único de entrega
    public static function generarCodigoEntrega(): string
    {
        do {
            $codigo = 'ENT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('codigo_entrega', $codigo)->exists());

        return $codigo;
    }
}