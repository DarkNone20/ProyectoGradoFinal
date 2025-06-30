<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DevolucionesController extends Controller
{
    // ------------------- MÉTODOS PARA LA VISTA WEB -------------------

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
                    'Estado' => 'Devuelto',
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

    // ------------------- MÉTODOS API REST -------------------

    /**
     * Lista los préstamos activos del usuario autenticado (GET /api/devoluciones)
     */
    public function apiIndex()
    {
        $usuario = auth()->user();

        $prestamosActivos = DB::table('Prestamos')
            ->join('Equipos', 'Prestamos.Serial', '=', 'Equipos.Serial')
            ->where('Prestamos.DocumentoId', $usuario->DocumentoId)
            ->whereNull('Prestamos.FechaDevolucion')
            ->select('Prestamos.*', 'Equipos.Marca', 'Equipos.Modelo')
            ->get();

        return response()->json($prestamosActivos);
    }

    /**
     * Muestra un préstamo específico activo para devolución (GET /api/devoluciones/{id})
     */
    public function apiShow($id)
    {
        $usuario = auth()->user();

        $prestamo = DB::table('Prestamos')
            ->join('Equipos', 'Prestamos.Serial', '=', 'Equipos.Serial')
            ->where('Prestamos.IdPrestamo', $id)
            ->where('Prestamos.DocumentoId', $usuario->DocumentoId)
            ->whereNull('Prestamos.FechaDevolucion')
            ->select('Prestamos.*', 'Equipos.Marca', 'Equipos.Modelo')
            ->first();

        if (!$prestamo) {
            return response()->json(['message' => 'Préstamo no válido o ya devuelto'], 404);
        }
        return response()->json($prestamo);
    }

    /**
     * Procesa la devolución de un préstamo (POST /api/devoluciones)
     * Request: { "id_prestamo": 123, "observaciones": "texto opcional" }
     */
    public function apiStore(Request $request)
    {
        $usuario = auth()->user();
        $now = Carbon::now('America/Bogota');

        // Validar que el préstamo pertenece al usuario y está activo
        $prestamo = DB::table('Prestamos')
            ->where('IdPrestamo', $request->id_prestamo)
            ->where('DocumentoId', $usuario->DocumentoId)
            ->whereNull('FechaDevolucion')
            ->first();

        if (!$prestamo) {
            return response()->json(['message' => 'Préstamo no válido o ya devuelto'], 400);
        }

        DB::beginTransaction();
        try {
            // Actualizar préstamo con datos de devolución
            DB::table('Prestamos')
                ->where('IdPrestamo', $request->id_prestamo)
                ->update([
                    'FechaDevolucion' => $now->toDateString(),
                    'HoraDevolucion' => $now->format('H:i:s'),
                    'Estado' => 'Devuelto',
                    'Observaciones' => $request->observaciones ?? null
                ]);

            // Actualizar estado del equipo
            DB::table('Equipos')
                ->where('Serial', $prestamo->Serial)
                ->update(['Disponibilidad' => 'Disponible']);

            DB::commit();

            return response()->json(['message' => 'Devolución realizada con éxito']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar devolución', [
                'error' => $e->getMessage(),
                'usuario' => $usuario->DocumentoId,
                'prestamo' => $request->id_prestamo
            ]);
            return response()->json(['message' => 'Error al procesar la devolución: '.$e->getMessage()], 500);
        }
    }

    /**
     * Actualiza observaciones o estado de devolución (PUT /api/devoluciones/{id})
     */
    public function apiUpdate(Request $request, $id)
    {
        $usuario = auth()->user();

        $prestamo = DB::table('Prestamos')
            ->where('IdPrestamo', $id)
            ->where('DocumentoId', $usuario->DocumentoId)
            ->first();

        if (!$prestamo) {
            return response()->json(['message' => 'Préstamo no encontrado'], 404);
        }

        $data = $request->only(['Observaciones', 'Estado']);
        $updated = DB::table('Prestamos')
            ->where('IdPrestamo', $id)
            ->update($data);

        return response()->json(['message' => 'Devolución actualizada']);
    }

}
