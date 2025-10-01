<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CambioPassword;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Mostrar historial de cambios de contraseña
     */
    public function historial()
    {
        $cambios = CambioPassword::with('usuario')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.historial_passwords', compact('cambios'));
    }
}
