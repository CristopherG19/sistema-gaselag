<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre', 'apellidos', 'correo', 'password', 'rol', 'reset_token', 'reset_expira',
        'activo', 'ultimo_acceso', 'notas'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'ultimo_acceso' => 'datetime',
    ];

    public function cambiosPassword()
    {
        return $this->hasMany(CambioPassword::class, 'usuario_id');
    }

    /**
     * Relación con remesas cargadas por el usuario
     */
    public function remesas()
    {
        return $this->hasMany(Remesa::class, 'usuario_id');
    }

    /**
     * Relación con remesas pendientes del usuario
     */
    public function remesasPendientes()
    {
        return $this->hasMany(RemesaPendiente::class, 'usuario_id');
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    /**
     * Verificar si el usuario es usuario normal
     */
    public function isUsuario(): bool
    {
        return $this->rol === 'usuario';
    }

    /**
     * Verificar si el usuario es operario de campo
     */
    public function isOperarioCampo(): bool
    {
        return $this->rol === 'operario_campo';
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActivo(): bool
    {
        return $this->activo === true;
    }

    /**
     * Relación con quejas creadas por el usuario
     */
    public function quejas()
    {
        return $this->hasMany(Queja::class, 'usuario_id');
    }

    /**
     * Relación con quejas asignadas al usuario
     */
    public function quejasAsignadas()
    {
        return $this->hasMany(Queja::class, 'asignado_a');
    }

    /**
     * Relación con entregas asignadas al operario
     */
    public function entregasAsignadas()
    {
        return $this->hasMany(EntregaCarga::class, 'operario_id');
    }

    /**
     * Relación con entregas creadas por el usuario
     */
    public function entregasCreadas()
    {
        return $this->hasMany(EntregaCarga::class, 'asignado_por');
    }

    /**
     * Obtener el rol en texto legible
     */
    public function getRolTextoAttribute(): string
    {
        return match($this->rol) {
            'admin' => 'Administrador',
            'usuario' => 'Usuario Normal',
            'operario_campo' => 'Operario de Campo',
            default => 'Desconocido'
        };
    }

    /**
     * Obtener el color del rol para la interfaz
     */
    public function getRolColorAttribute(): string
    {
        return match($this->rol) {
            'admin' => 'danger',
            'usuario' => 'primary',
            'operario_campo' => 'success',
            default => 'secondary'
        };
    }
}