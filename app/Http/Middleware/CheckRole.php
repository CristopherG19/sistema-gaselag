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
        
        // Verificar si el usuario está activo
        if (!$user->isActivo()) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['error' => 'Tu cuenta ha sido desactivada.']);
        }

        // Verificar rol (soporta múltiples roles separados por |)
        $rolesPermitidos = explode('|', $role);
        
        if (!in_array($user->rol, $rolesPermitidos)) {
            $rolTexto = match($role) {
                'admin' => 'Administrador',
                'usuario' => 'Usuario Normal',
                'operario_campo' => 'Operario de Campo',
                'admin|usuario' => 'Administrador o Usuario Normal',
                'admin|usuario|operario_campo' => 'cualquier rol autorizado',
                default => 'Rol específico'
            };
            return redirect()->back()->withErrors(['error' => "Solo los usuarios con rol de {$rolTexto} pueden acceder a esta función."]);
        }

        return $next($request);
    }
}