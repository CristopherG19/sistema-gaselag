<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre', 'apellidos', 'correo', 'password', 'rol', 'reset_token', 'reset_expira'
    ];

    protected $hidden = [
        'password', 'remember_token',
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
     * Obtener el rol en texto legible
     */
    public function getRolTextoAttribute(): string
    {
        return $this->rol === 'admin' ? 'Administrador' : 'Usuario Normal';
    }
}