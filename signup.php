<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once '/php/conexionB.php';
require_once 'envioC.php'; // Incluye tu archivo con PHPMailer

function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error de validación CSRF");
    }

    $nombre = trim($_POST['nombre']);
    $sexo = $_POST['sexo'];
    $estatuto = trim($_POST['estatuto']);
    $contraseña = $_POST['contraseña'];
    $correo = trim($_POST['correo']);

    // Validación
    if (empty($nombre) || empty($sexo) || empty($estatuto) || empty($contraseña) || empty($correo)) {
        $error = "Todos los campos son obligatorios";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Formato de correo electrónico inválido";
    } elseif (strlen($contraseña) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres";
    } else {
        try {
            // Verificar si el correo ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Este correo electrónico ya está registrado";
            } else {
                // Hash de la contraseña
                $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);

                // Insertar nuevo usuario
                $stmt = $pdo->prepare("INSERT INTO usuarios (Nombre, sexo, estatuto, contraseña, correo) VALUES (?, ?, ?, ?, ?)");
                $result = $stmt->execute([$nombre, $sexo, $estatuto, $contraseña_hash, $correo]);

                if ($result) {
                    $success = "Usuario registrado con éxito , Te enviaremos una guía a tu correo para que aprendas a usar el software fácilmente..";

                    // Enviar correo de confirmación
                   enviarCorreoPrueba($correo, $nombre, $sexo, $estatuto);
                    
                } else {
                    $error = "Error al registrar el usuario. Por favor, inténtelo de nuevo.";
                }
            }
        } catch (PDOException $e) {
            $error = "Ha ocurrido un error. Por favor, inténtelo de nuevo más tarde.";
            // error_log("Error en registro: " . $e->getMessage());
        }
    }
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="icon" href="imagenes/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style_signup.css">
</head>

<body>
    <div class="contenedor-formulario">
        <h2>Registro de Usuario</h2>
        <?php
        if ($error)
            echo "<p class='error'>$error</p>";
        if ($success)
            echo "<p class='success'>$success</p>";
        ?>
        <form method="post" action="" id="registroForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="campo animar derecha">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre ?? ''); ?>" required>
            </div>
            <div class="campo animar izquierda">
                <label for="sexo">Sexo:</label>
                <select id="sexo" name="sexo" required>
                    <option value="">Seleccione</option>
                    <option value="Masculino" <?php echo (isset($sexo) && $sexo == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                    <option value="Femenino" <?php echo (isset($sexo) && $sexo == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                    <option value="Otro" <?php echo (isset($sexo) && $sexo == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                </select>
            </div>
            <div class="campo animar derecha">
                <label for="estatuto">Estatuto:</label>
                <select id="estatuto" name="estatuto" required>
                    <option value="">Seleccione</option>
                    <option value="Docente" <?php echo (isset($estatuto) && $estatuto == 'Docente') ? 'selected' : ''; ?>>Docente</option>
                   
                </select>
            </div>
            <div class="campo animar izquierda">
                <label for="correo">Correo electrónico:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo ?? ''); ?>" required placeholder="usuario@uniguajira.edu.co">
            </div>
            <div class="campo animar derecha">
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" required minlength="8">
            </div>
            <div class="campo animar izquierda">
                <input type="submit" value="Registrarse">
            </div>
        </form>
        <p>¿Ya tienes una cuenta? <a href="index.php">Inicia sesión aquí</a></p>
        <br>
        <div style="background-color: #f0f4ff; border-left: 4px solid #3b82f6; padding: 10px 15px; margin-top: 15px; font-size: 0.9em; color: #1e3a8a;">
  <strong>Importante:</strong> Si no ves nuestro mensaje en tu bandeja de entrada, revisa la carpeta de <em>spam</em> o <em>correo no deseado</em>. Te enviaremos una guía para que aprendas a usar el software fácilmente.
</div>

    </div>
    <script>
        document.getElementById('registroForm').addEventListener('submit', function (e) {
            var password = document.getElementById('contraseña').value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                return;
            }
        });
    </script>
   

</body>
</html>