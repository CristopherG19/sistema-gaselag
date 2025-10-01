<?php

namespace App\Http\Controllers;

use App\Services\DbfParser;
use App\Services\RemesaService;
use App\Models\Remesa;
use App\Models\RemesaPendiente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * CONTROLADOR PRINCIPAL PARA REMESAS
 * Procesamiento directo: Upload → Preview → Inserción inmediata
 * Sin jobs, sin JSON temporal, sin servicios complejos
 */
class RemesaController extends Controller
{
    private DbfParser $dbfParser;
    private RemesaService $remesaService;

    public function __construct(DbfParser $dbfParser, RemesaService $remesaService)
    {
        $this->dbfParser = $dbfParser;
        $this->remesaService = $remesaService;
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario de upload
     */
    public function uploadForm()
    {
        return view('remesa_upload');
    }

    /**
     * Procesar upload y mostrar preview
     */
    public function upload(Request $request)
    {
        Log::info('=== UPLOAD INICIADO ===', ['user' => Auth::id()]);
        
        $request->validate([
            'dbf_file' => 'required|file|mimes:dbf|max:51200',
        ]);

        try {
            $file = $request->file('dbf_file');
            $tempPath = $file->store('temp_dbf', 'local');
            $fullPath = Storage::disk('local')->path($tempPath);
            
            Log::info('Archivo subido', ['temp_path' => $tempPath, 'full_path' => $fullPath]);
            
            // Parsear directamente
            $parsed = $this->dbfParser->parseFile($fullPath);
            $rows = $parsed['rows'] ?? [];
            $nroCarga = $this->extractNroCarga($rows[0] ?? []);
            
            Log::info('Archivo parseado', [
                'total_rows' => count($rows),
                'nro_carga' => $nroCarga,
                'first_row_sample' => array_slice($rows[0] ?? [], 0, 3)
            ]);
            
            // Guardar en sesión (sin verificar duplicados hasta seleccionar centro)
            session([
                'temp_dbf_data' => $rows,
                'temp_dbf_fields' => $parsed['fields'] ?? [],
                'temp_dbf_file' => $tempPath,
                'temp_dbf_nombre' => $file->getClientOriginalName(),
                'temp_nro_carga' => $nroCarga,
                'centros_disponibles' => $this->getCentrosServicio(),
            ]);
            
            return redirect()->route('remesa.preview');
            
        } catch (\Exception $e) {
            Log::error('Error en upload', ['error' => $e->getMessage(), 'user' => Auth::id()]);
            return back()->withErrors(['dbf_file' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar vista previa de datos antes de cargar al sistema
     */
    public function preview(Request $request)
    {
        $rows = session('temp_dbf_data');
        $nroCarga = session('temp_nro_carga');
        $nombreArchivo = session('temp_dbf_nombre');
        $centrosDisponibles = session('centros_disponibles', $this->getCentrosServicio());
        
        if (!$rows || !$nroCarga) {
            return redirect()->route('remesa.upload.form')
                ->withErrors(['error' => 'No hay datos para mostrar. Carga un archivo primero.']);
        }
        
        // Preparar paginación
        $perPage = 50;
        $currentPage = max(1, (int)$request->get('page', 1));
        $totalRecords = count($rows);
        $totalPages = ceil($totalRecords / $perPage);
        
        $offset = ($currentPage - 1) * $perPage;
        $rowsToShow = array_slice($rows, $offset, $perPage);
        
        return view('remesa_preview', [
            'rows' => $rowsToShow,
            'totalRecords' => $totalRecords,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'nro_carga' => $nroCarga,
            'nombre_archivo' => $nombreArchivo,
            'centros_disponibles' => $centrosDisponibles,
            'columns' => !empty($rows) ? array_keys($rows[0]) : [],
        ]);
    }

    /**
     * Verificar si existe duplicado para nro_carga y centro de servicio específicos
     */
    public function verificarDuplicado(Request $request)
    {
        $request->validate([
            'nro_carga' => 'required|string',
            'centro_servicio' => 'required|string',
        ]);

        $duplicado = Remesa::where('nro_carga', $request->nro_carga)
                          ->where('centro_servicio', $request->centro_servicio)
                          ->where('usuario_id', Auth::id())
                          ->exists();

        return response()->json([
            'duplicado' => $duplicado
        ]);
    }

    /**
     * SUBIR ARCHIVO COMO PENDIENTE - PRIMER PASO
     */
    public function subirComoPendiente(Request $request)
    {
        Log::info('=== SUBIR COMO PENDIENTE INICIADO ===', ['user' => Auth::id()]);
        
        $request->validate([
            'archivo_dbf' => 'required|file|mimes:dbf|max:51200', // 50MB max
        ]);

        try {
            $file = $request->file('archivo_dbf');
            $tempPath = $file->store('temp_dbf');
            $fullPath = Storage::path($tempPath);
            
            Log::info('Archivo subido', [
                'temp_path' => $tempPath,
                'full_path' => $fullPath
            ]);

            // Parsear archivo DBF
            $parser = new DbfParser();
            $parsed = $parser->parseFile($fullPath);
            $rows = $parsed['rows'] ?? [];
            
            if (empty($rows)) {
                Storage::delete($tempPath);
                return back()->withErrors(['error' => 'No se pudieron extraer datos del archivo DBF']);
            }

            // Extraer número de carga
            $nroCarga = $this->extractNroCarga($rows[0] ?? []);
            
            // Crear registro pendiente en la nueva tabla
            $datosDbf = [];
            if (!empty($rows)) {
                $datosDbf = [
                    'nis' => $rows[0]['NIS'] ?? '0000000',
                    'nromedidor' => $rows[0]['NROMEDIDOR'] ?? 'TEMP',
                    'diametro' => $rows[0]['DIAMETRO'] ?? '15',
                    'clase' => $rows[0]['CLASE'] ?? 'A',
                    'marcamed' => $rows[0]['MARCAMED'] ?? 'TEMP',
                    'reclamante' => $rows[0]['RECLAMANTE'] ?? 'TEMP',
                    'nomcli' => $rows[0]['NOMCLI'] ?? 'TEMP',
                    'dir_proc' => $rows[0]['DIR_PROC'] ?? 'TEMP',
                    'ref_cata' => $rows[0]['REF_CATA'] ?? 'TEMP',
                    'rsol' => $rows[0]['RSOL'] ?? '001',
                    'tin' => $rows[0]['TIN'] ?? 'TEMP',
                    'aol' => $rows[0]['AOL'] ?? 'TEMP',
                    'correcarta' => $rows[0]['CORRECARTA'] ?? '123456',
                    'enusorga' => $rows[0]['ENUSORGA'] ?? 12345,
                    'especial' => $rows[0]['ESPECIAL'] ?? 'N',
                    'reconsi' => $rows[0]['RECONSI'] ?? '01',
                    'hrrabas' => $rows[0]['HRRABAS'] ?? 'TEMP',
                    'regebas' => $rows[0]['REGEBAS'] ?? 'TEMP',
                    'empresa' => 1,
                    'masivo' => $rows[0]['MASIVO'] ?? 'N',
                    'ruta' => $rows[0]['RUTA'] ?? 'TEMP',
                    'gcv' => $rows[0]['GCV'] ?? 'TEMP',
                    'dbo_mode' => $rows[0]['DBO_MODE'] ?? 'TEMP',
                    'dbo_afab' => $rows[0]['DBO_AFAB'] ?? 1,
                    'dbo_max' => $rows[0]['DBO_MAX'] ?? 100.00,
                    'dbo_min' => $rows[0]['DBO_MIN'] ?? 0.00,
                    'dbo_perm' => $rows[0]['DBO_PERM'] ?? 50.00,
                    'dbo_tran' => $rows[0]['DBO_TRAN'] ?? 25.00,
                    'ref_dir_ca' => $rows[0]['REF_DIR_CA'] ?? 'TEMP',
                    'ref_dir_pr' => $rows[0]['REF_DIR_PR'] ?? 'TEMP',
                    'cup' => $rows[0]['CUP'] ?? 'TEMP',
                    'dbo_dseg' => $rows[0]['DBO_DSEG'] ?? 'TEMP',
                    'tarifa' => $rows[0]['TARIFA'] ?? 'TEMP',
                    'reclamo' => $rows[0]['RECLAMO'] ?? 'TEMP123'
                ];
            }

            $remesaPendiente = RemesaPendiente::create([
                'usuario_id' => Auth::id(),
                'nombre_archivo' => $file->getClientOriginalName(),
                'nro_carga' => $nroCarga,
                'fecha_carga' => now(),
                'datos_dbf' => $datosDbf,
            ]);

            // Guardar datos en sesión para el siguiente paso
            session([
                'temp_dbf_data' => $rows,
                'temp_dbf_file' => $tempPath,
                'temp_dbf_nombre' => $file->getClientOriginalName(),
                'temp_nro_carga' => $nroCarga,
                'temp_remesa_id' => $remesaPendiente->id
            ]);

            Log::info('Archivo parseado', [
                'total_rows' => count($rows),
                'nro_carga' => $nroCarga,
                'first_row_sample' => array_slice($rows[0] ?? [], 0, 3)
            ]);

            return redirect()->route('remesa.procesar.form')
                ->with('success', "Archivo subido exitosamente. {$nroCarga} registros listos para procesar.");

        } catch (\Exception $e) {
            Log::error('Error subiendo archivo', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return back()->withErrors(['error' => 'Error al subir archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * MOSTRAR FORMULARIO DE PROCESAMIENTO - SEGUNDO PASO
     */
    public function procesarForm(Request $request)
    {
        $rows = session('temp_dbf_data');
        $nroCarga = session('temp_nro_carga');
        $nombreArchivo = session('temp_dbf_nombre');
        
        // Si no hay datos en sesión, buscar la remesa pendiente más reciente del usuario
        if (!$rows || !$nroCarga) {
            $remesaPendiente = RemesaPendiente::where('usuario_id', Auth::id())
                                   ->orderBy('fecha_carga', 'desc')
                                   ->first();
            
            if (!$remesaPendiente) {
                return redirect()->route('remesa.lista')
                    ->withErrors(['error' => 'No hay remesas pendientes para procesar.']);
            }
            
            // Cargar datos del archivo temporal si existe
            $tempPath = session('temp_dbf_file');
            if ($tempPath && Storage::exists($tempPath)) {
                $parser = new DbfParser();
                $parsed = $parser->parseFile(Storage::path($tempPath));
                $rows = $parsed['rows'] ?? [];
            } else {
                // Si no hay archivo temporal, crear datos de ejemplo para el procesamiento
                $rows = [
                    [
                        'NIS' => $remesaPendiente->nis,
                        'NROMEDIDOR' => $remesaPendiente->nromedidor,
                        'NOMCLI' => $remesaPendiente->nomcli,
                        'DIR_PROC' => $remesaPendiente->dir_pro,
                    ]
                ];
            }
            
            $nroCarga = $remesaPendiente->nro_carga;
            $nombreArchivo = $remesaPendiente->nombre_archivo;
            
            // Guardar en sesión para el procesamiento
            session([
                'temp_dbf_data' => $rows,
                'temp_nro_carga' => $nroCarga,
                'temp_dbf_nombre' => $nombreArchivo,
                'temp_remesa_id' => $remesaPendiente->id
            ]);
        }

        // Obtener centros de servicio disponibles
        $centrosServicio = array_keys($this->getCentrosServicio());

        return view('remesa_procesar', [
            'nro_carga' => $nroCarga,
            'nombre_archivo' => $nombreArchivo,
            'total_registros' => count($rows),
            'centros_servicio' => $centrosServicio,
            'preview_data' => array_slice($rows, 0, 5) // Mostrar solo 5 registros de preview
        ]);
    }

    /**
     * PROCESAR REMESA PENDIENTE - SEGUNDO PASO
     */
    public function procesarPendiente(Request $request)
    {
        Log::info('=== PROCESAR PENDIENTE INICIADO ===', ['user' => Auth::id()]);
        
        $request->validate([
            'centro_servicio' => 'required|string',
        ]);
        
        $rows = session('temp_dbf_data');
        $nroCarga = session('temp_nro_carga');
        $centroServicio = $request->centro_servicio;
        $nombreArchivo = session('temp_dbf_nombre');
        $tempPath = session('temp_dbf_file');
        
        if (!$rows || !$nroCarga || !$centroServicio) {
            Log::error('No hay datos en sesión para procesar');
            return redirect()->route('remesa.upload.form')
                ->withErrors(['error' => 'No hay datos para procesar. Inicia el proceso de nuevo.']);
        }
        
        Log::info('Datos de sesión OK', [
            'total_rows' => count($rows),
            'nro_carga' => $nroCarga,
            'centro_servicio' => $centroServicio,
            'archivo' => $nombreArchivo
        ]);
        
        try {
            // Obtener el ID de la remesa pendiente desde la sesión
            $remesaId = session('temp_remesa_id');
            
            if (!$remesaId) {
                Log::error('No hay ID de remesa pendiente en sesión');
                return redirect()->route('remesa.upload.form')
                    ->withErrors(['error' => 'No hay datos para procesar. Inicia el proceso de nuevo.']);
            }

            // Verificar que la remesa pendiente existe
            $remesaPendiente = RemesaPendiente::where('id', $remesaId)
                                   ->where('usuario_id', Auth::id())
                                   ->first();
            
            if (!$remesaPendiente) {
                Log::error('Remesa pendiente no encontrada', ['remesa_id' => $remesaId]);
                return redirect()->route('remesa.upload.form')
                    ->withErrors(['error' => 'No se encontró la remesa pendiente. Inicia el proceso de nuevo.']);
            }

            // Verificar duplicados para el centro de servicio
            $existing = Remesa::where('nro_carga', $nroCarga)
                             ->where('centro_servicio', $centroServicio)
                             ->where('usuario_id', Auth::id())
                             ->where('id', '!=', $remesaId)
                             ->first();
            
            if ($existing) {
                Log::warning('Intento de procesamiento duplicado', ['nro_carga' => $nroCarga]);
                return redirect()->route('remesa.lista')
                    ->withErrors(['error' => "Ya existe remesa con nro_carga: {$nroCarga} para el centro: {$centroServicio}"]);
            }
            
            // USAR REMESA SERVICE CON GENERACIÓN AUTOMÁTICA DE OC
            $result = $this->remesaService->bulkInsert(
                $rows, 
                Auth::id(), 
                $nombreArchivo, 
                $nroCarga, 
                $centroServicio,
                $remesaId  // Excluir la remesa pendiente actual
            );
            
            // Eliminar el registro pendiente de la base de datos
            $remesaPendiente->delete();
            Log::info('Remesa pendiente eliminada de la base de datos', ['remesa_id' => $remesaId]);
            
            // Limpiar archivo temporal y sesión
            if ($tempPath && Storage::exists($tempPath)) {
                Storage::delete($tempPath);
                Log::info('Archivo temporal eliminado', ['path' => $tempPath]);
            }
            
            session()->forget(['temp_dbf_data', 'temp_dbf_fields', 'temp_dbf_file', 'temp_dbf_nombre', 'temp_nro_carga', 'temp_duplicado', 'temp_remesa_id']);
            
            Log::info('=== PROCESAMIENTO COMPLETADO ===', [
                'guardados' => $result['saved_records'],
                'errores' => $result['errors'],
                'nro_carga' => $nroCarga,
                'centro_servicio' => $centroServicio
            ]);
            
            return redirect()->route('remesa.lista')
                ->with('success', $result['message']);
                
        } catch (\Exception $e) {
            Log::error('=== ERROR EN PROCESAMIENTO ===', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'nro_carga' => $nroCarga,
                'centro_servicio' => $centroServicio
            ]);
            
            return back()->withErrors(['error' => 'Error al procesar: ' . $e->getMessage()]);
        }
    }

    /**
     * CARGAR AL SISTEMA - USANDO REMESA SERVICE CON OC (FLUJO ORIGINAL)
     */
    public function cargarAlSistema(Request $request)
    {
        Log::info('=== CARGAR AL SISTEMA CON OC INICIADO ===', ['user' => Auth::id()]);
        
        // Validar que se envió el centro de servicio
        $request->validate([
            'centro_servicio' => 'required|string',
        ]);
        
        $rows = session('temp_dbf_data');
        $nroCarga = session('temp_nro_carga');
        $centroServicio = $request->centro_servicio;
        $nombreArchivo = session('temp_dbf_nombre');
        $tempPath = session('temp_dbf_file');
        
        if (!$rows || !$nroCarga || !$centroServicio) {
            Log::error('No hay datos en sesión para cargar');
            return redirect()->route('remesa.upload.form')
                ->withErrors(['error' => 'No hay datos para cargar. Inicia el proceso de nuevo.']);
        }
        
        Log::info('Datos de sesión OK', [
            'total_rows' => count($rows),
            'nro_carga' => $nroCarga,
            'centro_servicio' => $centroServicio,
            'archivo' => $nombreArchivo
        ]);
        
        try {
            // Verificar duplicados una vez más (incluyendo centro de servicio)
            $existing = Remesa::where('nro_carga', $nroCarga)
                             ->where('centro_servicio', $centroServicio)
                             ->where('usuario_id', Auth::id())
                             ->first();
            
            if ($existing) {
                Log::warning('Intento de carga duplicada', ['nro_carga' => $nroCarga]);
                return redirect()->route('remesa.lista')
                    ->withErrors(['error' => "Ya existe remesa con nro_carga: {$nroCarga} para el centro: {$centroServicio}"]);
            }
            
            // USAR REMESA SERVICE CON GENERACIÓN AUTOMÁTICA DE OC
            $result = $this->remesaService->bulkInsert(
                $rows, 
                Auth::id(), 
                $nombreArchivo, 
                $nroCarga, 
                $centroServicio,
                $remesaId  // Excluir la remesa pendiente actual
            );
            
            // Eliminar el registro pendiente de la base de datos
            $remesaPendiente->delete();
            Log::info('Remesa pendiente eliminada de la base de datos', ['remesa_id' => $remesaId]);
            
            // Limpiar archivo temporal y sesión
            if ($tempPath && Storage::exists($tempPath)) {
                Storage::delete($tempPath);
                Log::info('Archivo temporal eliminado', ['path' => $tempPath]);
            }
            
            session()->forget(['temp_dbf_data', 'temp_dbf_fields', 'temp_dbf_file', 'temp_dbf_nombre', 'temp_nro_carga', 'temp_duplicado', 'temp_remesa_id']);
            
            Log::info('=== CARGA COMPLETADA CON OC ===', [
                'guardados' => $result['saved_records'],
                'errores' => $result['errors'],
                'nro_carga' => $nroCarga,
                'centro_servicio' => $centroServicio
            ]);
            
            return redirect()->route('remesa.lista')
                ->with('success', $result['message']);
                
        } catch (\Exception $e) {
            Log::error('=== ERROR EN CARGA CON OC ===', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'nro_carga' => $nroCarga,
                'centro_servicio' => $centroServicio
            ]);
            
            return back()->withErrors(['error' => 'Error al cargar: ' . $e->getMessage()]);
        }
    }

    /**
     * Helpers
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

    /**
     * Mostrar lista de remesas cargadas
     */
    public function lista(Request $request)
    {
        $estado = $request->get('estado', 'todos');
        $usuario = Auth::user();
        
        // Administradores ven todas las remesas, usuarios normales solo las suyas
        if ($usuario->isAdmin()) {
            $query = Remesa::query();
        } else {
        $query = Remesa::where('usuario_id', Auth::id());
        }

        // Manejar filtro de estado
        $remesasPendientes = collect();
        
        if ($estado === 'pendientes' || $estado === 'todos') {
            // Obtener remesas pendientes de la nueva tabla
            $pendientesQuery = RemesaPendiente::query();
            if (!$usuario->isAdmin()) {
                $pendientesQuery->where('usuario_id', Auth::id());
            }
            
            $remesasPendientes = $pendientesQuery
                ->orderBy('fecha_carga', 'desc')
                ->get()
                ->map(function ($remesa) {
                    return (object) [
                        'nro_carga' => $remesa->nro_carga,
                        'nombre_archivo' => $remesa->nombre_archivo,
                        'cargado_al_sistema' => false,
                        'total_registros' => 1, // Por ahora, cada pendiente es 1 registro
                        'fecha_carga' => $remesa->fecha_carga,
                        'primer_id' => $remesa->id,
                        'editado' => false,
                        'fecha_edicion' => null,
                        'usuario_id' => $remesa->usuario_id,
                        'usuario_nombre' => $remesa->usuario->correo ?? 'N/A',
                    ];
                });
        }

        if ($estado === 'cargadas' || $estado === 'todos') {
            // Aplicar filtro solo para remesas cargadas
            if ($estado === 'cargadas') {
                $query->where('cargado_al_sistema', true);
            }
        } else {
            // Si es solo pendientes, no necesitamos consultar la tabla de remesas
            $query = null;
        }

        // Obtener remesas cargadas si es necesario
        $remesasCargadas = collect();
        if ($query) {
        // Aplicar filtros adicionales
        if ($request->filled('nro_carga')) {
            $query->where('nro_carga', 'like', '%' . $request->nro_carga . '%');
        }

        if ($request->filled('nombre_archivo')) {
            $query->where('nombre_archivo', 'like', '%' . $request->nombre_archivo . '%');
        }

        // Agrupar por número de carga y obtener estadísticas
            $remesasCargadas = $query->selectRaw('
                nro_carga, 
                MIN(nombre_archivo) as nombre_archivo, 
                MIN(fecha_carga) as fecha_carga,
                MAX(cargado_al_sistema) as cargado_al_sistema,
                MAX(editado) as editado,
                MAX(fecha_edicion) as fecha_edicion,
                COUNT(*) as total_registros,
                    MIN(id) as primer_id,
                    MIN(usuario_id) as usuario_id
            ')
            ->groupBy('nro_carga')
            ->orderBy('fecha_carga', 'desc')
                ->get()
                ->map(function ($remesa) {
                    $usuario = \App\Models\Usuario::find($remesa->usuario_id);
                    $remesa->usuario_nombre = $usuario ? $usuario->correo : 'N/A';
                    return $remesa;
                });
        }

        // Combinar ambas colecciones
        $todasLasRemesas = $remesasPendientes->concat($remesasCargadas)
            ->sortByDesc('fecha_carga')
            ->values();

        // Aplicar filtros a la colección combinada
        if ($request->filled('nro_carga')) {
            $todasLasRemesas = $todasLasRemesas->filter(function ($remesa) use ($request) {
                return str_contains($remesa->nro_carga, $request->nro_carga);
            });
        }

        if ($request->filled('nombre_archivo')) {
            $todasLasRemesas = $todasLasRemesas->filter(function ($remesa) use ($request) {
                return str_contains($remesa->nombre_archivo, $request->nombre_archivo);
            });
        }

        // Crear paginación manual
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedItems = $todasLasRemesas->slice($offset, $perPage);
        
        // Crear objeto de paginación personalizado
        $remesas = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $todasLasRemesas->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );

        return view('remesa_lista', [
            'remesas' => $remesas,
            'estado' => $estado,
            'filtros' => $request->only(['nro_carga', 'nombre_archivo', 'estado'])
        ]);
    }

    /**
     * Vista general de todas las remesas con filtros avanzados
     */
    public function vistaGeneral(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $usuario = Auth::user();
        
        // Query base - administradores ven todos, usuarios normales solo los suyos
        $query = Remesa::query();
        if (!$usuario->isAdmin()) {
            $query->where('usuario_id', Auth::id());
        }
        
        // Aplicar filtros
        if ($request->filled('centro_servicio')) {
            $query->where('centro_servicio', $request->centro_servicio);
        }
        
        if ($request->filled('nro_carga')) {
            $query->where('nro_carga', 'like', '%' . $request->nro_carga . '%');
        }
        
        if ($request->filled('oc')) {
            $query->where('oc', 'like', '%' . $request->oc . '%');
        }
        
        if ($request->filled('nis')) {
            $query->where('nis', 'like', '%' . $request->nis . '%');
        }
        
        if ($request->filled('nromedidor')) {
            $query->where('nromedidor', 'like', '%' . $request->nromedidor . '%');
        }
        
        if ($request->filled('nomclie')) {
            $query->where('nomclie', 'like', '%' . $request->nomclie . '%');
        }
        
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_carga', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_carga', '<=', $request->fecha_hasta);
        }
        
        // Obtener registros con paginación
        $registros = $query->orderBy('oc', 'desc')
                          ->paginate($perPage)
                          ->withQueryString();
        
        // Obtener centros únicos para filtro
        $centrosQuery = Remesa::query();
        if (!$usuario->isAdmin()) {
            $centrosQuery->where('usuario_id', Auth::id());
        }
        $centrosDisponibles = $centrosQuery->distinct()
                                   ->pluck('centro_servicio')
                                   ->filter()
                                   ->sort();
        
        // Obtener números de carga únicos para filtro
        $nrosCargaQuery = Remesa::query();
        if (!$usuario->isAdmin()) {
            $nrosCargaQuery->where('usuario_id', Auth::id());
        }
        $nrosCargaDisponibles = $nrosCargaQuery->distinct()
                                     ->pluck('nro_carga')
                                     ->filter()
                                     ->sort();
        
        // Estadísticas generales
        $estadisticasQuery = Remesa::query();
        if (!$usuario->isAdmin()) {
            $estadisticasQuery->where('usuario_id', Auth::id());
        }
        
        $estadisticas = [
            'total_registros' => $estadisticasQuery->count(),
            'total_centros' => $centrosDisponibles->count(),
            'total_remesas' => $nrosCargaDisponibles->count(),
            'registros_editados' => $estadisticasQuery->where('editado', true)->count(),
        ];
        
        return view('general_remesas', [
            'registros' => $registros,
            'centrosDisponibles' => $centrosDisponibles,
            'nrosCargaDisponibles' => $nrosCargaDisponibles,
            'estadisticas' => $estadisticas,
            'filtros' => $request->only([
                'centro_servicio', 'nro_carga', 'oc', 'nis', 
                'nromedidor', 'nomclie', 'fecha_desde', 'fecha_hasta', 'per_page'
            ]),
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
    
    private function truncate($value, int $maxLength): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        $value = trim($value);
        if (strlen($value) > $maxLength) {
            Log::debug("Truncando campo", ['original' => $value, 'limit' => $maxLength]);
            return substr($value, 0, $maxLength);
        }
        
        return $value;
    }

    /**
     * Obtener lista de centros de servicio SEDAPAL disponibles
     */
    private function getCentrosServicio(): array
    {
        return [
            'SEDAPAL BREÑA' => 'SEDAPAL BREÑA',
            'SEDAPAL VILLA EL SALVADOR' => 'SEDAPAL VILLA EL SALVADOR', 
            'SEDAPAL ATE' => 'SEDAPAL ATE',
            'SEDAPAL COMAS' => 'SEDAPAL COMAS',
            'SEDAPAL SAN JUAN DE LURIGANCHO' => 'SEDAPAL SAN JUAN DE LURIGANCHO',
            'SEDAPAL CLIENTES ESPECIALES' => 'SEDAPAL CLIENTES ESPECIALES',
            'SEDAPAL CALLAO' => 'SEDAPAL CALLAO',
            'SEDAPAL SURQUILLO' => 'SEDAPAL SURQUILLO',
        ];
    }

    /**
     * Ver registros de una remesa específica
     */
    public function verRegistros(Request $request, string $nroCarga)
    {
        $perPage = $request->get('per_page', 50);
        $page = $request->get('page', 1);
        $usuario = Auth::user();
        
        // Buscar registros de la remesa específica
        $query = Remesa::where('nro_carga', $nroCarga);
        
        // Administradores ven todos los registros, usuarios normales solo los suyos
        if (!$usuario->isAdmin()) {
            $query->where('usuario_id', Auth::id());
        }
        
        // Aplicar filtros si se proporcionan
        if ($request->filled('nis')) {
            $query->where('nis', 'like', '%' . $request->nis . '%');
        }
        
        if ($request->filled('nromedidor')) {
            $query->where('nromedidor', 'like', '%' . $request->nromedidor . '%');
        }
        
        if ($request->filled('nomclie')) {
            $query->where('nomclie', 'like', '%' . $request->nomclie . '%');
        }
        
        if ($request->filled('centro_servicio')) {
            $query->where('centro_servicio', $request->centro_servicio);
        }
        
        // Obtener registros con paginación
        $registros = $query->orderBy('id')
                          ->paginate($perPage)
                          ->withQueryString();
        
        // Obtener información de la remesa
        $infoRemesaQuery = Remesa::where('nro_carga', $nroCarga);
        if (!$usuario->isAdmin()) {
            $infoRemesaQuery->where('usuario_id', Auth::id());
        }
        $infoRemesa = $infoRemesaQuery->select(['nro_carga', 'nombre_archivo', 'centro_servicio', 'fecha_carga'])
                           ->first();
        
        if (!$infoRemesa) {
            return redirect()->route('remesa.lista')
                ->withErrors(['error' => 'No se encontró la remesa especificada.']);
        }
        
        // Obtener centros únicos para filtro
        $centrosDisponiblesQuery = Remesa::where('nro_carga', $nroCarga);
        if (!$usuario->isAdmin()) {
            $centrosDisponiblesQuery->where('usuario_id', Auth::id());
        }
        $centrosDisponibles = $centrosDisponiblesQuery->distinct()
                                   ->pluck('centro_servicio')
                                   ->filter()
                                   ->sort();
        
        return view('remesa_registros', [
            'registros' => $registros,
            'nroCarga' => $nroCarga,
            'infoRemesa' => $infoRemesa,
            'centrosDisponibles' => $centrosDisponibles,
            'filtros' => $request->only(['nis', 'nromedidor', 'nomclie', 'centro_servicio', 'per_page']),
        ]);
    }

    /**
     * Editar un registro específico (solo administradores)
     */
    public function editarRegistro(Request $request, int $id)
    {
        $registro = Remesa::findOrFail($id);
        
        // Solo administradores pueden editar
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Solo los administradores pueden editar registros.');
        }
        
        if ($request->isMethod('post')) {
            // Procesar actualización
            $request->validate([
                'nis' => 'required|string|max:255',
                'nomclie' => 'required|string|max:255',
                'dir_proc' => 'required|string|max:255',
                'tel_clie' => 'nullable|string|max:20',
                'nromedidor' => 'nullable|string|max:50',
            ]);
            
            // Actualizar el registro
            $registro->update([
                'nis' => $request->nis,
                'nomclie' => $request->nomclie,
                'dir_proc' => $request->dir_proc,
                'tel_clie' => $request->tel_clie,
                'nromedidor' => $request->nromedidor,
                'editado' => true,
                'fecha_edicion' => now(),
            ]);
            
            return redirect()->route('remesa.ver.registros', $registro->nro_carga)
                ->with('success', 'Registro actualizado correctamente.');
        }
        
        return view('remesa_editar_registro', compact('registro'));
    }

    /**
     * Actualizar un registro específico
     */
    public function actualizarRegistro(Request $request, int $id)
    {
        return $this->editarRegistro($request, $id);
    }

    /**
     * Ver historial de cambios de un registro
     */
    public function verHistorial(Request $request, int $id)
    {
        $registro = Remesa::findOrFail($id);
        
        // Verificar permisos: administradores ven todo, usuarios normales solo sus registros
        if (!Auth::user()->isAdmin() && $registro->usuario_id !== Auth::id()) {
            abort(403, 'No tienes permisos para ver este registro.');
        }
        
        // Por ahora, mostrar información básica del registro
        return view('remesa_historial', compact('registro'));
    }

    /**
     * Gestionar registros de una remesa (vista con acciones masivas)
     */
    public function gestionarRegistros(Request $request, string $nroCarga)
    {
        $perPage = $request->get('per_page', 25);
        $usuario = Auth::user();
        
        // Buscar registros de la remesa específica
        $query = Remesa::where('nro_carga', $nroCarga);
        
        // Administradores ven todos los registros, usuarios normales solo los suyos
        if (!$usuario->isAdmin()) {
            $query->where('usuario_id', Auth::id());
        }
        
        // Aplicar filtros
        if ($request->filled('nis')) {
            $query->where('nis', 'like', '%' . $request->nis . '%');
        }
        
        if ($request->filled('nromedidor')) {
            $query->where('nromedidor', 'like', '%' . $request->nromedidor . '%');
        }
        
        if ($request->filled('nomclie')) {
            $query->where('nomclie', 'like', '%' . $request->nomclie . '%');
        }
        
        if ($request->filled('centro_servicio')) {
            $query->where('centro_servicio', $request->centro_servicio);
        }
        
        // Obtener registros con paginación
        $registros = $query->orderBy('id')
                          ->paginate($perPage)
                          ->withQueryString();
        
        // Obtener información de la remesa
        $infoRemesaQuery = Remesa::where('nro_carga', $nroCarga);
        if (!$usuario->isAdmin()) {
            $infoRemesaQuery->where('usuario_id', Auth::id());
        }
        $infoRemesa = $infoRemesaQuery->select(['nro_carga', 'nombre_archivo', 'centro_servicio', 'fecha_carga'])
                                     ->first();
        
        if (!$infoRemesa) {
            return redirect()->route('remesa.lista')
                ->withErrors(['error' => 'No se encontró la remesa especificada.']);
        }
        
        // Obtener centros únicos para filtro
        $centrosDisponiblesQuery = Remesa::where('nro_carga', $nroCarga);
        if (!$usuario->isAdmin()) {
            $centrosDisponiblesQuery->where('usuario_id', Auth::id());
        }
        $centrosDisponibles = $centrosDisponiblesQuery->distinct()
                                                     ->pluck('centro_servicio')
                                                     ->filter()
                                                     ->sort();
        
        return view('remesa_gestionar_registros', [
            'registros' => $registros,
            'nroCarga' => $nroCarga,
            'infoRemesa' => $infoRemesa,
            'centrosDisponibles' => $centrosDisponibles,
            'filtros' => $request->only(['nis', 'nromedidor', 'nomclie', 'centro_servicio', 'per_page']),
        ]);
    }

    /**
     * Editar metadatos de una remesa
     */
    public function editarMetadatos(Request $request, string $nroCarga)
    {
        $usuario = Auth::user();
        
        // Obtener información de la remesa
        $infoRemesaQuery = Remesa::where('nro_carga', $nroCarga);
        if (!$usuario->isAdmin()) {
            $infoRemesaQuery->where('usuario_id', Auth::id());
        }
        $infoRemesa = $infoRemesaQuery->select(['nro_carga', 'nombre_archivo', 'centro_servicio', 'fecha_carga'])
                                     ->first();
        
        if (!$infoRemesa) {
            return redirect()->route('remesa.lista')
                ->withErrors(['error' => 'No se encontró la remesa especificada.']);
        }
        
        if ($request->isMethod('post')) {
            $request->validate([
                'nombre_archivo' => 'required|string|max:255',
                'centro_servicio' => 'required|string|max:255',
            ]);
            
            // Actualizar metadatos de todos los registros de la remesa
            Remesa::where('nro_carga', $nroCarga)->update([
                'nombre_archivo' => $request->nombre_archivo,
                'centro_servicio' => $request->centro_servicio,
            ]);
            
            return redirect()->route('remesa.lista')
                ->with('success', 'Metadatos de la remesa actualizados correctamente.');
        }
        
        return view('remesa_editar_metadatos', compact('infoRemesa', 'nroCarga'));
    }

    /**
     * Actualizar metadatos de una remesa
     */
    public function actualizarMetadatos(Request $request, string $nroCarga)
    {
        return $this->editarMetadatos($request, $nroCarga);
    }
}