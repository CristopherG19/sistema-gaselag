<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GestionUsuariosController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Mostrar lista de usuarios
     */
    public function index(Request $request)
    {
        $query = Usuario::query();

        // Filtros
        if ($request->filled('buscar')) {
            $buscar = $request->get('buscar');
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('correo', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('rol')) {
            $query->where('rol', $request->get('rol'));
        }

        if ($request->filled('estado')) {
            $estado = $request->get('estado') === 'activo';
            $query->where('activo', $estado);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $usuarios = $query->paginate(20)->withQueryString();

        return view('admin.gestion_usuarios', compact('usuarios'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('admin.usuarios.create');
    }

    /**
     * Crear nuevo usuario
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correo' => 'required|email|unique:usuarios,correo',
            'password' => 'required|string|min:8|confirmed',
            'rol' => 'required|in:admin,usuario,operario_campo',
            'activo' => 'boolean',
            'notas' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'activo' => $request->boolean('activo', true),
            'notas' => $request->notas,
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->nombre} {$usuario->apellidos} creado exitosamente.");
    }

    /**
     * Mostrar detalles del usuario
     */
    public function show(Usuario $usuario)
    {
        return view('admin.usuarios.show', compact('usuario'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Usuario $usuario)
    {
        return view('admin.usuarios.edit', compact('usuario'));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, Usuario $usuario)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correo' => [
                'required',
                'email',
                Rule::unique('usuarios', 'correo')->ignore($usuario->id)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'rol' => 'required|in:admin,usuario,operario_campo',
            'activo' => 'boolean',
            'notas' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'rol' => $request->rol,
            'activo' => $request->boolean('activo', true),
            'notas' => $request->notas,
        ];

        // Actualizar contraseña solo si se proporciona
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->nombre} {$usuario->apellidos} actualizado exitosamente.");
    }

    /**
     * Eliminar usuario
     */
    public function destroy(Usuario $usuario)
    {
        // No permitir eliminar el propio usuario
        if ($usuario->id === Auth::id()) {
            return redirect()->back()
                ->withErrors(['error' => 'No puedes eliminar tu propio usuario.']);
        }

        $nombre = $usuario->nombre . ' ' . $usuario->apellidos;
        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$nombre} eliminado exitosamente.");
    }

    /**
     * Activar/desactivar usuario
     */
    public function toggleActivo(Usuario $usuario)
    {
        // No permitir desactivar el propio usuario
        if ($usuario->id === Auth::id()) {
            return redirect()->back()
                ->withErrors(['error' => 'No puedes desactivar tu propio usuario.']);
        }

        $usuario->update(['activo' => !$usuario->activo]);
        
        $estado = $usuario->activo ? 'activado' : 'desactivado';
        return redirect()->back()
            ->with('success', "Usuario {$usuario->nombre} {$usuario->apellidos} {$estado} exitosamente.");
    }

    /**
     * Resetear contraseña
     */
    public function resetPassword(Request $request, Usuario $usuario)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $usuario->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()
            ->with('success', "Contraseña de {$usuario->nombre} {$usuario->apellidos} actualizada exitosamente.");
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function estadisticas()
    {
        $estadisticas = [
            'total' => Usuario::count(),
            'activos' => Usuario::where('activo', true)->count(),
            'inactivos' => Usuario::where('activo', false)->count(),
            'admins' => Usuario::where('rol', 'admin')->count(),
            'usuarios' => Usuario::where('rol', 'usuario')->count(),
            'operarios' => Usuario::where('rol', 'operario_campo')->count(),
            'nuevos_mes' => Usuario::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json($estadisticas);
    }
}