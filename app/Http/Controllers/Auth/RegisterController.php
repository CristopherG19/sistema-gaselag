<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => 'required|email|unique:usuarios,correo',
            'password' => 'required|min:6|confirmed',
            'rol' => 'required|in:usuario,admin',
        ]);

        Usuario::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'password' => bcrypt($request->password),
            'rol' => $request->rol, // <-- Guarda el rol
        ]);

        return redirect('/login')->with('mensaje', '¡Registro exitoso! Ahora puedes iniciar sesión.');
    }
}
