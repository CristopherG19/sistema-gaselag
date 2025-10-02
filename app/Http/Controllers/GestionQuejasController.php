<?php

namespace App\Http\Controllers;

use App\Models\Queja;
use App\Models\Usuario;
use App\Models\Remesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GestionQuejasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar lista de quejas según el rol del usuario
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $query = Queja::with(['usuario', 'asignadoA', 'remesa']);

        // Filtros según el rol
        if ($usuario->isOperarioCampo()) {
            // Operarios solo ven quejas relacionadas a sus entregas
            $entregasIds = $usuario->entregasAsignadas()->pluck('id');
            $remesasIds = $usuario->entregasAsignadas()->pluck('remesa_id');
            
            $query->where(function($q) use ($usuario, $remesasIds) {
                $q->where('asignado_a', $usuario->id)
                  ->orWhereIn('remesa_id', $remesasIds);
            });
        } elseif ($usuario->isUsuario()) {
            // Usuarios normales ven sus propias quejas
            $query->where('usuario_id', $usuario->id);
        }
        // Los administradores ven todas las quejas

        // Filtros adicionales
        if ($request->filled('estado')) {
            $query->where('estado', $request->get('estado'));
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->get('prioridad'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->get('tipo'));
        }

        if ($request->filled('buscar')) {
            $buscar = $request->get('buscar');
            $query->where(function($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'fecha_creacion');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $quejas = $query->paginate(20)->withQueryString();

        // Obtener datos para filtros
        $estados = ['pendiente', 'en_proceso', 'resuelta', 'cancelada'];
        $prioridades = ['baja', 'media', 'alta', 'critica'];
        $tipos = ['general', 'tecnica', 'administrativa', 'sistema'];

        return view('quejas.index', compact('quejas', 'estados', 'prioridades', 'tipos'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $remesaId = $request->get('remesa_id');
        $remesa = $remesaId ? Remesa::find($remesaId) : null;
        
        $tipos = ['general', 'tecnica', 'administrativa', 'sistema'];
        $prioridades = ['baja', 'media', 'alta', 'critica'];

        return view('quejas.create', compact('remesa', 'tipos', 'prioridades'));
    }

    /**
     * Crear nueva queja
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:2000',
            'tipo' => 'required|in:general,tecnica,administrativa,sistema',
            'prioridad' => 'required|in:baja,media,alta,critica',
            'remesa_id' => 'nullable|exists:remesas,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $queja = Queja::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'tipo' => $request->tipo,
            'prioridad' => $request->prioridad,
            'usuario_id' => Auth::id(),
            'remesa_id' => $request->remesa_id,
            'fecha_creacion' => now(),
        ]);

        return redirect()->route('quejas.show', $queja)
            ->with('success', 'Queja creada exitosamente.');
    }

    /**
     * Mostrar detalles de la queja
     */
    public function show(Queja $queja)
    {
        $usuario = Auth::user();
        
        // Verificar permisos
        if ($usuario->isOperarioCampo() && !$this->puedeVerQueja($queja, $usuario)) {
            abort(403, 'No tienes permisos para ver esta queja.');
        }

        if ($usuario->isUsuario() && $queja->usuario_id !== $usuario->id) {
            abort(403, 'No tienes permisos para ver esta queja.');
        }

        $queja->load(['usuario', 'asignadoA', 'remesa']);

        return view('quejas.show', compact('queja'));
    }

    /**
     * Asignar queja (solo administradores)
     */
    public function asignar(Request $request, Queja $queja)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Solo los administradores pueden asignar quejas.');
        }

        $validator = Validator::make($request->all(), [
            'asignado_a' => 'required|exists:usuarios,id',
            'comentarios' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $queja->update([
            'asignado_a' => $request->asignado_a,
            'estado' => 'en_proceso',
            'fecha_asignacion' => now(),
            'comentarios' => $request->comentarios,
        ]);

        return redirect()->back()
            ->with('success', 'Queja asignada exitosamente.');
    }

    /**
     * Resolver queja
     */
    public function resolver(Request $request, Queja $queja)
    {
        $usuario = Auth::user();
        
        // Verificar permisos
        if (!$usuario->isAdmin() && $queja->asignado_a !== $usuario->id) {
            abort(403, 'No tienes permisos para resolver esta queja.');
        }

        $validator = Validator::make($request->all(), [
            'solucion' => 'required|string|max:2000',
            'comentarios' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $queja->resolver($request->solucion, $request->comentarios);

        return redirect()->back()
            ->with('success', 'Queja resuelta exitosamente.');
    }

    /**
     * Cambiar estado de la queja
     */
    public function cambiarEstado(Request $request, Queja $queja)
    {
        $usuario = Auth::user();
        
        // Solo administradores y usuarios asignados pueden cambiar estado
        if (!$usuario->isAdmin() && $queja->asignado_a !== $usuario->id) {
            abort(403, 'No tienes permisos para cambiar el estado de esta queja.');
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,en_proceso,resuelta,cancelada',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $queja->update(['estado' => $request->estado]);

        return redirect()->back()
            ->with('success', 'Estado de la queja actualizado exitosamente.');
    }

    /**
     * Verificar si un operario puede ver una queja
     */
    private function puedeVerQueja(Queja $queja, Usuario $usuario): bool
    {
        // Puede ver si está asignada a él
        if ($queja->asignado_a === $usuario->id) {
            return true;
        }

        // Puede ver si está relacionada con sus entregas
        if ($queja->remesa_id) {
            $entregasDelOperario = $usuario->entregasAsignadas()
                ->where('remesa_id', $queja->remesa_id)
                ->exists();
            
            return $entregasDelOperario;
        }

        return false;
    }

    /**
     * Obtener estadísticas de quejas
     */
    public function estadisticas()
    {
        $usuario = Auth::user();
        $query = Queja::query();

        // Filtrar según el rol
        if ($usuario->isOperarioCampo()) {
            $entregasIds = $usuario->entregasAsignadas()->pluck('id');
            $remesasIds = $usuario->entregasAsignadas()->pluck('remesa_id');
            
            $query->where(function($q) use ($usuario, $remesasIds) {
                $q->where('asignado_a', $usuario->id)
                  ->orWhereIn('remesa_id', $remesasIds);
            });
        } elseif ($usuario->isUsuario()) {
            $query->where('usuario_id', $usuario->id);
        }

        $estadisticas = [
            'total' => $query->count(),
            'pendientes' => $query->where('estado', 'pendiente')->count(),
            'en_proceso' => $query->where('estado', 'en_proceso')->count(),
            'resueltas' => $query->where('estado', 'resuelta')->count(),
            'criticas' => $query->where('prioridad', 'critica')->count(),
        ];

        return response()->json($estadisticas);
    }
}