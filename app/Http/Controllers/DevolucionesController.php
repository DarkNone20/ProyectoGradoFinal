<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // Importa la clase Mail
use App\Mail\DevolucionConObservaciones; // Importa tu Mailable

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

        // Validar que el préstamo pertenece al usuario y obtener datos del equipo
        $prestamo = DB::table('Prestamos')
            ->join('Equipos', 'Prestamos.Serial', '=', 'Equipos.Serial')
            ->where('IdPrestamo', $request->id_prestamo)
            ->where('Prestamos.DocumentoId', $usuario->DocumentoId)
            ->whereNull('FechaDevolucion')
            ->select('Prestamos.*', 'Equipos.Marca', 'Equipos.Modelo', 'Prestamos.SalaMovil') // Se incluyen datos del equipo
            ->first();

        if (!$prestamo) {
            return back()->with('error', 'Préstamo no válido o ya devuelto');
        }

        DB::beginTransaction();
        try {
            $fechaDevolucion = $now->toDateString();
            $horaDevolucion = $now->format('H:i:s');
            $observaciones = $request->observaciones ?? null;

            // Actualizar préstamo con datos de devolución
            DB::table('Prestamos')
                ->where('IdPrestamo', $request->id_prestamo)
                ->update([
                    'FechaDevolucion' => $fechaDevolucion,
                    'HoraDevolucion' => $horaDevolucion,
                    'Estado' => 'Devuelto',
                    'Observaciones' => $observaciones
                ]);

            // Actualizar estado del equipo
            DB::table('Equipos')
                ->where('Serial', $prestamo->Serial)
                ->update(['Disponibilidad' => 'Disponible']);

            DB::commit();

            // --- INICIO: LÓGICA DE ENVÍO DE CORREO ---
            if (!empty($observaciones)) {
                try {
                    $datosParaCorreo = [
                        'usuario'         => $usuario,
                        'prestamo'        => $prestamo,
                        'equipo'          => (object)['Marca' => $prestamo->Marca, 'Modelo' => $prestamo->Modelo],
                        'observaciones'   => $observaciones,
                        'fechaDevolucion' => $fechaDevolucion,
                        'horaDevolucion'  => $horaDevolucion
                    ];

                    // Correo
                    Mail::to('jhoncastillo0420@gmail.com')->send(new DevolucionConObservaciones($datosParaCorreo));

                } catch (\Exception $e) {
                    // Si falla el envío del correo, no deshagas la devolución, pero regístralo.
                    Log::error('Error al enviar correo de devolución con observaciones', [
                        'error'    => $e->getMessage(),
                        'prestamo' => $request->id_prestamo
                    ]);
                }
            }
            // --- FIN: LÓGICA DE ENVÍO DE CORREO ---

            return back()->with('success', 'Devolución realizada con éxito');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar devolución', [
                'error'   => $e->getMessage(),
                'usuario' => $usuario->DocumentoId,
                'prestamo'=> $request->id_prestamo
            ]);
            return back()->with('error', 'Error al procesar la devolución: ' . $e->getMessage());
        }
    }

    // ------------------- MÉTODOS API REST -------------------

   
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

  // Método para guardar la devolución con observaciones    
    public function apiStore(Request $request)
    {
        $usuario = auth()->user();
        $now = Carbon::now('America/Bogota');

        // Validar que el préstamo pertenece al usuario y está activo
        $prestamo = DB::table('Prestamos')
            ->join('Equipos', 'Prestamos.Serial', '=', 'Equipos.Serial')
            ->where('IdPrestamo', $request->id_prestamo)
            ->where('Prestamos.DocumentoId', $usuario->DocumentoId)
            ->whereNull('FechaDevolucion')
            ->select('Prestamos.*', 'Equipos.Marca', 'Equipos.Modelo', 'Prestamos.SalaMovil')
            ->first();

        if (!$prestamo) {
            return response()->json(['message' => 'Préstamo no válido o ya devuelto'], 400);
        }

        DB::beginTransaction();
        try {
            $fechaDevolucion = $now->toDateString();
            $horaDevolucion = $now->format('H:i:s');
            $observaciones = $request->observaciones ?? null;

            // Actualizar préstamo con datos de devolución
            DB::table('Prestamos')
                ->where('IdPrestamo', $request->id_prestamo)
                ->update([
                    'FechaDevolucion' => $fechaDevolucion,
                    'HoraDevolucion'  => $horaDevolucion,
                    'Estado'          => 'Devuelto',
                    'Observaciones'   => $observaciones
                ]);

            // Actualizar estado del equipo
            DB::table('Equipos')
                ->where('Serial', $prestamo->Serial)
                ->update(['Disponibilidad' => 'Disponible']);

            DB::commit();

            // --- INICIO: LÓGICA DE ENVÍO DE CORREO PARA API ---
            if (!empty($observaciones)) {
                try {
                    $datosParaCorreo = [
                        'usuario'         => $usuario,
                        'prestamo'        => $prestamo,
                        'equipo'          => (object)['Marca' => $prestamo->Marca, 'Modelo' => $prestamo->Modelo],
                        'observaciones'   => $observaciones,
                        'fechaDevolucion' => $fechaDevolucion,
                        'horaDevolucion'  => $horaDevolucion
                    ];
                    Mail::to('soporte@tuempresa.com')->send(new DevolucionConObservaciones($datosParaCorreo));
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo de devolución (API)', [
                        'error'    => $e->getMessage(),
                        'prestamo' => $request->id_prestamo
                    ]);
                }
            }
         

            return response()->json(['message' => 'Devolución realizada con éxito']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar devolución (API)', [
                'error'   => $e->getMessage(),
                'usuario' => $usuario->DocumentoId,
                'prestamo'=> $request->id_prestamo
            ]);
            return response()->json(['message' => 'Error al procesar la devolución: ' . $e->getMessage()], 500);
        }
    }

  // ------------------- MÉTODOS PARA LA API -------------------
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