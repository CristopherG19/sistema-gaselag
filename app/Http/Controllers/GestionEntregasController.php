<?php

namespace App\Http\Controllers;

use App\Models\EntregaCarga;
use App\Models\Remesa;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GestionEntregasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar lista de entregas según el rol del usuario
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $query = EntregaCarga::with(['remesa', 'operario', 'asignadoPor']);

        // Filtros según el rol
        if ($usuario->isOperarioCampo()) {
            // Operarios solo ven sus entregas asignadas
            $query->where('operario_id', $usuario->id);
        } elseif ($usuario->isUsuario()) {
            // Usuarios normales ven las entregas que crearon
            $query->where('asignado_por', $usuario->id);
        }
        // Los administradores ven todas las entregas

        // Filtros adicionales
        if ($request->filled('estado')) {
            $query->where('estado', $request->get('estado'));
        }

        if ($request->filled('operario_id')) {
            $query->where('operario_id', $request->get('operario_id'));
        }

        if ($request->filled('remesa_id')) {
            $query->where('remesa_id', $request->get('remesa_id'));
        }

        if ($request->filled('buscar')) {
            $buscar = $request->get('buscar');
            $query->where(function($q) use ($buscar) {
                $q->where('codigo_entrega', 'like', "%{$buscar}%")
                  ->orWhere('nombre_entrega', 'like', "%{$buscar}%")
                  ->orWhere('zona_asignada', 'like', "%{$buscar}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'fecha_asignacion');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $entregas = $query->paginate(20)->withQueryString();

        // Obtener datos para filtros
        $estados = ['asignada', 'en_proceso', 'completada', 'cancelada'];
        $operarios = Usuario::where('rol', 'operario_campo')->where('activo', true)->get();
        $remesas = Remesa::where('cargado_al_sistema', true)->get();

        return view('entregas.index', compact('entregas', 'estados', 'operarios', 'remesas'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $remesaId = $request->get('remesa_id');
        $remesa = $remesaId ? Remesa::find($remesaId) : null;
        
        $operarios = Usuario::where('rol', 'operario_campo')
            ->where('activo', true)
            ->get();

        return view('entregas.create', compact('remesa', 'operarios'));
    }

    /**
     * Crear nueva entrega
     */
    public function store(Request $request)
    {
        $usuario = Auth::user();
        
        // Solo administradores y usuarios normales pueden crear entregas
        if (!$usuario->isAdmin() && !$usuario->isUsuario()) {
            abort(403, 'No tienes permisos para crear entregas.');
        }

        $validator = Validator::make($request->all(), [
            'remesa_id' => 'required|exists:remesas,id',
            'operario_id' => 'required|exists:usuarios,id',
            'nombre_entrega' => 'required|string|max:255',
            'zona_asignada' => 'nullable|string|max:255',
            'instrucciones' => 'nullable|string|max:1000',
            'registros_asignados' => 'required|array|min:1',
            'registros_asignados.*' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar que el operario sea válido
        $operario = Usuario::find($request->operario_id);
        if (!$operario->isOperarioCampo()) {
            return redirect()->back()
                ->withErrors(['operario_id' => 'El usuario seleccionado no es un operario de campo.'])
                ->withInput();
        }

        // Obtener la remesa
        $remesa = Remesa::find($request->remesa_id);
        $totalRegistros = count($request->registros_asignados);

        $entrega = EntregaCarga::create([
            'codigo_entrega' => EntregaCarga::generarCodigoEntrega(),
            'nombre_entrega' => $request->nombre_entrega,
            'remesa_id' => $request->remesa_id,
            'operario_id' => $request->operario_id,
            'asignado_por' => $usuario->id,
            'registros_asignados' => $request->registros_asignados,
            'total_registros' => $totalRegistros,
            'zona_asignada' => $request->zona_asignada,
            'instrucciones' => $request->instrucciones,
            'estado' => 'asignada',
            'fecha_asignacion' => now(),
        ]);

        return redirect()->route('entregas.show', $entrega)
            ->with('success', 'Entrega creada exitosamente.');
    }

    /**
     * Mostrar detalles de la entrega
     */
    public function show(EntregaCarga $entrega)
    {
        $usuario = Auth::user();
        
        // Verificar permisos
        if ($usuario->isOperarioCampo() && $entrega->operario_id !== $usuario->id) {
            abort(403, 'No tienes permisos para ver esta entrega.');
        }

        if ($usuario->isUsuario() && $entrega->asignado_por !== $usuario->id) {
            abort(403, 'No tienes permisos para ver esta entrega.');
        }

        $entrega->load(['remesa', 'operario', 'asignadoPor']);

        return view('entregas.show', compact('entrega'));
    }

    /**
     * Iniciar entrega (solo operarios)
     */
    public function iniciar(EntregaCarga $entrega)
    {
        $usuario = Auth::user();
        
        if (!$usuario->isOperarioCampo() || $entrega->operario_id !== $usuario->id) {
            abort(403, 'No tienes permisos para iniciar esta entrega.');
        }

        if (!$entrega->puedeIniciar()) {
            return redirect()->back()
                ->withErrors(['error' => 'Esta entrega no puede ser iniciada.']);
        }

        $entrega->iniciar();

        return redirect()->back()
            ->with('success', 'Entrega iniciada exitosamente.');
    }

    /**
     * Completar entrega (solo operarios)
     */
    public function completar(Request $request, EntregaCarga $entrega)
    {
        $usuario = Auth::user();
        
        if (!$usuario->isOperarioCampo() || $entrega->operario_id !== $usuario->id) {
            abort(403, 'No tienes permisos para completar esta entrega.');
        }

        $validator = Validator::make($request->all(), [
            'observaciones' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        if (!$entrega->puedeCompletar()) {
            return redirect()->back()
                ->withErrors(['error' => 'Esta entrega no puede ser completada.']);
        }

        $entrega->completar($request->observaciones);

        return redirect()->back()
            ->with('success', 'Entrega completada exitosamente.');
    }

    /**
     * Actualizar progreso (solo operarios)
     */
    public function actualizarProgreso(Request $request, EntregaCarga $entrega)
    {
        $usuario = Auth::user();
        
        if (!$usuario->isOperarioCampo() || $entrega->operario_id !== $usuario->id) {
            abort(403, 'No tienes permisos para actualizar esta entrega.');
        }

        $validator = Validator::make($request->all(), [
            'progreso' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $entrega->actualizarProgreso($request->progreso);

        return redirect()->back()
            ->with('success', 'Progreso actualizado exitosamente.');
    }

    /**
     * Cancelar entrega
     */
    public function cancelar(Request $request, EntregaCarga $entrega)
    {
        $usuario = Auth::user();
        
        // Solo administradores y quien creó la entrega pueden cancelarla
        if (!$usuario->isAdmin() && $entrega->asignado_por !== $usuario->id) {
            abort(403, 'No tienes permisos para cancelar esta entrega.');
        }

        $validator = Validator::make($request->all(), [
            'motivo' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $entrega->cancelar($request->motivo);

        return redirect()->back()
            ->with('success', 'Entrega cancelada exitosamente.');
    }

    /**
     * Obtener estadísticas de entregas
     */
    public function estadisticas()
    {
        $usuario = Auth::user();
        $query = EntregaCarga::query();

        // Filtrar según el rol
        if ($usuario->isOperarioCampo()) {
            $query->where('operario_id', $usuario->id);
        } elseif ($usuario->isUsuario()) {
            $query->where('asignado_por', $usuario->id);
        }

        $estadisticas = [
            'total' => $query->count(),
            'asignadas' => $query->where('estado', 'asignada')->count(),
            'en_proceso' => $query->where('estado', 'en_proceso')->count(),
            'completadas' => $query->where('estado', 'completada')->count(),
            'canceladas' => $query->where('estado', 'cancelada')->count(),
        ];

        return response()->json($estadisticas);
    }
}