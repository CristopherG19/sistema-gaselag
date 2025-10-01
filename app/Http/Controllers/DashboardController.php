<?php

namespace App\Http\Controllers;

use App\Models\Remesa;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $usuario = Auth::user();
        
        // Obtener estadísticas de remesas del usuario
        $estadisticas = $this->getEstadisticasRemesas($usuario->id);
        
        return view('dashboard', compact('usuario', 'estadisticas'));
    }

    /**
     * Obtener estadísticas de remesas para el dashboard
     */
    private function getEstadisticasRemesas(int $userId): array
    {
        try {
            $usuario = Auth::user();
            
            // Administradores ven todas las remesas, usuarios normales solo las suyas
            if ($usuario->isAdmin()) {
                // Total de remesas únicas (por nro_carga) - TODAS
                $totalRemesas = Remesa::where('cargado_al_sistema', true)
                                     ->distinct('nro_carga')
                                     ->count('nro_carga');

                // Total de registros - TODOS
                $totalRegistros = Remesa::where('cargado_al_sistema', true)
                                       ->count();

                // Última carga - DE TODO EL SISTEMA
                $ultimaCarga = Remesa::where('cargado_al_sistema', true)
                                    ->latest('fecha_carga')
                                    ->first();
            } else {
                // Usuarios normales ven solo sus remesas
                $totalRemesas = Remesa::where('usuario_id', $userId)
                                     ->where('cargado_al_sistema', true)
                                     ->distinct('nro_carga')
                                     ->count('nro_carga');

                $totalRegistros = Remesa::where('usuario_id', $userId)
                                       ->where('cargado_al_sistema', true)
                                       ->count();

                $ultimaCarga = Remesa::where('usuario_id', $userId)
                                    ->where('cargado_al_sistema', true)
                                    ->latest('fecha_carga')
                                    ->first();
            }

            $ultimaCargaFormateada = $ultimaCarga 
                ? $ultimaCarga->fecha_carga->format('d/m/Y H:i')
                : 'N/A';

            return [
                'total_remesas' => $totalRemesas,
                'total_registros' => $totalRegistros,
                'ultima_carga' => $ultimaCargaFormateada
            ];

        } catch (\Exception $e) {
            // En caso de error, retornar valores por defecto
            return [
                'total_remesas' => 0,
                'total_registros' => 0,
                'ultima_carga' => 'N/A'
            ];
        }
    }
}
