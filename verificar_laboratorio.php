<?php

// Script de verificación del sistema de laboratorio
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICACIÓN DEL SISTEMA DE LABORATORIO ===\n\n";

try {
    // Verificar tabla bancos_ensayo
    $bancosCount = DB::table('bancos_ensayo')->count();
    echo "✅ Tabla 'bancos_ensayo' creada - Registros: {$bancosCount}\n";
    
    if ($bancosCount > 0) {
        $bancos = DB::table('bancos_ensayo')->select('nombre', 'capacidad_maxima', 'estado')->get();
        foreach ($bancos as $banco) {
            echo "   - {$banco->nombre} (Capacidad: {$banco->capacidad_maxima}, Estado: {$banco->estado})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error en tabla 'bancos_ensayo': " . $e->getMessage() . "\n";
}

try {
    // Verificar tabla ensayos
    $ensayosCount = DB::table('ensayos')->count();
    echo "✅ Tabla 'ensayos' creada - Registros: {$ensayosCount}\n";
    
} catch (Exception $e) {
    echo "❌ Error en tabla 'ensayos': " . $e->getMessage() . "\n";
}

try {
    // Verificar usuarios técnicos
    $tecnicosCount = DB::table('usuarios')->where('rol', 'tecnico_laboratorio')->count();
    echo "✅ Usuarios técnicos de laboratorio: {$tecnicosCount}\n";
    
    if ($tecnicosCount > 0) {
        $tecnicos = DB::table('usuarios')
            ->where('rol', 'tecnico_laboratorio')
            ->select('nombre', 'apellidos', 'correo')
            ->get();
        
        foreach ($tecnicos as $tecnico) {
            echo "   - {$tecnico->nombre} {$tecnico->apellidos} ({$tecnico->correo})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error verificando usuarios: " . $e->getMessage() . "\n";
}

echo "\n=== RUTAS DEL SISTEMA DE LABORATORIO ===\n";
echo "🔗 Dashboard: http://127.0.0.1:8000/laboratorio\n";
echo "🔗 Nuevo Ensayo: http://127.0.0.1:8000/laboratorio/nuevo-ensayo\n";
echo "🔗 Lista Ensayos: http://127.0.0.1:8000/laboratorio/ensayos\n";
echo "🔗 Gestión Bancos: http://127.0.0.1:8000/laboratorio/bancos\n";

echo "\n=== USUARIOS DE PRUEBA ===\n";
echo "📧 tecnico.lab@gaselag.com - Password: tecnico123\n";
echo "📧 maria.especialista@gaselag.com - Password: especialista123\n";

echo "\n¡Sistema de laboratorio listo para usar! 🧪\n";
