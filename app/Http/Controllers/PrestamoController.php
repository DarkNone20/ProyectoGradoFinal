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
                    ->first();

                if (!$equipoDisponible) {
                    $mensajeError = 'No hay equipos disponibles.';
                    $puedePrestar = false;
                }
            } else {
                $mensajeError = 'No estás en horario de clase.';
            }
        }

        return view('prestamo/prestamo', compact(
            'usuario',
            'puedePrestar',
            'equipoDisponible',
            'grupoValido',
            'mensajeError'
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
        $now = Carbon::now();
        
        // 1. Verificar día de la semana (0=domingo, 6=sábado)
        $diaClase = Carbon::parse($grupo->FechaInicial)->dayOfWeek;
        if ($now->dayOfWeek != $diaClase) {
            return false;
        }
        
        // 2. Verificar rango de fechas (incluyendo los días límite)
        if (!$now->betweenIncluded(Carbon::parse($grupo->FechaInicial), Carbon::parse($grupo->FechaFinal))) {
            return false;
        }
        
        // 3. Verificar rango de horas (comparación directa de strings)
        $horaActual = $now->format('H:i:s');
        return $horaActual >= $grupo->HoraInicial && $horaActual <= $grupo->HoraFinal;
    }
}