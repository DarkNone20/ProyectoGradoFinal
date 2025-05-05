<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrestamoController extends Controller
{
    /**
     * Muestra la vista de préstamo con validación básica
     */
    public function index()
    {
        $usuario = auth()->user();
        $puedePrestar = false;
        $equipoDisponible = null;
        $grupoValido = null;
        $mensajeError = null;
        $usuarioAutenticado = auth()->user();
    
        // 1. Verificar si el usuario está en algún grupo
        $gruposUsuario = DB::table('Usuario_Grupo')
            ->join('Grupos', 'Usuario_Grupo.IdGrupo', '=', 'Grupos.IdGrupo')
            ->where('Usuario_Grupo.DocumentoId', $usuario->DocumentoId)
            ->get();
    
        if ($gruposUsuario->isEmpty()) {
            $mensajeError = 'No puedes realizar préstamos porque no estás en clase.';
        } else {
            // 2. Buscar un grupo que coincida con el horario actual
            foreach ($gruposUsuario as $grupo) {
                if ($this->validarHorarioGrupo($grupo)) {
                    $puedePrestar = true;
                    $grupoValido = $grupo;
                    break;
                }
            }
    
            // 3. Si puede prestar, verificar equipos disponibles
            if ($puedePrestar) {
                $equipoDisponible = DB::table('Equipos')
                    ->where('Estado', 'Disponible')
                    ->where('SalaMovil', $grupoValido->SalaMovil ?? null) // Filtra por sala específica
                    ->first();
    
                if (!$equipoDisponible) {
                    $mensajeError = 'No hay equipos disponibles en '.($grupoValido->SalaMovil ?? 'la sala asignada');
                    // Registro para depuración
                    \Log::info('No hay equipos disponibles', [
                        'sala_movil' => $grupoValido->SalaMovil ?? null,
                        'total_disponibles' => DB::table('Equipos')->where('Estado', 'Disponible')->count()
                    ]);
                    $puedePrestar = false;
                }
            } else {
                $mensajeError = 'No estás en horario de clase. Horario válido: ';
                foreach ($gruposUsuario as $grupo) {
                    $mensajeError .= $grupo->DiaSemana.' '.$grupo->HoraInicial.'-'.$grupo->HoraFinal.', ';
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
            'usuarioAutenticado'
        ));
    }
    /**
     * Procesa la solicitud de préstamo
     */
    public function realizarPrestamo(Request $request)
    {
        $usuario = auth()->user();

        // 1. Validar que esté en horario de clase
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
            return back()->with('error', 'No estás en horario de clase para realizar préstamos');
        }

        // 2. Verificar disponibilidad del equipo
        $equipo = DB::table('Equipos')
            ->where('Serial', $request->serial)
            ->where('Estado', 'Disponible')
            ->first();

        if (!$equipo) {
            return back()->with('error', 'El equipo seleccionado ya no está disponible');
        }

        // 3. Registrar el préstamo
        DB::beginTransaction();
        try {
            $idPrestamo = DB::table('Prestamos')->insertGetId([
                'Serial' => $request->serial,
                'ActivoFijo' => $request->activo_fijo,
                'GrupoId' => $grupoValido->IdGrupo,
                'DocumentoId' => $usuario->DocumentoId,
                'SalaMovil' => $grupoValido->SalaMovil ?? null,
                'FechaI' => Carbon::now()->toDateString(),
                'FechaF' => Carbon::now()->addHours($grupoValido->Duracion ?? 1)->toDateString(),
                'HoraI' => Carbon::now()->format('H:i:s'),
                'HoraF' => Carbon::now()->addHours($grupoValido->Duracion ?? 1)->format('H:i:s'),
                'Duracion' => $grupoValido->Duracion ?? 1
            ]);

            DB::table('Equipos')
                ->where('Serial', $request->serial)
                ->update(['Estado' => 'Prestado']);

            DB::commit();
            
            return back()->with('success', 'Préstamo realizado con éxito. Código: PR-'.$idPrestamo);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el préstamo: '.$e->getMessage());
        }
    }

    /**
     * Valida si el horario actual coincide con el grupo
     */
    private function validarHorarioGrupo($grupo)
    {
        $now = Carbon::now('America/Bogota');
        
        // 1. Verificar rango de fechas (formato Y-m-d)
        $fechaInicial = Carbon::parse($grupo->FechaInicial, 'America/Bogota')->startOfDay();
        $fechaFinal = Carbon::parse($grupo->FechaFinal, 'America/Bogota')->endOfDay();
        
        if (!$now->betweenIncluded($fechaInicial, $fechaFinal)) {
            \Log::debug('Fecha fuera de rango', [
                'now' => $now,
                'fecha_inicial' => $fechaInicial,
                'fecha_final' => $fechaFinal
            ]);
            return false;
        }
        
        // 2. Verificar día de la semana (asumiendo campo DiaSemana en Grupos)
        if (isset($grupo->DiaSemana)) {
            $diasMap = [
                'Lunes' => 1, 'Martes' => 2, 'Miercoles' => 3, 'Miércoles' => 3,
                'Jueves' => 4, 'Viernes' => 5, 'Sabado' => 6, 'Sábado' => 6,
                'Domingo' => 0
            ];
            
            $diaActual = $now->dayOfWeekIso; // 1-7 (Lunes-Domingo)
            $diaGrupo = $diasMap[$grupo->DiaSemana] ?? null;
            
            if ($diaGrupo === null || $diaActual != $diaGrupo) {
                \Log::debug('Día no coincide', [
                    'dia_actual' => $diaActual,
                    'dia_grupo' => $grupo->DiaSemana,
                    'mapeo' => $diaGrupo
                ]);
                return false;
            }
        }
        
        // 3. Verificar rango de horas
        try {
            $horaInicio = Carbon::parse($grupo->HoraInicial, 'America/Bogota');
            $horaFin = Carbon::parse($grupo->HoraFinal, 'America/Bogota');
            
            $horaActual = $now->copy()->setDate(2000, 1, 1); // Fecha arbitraria para comparar solo hora
            
            $valido = $horaActual->betweenIncluded(
                $horaInicio->setDate(2000, 1, 1),
                $horaFin->setDate(2000, 1, 1)
            );
            
            if (!$valido) {
                \Log::debug('Horario no válido', [
                    'hora_actual' => $horaActual->format('H:i:s'),
                    'hora_inicio' => $horaInicio->format('H:i:s'),
                    'hora_fin' => $horaFin->format('H:i:s')
                ]);
            }
            
            return $valido;
        } catch (\Exception $e) {
            \Log::error('Error al parsear horarios', [
                'error' => $e->getMessage(),
                'hora_inicial' => $grupo->HoraInicial,
                'hora_final' => $grupo->HoraFinal
            ]);
            return false;
        }
    }
}