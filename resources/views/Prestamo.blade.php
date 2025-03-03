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
                <p>JJCASTILLO</p>
            </div>
        </div>
    </div>

    <div class="Principal">
       
            <div class="Principal-Uno">

              
                <table>
                    <tr><td><strong>Locker:</strong></td></tr>
                    <tr><td><strong>Equipo:</strong></td></tr>
                    <tr><td><strong>Serial:</strong></td></tr>
                    <tr><td><strong>Modelo:</strong></td></tr>
                    <tr><td><strong>Duración:</strong></td></tr>
                    <tr><td><strong>Hora Inicial:</strong></td></tr>
                    <tr><td><strong>Hora Final:</strong></td></tr>
                </table>
            </div>
        
    </div>

    <div class="Boton">
        <a href="{{ url('/') }}"><button type="button"><img src="{{ asset('Imagenes/Cambio.png') }}" alt="Salir"></button></a>
    </div>

</body>
<footer>
    <p></p>
</footer>

</html>
