<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('assets/style-Prestamo.css') }}">
    <title>Devolución de Equipos</title>
</head>

<body>

    <div class="Encabezado">
        <div class="Logo">
            <a href="https://www.icesi.edu.co/"> <img src="{{ asset('Imagenes/Logo2.png') }}" alt="Logo"></a>
        </div>

        <div class="Titulo">
            <h1>Sistema de Devolución de Portátiles</h1>
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
        
        @if($prestamosActivos->isNotEmpty())
            @foreach($prestamosActivos as $prestamo)
                <div class="Principal-Uno">
                    <form action="{{ route('devolucion.procesar') }}" method="POST">
                        @csrf
                        <table>
                            <tr><td><strong>Equipo:</strong></td><td>{{ $prestamo->Marca }}</td></tr>
                            <tr><td><strong>Serial:</strong></td><td>{{ $prestamo->Serial }}</td></tr>
                            <tr><td><strong>Modelo:</strong></td><td>{{ $prestamo->Modelo }}</td></tr>
                            <tr><td><strong>Fecha Préstamo:</strong></td><td>{{ $prestamo->FechaI }}</td></tr>
                            <tr><td><strong>Hora Préstamo:</strong></td><td>{{ $prestamo->HoraI }}</td></tr>
                            <tr><td><strong>Fecha Vencimiento:</strong></td><td>{{ $prestamo->FechaF }}</td></tr>
                            <tr><td><strong>Hora Vencimiento:</strong></td><td>{{ $prestamo->HoraF }}</td></tr>
                            <tr>
                                <td><strong>Observaciones:</strong></td>
                                <td><textarea name="observaciones" rows="2" style="width: 100%"></textarea></td>
                            </tr>
                        </table>
                        <input type="hidden" name="id_prestamo" value="{{ $prestamo->IdPrestamo }}">
                        <button type="submit" class="btn-prestamo">Realizar Devolución</button>
                    </form>
                </div>
            @endforeach
        @else
            <div class="alert alert-info">
                <h3>No tienes equipos pendientes por devolver</h3>
                <p>Actualmente no tienes ningún préstamo activo en el sistema.</p>
            </div>
        @endif
    </div>

    <div class="Boton">
        <a href="{{ url('/home') }}"><button type="submit" value="Salir"><img
                    src="{{ asset('Imagenes/Cambio.png') }}" alt="Logo"></button> </a>
    </div>

</body>
<footer>
    <p></p>
</footer>

</html>