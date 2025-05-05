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
            @if($errors->any())
                <div style="color: red; margin-bottom: 15px;">
                    {{ $errors->first('loginError') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="LogoI"><img src="{{ asset('Imagenes/Logo.jpg') }}" alt="Logo"></div>
                <label for="DocumentoId"></label>
                <input type="text" name="DocumentoId" id="DocumentoId" class="control" 
                       value="{{ old('DocumentoId') }}" placeholder="Usuario" required autofocus>
                <br>
                <label for="password"></label>
                <input type="password" name="password" id="password" class="control" placeholder="Contraseña" required>
                <br>
                <button type="submit">Iniciar sesión</button>
            </form>
        </div>
    </div>
</body>
</html>