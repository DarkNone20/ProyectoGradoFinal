<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DevolucionesController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();
        $usuarioAutenticado = $usuario;
        
        // Obtener préstamos activos del usuario
        $prestamosActivos = DB::table('Prestamos')
            ->join('Equipos', 'Prestamos.Serial', '=', 'Equipos.Serial')
            ->where('Prestamos.DocumentoId', $usuario->DocumentoId)
            ->whereNull('Prestamos.FechaDevolucion')
            ->select('Prestamos.*', 'Equipos.Marca', 'Equipos.Modelo')
            ->get();
            
        return view("devolucion/devoluciones", compact('usuarioAutenticado', 'prestamosActivos'));
    }
    
    public function procesarDevolucion(Request $request)
{
    $usuario = auth()->user();
    $now = Carbon::now('America/Bogota');
    
    // Validar que el préstamo pertenece al usuario
    $prestamo = DB::table('Prestamos')
        ->where('IdPrestamo', $request->id_prestamo)
        ->where('DocumentoId', $usuario->DocumentoId)
        ->whereNull('FechaDevolucion')
        ->first();
        
    if (!$prestamo) {
        return back()->with('error', 'Préstamo no válido o ya devuelto');
    }
    
    DB::beginTransaction();
    try {
        // Actualizar préstamo con datos de devolución
        DB::table('Prestamos')
            ->where('IdPrestamo', $request->id_prestamo)
            ->update([
                'FechaDevolucion' => $now->toDateString(),
                'HoraDevolucion' => $now->format('H:i:s'),
                'Estado' => 'Devuelto', // Añade esta línea
                'Observaciones' => $request->observaciones ?? null
            ]);
            
        // Actualizar estado del equipo
        DB::table('Equipos')
            ->where('Serial', $prestamo->Serial)
            ->update(['Disponibilidad' => 'Disponible']);
            
        DB::commit();
        
        return back()->with('success', 'Devolución realizada con éxito');
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al procesar devolución', [
            'error' => $e->getMessage(),
            'usuario' => $usuario->DocumentoId,
            'prestamo' => $request->id_prestamo
        ]);
        return back()->with('error', 'Error al procesar la devolución: '.$e->getMessage());
    }
}
}