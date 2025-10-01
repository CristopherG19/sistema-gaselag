<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class PasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'correo' => 'required|email|exists:usuarios,correo',
        ]);

        $usuario = Usuario::where('correo', $request->correo)->first();
        $token = Str::random(60);

        $usuario->reset_token = $token;
        $usuario->reset_expira = now()->addHour();
        $usuario->save();

        // Aquí puedes enviar el correo con el enlace de recuperación
        // Ejemplo simple (debes configurar Mail en .env):
        Mail::raw("Haz clic aquí para restablecer tu contraseña: " . url('/reset-password/'.$token), function($message) use ($usuario) {
            $message->to($usuario->correo)
                    ->subject('Recupera tu contraseña');
        });

        return back()->with('mensaje', 'Se ha enviado un enlace de recuperación a tu correo.');
    }

    public function showResetForm($token)
    {
        return view('reset-password', compact('token'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
            'token' => 'required'
        ]);

        $usuario = Usuario::where('reset_token', $request->token)
            ->where('reset_expira', '>', now())
            ->first();

        if (!$usuario) {
            return back()->withErrors(['token' => 'Token inválido o expirado.']);
        }

        $usuario->password = Hash::make($request->password);
        $usuario->reset_token = null;
        $usuario->reset_expira = null;
        $usuario->save();

        return redirect('/login')->with('mensaje', '¡Contraseña restablecida correctamente!');
    }
}
