<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    /**
     * Recibir logs desde el frontend
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timestamp' => 'required|string',
            'level' => 'required|string|in:error,warn,info,debug',
            'message' => 'required|string',
            'data' => 'nullable',
            'url' => 'required|string',
            'userAgent' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid log data'], 400);
        }

        $logData = $request->all();
        
        // Agregar información adicional del servidor
        $logData['ip'] = $request->ip();
        $logData['user_id'] = Auth::id();
        $logData['session_id'] = session()->getId();

        // Formatear mensaje para el log de Laravel
        $logMessage = sprintf(
            '[FRONTEND] %s | URL: %s | User: %s | IP: %s | Data: %s',
            $logData['message'],
            $logData['url'],
            $logData['user_id'] ?? 'Guest',
            $logData['ip'],
            $logData['data'] ? json_encode($logData['data']) : 'null'
        );

        // Escribir al log de Laravel según el nivel
        switch ($logData['level']) {
            case 'error':
                Log::error($logMessage, $logData);
                break;
            case 'warn':
                Log::warning($logMessage, $logData);
                break;
            case 'info':
                Log::info($logMessage, $logData);
                break;
            case 'debug':
                Log::debug($logMessage, $logData);
                break;
        }

        return response()->json(['status' => 'logged'], 200);
    }

    /**
     * Ver logs del sistema (solo para administradores)
     */
    public function index(Request $request)
    {
        // Solo usuarios autenticados pueden ver logs
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $level = $request->get('level', 'all');
        $lines = $request->get('lines', 100);

        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (!file_exists($logFile)) {
                return response()->json(['logs' => []], 200);
            }

            $logs = [];
            $file = new \SplFileObject($logFile);
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();
            
            // Leer las últimas N líneas
            $startLine = max(0, $totalLines - $lines);
            $file->seek($startLine);
            
            while (!$file->eof()) {
                $line = $file->current();
                $file->next();
                
                if (strpos($line, '[FRONTEND]') !== false) {
                    if ($level === 'all' || strpos($line, strtoupper($level)) !== false) {
                        $logs[] = trim($line);
                    }
                }
            }

            return response()->json([
                'logs' => array_reverse($logs),
                'total_lines' => count($logs),
                'file_size' => filesize($logFile)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error reading logs: ' . $e->getMessage());
            return response()->json(['error' => 'Could not read logs'], 500);
        }
    }

    /**
     * Limpiar logs antiguos (solo administradores)
     */
    public function clean(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (file_exists($logFile)) {
                // Crear backup antes de limpiar
                $backupFile = storage_path('logs/laravel_backup_' . date('Y-m-d_H-i-s') . '.log');
                copy($logFile, $backupFile);
                
                // Limpiar el archivo de log
                file_put_contents($logFile, '');
                
                Log::info('Logs cleaned by user: ' . Auth::id());
                
                return response()->json([
                    'status' => 'cleaned',
                    'backup_created' => basename($backupFile)
                ], 200);
            }

            return response()->json(['status' => 'no_logs_found'], 200);

        } catch (\Exception $e) {
            Log::error('Error cleaning logs: ' . $e->getMessage());
            return response()->json(['error' => 'Could not clean logs'], 500);
        }
    }
}