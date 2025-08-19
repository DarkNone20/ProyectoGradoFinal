<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="{{ asset('assets/style-Home.css') }}">


    <title>Home</title>
</head>

<body>
  
    <div class="Encabezado">
        <div class="Logo">
            <a href="https://www.icesi.edu.co/"> <!--<img src="{{ asset('Imagenes/Logo2.png') }}" alt="Logo">--> </a>
        </div>

        <div class="Titulo">
            <h1>Sistema de Prestamo de Portatiles</h1>
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
    </div>

    <div class="Principal">

        <div class="Principal-Uno">


            <div class="Imagenes"> <a href="{{ url('Prestamo') }}"><img src="{{ asset('Imagenes/Portatil2.png') }}"
                        alt="Logo"></a></div>
            <div>
                <h2>Prestamos</h2>
            </div>

        </div>

        <div class="Principal-Dos">

            <div class="Imagenes"><a href="{{ url('Devoluciones') }}"><img src="{{ asset('Imagenes/Devolucion3.png') }}" alt="Logo"></a></div>
            <div>
                <h2>Devoluciones</h2>
            </div>

        </div>


    </div>

    <div class="Boton">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" value="Salir">
            <img src="{{ asset('Imagenes/Cambio.png') }}" alt="Cerrar sesión">
        </button>
    </form>
</div>

</body>
<footer>
    <p></p>
</footer>

</html>
