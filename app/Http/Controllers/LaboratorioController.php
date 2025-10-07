<?php

namespace App\Http\Controllers;

use App\Models\BancoEnsayo;
use App\Models\Ensayo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaboratorioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isTecnicoLaboratorio() && !Auth::user()->isAdmin()) {
                abort(403, 'No tienes permisos para acceder al sistema de laboratorio.');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard principal del laboratorio
     */
    public function index()
    {
        $bancos = BancoEnsayo::with(['ensayosEnProceso.tecnico'])
                            ->activos()
                            ->get();

        $estadisticas = [
            'ensayos_hoy' => Ensayo::whereDate('created_at', today())->count(),
            'ensayos_en_proceso' => Ensayo::enProceso()->count(),
            'ensayos_completados_hoy' => Ensayo::whereDate('fecha_finalizacion', today())->count(),
            'ensayos_aprobados_hoy' => Ensayo::whereDate('fecha_finalizacion', today())->aprobados()->count(),
            'bancos_disponibles' => $bancos->where('estado', 'activo')->count(),
            'capacidad_total' => $bancos->sum('capacidad_maxima'),
            'capacidad_ocupada' => Ensayo::enProceso()->count()
        ];

        return view('laboratorio.dashboard', compact('bancos', 'estadisticas'));
    }

    /**
     * Mostrar formulario para nuevo ensayo
     */
    public function nuevoEnsayo()
    {
        $bancosDisponibles = BancoEnsayo::disponibles()->get();
        $tecnicos = Usuario::where('rol', 'tecnico_laboratorio')->get();

        return view('laboratorio.nuevo_ensayo', compact('bancosDisponibles', 'tecnicos'));
    }

    /**
     * Crear nuevo ensayo
     */
    public function crearEnsayo(Request $request)
    {
        $request->validate([
            'nro_medidor' => 'required|string|max:50',
            'banco_ensayo_id' => 'required|exists:bancos_ensayo,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'calibre' => 'nullable|numeric|min:0',
            'clase_metrologia' => 'nullable|string|max:20',
            'ano_fabricacion' => 'nullable|integer|min:1900|max:' . date('Y'),
            'tipo_ensayo' => 'required|in:verificacion_inicial,verificacion_periodica,reparacion'
        ]);

        // Verificar que el banco seleccionado esté disponible
        $banco = BancoEnsayo::findOrFail($request->banco_ensayo_id);
        if (!$banco->estaDisponible()) {
            return back()->withErrors(['banco_ensayo_id' => 'El banco seleccionado no está disponible.']);
        }

        $ensayo = Ensayo::create([
            'nro_medidor' => $request->nro_medidor,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'calibre' => $request->calibre,
            'clase_metrologia' => $request->clase_metrologia,
            'ano_fabricacion' => $request->ano_fabricacion,
            'banco_ensayo_id' => $request->banco_ensayo_id,
            'tecnico_id' => Auth::id(),
            'tipo_ensayo' => $request->tipo_ensayo,
            'estado' => 'pendiente'
        ]);

        return redirect()->route('laboratorio.ensayo', $ensayo->id)
                        ->with('success', 'Ensayo creado exitosamente.');
    }

    /**
     * Ver/editar ensayo específico
     */
    public function ensayo($id)
    {
        $ensayo = Ensayo::with(['bancoEnsayo', 'tecnico'])->findOrFail($id);
        
        // Verificar permisos
        if (!Auth::user()->isAdmin() && $ensayo->tecnico_id !== Auth::id()) {
            abort(403, 'No tienes permisos para ver este ensayo.');
        }

        return view('laboratorio.ensayo', compact('ensayo'));
    }

    /**
     * Iniciar ensayo
     */
    public function iniciarEnsayo($id)
    {
        $ensayo = Ensayo::findOrFail($id);
        
        if ($ensayo->estado !== 'pendiente') {
            return back()->withErrors(['estado' => 'El ensayo ya fue iniciado o completado.']);
        }

        $ensayo->iniciar();

        return back()->with('success', 'Ensayo iniciado exitosamente.');
    }

    /**
     * Actualizar datos de ensayo
     */
    public function actualizarEnsayo(Request $request, $id)
    {
        $ensayo = Ensayo::findOrFail($id);
        
        if ($ensayo->estado === 'completado') {
            return back()->withErrors(['estado' => 'No se puede modificar un ensayo completado.']);
        }

        $request->validate([
            'caudal_q1' => 'nullable|numeric|min:0',
            'caudal_q2' => 'nullable|numeric|min:0',
            'caudal_q3' => 'nullable|numeric|min:0',
            'volumen_ensayo_q1' => 'nullable|numeric|min:0',
            'volumen_medidor_q1' => 'nullable|numeric|min:0',
            'volumen_ensayo_q2' => 'nullable|numeric|min:0',
            'volumen_medidor_q2' => 'nullable|numeric|min:0',
            'volumen_ensayo_q3' => 'nullable|numeric|min:0',
            'volumen_medidor_q3' => 'nullable|numeric|min:0',
            'temperatura' => 'nullable|numeric|min:-10|max:60',
            'presion' => 'nullable|numeric|min:0',
            'humedad' => 'nullable|numeric|min:0|max:100',
            'observaciones' => 'nullable|string'
        ]);

        $ensayo->fill($request->only([
            'caudal_q1', 'caudal_q2', 'caudal_q3',
            'volumen_ensayo_q1', 'volumen_medidor_q1',
            'volumen_ensayo_q2', 'volumen_medidor_q2',
            'volumen_ensayo_q3', 'volumen_medidor_q3',
            'temperatura', 'presion', 'humedad', 'observaciones'
        ]));

        // Calcular errores automáticamente
        $ensayo->actualizarTodosLosErrores();
        $ensayo->save();

        return back()->with('success', 'Datos actualizados correctamente.');
    }

    /**
     * Finalizar ensayo
     */
    public function finalizarEnsayo(Request $request, $id)
    {
        $ensayo = Ensayo::findOrFail($id);
        
        if ($ensayo->estado !== 'en_proceso') {
            return back()->withErrors(['estado' => 'Solo se pueden finalizar ensayos en proceso.']);
        }

        $request->validate([
            'defectos_encontrados' => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        $ensayo->defectos_encontrados = $request->defectos_encontrados;
        $ensayo->observaciones = $request->observaciones;
        
        $ensayo->finalizar();

        // Generar certificado si es aprobado
        if ($ensayo->resultado_final === 'aprobado') {
            $ensayo->nro_certificado = $ensayo->generarNumeroCertificado();
            $ensayo->fecha_certificado = now();
            $ensayo->save();
        }

        return back()->with('success', 'Ensayo finalizado exitosamente.');
    }

    /**
     * Listado de ensayos
     */
    public function ensayos(Request $request)
    {
        $query = Ensayo::with(['bancoEnsayo', 'tecnico']);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('resultado')) {
            $query->where('resultado_final', $request->resultado);
        }

        if ($request->filled('tecnico_id')) {
            $query->where('tecnico_id', $request->tecnico_id);
        }

        if ($request->filled('nro_medidor')) {
            $query->where('nro_medidor', 'like', '%' . $request->nro_medidor . '%');
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $ensayos = $query->orderBy('created_at', 'desc')->paginate(20);
        $tecnicos = Usuario::where('rol', 'tecnico_laboratorio')->get();

        return view('laboratorio.ensayos', compact('ensayos', 'tecnicos'));
    }

    /**
     * Gestión de bancos de ensayo
     */
    public function bancos()
    {
        $bancos = BancoEnsayo::with(['ensayosEnProceso'])->get();
        return view('laboratorio.bancos', compact('bancos'));
    }

    /**
     * Generar reporte PDF del ensayo
     */
    public function generarCertificado($id)
    {
        $ensayo = Ensayo::with(['bancoEnsayo', 'tecnico'])->findOrFail($id);
        
        if ($ensayo->estado !== 'completado' || $ensayo->resultado_final !== 'aprobado') {
            return back()->withErrors(['certificado' => 'Solo se pueden generar certificados para ensayos aprobados.']);
        }

        // Aquí implementarías la generación del PDF
        // Por ahora retornamos una vista de ejemplo
        return view('laboratorio.certificado', compact('ensayo'));
    }

    /**
     * API para obtener estado de bancos (para actualizaciones en tiempo real)
     */
    public function estadoBancos()
    {
        $bancos = BancoEnsayo::with(['ensayosEnProceso'])->activos()->get();
        
        return response()->json($bancos->map(function ($banco) {
            return [
                'id' => $banco->id,
                'nombre' => $banco->nombre,
                'capacidad_maxima' => $banco->capacidad_maxima,
                'capacidad_ocupada' => $banco->ensayosEnProceso->count(),
                'disponible' => $banco->estaDisponible(),
                'ensayos_activos' => $banco->ensayosEnProceso()->count()
            ];
        }));
    }
}
