<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'php/conexionB.php';

$conn = $GLOBALS['conn']; // Usa la conexión global definida en conexionB.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];

    // Consulta segura con MySQLi
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    // Verificar si el usuario existe y la contraseña es correcta
    if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
        $_SESSION['id_registrador'] = $usuario['id_registrador'];
        $_SESSION['nombre_usuario'] = $usuario['Nombre'];

        // Redirigir según el tipo de usuario
        if ($usuario['id_registrador'] == 3) {
            header("Location: php/upload.php"); // Administrador
        } else {
            header("Location: php/registro.php"); // Usuario común
        }
        exit();
    } else {
        $error = "Correo electrónico o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/styless.css">
    <link rel="icon" href="imagenes/favicon.ico" type="image/x-icon">
      <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="contenedor-formulario">
        <img src="imagenes/logo.ico" alt="Logo de la empresa" width="200" height="auto">
        <h1>Iniciar Sesión</h1>
        
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <form method="post" action="">
            <div>
                <label for="correo">Correo electrónico:</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            <div>
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" required>
            </div>
            <div>
                <input type="submit" value="Iniciar Sesión">
            </div>
        </form>

        <p>¿No tienes una cuenta? <a href="signup.php">Regístrate aquí</a></p>

        <div style="background-color: #f0f4ff; border-left: 4px solid #3b82f6; padding: 10px 15px; margin-top: 15px; font-size: 0.9em; color: #1e3a8a;">
            <i><strong>Importante: Si eres estudiante y deseas ver tus proyectos en los que participas</strong></i>
            <p><a href="vistaEstudiante.php">Ver</a></p>
        </div>
    </div>
</body>
</html>
