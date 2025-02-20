<?php
session_start();

// Configuración de la base de datos
$servername = "localhost";
$username = "tu_usuario";
$password = "tu_contraseña";
$dbname = "nombre_de_tu_base_de_datos";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener datos del formulario
$usuario = $_POST['Usuario'];
$password = $_POST['Password'];

// Consulta SQL para verificar el usuario
$sql = "SELECT * FROM Usuarios WHERE DocumentoId = '$usuario'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Usuario encontrado, verificar contraseña
    $row = $result->fetch_assoc();
    if ($password === $row['DocumentoId']) { // Aquí deberías usar un método seguro para verificar contraseñas, como password_verify() si usas hash
        // Iniciar sesión
        $_SESSION['loggedin'] = true;
        $_SESSION['DocumentoId'] = $usuario;
        $_SESSION['Nombre'] = $row['Nombre'];
        
        // Redirigir al usuario a la página de inicio
        header("Location: inicio.php");
    } else {
        echo "Contraseña incorrecta.";
    }
} else {
    echo "Usuario no encontrado.";
}

$conn->close();
?>