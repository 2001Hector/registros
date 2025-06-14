<?php
session_start();
require_once 'conexionB.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];

    // Consulta para obtener el usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch();

    // Verificar si el usuario existe y si la contraseña es correcta
    if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
        $_SESSION['id_registrador'] = $usuario['id_registrador'];
        $_SESSION['nombre_usuario'] = $usuario['Nombre'];

        // Redirigir dependiendo del id_registrador
        if ($usuario['id_registrador'] == 3) {  // Si el id_registrador es 1
            header("Location: php/Upload.php"); // Ventana administrativa
        } else {
            header("Location: php/registro.php"); // Ventana de usuario regular
        }
        exit();
    } else {
        $error = "CorreoS electrónico o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styless.css">
    <title>Iniciar Sesión</title>
    <link rel="icon" href="imagenes/favicon.ico" type="image/x-icon">
    
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
 <i> <strong>Importante:Si eres estudiantes deseas ver tus proyecto en que se encuentra participes</strong></i>
  <p><a href="vistaEstudiante.php">Ver</a></p>
</div>
    </div>
    
</body>
</html>

