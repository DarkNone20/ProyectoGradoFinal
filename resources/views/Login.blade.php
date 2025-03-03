<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('assets/prueba.css') }}">
</head>
<body>
    <div class="Formulario">
        <div class="group">
            @if ($errors->has('loginError'))
                <p style="color: red;">{{ $errors->first('loginError') }}</p>
            @endif
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="LogoI"><img src="{{ asset('Imagenes/Logo.jpg') }}" alt="Logo"></div>
                <label for="DocumentoId"></label>
                <input type="text" name="DocumentoId" class="control" placeholder="Usuario" required>
                <br>
                <label for="password"></label>
                <input type="password" name="password" class="control" placeholder="password" required>
                <br>
                <button type="submit">Iniciar sesión</button>
            </form>
        </div>
    </div>
</body>
</html>