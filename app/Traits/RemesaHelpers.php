<?php

namespace App\Traits;

/**
 * Trait para funciones comunes relacionadas con Remesas
 */
trait RemesaHelpers
{
    /**
     * Extraer número de carga del primer registro DBF
     */
    protected function extractNroCarga(array $row): string
    {
        // Intentar diferentes campos donde puede estar el número de carga
        $possibleFields = ['NRO_CARGA', 'nro_carga', 'NROCARGA', 'nrocarga'];
        
        foreach ($possibleFields as $field) {
            if (isset($row[$field]) && !empty(trim($row[$field]))) {
                return trim($row[$field]);
            }
        }
        
        // Si no encuentra en campos típicos, usar el primer campo no vacío
        foreach ($row as $key => $value) {
            if (!empty(trim($value)) && is_string($value)) {
                return trim($value);
            }
        }
        
        return 'SIN_NRO_CARGA';
    }

    /**
     * Obtener centros de servicio desde configuración
     */
    protected function getCentrosServicio(): array
    {
        return config('centros_servicio.centros', []);
    }

    /**
     * Obtener centro de servicio por defecto
     */
    protected function getCentroServicioDefault(): ?string
    {
        return config('centros_servicio.default');
    }

    /**
     * Formatear nombre de archivo para almacenamiento
     */
    protected function formatearNombreArchivo(string $nombreOriginal): string
    {
        // Limpiar caracteres especiales y espacios
        $nombre = pathinfo($nombreOriginal, PATHINFO_FILENAME);
        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
        
        $nombre = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombre);
        $nombre = preg_replace('/_+/', '_', $nombre);
        $nombre = trim($nombre, '_');
        
        return $nombre . '.' . $extension;
    }

    /**
     * Generar estadísticas básicas de un array de datos
     */
    protected function generarEstadisticasBasicas(array $datos): array
    {
        if (empty($datos)) {
            return [
                'total_registros' => 0,
                'campos_disponibles' => 0,
                'memoria_estimada' => '0 MB'
            ];
        }

        $totalRegistros = count($datos);
        $camposDisponibles = count(array_keys($datos[0] ?? []));
        $memoriaEstimada = round(memory_get_usage() / 1024 / 1024, 2);

        return [
            'total_registros' => $totalRegistros,
            'campos_disponibles' => $camposDisponibles,
            'memoria_estimada' => $memoriaEstimada . ' MB'
        ];
    }

    /**
     * Limpiar datos temporales de sesión
     */
    protected function limpiarDatosTemporales(): void
    {
        session()->forget([
            'temp_dbf_data', 
            'temp_dbf_fields', 
            'temp_dbf_file', 
            'temp_dbf_nombre', 
            'temp_nro_carga', 
            'temp_duplicado',
            'centros_disponibles'
        ]);
    }

    /**
     * Validar estructura básica de datos DBF
     */
    protected function validarEstructuraDbf(array $datos): array
    {
        $errores = [];

        if (empty($datos)) {
            $errores[] = 'El archivo no contiene datos';
            return $errores;
        }

        if (!is_array($datos[0])) {
            $errores[] = 'Estructura de datos inválida';
            return $errores;
        }

        // Verificar campos mínimos requeridos
        $camposRequeridos = ['nis', 'nromedidor', 'nomclie'];
        $primerRegistro = $datos[0];

        foreach ($camposRequeridos as $campo) {
            $encontrado = false;
            foreach (array_keys($primerRegistro) as $key) {
                if (strtolower($key) === strtolower($campo)) {
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado) {
                $errores[] = "Campo requerido no encontrado: {$campo}";
            }
        }

        return $errores;
    }
}