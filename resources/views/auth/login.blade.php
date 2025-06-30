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
            @if ($errors->any())
                <div style="color: red; margin-bottom: 15px;">
                    {{ $errors->first('loginError') }}
                </div>
            @endif

            <form id="loginForm">
                @csrf
                <!-- Campo CSRF visible para JS -->
                <input type="hidden" name="_token" id="csrf-token" value="{{ csrf_token() }}">
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
            <div id="error-message" style="color: red; margin-bottom: 15px;"></div>

            <script>
                document.getElementById('loginForm').addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const DocumentoId = this.DocumentoId.value;
                    const password = this.password.value;
                    const csrfToken = document.getElementById('csrf-token').value;

                    // Llama a la API de login enviando el token CSRF en headers
                    const response = await fetch('/api/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken // CSRF token en el header
                        },
                        credentials: 'same-origin', // ¡IMPORTANTE! Enviar cookie de sesión
                        body: JSON.stringify({
                            DocumentoId,
                            password
                        })
                    });

                    let data;
                    try {
                        data = await response.json();
                    } catch (e) {
                        data = {};
                    }

                    if (response.ok) {
                        // Login exitoso: redirige al usuario
                        window.location.href = '/home';
                    } else {
                        // Muestra el error de la API
                        document.getElementById('error-message').textContent = data.message ||
                            'Usuario o contraseña incorrectos';
                    }
                });
            </script>

        </div>
    </div>
</body>

</html>
