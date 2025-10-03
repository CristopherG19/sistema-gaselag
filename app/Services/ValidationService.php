<?php

namespace App\Services;

use App\Models\Remesa;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para validaciones comunes del sistema
 */
class ValidationService
{
    /**
     * Validar duplicado de remesa por nro_carga, centro_servicio y usuario
     */
    public function validarDuplicadoRemesa(string $nroCarga, string $centroServicio, int $usuarioId, ?int $excludeId = null): array
    {
        $query = Remesa::where('nro_carga', $nroCarga)
                       ->where('centro_servicio', $centroServicio)
                       ->where('usuario_id', $usuarioId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $duplicado = $query->first();
        
        return [
            'es_duplicado' => $duplicado !== null,
            'remesa_existente' => $duplicado,
            'mensaje' => $duplicado 
                ? "Ya existe remesa con nro_carga: {$nroCarga} para el centro: {$centroServicio}"
                : null
        ];
    }

    /**
     * Validar estructura mínima de datos DBF
     */
    public function validarEstructuraDbf(array $datos): array
    {
        $errores = [];
        $advertencias = [];

        if (empty($datos)) {
            $errores[] = 'El archivo no contiene datos';
            return ['errores' => $errores, 'advertencias' => $advertencias];
        }

        if (!is_array($datos[0])) {
            $errores[] = 'Estructura de datos inválida';
            return ['errores' => $errores, 'advertencias' => $advertencias];
        }

        $primerRegistro = $datos[0];
        
        // Campos requeridos (convertir a minúsculas para comparación)
        $camposRequeridos = ['nis', 'nromedidor', 'nomclie'];
        $camposEncontrados = array_map('strtolower', array_keys($primerRegistro));

        foreach ($camposRequeridos as $campo) {
            if (!in_array(strtolower($campo), $camposEncontrados)) {
                $errores[] = "Campo requerido no encontrado: {$campo}";
            }
        }

        // Verificar campos opcionales pero recomendados
        $camposRecomendados = ['direccion', 'distrito', 'telefono'];
        foreach ($camposRecomendados as $campo) {
            if (!in_array(strtolower($campo), $camposEncontrados)) {
                $advertencias[] = "Campo recomendado no encontrado: {$campo}";
            }
        }

        // Verificar que no haya registros vacíos
        $registrosVacios = 0;
        foreach ($datos as $index => $registro) {
            $valoresNoVacios = array_filter($registro, function($valor) {
                return !empty(trim($valor));
            });
            
            if (empty($valoresNoVacios)) {
                $registrosVacios++;
            }
        }

        if ($registrosVacios > 0) {
            $advertencias[] = "Se encontraron {$registrosVacios} registros vacíos que serán omitidos";
        }

        return [
            'errores' => $errores,
            'advertencias' => $advertencias,
            'total_registros' => count($datos),
            'registros_vacios' => $registrosVacios,
            'campos_disponibles' => count($primerRegistro)
        ];
    }

    /**
     * Validar formato de archivo
     */
    public function validarFormatoArchivo(string $nombreArchivo, int $tamaño): array
    {
        $errores = [];
        $advertencias = [];

        // Validar extensión
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        if ($extension !== 'dbf') {
            $errores[] = 'El archivo debe tener extensión .dbf';
        }

        // Validar tamaño (máximo 50MB)
        $tamañoMB = $tamaño / 1024 / 1024;
        if ($tamañoMB > 50) {
            $errores[] = 'El archivo es demasiado grande (máximo 50MB)';
        } elseif ($tamañoMB > 20) {
            $advertencias[] = 'Archivo grande, el procesamiento puede tomar más tiempo';
        }

        // Validar nombre de archivo
        if (strlen($nombreArchivo) > 255) {
            $errores[] = 'El nombre del archivo es demasiado largo';
        }

        if (preg_match('/[<>:"|?*]/', $nombreArchivo)) {
            $errores[] = 'El nombre del archivo contiene caracteres no válidos';
        }

        return [
            'errores' => $errores,
            'advertencias' => $advertencias,
            'tamaño_mb' => round($tamañoMB, 2),
            'extension' => $extension
        ];
    }

    /**
     * Validar permisos de usuario para acción específica
     */
    public function validarPermisosUsuario($usuario, string $accion, $recurso = null): array
    {
        $permisos = [];

        switch ($accion) {
            case 'upload_remesa':
                $permisos['permitido'] = $usuario->isAdmin() || $usuario->hasRole('editor');
                $permisos['mensaje'] = $permisos['permitido'] 
                    ? null 
                    : 'No tienes permisos para subir remesas';
                break;

            case 'editar_remesa':
                if ($recurso && method_exists($recurso, 'usuario_id')) {
                    $permisos['permitido'] = $usuario->isAdmin() || $recurso->usuario_id === $usuario->id;
                    $permisos['mensaje'] = $permisos['permitido'] 
                        ? null 
                        : 'Solo puedes editar tus propias remesas';
                } else {
                    $permisos['permitido'] = $usuario->isAdmin();
                    $permisos['mensaje'] = $permisos['permitido'] 
                        ? null 
                        : 'No tienes permisos para editar remesas';
                }
                break;

            case 'ver_todas_remesas':
                $permisos['permitido'] = $usuario->isAdmin();
                $permisos['mensaje'] = $permisos['permitido'] 
                    ? null 
                    : 'Solo puedes ver tus propias remesas';
                break;

            default:
                $permisos['permitido'] = false;
                $permisos['mensaje'] = 'Acción no reconocida';
        }

        return $permisos;
    }
}