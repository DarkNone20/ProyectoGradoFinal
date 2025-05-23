<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="{{ asset('assets/style-Renovacion.css') }}">


    <title>Renovacion</title>
</head>

<body>

    <div class="Encabezado">
        <div class="Logo">
            <a href="https://www.icesi.edu.co/"> <img src="{{ asset('Imagenes/Logo2.png') }}" alt="Logo"></a>
        </div>

        <div class="Titulo">
            <h1>Sistema de Renovacion de Portatiles</h1>
        </div>

        <div class="Icono">
            <div class="Icono-Uno">
                <img src="{{ asset('Imagenes/Usuario.png') }}" alt="Logo">
            </div>
            <div class="Icono-Dos">
                <p>{{ $usuarioAutenticado->Nombre ?? 'Invitado' }}</p>
            </div>
        </div>
    </div>

    <div class="Principal">

        <div class="Principal-Uno">


            <div class="Imagenes"> <a href="#"><img src="{{ asset('Imagenes/Reno.png') }}" alt="Logo"></a>
            </div>
            <div>
                <h2>Renovacion</h2>
            </div>

        </div>




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
