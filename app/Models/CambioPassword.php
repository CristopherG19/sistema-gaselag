<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CambioPassword extends Model
{
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
