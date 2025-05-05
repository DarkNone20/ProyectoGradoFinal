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
                        <tr><td><strong>Locker:</strong></td><td>{{ $equipoDisponible->SalaMovil }}</td></tr>
                        <tr><td><strong>Equipo:</strong></td><td>{{ $equipoDisponible->Marca }}</td></tr>
                        <tr><td><strong>Serial:</strong></td><td>{{ $equipoDisponible->Serial }}</td></tr>
                        <tr><td><strong>Modelo:</strong></td><td>{{ $equipoDisponible->Modelo }}</td></tr>
                        <tr><td><strong>Duración:</strong></td><td>{{ $grupoUsuario->Duracion ?? 1 }} horas</td></tr>
                        <tr><td><strong>Hora Inicial:</strong></td><td>{{ $grupoUsuario->HoraInicial }}</td></tr>
                        <tr><td><strong>Hora Final:</strong></td><td>{{ $grupoUsuario->HoraFinal }}</td></tr>
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
            </div>
        @endif
    </div>

    <div class="Boton">
        <a href="{{ url('/home') }}"><button type="button"><img src="{{ asset('Imagenes/Cambio.png') }}" alt="Salir"></button></a>
    </div>

</body>
<footer>
    <p></p>
</footer>

</html>