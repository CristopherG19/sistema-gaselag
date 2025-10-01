<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoginLog;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('correo', 'password');

        $request->validate([
            'correo' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['correo' => $credentials['correo'], 'password' => $credentials['password']])) {
            // Registrar el log
            LoginLog::create([
                'usuario_id' => Auth::user()->id, // Cambiado de auth()->user()->id a Auth::user()->id
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'correo' => 'Credenciales incorrectas.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
