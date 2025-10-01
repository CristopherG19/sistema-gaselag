<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['error' => 'Debes iniciar sesión para acceder a esta función.']);
        }

        $user = Auth::user();
        
        // Verificar el rol del usuario
        if ($user->rol !== $role) {
            $rolTexto = $role === 'admin' ? 'Administrador' : 'Usuario Normal';
            return redirect()->back()->withErrors(['error' => "Solo los usuarios con rol de {$rolTexto} pueden acceder a esta función."]);
        }

        return $next($request);
    }
}