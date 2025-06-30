<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PrestamoController extends Controller
{
    /**
     * Muestra la vista de préstamo con validación mejorada
     */
      public function index()
    {
        $usuario = auth()->user();
        $puedePrestar = false;
        $equipoDisponible = null;
        $grupoValido = null;
        $mensajeError = null;
        $usuarioAutenticado = auth()->user();
    
        // 1. Verificar si el usuario tiene préstamos activos (mejorado)
        $prestamoActivo = DB::table('Prestamos')
            ->where('DocumentoId', $usuario->DocumentoId)
            ->where(function($query) {
                $query->whereNull('FechaDevolucion')
                      ->orWhereNull('HoraDevolucion');
            })
            ->where('FechaI', '<=', Carbon::today())
            ->where('FechaF', '>=', Carbon::today())
            ->first();
            
        if ($prestamoActivo) {
            $mensajeError = 'Ya tienes un préstamo activo (Serial: '.$prestamoActivo->Serial.'). Debes devolver este equipo antes de solicitar otro.';
            Log::info('Usuario con préstamo activo', [
                'usuario' => $usuario->DocumentoId,
                'prestamo_id' => $prestamoActivo->IdPrestamo,
                'serial' => $prestamoActivo->Serial,
                'fecha_inicio' => $prestamoActivo->FechaI,
                'fecha_fin' => $prestamoActivo->FechaF
            ]);
            
            return view('prestamo/prestamo', [
                'usuario' => $usuario,
                'puedePrestar' => false,
                'equipoDisponible' => null,
                'grupoValido' => null,
                'mensajeError' => $mensajeError,
                'usuarioAutenticado' => $usuarioAutenticado,
                'gruposUsuario' => collect() // Enviamos colección vacía para evitar errores en la vista
            ]);
        }
    
        // Resto del código permanece igual...
        $gruposUsuario = DB::table('Usuario_Grupo')
            ->join('Grupos', 'Usuario_Grupo.IdGrupo', '=', 'Grupos.IdGrupo')
            ->where('Usuario_Grupo.DocumentoId', $usuario->DocumentoId)
            ->get();
    
        if ($gruposUsuario->isEmpty()) {
            $mensajeError = 'No puedes realizar préstamos porque no estás en clase.';
        } else {
            foreach ($gruposUsuario as $grupo) {
                if ($this->validarHorarioGrupo($grupo)) {
                    $puedePrestar = true;
                    $grupoValido = $grupo;
                    break;
                }
            }
    
            if ($puedePrestar) {
                $equipoDisponible = $this->verificarEquiposDisponibles($grupoValido->SalaMovil ?? null);
    
                if (!$equipoDisponible) {
                    $mensajeError = 'No hay equipos disponibles en la sala '.($grupoValido->SalaMovil ?? 'asignada');
                    $puedePrestar = false;
                }
            } else {
                $mensajeError = 'No estás en horario de clase. Horarios válidos: ';
                foreach ($gruposUsuario as $grupo) {
                    $mensajeError .= $grupo->DiaSemana.' '.$grupo->HoraInicial.'-'.$grupo->HoraFinal.' (Sala: '.$grupo->SalaMovil.'), ';
                }
                $mensajeError = rtrim($mensajeError, ', ');
            }
        }
    
        return view('prestamo/prestamo', compact(
            'usuario',
            'puedePrestar',
            'equipoDisponible',
            'grupoValido',
            'mensajeError',
            'usuarioAutenticado',
            'gruposUsuario'
        ));
    }

    /**
     * Procesa la solicitud de préstamo con validaciones mejoradas
     */
    public function realizarPrestamo(Request $request)
    {
        $usuario = auth()->user();
        $now = Carbon::now('America/Bogota');

        // 1. Verificar si el usuario tiene préstamos activos
        $prestamoActivo = DB::table('Prestamos')
            ->where('DocumentoId', $usuario->DocumentoId)
            ->where(function($query) {
                $query->whereNull('FechaDevolucion')
                      ->orWhereNull('HoraDevolucion');
            })
            ->first();
            
        if ($prestamoActivo) {
            Log::warning('Intento de préstamo con préstamo activo existente', [
                'usuario' => $usuario->DocumentoId,
                'prestamo_activo' => $prestamoActivo->IdPrestamo
            ]);
            return back()->with('error', 'Ya tienes un préstamo activo (Serial: '.$prestamoActivo->Serial.'). Debes devolverlo antes de solicitar otro.');
        }

        // 2. Validar que esté en horario de clase
        $grupoValido = null;
        $gruposUsuario = DB::table('Usuario_Grupo')
            ->join('Grupos', 'Usuario_Grupo.IdGrupo', '=', 'Grupos.IdGrupo')
            ->where('Usuario_Grupo.DocumentoId', $usuario->DocumentoId)
            ->get();

        foreach ($gruposUsuario as $grupo) {
            if ($this->validarHorarioGrupo($grupo)) {
                $grupoValido = $grupo;
                break;
            }
        }

        if (!$grupoValido) {
            Log::warning('Intento de préstamo fuera de horario', [
                'usuario' => $usuario->DocumentoId,
                'hora_actual' => $now->format('Y-m-d H:i:s')
            ]);
            return back()->with('error', 'No estás en horario de clase para realizar préstamos');
        }

        // 3. Verificar disponibilidad del equipo
        $equipo = DB::table('Equipos')
            ->where('Serial', $request->serial)
            ->where('Estado', 'Activo')
            ->where('Disponibilidad', 'Disponible')
            ->where('SalaMovil', $grupoValido->SalaMovil ?? null)
            ->first();

        if (!$equipo) {
            Log::warning('Intento de préstamo de equipo no disponible', [
                'serial' => $request->serial,
                'usuario' => $usuario->DocumentoId,
                'sala' => $grupoValido->SalaMovil ?? null
            ]);
            return back()->with('error', 'El equipo seleccionado ya no está disponible');
        }

        // 4. Registrar el préstamo
        DB::beginTransaction();
        try {
            $idPrestamo = DB::table('Prestamos')->insertGetId([
                'Serial' => $request->serial,
                'ActivoFijo' => $request->activo_fijo,
                'GrupoId' => $grupoValido->IdGrupo,
                'DocumentoId' => $usuario->DocumentoId,
                'SalaMovil' => $grupoValido->SalaMovil ?? null,
                'FechaI' => $now->toDateString(),
                'FechaF' => $now->addHours($grupoValido->Duracion ?? 1)->toDateString(),
                'HoraI' => $now->format('H:i:s'),
                'HoraF' => $now->addHours($grupoValido->Duracion ?? 1)->format('H:i:s'),
                'Duracion' => $grupoValido->Duracion ?? 1
            ]);

            // Actualizar estado del equipo
            DB::table('Equipos')
                ->where('Serial', $request->serial)
                ->update(['Disponibilidad' => 'En Prestamo']);

            DB::commit();
            
            Log::info('Préstamo realizado con éxito', [
                'prestamo_id' => $idPrestamo,
                'usuario' => $usuario->DocumentoId,
                'equipo' => $request->serial
            ]);
            
            return back()->with('success', 'Préstamo realizado con éxito. Código: PR-'.$idPrestamo);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar préstamo', [
                'error' => $e->getMessage(),
                'usuario' => $usuario->DocumentoId,
                'equipo' => $request->serial
            ]);
            return back()->with('error', 'Error al procesar el préstamo: '.$e->getMessage());
        }
    }

    /**
     * Valida si el horario actual coincide con el grupo (mejorado)
     */
    private function validarHorarioGrupo($grupo)
    {
        $now = Carbon::now('America/Bogota');
        
        // 1. Verificar rango de fechas
        try {
            $fechaInicial = Carbon::parse($grupo->FechaInicial)->startOfDay();
            $fechaFinal = Carbon::parse($grupo->FechaFinal)->endOfDay();
            
            if (!$now->between($fechaInicial, $fechaFinal)) {
                Log::debug('Fecha fuera de rango', [
                    'grupo_id' => $grupo->IdGrupo,
                    'now' => $now->format('Y-m-d'),
                    'fecha_inicial' => $fechaInicial->format('Y-m-d'),
                    'fecha_final' => $fechaFinal->format('Y-m-d')
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error al parsear fechas', ['error' => $e->getMessage()]);
            return false;
        }
        
        // 2. Verificar día de la semana
        $diasMap = [
            'Lunes' => Carbon::MONDAY,
            'Martes' => Carbon::TUESDAY, 
            'Miércoles' => Carbon::WEDNESDAY,
            'Jueves' => Carbon::THURSDAY,
            'Viernes' => Carbon::FRIDAY,
            'Sábado' => Carbon::SATURDAY,
            'Domingo' => Carbon::SUNDAY
        ];
        
        if (!isset($diasMap[$grupo->DiaSemana])) {
            Log::error('Día de la semana no reconocido', ['dia' => $grupo->DiaSemana]);
            return false;
        }
        
        if ($now->dayOfWeek != $diasMap[$grupo->DiaSemana]) {
            Log::debug('Día no coincide', [
                'dia_actual' => $now->dayOfWeek,
                'dia_grupo' => $grupo->DiaSemana
            ]);
            return false;
        }
        
        // 3. Verificar rango de horas
        try {
            $horaInicio = Carbon::createFromTimeString($grupo->HoraInicial);
            $horaFin = Carbon::createFromTimeString($grupo->HoraFinal);
            
            $horaActual = $now->copy()->setDate(2000, 1, 1);
            $horaInicioComparar = $horaInicio->setDate(2000, 1, 1);
            $horaFinComparar = $horaFin->setDate(2000, 1, 1);
            
            return $horaActual->between($horaInicioComparar, $horaFinComparar);
        } catch (\Exception $e) {
            Log::error('Error al parsear horarios', [
                'error' => $e->getMessage(),
                'hora_inicial' => $grupo->HoraInicial,
                'hora_final' => $grupo->HoraFinal
            ]);
            return false;
        }
    }

    /**
     * Verifica equipos disponibles en una sala específica
     */
    private function verificarEquiposDisponibles($sala)
    {
        $equipo = DB::table('Equipos')
            ->where('Estado', 'Activo')
            ->where('Disponibilidad', 'Disponible')
            ->when($sala, function($query, $sala) {
                return $query->where('SalaMovil', $sala);
            })
            ->first();

        Log::info('Equipo disponible encontrado', [
            'sala' => $sala,
            'equipo' => $equipo ? $equipo->Serial : null
        ]);

        return $equipo;
    }

    /**
     * Actualiza la disponibilidad de un equipo
     */
    public function actualizarDisponibilidad($serial, $disponibilidad)
    {
        try {
            DB::table('Equipos')
                ->where('Serial', $serial)
                ->update(['Disponibilidad' => $disponibilidad]);
                
            Log::info('Disponibilidad actualizada', [
                'serial' => $serial,
                'disponibilidad' => $disponibilidad
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error al actualizar disponibilidad', [
                'error' => $e->getMessage(),
                'serial' => $serial
            ]);
            return false;
        }
    }


    public function apiShow($id)
{
    $usuario = auth()->user();

    $prestamo = DB::table('Prestamos')
        ->where('IdPrestamo', $id)
        ->where('DocumentoId', $usuario->DocumentoId) // Solo ve sus préstamos
        ->first();

    if (!$prestamo) {
        return response()->json(['message' => 'Préstamo no encontrado'], 404);
    }

    return response()->json($prestamo);
}
// ESP8266 consulta si debe abrir el cajón
public function accionCajon()
{
    // Buscamos un préstamo pendiente del día de hoy que aún no se ha abierto el cajón
    $prestamo = DB::table('Prestamos')
        ->whereNull('FechaDevolucion')
        ->whereNull('HoraDevolucion')
        ->where('AccionCajon', null) // Nuevo campo, ver nota abajo
        ->whereDate('FechaI', today())
        ->orderBy('IdPrestamo', 'asc')
        ->first();

    if ($prestamo) {
        // Puedes devolver información extra si lo necesitas
        return response('open', 200);
    }
    return response('close', 200);
}

// ESP8266 avisa que abrió (marca el campo en la base de datos)
public function accionCajonRealizada(Request $request)
{
    // Opcional: puedes pasar el ID de préstamo por el body si tienes más de uno pendiente
    $prestamo = DB::table('Prestamos')
        ->whereNull('FechaDevolucion')
        ->whereNull('HoraDevolucion')
        ->where('AccionCajon', null)
        ->orderBy('IdPrestamo', 'asc')
        ->first();

    if ($prestamo) {
        DB::table('Prestamos')
            ->where('IdPrestamo', $prestamo->IdPrestamo)
            ->update(['AccionCajon' => now()]); // Guarda la fecha/hora de apertura
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false, 'msg' => 'No encontrado']);
}
// ESP8266 consulta si debe abrir el cajón PARA DEVOLUCIÓN
public function accionCajonDevolucion()
{
    $prestamo = DB::table('Prestamos')
        ->whereNotNull('AccionCajon') // Cajón ya se abrió para préstamo
        ->whereNull('FechaDevolucion') // Aún no se ha devuelto
        ->whereNull('HoraDevolucion')
        ->whereNull('AccionCajonDevolucion') // Cajón NO se ha abierto para devolución
       ->orderBy('IdPrestamo', 'asc')
        ->first();

    if ($prestamo) {
        return response('open', 200);
    }
    return response('close', 200);
}

// ESP8266 avisa que abrió el cajón PARA DEVOLUCIÓN
public function accionCajonDevolucionRealizada(Request $request)
{
    $prestamo = DB::table('Prestamos')
        ->whereNotNull('AccionCajon')
        ->whereNull('FechaDevolucion')
        ->whereNull('HoraDevolucion')
        ->whereNull('AccionCajonDevolucion')
        ->orderBy('IdPrestamo', 'asc')
        ->first();

    if ($prestamo) {
        DB::table('Prestamos')
            ->where('IdPrestamo', $prestamo->IdPrestamo)
            ->update(['AccionCajonDevolucion' => now()]);
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false, 'msg' => 'No encontrado']);
}

public function apiIndex()
{
    // Ejemplo sencillo, puedes personalizarlo
    $prestamos = DB::table('Prestamos')->get();
    return response()->json($prestamos);
}

}