<?php

namespace App\Http\Controllers;

use App\Services\DbfParser;
use App\Services\RemesaService;
use App\Services\RemesaServiceSimple; // Servicio simple para debugging
use App\Models\Remesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Jobs\ImportRemesaJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para el sistema de Remesas
 * 
 * Maneja la carga, visualización y edición de archivos DBF
 * usando servicios especializados para la lógica de negocio
 */
class RemesaController extends Controller
{
    // private RemesaService $remesaService; // Comentado temporalmente
    private DbfParser $dbfParser;

    /**
     * Constructor con inyección de dependencias
     */
    public function __construct(DbfParser $dbfParser)
    {
        // Comentamos temporalmente para usar el servicio simple
        // $this->remesaService = $remesaService;
        $this->dbfParser = $dbfParser;
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario de carga de archivo DBF
     */
    public function uploadForm()
    {
        return view('remesa_upload');
    }

    /**
     * Procesar carga temporal de archivo DBF
     */
    public function upload(Request $request)
    {
        Log::info('=== UPLOAD INICIADO (PRINCIPAL) ===', ['user' => Auth::id()]);
        
        $this->validateUploadRequest($request);

        try {
            $file = $request->file('dbf_file');
            $tempPath = $this->storeTemporaryFile($file);
            $fullPath = Storage::disk('local')->path($tempPath);
            
            Log::info('Archivo subido (principal)', ['temp_path' => $tempPath, 'full_path' => $fullPath]);
            
            // Parsear directamente
            $parsed = $this->dbfParser->parseFile($fullPath);
            $rows = $parsed['rows'] ?? [];
            $nroCarga = $this->extractNroCarga($rows[0] ?? []);
            
            Log::info('Archivo parseado (principal)', [
                'total_rows' => count($rows),
                'nro_carga' => $nroCarga,
                'first_row_sample' => array_slice($rows[0] ?? [], 0, 3)
            ]);
            
            // Verificar duplicados
            $duplicate = Remesa::where('nro_carga', $nroCarga)
                              ->where('usuario_id', Auth::id())
                              ->first();
            
            // Guardar en sesión TODO (rows, path, nro_carga, etc) - SIMPLE
            session([
                'temp_dbf_data' => $rows,
                'temp_dbf_fields' => $parsed['fields'] ?? [],
                'temp_dbf_file' => $tempPath,
                'temp_dbf_nombre' => $file->getClientOriginalName(),
                'temp_nro_carga' => $nroCarga,
                'temp_duplicado' => $duplicate,
            ]);
            
            return redirect()->route('remesa.preview');
            
        } catch (\Exception $e) {
            Log::error('Error en upload (principal)', ['error' => $e->getMessage(), 'user' => Auth::id()]);
            return back()->withErrors(['dbf_file' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar vista previa de datos antes de cargar al sistema
     */
    public function preview(Request $request)
    {
        $sessionData = $this->getSessionData();
        
        if (!$sessionData['datos']) {
            return redirect()->route('remesa.upload.form')
                ->withErrors(['error' => 'No hay datos para mostrar. Carga un archivo primero.']);
        }

        $paginationData = $this->preparePaginationData($sessionData['datos'], $request);

        return view('remesa_preview', array_merge($sessionData, $paginationData));
    }

    /**
     * Cargar datos definitivamente al sistema
     */
    public function cargarAlSistema(Request $request)
    {
        Log::info('=== CARGAR AL SISTEMA INICIADO (PRINCIPAL) ===', ['user' => Auth::id()]);
        
        $rows = session('temp_dbf_data');
        $nroCarga = session('temp_nro_carga');
        $nombreArchivo = session('temp_dbf_nombre');
        $tempPath = session('temp_dbf_file');
        
        if (!$rows || !$nroCarga) {
            Log::error('No hay datos en sesión para cargar');
            return redirect()->route('remesa.upload.form')
                ->withErrors(['error' => 'No hay datos para cargar. Inicia el proceso de nuevo.']);
        }
        
        Log::info('Datos de sesión OK (principal)', [
            'total_rows' => count($rows),
            'nro_carga' => $nroCarga,
            'archivo' => $nombreArchivo
        ]);
        
        try {
            // Verificar duplicados una vez más
            $existing = Remesa::where('nro_carga', $nroCarga)
                             ->where('usuario_id', Auth::id())
                             ->first();
            
            if ($existing) {
                Log::warning('Intento de carga duplicada (principal)', ['nro_carga' => $nroCarga]);
                return redirect()->route('remesa.lista')
                    ->withErrors(['error' => "Ya existe remesa con nro_carga: {$nroCarga}"]);
            }
            
            // INSERCIÓN DIRECTA - SIN JOBS (igual que sistema simple)
            $savedCount = 0;
            $errors = 0;
            $batchSize = 100;
            
            Log::info('Iniciando inserción directa (principal)', ['total_registros' => count($rows)]);
            
            DB::transaction(function () use ($rows, $nroCarga, $nombreArchivo, &$savedCount, &$errors, $batchSize) {
                $batches = array_chunk($rows, $batchSize);
                
                foreach ($batches as $batchIndex => $batch) {
                    $batchData = [];
                    
                    foreach ($batch as $row) {
                        try {
                            $record = [
                                'usuario_id' => Auth::id(),
                                'nombre_archivo' => $nombreArchivo,
                                'nro_carga' => $nroCarga,
                                'fecha_carga' => now(),
                                'cargado_al_sistema' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                                // Campos del DBF - mapeo básico
                                'nis' => $this->truncate($row['NIS'] ?? null, 7),
                                'nromedidor' => $this->truncate($row['NROMEDIDOR'] ?? null, 10),
                                'diametro' => $this->truncate($row['DIAMETRO'] ?? null, 2),
                                'clase' => $this->truncate($row['CLASE'] ?? null, 1),
                                'nomclie' => $this->truncate($row['NOMCLIE'] ?? null, 60),
                                'dir_proc' => $this->truncate($row['DIR_PROC'] ?? null, 171),
                                'dir_cata' => $this->truncate($row['DIR_CATA'] ?? null, 171),
                                'tel_clie' => $this->truncate($row['TEL_CLIE'] ?? null, 16),
                            ];
                            
                            $batchData[] = $record;
                            
                        } catch (\Exception $e) {
                            $errors++;
                            Log::warning('Error procesando registro (principal)', ['error' => $e->getMessage()]);
                        }
                    }
                    
                    if (!empty($batchData)) {
                        DB::table('remesas')->insert($batchData);
                        $savedCount += count($batchData);
                        
                        Log::info("Batch insertado (principal)", [
                            'batch' => $batchIndex + 1,
                            'registros' => count($batchData),
                            'total_guardados' => $savedCount
                        ]);
                    }
                }
            });
            
            // Limpiar archivo temporal y sesión
            if ($tempPath && Storage::exists($tempPath)) {
                Storage::delete($tempPath);
                Log::info('Archivo temporal eliminado (principal)', ['path' => $tempPath]);
            }
            
            // Limpiar archivos JSON temporales si existen
            $jsonPath = session('temp_dbf_json');
            if ($jsonPath && Storage::exists($jsonPath)) {
                Storage::delete($jsonPath);
                Log::info('Archivo JSON temporal eliminado (principal)', ['path' => $jsonPath]);
            }
            
            session()->forget([
                'temp_dbf_data', 'temp_dbf_fields', 'temp_dbf_file', 
                'temp_dbf_nombre', 'temp_nro_carga', 'temp_duplicado', 'temp_dbf_json'
            ]);
            
            Log::info('=== CARGA COMPLETADA (PRINCIPAL) ===', [
                'guardados' => $savedCount,
                'errores' => $errors,
                'nro_carga' => $nroCarga
            ]);
            
            return redirect()->route('remesa.lista')
                ->with('success', "Remesa cargada exitosamente. {$savedCount} registros guardados.");
                
        } catch (\Exception $e) {
            Log::error('=== ERROR EN CARGA (PRINCIPAL) ===', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'nro_carga' => $nroCarga
            ]);
            
            return back()->withErrors(['error' => 'Error al cargar: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar lista de remesas cargadas
     */
    public function lista(Request $request)
    {
        $estado = $request->get('estado', 'todos');
        
        $query = Remesa::where('usuario_id', Auth::id());

        // Aplicar filtro de estado
        switch ($estado) {
            case 'pendientes':
                $query->where('cargado_al_sistema', false);
                break;
            case 'cargadas':
                $query->where('cargado_al_sistema', true);
                break;
            default: // 'todos'
                // No aplicar filtro de estado
                break;
        }

        // Aplicar filtros adicionales
        if ($request->filled('nro_carga')) {
            $query->where('nro_carga', 'like', '%' . $request->nro_carga . '%');
        }

        if ($request->filled('nombre_archivo')) {
            $query->where('nombre_archivo', 'like', '%' . $request->nombre_archivo . '%');
        }

        // Agrupar por número de carga y obtener estadísticas
        $remesas = $query->selectRaw('
                nro_carga, 
                nombre_archivo, 
                fecha_carga,
                cargado_al_sistema,
                MAX(editado) as editado,
                MAX(fecha_edicion) as fecha_edicion,
                COUNT(*) as total_registros,
                MIN(id) as primer_id
            ')
            ->groupBy(['nro_carga', 'nombre_archivo', 'fecha_carga', 'cargado_al_sistema'])
            ->orderBy('fecha_carga', 'desc')
            ->paginate(config('remesas.pagination.list_per_page', 20));

        return view('remesa_lista', [
            'remesas' => $remesas,
            'estado' => $estado,
            'filtros' => $request->only(['nro_carga', 'nombre_archivo', 'estado'])
        ]);
    }

    /**
     * Cancelar proceso y limpiar datos temporales
     */
    public function cancelar()
    {
        $tempFile = session('temp_dbf_file');
        
        if ($tempFile && Storage::exists($tempFile)) {
            Storage::delete($tempFile);
        }

        session()->forget([
            'temp_dbf_data', 'temp_dbf_fields', 'temp_dbf_file', 
            'temp_dbf_nombre', 'temp_nro_carga', 'temp_duplicado'
        ]);

        return redirect()->route('remesa.upload.form')
            ->with('info', 'Proceso cancelado. Los datos temporales han sido eliminados.');
    }

    /**
     * Ver registros de una remesa específica
     */
    public function verRegistros(Request $request, $nroCarga)
    {
        $query = Remesa::where('usuario_id', Auth::id())
                      ->where('nro_carga', $nroCarga);

        // Aplicar filtros adicionales si se proporcionan
        if ($request->filled('nis')) {
            $query->where('nis', 'like', '%' . $request->nis . '%');
        }

        if ($request->filled('nomcli')) {
            $query->where('nomcli', 'like', '%' . $request->nomcli . '%');
        }

        if ($request->filled('nromedidor')) {
            $query->where('nromedidor', 'like', '%' . $request->nromedidor . '%');
        }

        $registros = $query->orderBy('id')
                          ->paginate(config('remesas.pagination.records_per_page', 50))
                          ->appends($request->query());

        // Obtener información básica de la remesa
        $infoRemesa = Remesa::where('usuario_id', Auth::id())
                           ->where('nro_carga', $nroCarga)
                           ->select('nombre_archivo', 'fecha_carga', 'cargado_al_sistema')
                           ->first();

        return view('remesa_ver_registros_new', [
            'registros' => $registros,
            'nroCarga' => $nroCarga,
            'infoRemesa' => $infoRemesa,
            'filtros' => $request->only(['nis', 'nomcli', 'nromedidor'])
        ]);
    }

    /**
     * Mostrar formulario de edición de un registro
     */
    public function editarRegistro($id)
    {
        $registro = Remesa::where('usuario_id', Auth::id())
                         ->findOrFail($id);

        return view('remesa_editar_registro_new', [
            'registro' => $registro
        ]);
    }

    /**
     * Actualizar un registro específico
     */
    public function actualizarRegistro(Request $request, $id)
    {
        $registro = Remesa::where('usuario_id', Auth::id())
                         ->findOrFail($id);

        $this->validateUpdateRequest($request);

        try {
            // Comentado temporalmente mientras usamos el servicio simple
            /*
            $updated = $this->remesaService->updateRecord(
                $id, 
                $request->validated(), 
                Auth::id()
            );

            if ($updated) {
                return redirect()
                    ->route('remesa.ver.registros', $registro->nro_carga)
                    ->with('success', 'Registro actualizado exitosamente.');
            } else {
                return back()->with('info', 'No se realizaron cambios en el registro.');
            }
            */
            
            return back()->with('info', 'Función de actualización temporalmente deshabilitada.');

        } catch (\Exception $e) {
            Log::error('Error actualizando registro de remesa', [
                'error' => $e->getMessage(),
                'registro_id' => $id,
                'usuario_id' => Auth::id()
            ]);

            return back()->withErrors(['error' => 'Error al actualizar el registro: ' . $e->getMessage()]);
        }
    }

    /**
     * Ver historial de cambios de un registro
     */
    public function verHistorial($id)
    {
        $registro = Remesa::where('usuario_id', Auth::id())
                         ->findOrFail($id);

        // Aquí se implementaría la lógica del historial de cambios
        // Por ahora retornamos un arreglo vacío
        $historial = [];

        return view('remesa_historial', [
            'registro' => $registro,
            'historial' => $historial
        ]);
    }

    /**
     * Validar request de upload
     */
    private function validateUploadRequest(Request $request): void
    {
        $maxSize = config('remesas.dbf.max_file_size', 51200);
        
        $request->validate([
            'dbf_file' => "required|file|mimes:dbf|max:{$maxSize}",
        ], [
            'dbf_file.required' => 'Debes seleccionar un archivo DBF.',
            'dbf_file.mimes' => 'El archivo debe ser de tipo DBF.',
            'dbf_file.max' => "El archivo no debe exceder los " . ($maxSize/1024) . "MB.",
        ]);
    }

    /**
     * Validar request de actualización
     */
    private function validateUpdateRequest(Request $request): void
    {
        $request->validate([
            // Campos editables en la nueva interfaz (actualizados según nueva estructura)
            'nomclie' => 'required|string|max:60',
            'tel_clie' => 'nullable|string|max:16',
            'reclamante' => 'nullable|string|max:60',
            'dir_proc' => 'required|string|max:171',
            'ref_dir_ca' => 'nullable|string|max:60',
            'ref_dir_pr' => 'nullable|string|max:60',
            'dir_cata' => 'nullable|string|max:171',
        ], [
            // Mensajes personalizados
            'nomclie.required' => 'El nombre del cliente es obligatorio.',
            'nomclie.max' => 'El nombre del cliente no puede exceder 60 caracteres.',
            'dir_proc.required' => 'La dirección de propiedad es obligatoria.',
            'dir_proc.max' => 'La dirección de propiedad no puede exceder 171 caracteres.',
            'tel_clie.max' => 'El teléfono no puede exceder 16 caracteres.',
            'reclamante.max' => 'El reclamante no puede exceder 60 caracteres.',
            'ref_dir_ca.max' => 'La referencia dirección CA no puede exceder 60 caracteres.',
            'ref_dir_pr.max' => 'La referencia dirección PR no puede exceder 60 caracteres.',
            'dir_cata.max' => 'La dirección catastral no puede exceder 171 caracteres.',
        ]);
    }

    /**
     * Almacenar archivo temporal
     */
    private function storeTemporaryFile($file): string
    {
        $disk = config('remesas.dbf.temp_storage_disk', 'local');
        $path = config('remesas.dbf.temp_storage_path', 'temp_dbf');
        
        return $file->store($path, $disk);
    }

    /**
     * Almacenar datos en sesión
     */
    private function storeSessionData(array $processedData, string $tempPath, string $originalName, $duplicate): void
    {
        session([
            'temp_dbf_data' => $processedData['rows'],
            'temp_dbf_fields' => $processedData['fields'],
            'temp_dbf_file' => $tempPath,
            'temp_dbf_nombre' => $originalName,
            'temp_nro_carga' => $processedData['nro_carga'],
            'temp_duplicado' => $duplicate
        ]);
    }

    /**
     * Obtener datos de sesión (simplificado)
     */
    private function getSessionData(): array
    {
        return [
            'datos' => session('temp_dbf_data'),
            'campos' => session('temp_dbf_fields'),
            'ruta_temporal' => session('temp_dbf_file'),
            'nombre_archivo' => session('temp_dbf_nombre'),
            'nro_carga' => session('temp_nro_carga'),
            'duplicado' => session('temp_duplicado'),
        ];
    }

    /**
     * Preparar datos de paginación
     */
    private function preparePaginationData(array $datos, Request $request): array
    {
        $perPage = config('remesas.pagination.preview_per_page', 50);
        $currentPage = max(1, (int)$request->get('page', 1));
        $totalRecords = count($datos);
        $totalPages = ceil($totalRecords / $perPage);

        $offset = ($currentPage - 1) * $perPage;
        $datosPagina = array_slice($datos, $offset, $perPage);
        $columnas = !empty($datos) ? array_keys($datos[0]) : [];

        return [
            'rows' => $datosPagina,
            'columns' => $columnas,
            'totalRecords' => $totalRecords,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages
        ];
    }

    /**
     * Limpiar datos temporales
     */
    private function cleanupTemporaryData(string $tempPath): void
    {
        if (Storage::exists($tempPath)) {
            Storage::delete($tempPath);
        }

        session()->forget([
            'temp_dbf_data', 'temp_dbf_fields', 'temp_dbf_file', 
            'temp_dbf_nombre', 'temp_nro_carga', 'temp_duplicado'
        ]);
    }

    /**
     * Log de errores de upload
     */
    private function logUploadError(\Exception $e, $file = null): void
    {
        Log::error('Error procesando archivo DBF', [
            'error' => $e->getMessage(),
            'usuario_id' => Auth::id(),
            'archivo' => $file ? $file->getClientOriginalName() : 'unknown'
        ]);
    }

    /**
     * Truncar texto a longitud máxima (helper del sistema simple)
     */
    private function truncate($value, int $maxLength): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        $value = trim($value);
        if (strlen($value) > $maxLength) {
            Log::debug("Truncando campo (principal)", ['original' => $value, 'limit' => $maxLength]);
            return substr($value, 0, $maxLength);
        }
        
        return $value;
    }

    /**
     * Extraer número de carga del primer registro (helper del sistema simple)
     */
    private function extractNroCarga(array $row): string
    {
        $fields = ['NROCARGA', 'ENUSORGA', 'NRO_CARGA'];
        
        foreach ($fields as $field) {
            if (isset($row[$field]) && !empty(trim($row[$field]))) {
                return trim($row[$field]);
            }
        }
        
        return 'AUTO_' . time() . '_' . rand(1000, 9999);
    }
}