<?php

/** @var \Illuminate\Routing\Router $router */

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RemesaController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\GestionUsuariosController;
use App\Http\Controllers\GestionQuejasController;
use App\Http\Controllers\GestionEntregasController;

Route::get('/', [LoginController::class, 'showLoginForm']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/forgot-password', [PasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordController::class, 'reset'])->name('password.update');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/historial-passwords', [AdminController::class, 'historial'])->name('admin.historial-passwords');
});

// === RUTAS PARA SISTEMA DE REMESAS ===
Route::middleware(['auth'])->group(function () {
    // Rutas que pueden usar todos los usuarios autenticados
    Route::get('/remesa/lista', [RemesaController::class, 'lista'])->name('remesa.lista');
    Route::get('/remesas/general', [RemesaController::class, 'vistaGeneral'])->name('remesas.general');
    Route::get('/remesa/{nroCarga}/registros', [RemesaController::class, 'verRegistros'])->name('remesa.ver.registros');
    Route::get('/remesa/registro/{id}/detalle', [RemesaController::class, 'verDetalleRegistro'])->name('remesa.ver.detalle');
    Route::get('/remesa/registro/{id}/historial', [RemesaController::class, 'verHistorial'])->name('remesa.ver.historial');
    
    // Rutas que solo pueden usar administradores (temporalmente sin middleware)
    Route::middleware(['auth'])->group(function () {
        // Flujo original (mantener para compatibilidad)
        Route::get('/remesa/upload', [RemesaController::class, 'uploadForm'])->name('remesa.upload.form');
        Route::post('/remesa/upload', [RemesaController::class, 'upload'])->name('remesa.upload');
        Route::get('/remesa/preview', [RemesaController::class, 'preview'])->name('remesa.preview');
        Route::post('/remesa/verificar-duplicado', [RemesaController::class, 'verificarDuplicado'])->name('remesa.verificar.duplicado');
        Route::post('/remesa/cargar-sistema', [RemesaController::class, 'cargarAlSistema'])->name('remesa.cargar.sistema');
        
        // Nuevo flujo de dos pasos
        Route::post('/remesa/subir-pendiente', [RemesaController::class, 'subirComoPendiente'])->name('remesa.subir.pendiente');
        Route::get('/remesa/procesar/{id?}', [RemesaController::class, 'procesarForm'])->name('remesa.procesar.form');
        Route::post('/remesa/procesar', [RemesaController::class, 'procesarPendiente'])->name('remesa.procesar');
        Route::post('/remesa/procesar-directo/{id}', [RemesaController::class, 'procesarPendienteDirecto'])->name('remesa.procesar.directo');
        Route::post('/remesa/procesar-todos-pendientes', [RemesaController::class, 'procesarTodosPendientes'])->name('remesa.procesar.todos.pendientes');
        Route::delete('/remesa/eliminar-pendiente/{id}', [RemesaController::class, 'eliminarPendiente'])->name('remesa.eliminar.pendiente');
        
        Route::get('/remesa/cancelar', [RemesaController::class, 'cancelar'])->name('remesa.cancelar');
        
        // Rutas para editar registros específicos (solo admin)
        Route::get('/remesa/registro/{id}/editar', [RemesaController::class, 'editarRegistro'])->name('remesa.editar.registro');
        Route::put('/remesa/registro/{id}', [RemesaController::class, 'actualizarRegistro'])->name('remesa.actualizar.registro');
        Route::post('/remesa/registro/{id}/actualizar-post', [RemesaController::class, 'actualizarRegistro'])->name('remesa.actualizar.registro.post');
        
        // Ruta para refrescar token CSRF
        Route::get('/refresh-csrf', function() {
            return response()->json(['token' => csrf_token()]);
        });
        
        // Ruta de prueba simple para actualización
        Route::post('/test-update/{id}', function($id) {
            \Log::error('🧪 TEST UPDATE INICIADO', ['id' => $id, 'user' => auth()->id()]);
            
            try {
                $registro = \App\Models\Remesa::findOrFail($id);
                
                \Log::error('🧪 REGISTRO ENCONTRADO', ['nro_carga' => $registro->nro_carga]);
                
                $registro->update([
                    'editado' => true,
                    'fecha_edicion' => now(),
                    'editado_por' => auth()->id(),
                ]);
                
                \Log::error('🧪 ACTUALIZADO EXITOSO');
                
                return response()->json(['success' => true, 'message' => 'Test exitoso']);
                
            } catch (\Exception $e) {
                \Log::error('🧪 ERROR EN TEST', ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        })->name('test.update');
        
        // Ruta de debug
        Route::get('/debug-info', function() {
            $debugLogPath = storage_path('logs/debug_update.log');
            $laravelLogPath = storage_path('logs/laravel.log');
            
            $debugLog = 'No hay logs de debug específicos disponibles.';
            $laravelLog = 'No hay logs de Laravel disponibles.';
            
            // Leer debug log específico
            if (file_exists($debugLogPath)) {
                $content = file_get_contents($debugLogPath);
                $lines = explode("\n", $content);
                $debugLog = implode("\n", array_slice($lines, -20));
            }
            
            // Leer logs de Laravel más recientes
            if (file_exists($laravelLogPath)) {
                $content = file_get_contents($laravelLogPath);
                $lines = explode("\n", $content);
                $laravelLog = implode("\n", array_slice($lines, -30));
            }
            
            // Información adicional del sistema
            $sessionData = session()->all();
            $phpInfo = [
                'php_version' => phpversion(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'session_save_handler' => ini_get('session.save_handler'),
                'session_gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
            ];
            
            return view('debug', compact('debugLog', 'laravelLog', 'sessionData', 'phpInfo'));
        })->name('debug.info');
        
        // Rutas para gestionar remesas (solo admin)
        Route::get('/remesa/{nroCarga}/gestionar', [RemesaController::class, 'gestionarRegistros'])->name('remesa.gestionar.registros');
        Route::get('/remesa/{nroCarga}/editar-metadatos', [RemesaController::class, 'editarMetadatos'])->name('remesa.editar.metadatos');
        Route::put('/remesa/{nroCarga}/metadatos', [RemesaController::class, 'actualizarMetadatos'])->name('remesa.actualizar.metadatos');
    });
});

// === RUTAS DEL SISTEMA DE LOGGING ===
Route::prefix('api')->group(function () {
    Route::post('/logs', [LogController::class, 'store'])->name('logs.store');
    Route::get('/logs', [LogController::class, 'index'])->middleware('auth')->name('logs.index');
    Route::delete('/logs', [LogController::class, 'clean'])->middleware('auth')->name('logs.clean');
});

// === RUTAS DE GESTIÓN DE USUARIOS (SOLO ADMINISTRADORES) ===
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('usuarios', GestionUsuariosController::class);
    Route::post('usuarios/{usuario}/toggle-activo', [GestionUsuariosController::class, 'toggleActivo'])->name('admin.usuarios.toggle-activo');
    Route::post('usuarios/{usuario}/reset-password', [GestionUsuariosController::class, 'resetPassword'])->name('admin.usuarios.reset-password');
    Route::get('usuarios/estadisticas', [GestionUsuariosController::class, 'estadisticas'])->name('admin.usuarios.estadisticas');
});

// === RUTAS DE GESTIÓN DE QUEJAS (TODOS LOS USUARIOS AUTENTICADOS) ===
Route::middleware(['auth'])->group(function () {
    Route::resource('quejas', GestionQuejasController::class);
    Route::post('quejas/{queja}/asignar', [GestionQuejasController::class, 'asignar'])->middleware('role:admin')->name('quejas.asignar');
    Route::post('quejas/{queja}/resolver', [GestionQuejasController::class, 'resolver'])->name('quejas.resolver');
    Route::post('quejas/{queja}/cambiar-estado', [GestionQuejasController::class, 'cambiarEstado'])->name('quejas.cambiar-estado');
    Route::get('quejas/estadisticas', [GestionQuejasController::class, 'estadisticas'])->name('quejas.estadisticas');
    Route::post('quejas/buscar-oc', [GestionQuejasController::class, 'buscarOC'])->name('quejas.buscar-oc');
});

// === RUTAS DE GESTIÓN DE ENTREGAS (ADMINISTRADORES Y USUARIOS NORMALES) ===
Route::middleware(['auth', 'role:admin|usuario'])->group(function () {
    Route::resource('entregas', GestionEntregasController::class);
    Route::get('entregas/estadisticas', [GestionEntregasController::class, 'estadisticas'])->name('entregas.estadisticas');
    Route::post('entregas/filtrar-remesas', [GestionEntregasController::class, 'filtrarRemesas'])->name('entregas.filtrar-remesas');
});

// === RUTAS ESPECÍFICAS PARA OPERARIOS DE CAMPO ===
Route::middleware(['auth', 'role:operario_campo'])->group(function () {
    Route::post('entregas/{entrega}/iniciar', [GestionEntregasController::class, 'iniciar'])->name('entregas.iniciar');
    Route::post('entregas/{entrega}/completar', [GestionEntregasController::class, 'completar'])->name('entregas.completar');
    Route::post('entregas/{entrega}/actualizar-progreso', [GestionEntregasController::class, 'actualizarProgreso'])->name('entregas.actualizar-progreso');
});
