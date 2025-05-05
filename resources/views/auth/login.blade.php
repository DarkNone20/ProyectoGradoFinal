<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('assets/prueba.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="Formulario">
        <div class="group">
            <div id="error-message" style="color: red; display: none;"></div>
            
            <form id="loginForm">
                <div class="LogoI"><img src="{{ asset('Imagenes/Logo.jpg') }}" alt="Logo"></div>
                <label for="DocumentoId"></label>
                <input type="text" name="DocumentoId" id="DocumentoId" class="control" placeholder="Usuario" required>
                <br>
                <label for="password"></label>
                <input type="password" name="password" id="password" class="control" placeholder="password" required>
                <br>
                <button type="submit">Iniciar sesión</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const errorElement = document.getElementById('error-message');
            errorElement.style.display = 'none';
            
            const formData = {
                DocumentoId: document.getElementById('DocumentoId').value,
                password: document.getElementById('password').value
            };

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Error en la autenticación');
                }

                // Guardar el token en localStorage
                localStorage.setItem('auth_token', data.token);
                
                // Redirigir al dashboard
                window.location.href = '/home';
                
            } catch (error) {
                errorElement.textContent = error.message;
                errorElement.style.display = 'block';
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>