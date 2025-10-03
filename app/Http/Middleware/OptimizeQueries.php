<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Middleware para detectar y optimizar consultas N+1
 */
class OptimizeQueries
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo en desarrollo
        if (!config('app.debug', false)) {
            return $next($request);
        }

        $startQueries = count(DB::getQueryLog());
        DB::enableQueryLog();

        $response = $next($request);

        $queries = DB::getQueryLog();
        $totalQueries = count($queries);

        // Alertar si hay demasiadas consultas
        if ($totalQueries > 50) {
            Log::warning('Posible problema N+1 detectado', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'total_queries' => $totalQueries,
                'queries_sample' => array_slice($queries, -5) // Últimas 5 consultas
            ]);
        }

        // Agregar header para debugging
        if (config('app.debug')) {
            $response->headers->set('X-Database-Queries', $totalQueries);
        }

        return $response;
    }
}