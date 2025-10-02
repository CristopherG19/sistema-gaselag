<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queja extends Model
{
    use HasFactory;

    protected $table = 'quejas';

    protected $fillable = [
        'titulo',
        'descripcion',
        'estado',
        'prioridad',
        'tipo',
        'usuario_id',
        'asignado_a',
        'remesa_id',
        'fecha_creacion',
        'fecha_asignacion',
        'fecha_resolucion',
        'solucion',
        'comentarios',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_asignacion' => 'datetime',
        'fecha_resolucion' => 'datetime',
    ];

    // Relaciones
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'asignado_a');
    }

    public function remesa(): BelongsTo
    {
        return $this->belongsTo(Remesa::class);
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }

    public function scopeResueltas($query)
    {
        return $query->where('estado', 'resuelta');
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    public function scopeAsignadasA($query, $usuarioId)
    {
        return $query->where('asignado_a', $usuarioId);
    }

    public function scopeDelUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    // Accessors
    public function getEstadoTextoAttribute(): string
    {
        return match($this->estado) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'resuelta' => 'Resuelta',
            'cancelada' => 'Cancelada',
            default => 'Desconocido'
        };
    }

    public function getPrioridadTextoAttribute(): string
    {
        return match($this->prioridad) {
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'critica' => 'Crítica',
            default => 'Desconocida'
        };
    }

    public function getPrioridadColorAttribute(): string
    {
        return match($this->prioridad) {
            'baja' => 'success',
            'media' => 'warning',
            'alta' => 'danger',
            'critica' => 'dark',
            default => 'secondary'
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'pendiente' => 'warning',
            'en_proceso' => 'info',
            'resuelta' => 'success',
            'cancelada' => 'secondary',
            default => 'light'
        };
    }

    // Métodos de utilidad
    public function puedeSerAsignada(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function puedeSerResuelta(): bool
    {
        return in_array($this->estado, ['pendiente', 'en_proceso']);
    }

    public function marcarComoEnProceso(): void
    {
        if ($this->estado === 'pendiente') {
            $this->update([
                'estado' => 'en_proceso',
                'fecha_asignacion' => now()
            ]);
        }
    }

    public function resolver(string $solucion, string $comentarios = null): void
    {
        $this->update([
            'estado' => 'resuelta',
            'solucion' => $solucion,
            'comentarios' => $comentarios,
            'fecha_resolucion' => now()
        ]);
    }
}