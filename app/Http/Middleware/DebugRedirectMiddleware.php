<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Si es un redirect, loggear la información
        if ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400) {
            Log::error('🔄 REDIRECT DETECTADO', [
                'status' => $response->getStatusCode(),
                'location' => $response->headers->get('Location'),
                'url' => $request->url(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'is_authenticated' => auth()->check(),
            ]);
        }
        
        return $response;
    }
}
