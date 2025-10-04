<?php

namespace App\Http\Controllers;

use App\Services\DbfParser;
use App\Services\RemesaService;
use App\Models\Remesa;
use App\Models\RemesaPendiente;
use App\Traits\RemesaHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * CONTROLADOR PRINCIPAL PARA REMESAS
 * Procesamiento directo: Upload → Preview → Inserción inmediata
 * Refactorizado con trait RemesaHelpers para funciones comunes
 */
class RemesaController extends Controller
{
    use RemesaHelpers;
    
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
        
        // Verificar duplicados automáticamente para todos los centros
        $duplicados = [];
        foreach ($centrosDisponibles as $centro) {
            $existe = Remesa::where('nro_carga', $nroCarga)
                           ->where('centro_servicio', $centro)
                           ->where('usuario_id', Auth::id())
                           ->exists();
            if ($existe) {
                $duplicados[] = $centro;
            }
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
            'duplicados' => $duplicados,
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
     * SUBIR ARCHIVOS COMO PENDIENTES - PRIMER PASO (MÚLTIPLES ARCHIVOS)
     */
    public function subirComoPendiente(Request $request)
    {
        Log::info('=== SUBIR COMO PENDIENTE INICIADO ===', ['user' => Auth::id()]);
        
        // Validar si es múltiple o archivo único
        if ($request->hasFile('archivos_dbf')) {
            return $this->subirMultiplesArchivos($request);
        } else {
            return $this->subirArchivoUnico($request);
        }
    }

    /**
     * SUBIR MÚLTIPLES ARCHIVOS COMO PENDIENTES
     */
    private function subirMultiplesArchivos(Request $request)
    {
        Log::info('=== SUBIR MÚLTIPLES ARCHIVOS INICIADO ===', ['user' => Auth::id()]);
        
        $request->validate([
            'archivos_dbf' => 'required|array|min:1|max:100', // Máximo 100 archivos - ampliado para lotes grandes
            'archivos_dbf.*' => 'required|file|mimes:dbf|max:51200', // 50MB max por archivo
        ]);

        $archivosProcesados = [];
        $errores = [];

        try {
            $archivos = $request->file('archivos_dbf');
            
            $totalArchivos = count($archivos);
            Log::info("Iniciando procesamiento masivo de {$totalArchivos} archivos");
            
            foreach ($archivos as $index => $file) {
                try {
                    $progreso = $index + 1;
                    Log::info("Procesando archivo {$progreso}/{$totalArchivos}: {$file->getClientOriginalName()}");
                    
                    $resultado = $this->procesarArchivoIndividual($file, $index);
                    if ($resultado['success']) {
                        $archivosProcesados[] = $resultado['data'];
                        Log::info("✅ Archivo {$progreso}/{$totalArchivos} procesado exitosamente");
                    } else {
                        $errores[] = $resultado['error'];
                        Log::warning("⚠️ Error en archivo {$progreso}/{$totalArchivos}: {$resultado['error']}");
                    }
                } catch (\Exception $e) {
                    $errores[] = "Error en archivo {$file->getClientOriginalName()}: " . $e->getMessage();
                    Log::error('Error procesando archivo individual', [
                        'archivo' => $file->getClientOriginalName(),
                        'progreso' => "{$progreso}/{$totalArchivos}",
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Preparar mensaje de resultado
            $mensaje = "Se procesaron " . count($archivosProcesados) . " archivo(s) correctamente.";
            if (!empty($errores)) {
                $mensaje .= " Errores: " . implode(', ', $errores);
            }

            if (empty($archivosProcesados)) {
                return back()->withErrors(['error' => 'No se pudo procesar ningún archivo. ' . implode(', ', $errores)]);
            }

            return redirect()->route('remesa.lista')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            Log::error('Error general en subida múltiple', [
                'error' => $e->getMessage(),
                'user' => Auth::id()
            ]);
            
            return back()->withErrors(['error' => 'Error al procesar los archivos: ' . $e->getMessage()]);
        }
    }

    /**
     * SUBIR ARCHIVO ÚNICO COMO PENDIENTE (MÉTODO ORIGINAL)
     */
    private function subirArchivoUnico(Request $request)
    {
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

            // Asegurar que $rows sea serializable
            $datosParaGuardar = is_array($rows) ? $rows : json_decode($rows, true);
            
            Log::info('Guardando remesa pendiente', [
                'nro_carga' => $nroCarga,
                'total_rows' => is_array($datosParaGuardar) ? count($datosParaGuardar) : 'not array',
                'first_row_type' => is_array($datosParaGuardar) && count($datosParaGuardar) > 0 ? gettype($datosParaGuardar[0]) : 'no data'
            ]);

            $remesaPendiente = RemesaPendiente::create([
                'usuario_id' => Auth::id(),
                'nombre_archivo' => $file->getClientOriginalName(),
                'nro_carga' => $nroCarga,
                'fecha_carga' => now(),
                'datos_dbf' => $datosParaGuardar, // Guardar todos los registros, no solo el primero
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
     * PROCESAR ARCHIVO INDIVIDUAL (HELPER PARA MÚLTIPLES ARCHIVOS)
     */
    private function procesarArchivoIndividual($file, $index)
    {
        try {
            $tempPath = $file->store('temp_dbf');
            $fullPath = Storage::path($tempPath);
            
            Log::info('Procesando archivo individual', [
                'archivo' => $file->getClientOriginalName(),
                'temp_path' => $tempPath,
                'index' => $index
            ]);

            // Parsear archivo DBF
            $parser = new DbfParser();
            $parsed = $parser->parseFile($fullPath);
            $rows = $parsed['rows'] ?? [];
            
            if (empty($rows)) {
                Storage::delete($tempPath);
                return [
                    'success' => false,
                    'error' => "No se pudieron extraer datos del archivo {$file->getClientOriginalName()}"
                ];
            }

            // Extraer número de carga
            $nroCarga = $this->extractNroCarga($rows[0] ?? []);
            
            $userId = Auth::id();
            $nombreArchivo = $file->getClientOriginalName();
            
            // Verificar duplicados por nombre de archivo
            if (RemesaPendiente::existeArchivoPorUsuario($nombreArchivo, $userId)) {
                Storage::delete($tempPath);
                return [
                    'success' => false,
                    'error' => "El archivo {$nombreArchivo} ya existe en pendientes"
                ];
            }
            
            // Verificar duplicados por número de carga en pendientes
            if (RemesaPendiente::existeNroCargaPorUsuario($nroCarga, $userId)) {
                Storage::delete($tempPath);
                return [
                    'success' => false,
                    'error' => "Ya existe una remesa pendiente con el número de carga {$nroCarga}"
                ];
            }
            
            // Verificar si ya existe en la tabla principal (remesas procesadas)
            if (Remesa::where('nro_carga', $nroCarga)->where('usuario_id', $userId)->exists()) {
                Storage::delete($tempPath);
                return [
                    'success' => false,
                    'error' => "La remesa con número de carga {$nroCarga} ya está cargada en el sistema"
                ];
            }
            
            $remesaPendiente = RemesaPendiente::create([
                'nro_carga' => $nroCarga,
                'nombre_archivo' => $file->getClientOriginalName(),
                'fecha_carga' => now(),
                'usuario_id' => Auth::id(),
                'datos_dbf' => [
                    'rows' => $rows,
                    'metadata' => [
                        'total_records' => count($rows),
                        'processed_at' => now()->toISOString(),
                        'file_size' => $file->getSize(),
                    ]
                ]
            ]);

            // Limpiar archivo temporal ya que los datos están guardados en JSON
            Storage::delete($tempPath);

            Log::info('Archivo individual procesado exitosamente', [
                'remesa_id' => $remesaPendiente->id,
                'nro_carga' => $nroCarga,
                'total_rows' => count($rows)
            ]);

            return [
                'success' => true,
                'data' => [
                    'id' => $remesaPendiente->id,
                    'nro_carga' => $nroCarga,
                    'nombre_archivo' => $file->getClientOriginalName(),
                    'total_rows' => count($rows)
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error procesando archivo individual', [
                'archivo' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => "Error procesando {$file->getClientOriginalName()}: " . $e->getMessage()
            ];
        }
    }

    /**
     * MOSTRAR FORMULARIO DE PROCESAMIENTO - SEGUNDO PASO
     */
    public function procesarForm(Request $request, $id = null)
    {
        Log::info('=== PROCESAR FORM INICIADO ===', [
            'user' => Auth::id(),
            'id' => $id,
            'has_session_data' => !empty(session('temp_dbf_data')),
            'has_session_nro_carga' => !empty(session('temp_nro_carga'))
        ]);
        
        $remesaPendiente = null;
        $rows = null;
        $nroCarga = null;
        $nombreArchivo = null;
        
        // Priorizar el ID específico pasado como parámetro
        if ($id) {
            $remesaPendiente = RemesaPendiente::where('id', $id)
                                           ->where('usuario_id', Auth::id())
                                           ->first();
            
            if (!$remesaPendiente) {
                Log::warning('No se encontró remesa pendiente específica', ['id_buscado' => $id]);
                return redirect()->route('remesa.lista')
                    ->withErrors(['error' => 'No se encontró la remesa pendiente especificada.']);
            }
        } else {
            // Si no hay ID específico, usar datos de sesión o buscar la más reciente
            $rows = session('temp_dbf_data');
            $nroCarga = session('temp_nro_carga');
            $nombreArchivo = session('temp_dbf_nombre');
            
            if (!$rows || !$nroCarga) {
                $remesaPendiente = RemesaPendiente::where('usuario_id', Auth::id())
                                       ->orderBy('fecha_carga', 'desc')
                                       ->first();
            }
        }
        
        // Si tenemos una remesa pendiente específica, cargar sus datos
        if ($remesaPendiente) {
            Log::info('Remesa pendiente encontrada', [
                'remesa_id' => $remesaPendiente->id,
                'nro_carga' => $remesaPendiente->nro_carga,
                'usuario_id' => $remesaPendiente->usuario_id
            ]);
            
            // Cargar datos del archivo temporal si existe
            $tempPath = session('temp_dbf_file');
            if ($tempPath && Storage::exists($tempPath)) {
                $parser = new DbfParser();
                $parsed = $parser->parseFile(Storage::path($tempPath));
                $rows = $parsed['rows'] ?? [];
            } else {
                // Si no hay archivo temporal, usar los datos reales de datos_dbf
                // Forzar conversión desde JSON string si es necesario
                $rawData = $remesaPendiente->getAttributes()['datos_dbf'] ?? null;
                $datos_dbf = null;
                
                if (is_string($rawData)) {
                    // Intentar decodificar el JSON
                    $firstDecode = json_decode($rawData, true);
                    
                    Log::info('Debug decodificación', [
                        'raw_data_length' => strlen($rawData),
                        'raw_data_preview' => substr($rawData, 0, 100),
                        'first_decode_type' => gettype($firstDecode),
                        'first_decode_error' => json_last_error_msg(),
                        'is_string_first' => is_string($firstDecode)
                    ]);
                    
                    // Verificar si hubo error en el primer decode
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('Error decodificando JSON', ['error' => json_last_error_msg()]);
                        $datos_dbf = null;
                    } else {
                        // Si el primer decodificado devuelve un string, intentar segundo decodificado
                        if (is_string($firstDecode)) {
                            $secondDecode = json_decode($firstDecode, true);
                            Log::info('Segundo decode', [
                                'second_decode_type' => gettype($secondDecode),
                                'second_decode_error' => json_last_error_msg()
                            ]);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $datos_dbf = $secondDecode;
                            } else {
                                $datos_dbf = $firstDecode; // Usar el string como está
                            }
                        } else {
                            $datos_dbf = $firstDecode;
                        }
                    }
                } elseif (is_array($rawData)) {
                    $datos_dbf = $rawData;
                } else {
                    // Usar el cast del modelo como fallback
                    $datos_dbf = $remesaPendiente->datos_dbf;
                }
                
                Log::info('Datos DBF cargados', [
                    'raw_data_type' => gettype($rawData),
                    'datos_dbf_type' => gettype($datos_dbf),
                    'is_array' => is_array($datos_dbf),
                    'count' => is_array($datos_dbf) ? count($datos_dbf) : 'not array',
                    'sample_data' => is_array($datos_dbf) ? 'array_data' : substr(strval($datos_dbf), 0, 100)
                ]);
                
                if (is_array($datos_dbf) && !empty($datos_dbf)) {
                    // Verificar si contiene registros reales o solo metadatos
                    if (isset($datos_dbf[0]) && is_array($datos_dbf[0]) && isset($datos_dbf[0]['NIS'])) {
                        // Son registros reales del DBF
                        $rows = $datos_dbf;
                    } elseif (isset($datos_dbf['first_row_sample']) && is_array($datos_dbf['first_row_sample'])) {
                        // Son metadatos, usar el sample como único registro
                        $rows = [$datos_dbf['first_row_sample']];
                        Log::warning('Usando datos de muestra desde metadatos', [
                            'remesa_id' => $remesaPendiente->id,
                            'nro_carga' => $remesaPendiente->nro_carga
                        ]);
                    } else {
                        // Estructura desconocida
                        Log::error('Estructura de datos_dbf desconocida', [
                            'remesa_id' => $remesaPendiente->id,
                            'structure' => array_keys($datos_dbf)
                        ]);
                        $rows = [];
                    }
                } else {
                    // Fallback: crear datos de ejemplo solo si no hay datos reales
                    $rows = [
                        [
                            'NIS' => 'N/A',
                            'NROMEDIDOR' => 'N/A',
                            'NOMCLI' => 'N/A',
                            'DIR_PROC' => 'N/A',
                        ]
                    ];
                }
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

        // Validar que tenemos los datos necesarios
        if (!$rows || !$nroCarga || empty($rows)) {
            Log::error('No hay datos para procesar', [
                'has_rows' => !empty($rows),
                'has_nro_carga' => !empty($nroCarga),
                'id' => $id
            ]);
            return redirect()->route('remesa.lista')
                ->withErrors(['error' => 'No se encontraron datos para procesar. Intenta cargar la remesa nuevamente.']);
        }

        // Obtener centros de servicio disponibles
        $centrosServicio = array_keys($this->getCentrosServicio());

        Log::info('Mostrando vista de procesamiento', [
            'nro_carga' => $nroCarga,
            'total_registros' => count($rows),
            'centros_servicio_count' => count($centrosServicio)
        ]);
        
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
     * ELIMINAR REMESA PENDIENTE
     */
    public function eliminarPendiente(Request $request, $id)
    {
        try {
            $remesaPendiente = RemesaPendiente::where('id', $id)
                                           ->where('usuario_id', Auth::id())
                                           ->first();
            
            if (!$remesaPendiente) {
                return redirect()->route('remesa.lista')
                    ->withErrors(['error' => 'No se encontró la remesa pendiente para eliminar.']);
            }
            
            // Eliminar archivo temporal si existe
            $tempPath = session('temp_dbf_file');
            if ($tempPath && Storage::exists($tempPath)) {
                Storage::delete($tempPath);
            }
            
            // Eliminar la remesa pendiente
            $remesaPendiente->delete();
            
            Log::info('Remesa pendiente eliminada', [
                'remesa_id' => $id,
                'nro_carga' => $remesaPendiente->nro_carga,
                'usuario' => Auth::id()
            ]);
            
            return redirect()->route('remesa.lista')
                ->with('success', "Remesa pendiente {$remesaPendiente->nro_carga} eliminada correctamente.");
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar remesa pendiente', [
                'error' => $e->getMessage(),
                'remesa_id' => $id,
                'usuario' => Auth::id()
            ]);
            
            return redirect()->route('remesa.lista')
                ->withErrors(['error' => 'Error al eliminar la remesa pendiente: ' . $e->getMessage()]);
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
                $centroServicio
            );
            
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
     * Helpers - extractNroCarga ahora está en RemesaHelpers trait
     */

    /**
     * Mostrar lista de remesas cargadas
     */
    public function lista(Request $request)
    {
        $estado = $request->get('estado', 'todos');
        $usuario = Auth::user();
        $perPage = 20;
        
        // Si solo se solicitan pendientes, usar solo la tabla de pendientes
        if ($estado === 'pendientes') {
            $query = RemesaPendiente::query();
            if (!$usuario->isAdmin()) {
                $query->where('usuario_id', Auth::id());
            }
            
            // Aplicar filtros
            if ($request->filled('nro_carga')) {
                $query->where('nro_carga', 'like', '%' . $request->nro_carga . '%');
            }
            if ($request->filled('nombre_archivo')) {
                $query->where('nombre_archivo', 'like', '%' . $request->nombre_archivo . '%');
            }
            
            $remesas = $query->select(['id', 'nro_carga', 'nombre_archivo', 'fecha_carga', 'usuario_id', 'datos_dbf'])
                ->orderBy('fecha_carga', 'desc')
                ->paginate($perPage)
                ->through(function ($remesa) {
                    // Contar registros reales en datos_dbf
                    $datos_dbf = is_array($remesa->datos_dbf) ? $remesa->datos_dbf : json_decode($remesa->datos_dbf, true);
                    $totalRegistros = is_array($datos_dbf) ? count($datos_dbf) : 1;
                    
                    return (object) [
                        'nro_carga' => $remesa->nro_carga,
                        'nombre_archivo' => $remesa->nombre_archivo,
                        'cargado_al_sistema' => false,
                        'total_registros' => $totalRegistros,
                        'fecha_carga' => $remesa->fecha_carga,
                        'primer_id' => $remesa->id,
                        'editado' => false,
                        'fecha_edicion' => null,
                        'usuario_id' => $remesa->usuario_id,
                        'usuario_nombre' => $remesa->usuario->correo ?? 'N/A',
                    ];
                });
        }
        // Si solo se solicitan cargadas, usar solo la tabla de remesas
        elseif ($estado === 'cargadas') {
            $query = Remesa::query();
            if (!$usuario->isAdmin()) {
                $query->where('usuario_id', Auth::id());
            }
            
            $query->where('cargado_al_sistema', true);
            
            // Aplicar filtros
            if ($request->filled('nro_carga')) {
                $query->where('nro_carga', 'like', '%' . $request->nro_carga . '%');
            }
            if ($request->filled('nombre_archivo')) {
                $query->where('nombre_archivo', 'like', '%' . $request->nombre_archivo . '%');
            }
            
            $remesas = $query->selectRaw('
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
            ->paginate($perPage)
            ->through(function ($remesa) {
                $usuario = \App\Models\Usuario::find($remesa->usuario_id);
                $remesa->usuario_nombre = $usuario ? $usuario->correo : 'N/A';
                return $remesa;
            });
        }
        // Si se solicitan todos, combinar ambas consultas usando UNION
        else {
            // Crear consultas base
            $pendientesQuery = RemesaPendiente::query();
            $cargadasQuery = Remesa::query();
            
            if (!$usuario->isAdmin()) {
                $pendientesQuery->where('usuario_id', Auth::id());
                $cargadasQuery->where('usuario_id', Auth::id());
            }
            
            // Aplicar filtros a ambas consultas
            if ($request->filled('nro_carga')) {
                $pendientesQuery->where('nro_carga', 'like', '%' . $request->nro_carga . '%');
                $cargadasQuery->where('nro_carga', 'like', '%' . $request->nro_carga . '%');
            }
            if ($request->filled('nombre_archivo')) {
                $pendientesQuery->where('nombre_archivo', 'like', '%' . $request->nombre_archivo . '%');
                $cargadasQuery->where('nombre_archivo', 'like', '%' . $request->nombre_archivo . '%');
            }
            
            // Preparar datos de pendientes
            $pendientes = $pendientesQuery
                ->select(['id', 'nro_carga', 'nombre_archivo', 'fecha_carga', 'usuario_id', 'datos_dbf'])
                ->get()
                ->map(function ($remesa) {
                    $datos_dbf = is_array($remesa->datos_dbf) ? $remesa->datos_dbf : json_decode($remesa->datos_dbf, true);
                    $totalRegistros = is_array($datos_dbf) ? count($datos_dbf) : 1;
                    
                    return (object) [
                        'nro_carga' => $remesa->nro_carga,
                        'nombre_archivo' => $remesa->nombre_archivo,
                        'cargado_al_sistema' => false,
                        'total_registros' => $totalRegistros,
                        'fecha_carga' => $remesa->fecha_carga,
                        'primer_id' => $remesa->id,
                        'editado' => false,
                        'fecha_edicion' => null,
                        'usuario_id' => $remesa->usuario_id,
                        'usuario_nombre' => $remesa->usuario->correo ?? 'N/A',
                    ];
                });
            
            // Preparar datos de cargadas
            $cargadas = $cargadasQuery
                ->selectRaw('
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
                ->get()
                ->map(function ($remesa) {
                    $usuario = \App\Models\Usuario::find($remesa->usuario_id);
                    $remesa->usuario_nombre = $usuario ? $usuario->correo : 'N/A';
                    return $remesa;
                });
            
            // Combinar y ordenar
            $todasLasRemesas = $pendientes->concat($cargadas)
                ->sortByDesc('fecha_carga')
                ->values();
            
            // Crear paginación manual para la colección combinada
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedItems = $todasLasRemesas->slice($offset, $perPage);
            
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
        }

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
     * getCentrosServicio ahora está en RemesaHelpers trait
     */

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

        // Calcular estadísticas TOTALES de la remesa (no solo de la página actual)
        $estadisticasQuery = Remesa::where('nro_carga', $nroCarga);
        if (!$usuario->isAdmin()) {
            $estadisticasQuery->where('usuario_id', Auth::id());
        }
        
        // Aplicar los mismos filtros que en la consulta principal para estadísticas correctas
        if ($request->filled('nis')) {
            $estadisticasQuery->where('nis', 'like', '%' . $request->nis . '%');
        }
        if ($request->filled('nromedidor')) {
            $estadisticasQuery->where('nromedidor', 'like', '%' . $request->nromedidor . '%');
        }
        if ($request->filled('nomclie')) {
            $estadisticasQuery->where('nomclie', 'like', '%' . $request->nomclie . '%');
        }
        if ($request->filled('centro_servicio')) {
            $estadisticasQuery->where('centro_servicio', $request->centro_servicio);
        }

        $estadisticas = [
            'total_registros' => $estadisticasQuery->count(),
            'registros_originales' => $estadisticasQuery->where('editado', false)->count(),
            'registros_editados' => $estadisticasQuery->where('editado', true)->count(),
            'total_centros' => $centrosDisponibles->count()
        ];
        
        return view('remesa_registros', [
            'registros' => $registros,
            'nroCarga' => $nroCarga,
            'infoRemesa' => $infoRemesa,
            'centrosDisponibles' => $centrosDisponibles,
            'estadisticas' => $estadisticas,
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
            // Procesar actualización - Solo validar campos editables
            $request->validate([
                'retfech' => 'nullable|date',
                'rethor' => 'nullable|date_format:H:i',
                'fechaprog' => 'nullable|date',
                'horaprog' => 'nullable|date_format:H:i',
            ]);
            
            // Procesar las horas a formato decimal
            $rethor = null;
            if ($request->rethor) {
                [$hours, $minutes] = explode(':', $request->rethor);
                $rethor = (float)$hours + ((float)$minutes / 60);
            }
            
            $horaprog = null;
            if ($request->horaprog) {
                [$hours, $minutes] = explode(':', $request->horaprog);
                $horaprog = (float)$hours + ((float)$minutes / 60);
            }
            
            // Guardar campos anteriores para comparación
            $camposOriginales = [
                'retfech' => $registro->retfech,
                'rethor' => $registro->rethor,
                'fechaprog' => $registro->fechaprog,
                'horaprog' => $registro->horaprog,
            ];
            
            // Actualizar solo los campos editables
            $registro->update([
                'retfech' => $request->retfech,
                'rethor' => $rethor,
                'fechaprog' => $request->fechaprog,  
                'horaprog' => $horaprog,
                'editado' => true,
                'fecha_edicion' => now(),
                'editado_por' => Auth::id(),
                'campos_editados' => array_keys(array_filter([
                    'retfech' => $camposOriginales['retfech'] != $request->retfech,
                    'rethor' => $camposOriginales['rethor'] != $rethor,
                    'fechaprog' => $camposOriginales['fechaprog'] != $request->fechaprog,
                    'horaprog' => $camposOriginales['horaprog'] != $horaprog,
                ]))
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
     * Ver todos los detalles de un registro específico
     */
    public function verDetalleRegistro(Request $request, int $id)
    {
        $registro = Remesa::findOrFail($id);
        
        // Verificar permisos: administradores ven todo, usuarios normales solo sus registros
        if (!Auth::user()->isAdmin() && $registro->usuario_id !== Auth::id()) {
            abort(403, 'No tienes permisos para ver este registro.');
        }
        
        return view('remesa_detalle_registro', compact('registro'));
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

    /**
     * Procesar todos los archivos pendientes de una vez
     */
    public function procesarTodosPendientes(Request $request)
    {
        $usuario = Auth::user();
        
        // Obtener todos los archivos pendientes del usuario
        $pendientesQuery = RemesaPendiente::orderBy('created_at', 'asc');
        if (!$usuario->isAdmin()) {
            $pendientesQuery->where('usuario_id', Auth::id());
        }
        
        $archivosPendientes = $pendientesQuery->get();
        
        if ($archivosPendientes->isEmpty()) {
            return redirect()->route('remesa.lista')
                ->with('warning', 'No hay archivos pendientes para procesar.');
        }

        Log::info('=== PROCESAMIENTO MASIVO DE PENDIENTES INICIADO ===', [
            'user' => Auth::id(),
            'total_archivos' => $archivosPendientes->count()
        ]);

        $procesados = [];
        $errores = [];
        $totalRegistros = 0;

        // Configurar límites para procesamiento masivo
        ini_set('memory_limit', config('remesas.dbf.processing.memory_limit', '1024M'));
        ini_set('max_execution_time', config('remesas.dbf.processing.time_limit', 900)); // 15 minutos

        try {
            foreach ($archivosPendientes as $index => $pendiente) {
                $progreso = $index + 1;
                $total = $archivosPendientes->count();
                
                Log::info("Procesando archivo pendiente {$progreso}/{$total}: {$pendiente->nombre_archivo}");
                
                try {
                    // Procesar archivo pendiente a BD
                    $resultado = $this->procesarPendienteABD($pendiente);
                    
                    if ($resultado['success']) {
                        $procesados[] = [
                            'archivo' => $pendiente->nombre_archivo,
                            'nro_carga' => $pendiente->nro_carga,
                            'registros' => $resultado['registros_insertados']
                        ];
                        $totalRegistros += $resultado['registros_insertados'];
                        
                        Log::info("✅ Archivo {$progreso}/{$total} procesado exitosamente", [
                            'archivo' => $pendiente->nombre_archivo,
                            'registros' => $resultado['registros_insertados']
                        ]);
                    } else {
                        $errores[] = "Error en {$pendiente->nombre_archivo}: {$resultado['error']}";
                        Log::warning("⚠️ Error en archivo {$progreso}/{$total}", [
                            'archivo' => $pendiente->nombre_archivo,
                            'error' => $resultado['error']
                        ]);
                    }
                } catch (\Exception $e) {
                    $errores[] = "Error crítico en {$pendiente->nombre_archivo}: " . $e->getMessage();
                    Log::error('Error crítico procesando pendiente', [
                        'archivo' => $pendiente->nombre_archivo,
                        'progreso' => "{$progreso}/{$total}",
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Preparar mensaje de resultado
            $mensaje = "Procesamiento masivo completado: ";
            $mensaje .= count($procesados) . " archivo(s) procesados exitosamente";
            $mensaje .= " ({$totalRegistros} registros insertados en BD)";
            
            if (!empty($errores)) {
                $mensaje .= ". Errores en " . count($errores) . " archivo(s)";
            }

            Log::info('=== PROCESAMIENTO MASIVO COMPLETADO ===', [
                'user' => Auth::id(),
                'procesados' => count($procesados),
                'errores' => count($errores),
                'total_registros' => $totalRegistros
            ]);

            if (empty($procesados)) {
                return redirect()->route('remesa.lista')
                    ->withErrors(['error' => 'No se pudo procesar ningún archivo. Errores: ' . implode(', ', $errores)]);
            }

            $tipoMensaje = empty($errores) ? 'success' : 'warning';
            return redirect()->route('remesa.lista')
                ->with($tipoMensaje, $mensaje);

        } catch (\Exception $e) {
            Log::error('Error general en procesamiento masivo de pendientes', [
                'error' => $e->getMessage(),
                'user' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('remesa.lista')
                ->withErrors(['error' => 'Error general en el procesamiento masivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Procesar un archivo pendiente específico a la base de datos
     */
    private function procesarPendienteABD(RemesaPendiente $pendiente)
    {
        try {
            Log::info('Iniciando procesamiento de pendiente a BD', [
                'archivo' => $pendiente->nombre_archivo,
                'nro_carga' => $pendiente->nro_carga
            ]);

            // Verificar si ya existe la remesa
            $existeRemesa = Remesa::where('nro_carga', $pendiente->nro_carga)->exists();
            if ($existeRemesa) {
                return [
                    'success' => false,
                    'error' => 'La remesa ya existe en la base de datos'
                ];
            }

            // Procesar con RemesaService
            $service = new RemesaService();
            $resultado = $service->procesarPendienteCompleto($pendiente);

            if ($resultado['success']) {
                // Eliminar el archivo pendiente después del procesamiento exitoso
                $pendiente->delete();
                
                return [
                    'success' => true,
                    'registros_insertados' => $resultado['registros_insertados'] ?? 0
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $resultado['error'] ?? 'Error desconocido'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error procesando pendiente a BD', [
                'archivo' => $pendiente->nombre_archivo,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}