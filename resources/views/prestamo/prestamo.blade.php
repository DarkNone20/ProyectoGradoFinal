<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('assets/style-Prestamo.css') }}">
    <title>Prestamo</title>
</head>

<body>

    <div class="Encabezado">
        <div class="Logo">
            <a href="https://www.icesi.edu.co/"> <img src="{{ asset('Imagenes/Logo2.png') }}" alt="Logo"></a>
        </div>

        <div class="Titulo">
            <h1>Sistema de Prestamo de Portatiles</h1>
        </div>

        <div class="Icono">
            <div class="Icono-Uno">
                <img src="{{ asset('Imagenes/Usuario.png') }}" alt="Usuario">
            </div>
            <div class="Icono-Dos">
                <p>{{ $usuarioAutenticado->Nombre ?? 'Invitado' }}</p>
            </div>
        </div>
    </div>

    <div class="Principal">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        
        @if($puedePrestar && $equipoDisponible)
            <div class="Principal-Uno">
                <form action="{{ route('prestamo.realizar') }}" method="POST">
                    @csrf
                    <table>
                        <tr><td><strong>Locker:</strong></td><td>{{ $equipoDisponible->SalaMovil ?? 'No especificado' }}</td></tr>
                        <tr><td><strong>Equipo:</strong></td><td>{{ $equipoDisponible->Marca }}</td></tr>
                        <tr><td><strong>Serial:</strong></td><td>{{ $equipoDisponible->Serial }}</td></tr>
                        <tr><td><strong>Modelo:</strong></td><td>{{ $equipoDisponible->Modelo }}</td></tr>
                        <tr><td><strong>Duración:</strong></td><td>{{ $grupoValido->Duracion ?? 1 }} horas</td></tr>
                        <tr><td><strong>Hora Inicial:</strong></td><td>{{ $grupoValido->HoraInicial }}</td></tr>
                        <tr><td><strong>Hora Final:</strong></td><td>{{ $grupoValido->HoraFinal }}</td></tr>
                    </table>
                    <input type="hidden" name="serial" value="{{ $equipoDisponible->Serial }}">
                    <input type="hidden" name="activo_fijo" value="{{ $equipoDisponible->ActivoFijo }}">
                    <button type="submit" class="btn-prestamo">Realizar Préstamo</button>
                </form>
            </div>
        @else
            <div class="alert alert-info">
                <h3>No puedes realizar préstamos en este momento</h3>
                <p>{{ $mensajeError }}</p>
                
                @if(isset($grupoValido))
                    <div style="margin-top: 15px; padding: 10px; background: #f8f9fa;">
                        <h4>Información del Grupo:</h4>
                        <p><strong>Curso:</strong> {{ $grupoValido->NombreCurso ?? 'No especificado' }}</p>
                        <p><strong>Profesor:</strong> {{ $grupoValido->NombreProfesor ?? 'No especificado' }}</p>
                        <p><strong>Horario:</strong> {{ $grupoValido->HoraInicial }} - {{ $grupoValido->HoraFinal }}</p>
                        <p><strong>Duración:</strong> {{ $grupoValido->Duracion ?? 1 }} horas</p>
                    </div>
                @endif
                
                <div style="margin-top: 15px; padding: 10px; background: #f8f9fa;">
                    <h4>Información del Sistema:</h4>
                    <p><strong>Hora actual:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                    <p><strong>Equipos disponibles:</strong> {{ DB::table('Equipos')->where('Estado', 'Disponible')->count() }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="Boton">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">
                <img src="{{ asset('Imagenes/Cambio.png') }}" alt="Salir">
            </button>
        </form>
    </div>

</body>
<footer>
    <p></p>
</footer>

</html>