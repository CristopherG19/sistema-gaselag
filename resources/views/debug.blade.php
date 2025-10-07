<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Sistema Remesas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .log-container { max-height: 400px; overflow-y: auto; background: #f8f9fa; border-radius: 5px; padding: 10px; }
        .log-line { font-size: 11px; margin-bottom: 2px; }
        .error-log { color: #dc3545; }
        .info-log { color: #0d6efd; }
        .warning-log { color: #fd7e14; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>🔍 Sistema de Debug - Actualización de Registros</h1>
                    <div>
                        <button onclick="location.reload()" class="btn btn-success">🔄 Refrescar</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">🏠 Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-3">
            <!-- Estado de Autenticación -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">🔐 Estado de Autenticación</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><td><strong>Autenticado:</strong></td><td>{{ auth()->check() ? '✅ Sí' : '❌ No' }}</td></tr>
                            @if(auth()->check())
                                <tr><td><strong>Usuario ID:</strong></td><td>{{ auth()->id() }}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>{{ auth()->user()->email ?? 'N/A' }}</td></tr>
                                <tr><td><strong>Es Admin:</strong></td><td>{{ auth()->user()->isAdmin() ? '✅ Sí' : '❌ No' }}</td></tr>
                            @endif
                            <tr><td><strong>Session ID:</strong></td><td><small>{{ session()->getId() }}</small></td></tr>
                            <tr><td><strong>CSRF Token:</strong></td><td><small>{{ substr(csrf_token(), 0, 10) }}...</small></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Información del Sistema -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">⚙️ Configuración PHP</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            @foreach($phpInfo as $key => $value)
                                <tr><td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></td><td>{{ $value }}</td></tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Datos de Sesión -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0">📊 Datos de Sesión</h6>
                    </div>
                    <div class="card-body">
                        <div class="log-container" style="max-height: 200px;">
                            @if(empty($sessionData))
                                <p class="text-muted">No hay datos de sesión</p>
                            @else
                                @foreach($sessionData as $key => $value)
                                    <div class="log-line">
                                        <strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : (is_string($value) ? substr($value, 0, 50) : $value) }}
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <!-- Debug Log Específico -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">🐛 Debug Log Específico (Últimas 20 líneas)</h6>
                    </div>
                    <div class="card-body">
                        <div class="log-container">
                            @if(trim($debugLog) === 'No hay logs de debug específicos disponibles.')
                                <p class="text-muted">{{ $debugLog }}</p>
                            @else
                                <pre style="font-size: 11px; margin: 0;">{{ $debugLog }}</pre>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Laravel Log -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">📝 Laravel Log (Últimas 30 líneas)</h6>
                    </div>
                    <div class="card-body">
                        <div class="log-container">
                            @if(trim($laravelLog) === 'No hay logs de Laravel disponibles.')
                                <p class="text-muted">{{ $laravelLog }}</p>
                            @else
                                <pre style="font-size: 11px; margin: 0;">{{ $laravelLog }}</pre>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle"></i> Instrucciones para Debug:</h6>
                    <ol>
                        <li>Ve a <a href="{{ route('remesa.lista') }}" target="_blank">Lista de Remesas</a> y selecciona una para editar</li>
                        <li>Intenta actualizar un registro</li>
                        <li>Cuando te redirija al login, vuelve aquí y refresca esta página</li>
                        <li>Revisa los logs para ver qué sucedió</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh cada 10 segundos si está en desarrollo
        if(location.hostname === '127.0.0.1' || location.hostname === 'localhost') {
            setTimeout(() => {
                if(confirm('¿Refrescar la página de debug para ver nuevos logs?')) {
                    location.reload();
                }
            }, 10000);
        }
    </script>
</body>
</html>
