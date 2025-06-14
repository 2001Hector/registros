<?php
// editar_proyecto.php
require_once '../conexionB.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_registrador'])) {
    header("Location: login.php");
    exit;
}

$usuarioId = $_SESSION['id_registrador'];
$errors = [];
$success = '';

// Obtener el ID del proyecto a editar
if (!isset($_GET['id'])) {
    header("Location: registro.php");
    exit;
}
$id_proyecto = $_GET['id'];

// Verificar que el proyecto pertenece al usuario
$stmt = $conn->prepare("SELECT * FROM registro WHERE id_registro = ? AND id_registrador = ?");
$stmt->bind_param("ii", $id_proyecto, $usuarioId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Proyecto no encontrado o no tienes permiso para editarlo";
    header("Location: registro.php");
    exit;
}

$proyecto = $result->fetch_assoc();

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_edicion'])) {
    $ano = $_POST['ano'];
    $docentes = $_POST['docentes'];
    $estudiantes = $_POST['estudiantes'];
    $ids_estudiantes = isset($_POST['ids_estudiantes']) ? $_POST['ids_estudiantes'] : '';
    $id_programa = $_POST['id_programa'];
    $id_tipo = $_POST['id_tipo'];
    $modalidad = $_POST['modalidad'];
    $nom_proyecto = $_POST['nom_proyecto'];

    // Validaciones
    if (empty($ano))
        $errors[] = "* Agregar el año del proyecto";
    if (empty($docentes))
        $errors[] = "* Agregar los docentes del proyecto";
    if (empty($estudiantes))
        $errors[] = "* Agregar los estudiantes del proyecto";
    if (empty($id_programa))
        $errors[] = "* Seleccionar el programa";
    if (empty($id_tipo))
        $errors[] = "* Seleccionar el tipo de proyecto";
    if (empty($modalidad))
        $errors[] = "* Seleccionar modalidad de proyecto";
    if (empty($nom_proyecto))
        $errors[] = "* Agregar el nombre del proyecto";

    if (empty($errors)) {
        $sql = "UPDATE registro 
                SET ano = ?, 
                    docentes = ?, 
                    estudiantes = ?, 
                    ids_estudiantes = ?, 
                    id_programa = ?, 
                    id_tipo = ?, 
                    modalidad = ?, 
                    nom_proyecto = ? 
                WHERE id_registro = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssi",
            $ano,
            $docentes,
            $estudiantes,
            $ids_estudiantes,
            $id_programa,
            $id_tipo,
            $modalidad,
            $nom_proyecto,
            $id_proyecto
        );

        if ($stmt->execute()) {
            // Redirige automáticamente a registro.php luego de actualizar
            header("Location: ../registro.php");
            exit;
        } else {
            $errors[] = "Error al actualizar el proyecto: " . $conn->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Proyecto</title>
    <link rel="icon" href="../../imagenes/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../css/style_reg_usuar.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            color: #2d3748;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            border-bottom: 2px solid #4a5568;
            padding-bottom: 0.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #4a5568;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
        }

        .btn {
            background-color: #4299e1;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s;
            display: block;
            width: 100%;
            margin-top: 1rem;
        }

        .btn:hover {
            background-color: #3182ce;
        }

        .btn-cancel {
            background-color: #e53e3e;
        }

        .btn-cancel:hover {
            background-color: #c53030;
        }

        .error {
            color: #e53e3e;
            background-color: #fed7d7;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .success {
            color: #38a169;
            background-color: #c6f6d5;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .flex-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>
    <header>
        <img src="../../imagenes/logo.ico" alt="Logo"
            style="width: 150px; height: 150px; object-fit: cover; border-radius: 20px;">
    </header>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p class="error"><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php endif; ?>

    <div class="form-container">
        <h2>EDITAR PROYECTO</h2>
        <form action="editar_proyecto.php?id=<?= $id_proyecto ?>" method="post">
            <input type="hidden" name="id_registrador" value="<?= htmlspecialchars($usuarioId) ?>">

            <label for="docentes">Docentes:</label>
            <input type="text" id="docentes" name="docentes" value="<?= htmlspecialchars($proyecto['docentes']) ?>"
                required>

            <label for="estudiantes">Estudiantes:</label>
            <textarea id="estudiantes" name="estudiantes"
                required><?= htmlspecialchars($proyecto['estudiantes']) ?></textarea>

            <label for="ids_estudiantes">IDs de Estudiantes (separados por comas):</label>
            <input type="text" id="ids_estudiantes" name="ids_estudiantes"
                value="<?= htmlspecialchars($proyecto['ids_estudiantes']) ?>" placeholder="Ej: 12345, 67890, 54321">

            <label for="ano">Fecha:</label>
            <input type="date" id="ano" name="ano" value="<?= htmlspecialchars($proyecto['ano']) ?>" required>

            <label for="id_tipo">Tipo de Proyecto:</label>
            <select id="id_tipo" name="id_tipo" required>
               <option value="Proyectos de investigación básica" <?= $proyecto['id_tipo'] == 'Proyectos de investigación básica' ? 'selected' : '' ?>>Proyectos de investigación básica</option>
<option value="Proyectos de investigación aplicada" <?= $proyecto['id_tipo'] == 'Proyectos de investigación aplicada' ? 'selected' : '' ?>>Proyectos de investigación aplicada</option>
<option value="Proyectos de desarrollo tecnológico" <?= $proyecto['id_tipo'] == 'Proyectos de desarrollo tecnológico' ? 'selected' : '' ?>>Proyectos de desarrollo tecnológico</option>
<option value="Proyectos de innovación educativa" <?= $proyecto['id_tipo'] == 'Proyectos de innovación educativa' ? 'selected' : '' ?>>Proyectos de innovación educativa</option>
<option value="Proyectos interdisciplinarios" <?= $proyecto['id_tipo'] == 'Proyectos interdisciplinarios' ? 'selected' : '' ?>>Proyectos interdisciplinarios</option>
<option value="Proyectos de cooperación internacional" <?= $proyecto['id_tipo'] == 'Proyectos de cooperación internacional' ? 'selected' : '' ?>>Proyectos de cooperación internacional</option>
<option value="Proyectos de sostenibilidad ambiental" <?= $proyecto['id_tipo'] == 'Proyectos de sostenibilidad ambiental' ? 'selected' : '' ?>>Proyectos de sostenibilidad ambiental</option>
<option value="Proyectos de impacto social" <?= $proyecto['id_tipo'] == 'Proyectos de impacto social' ? 'selected' : '' ?>>Proyectos de impacto social</option>
<option value="Proyectos de emprendimiento" <?= $proyecto['id_tipo'] == 'Proyectos de emprendimiento' ? 'selected' : '' ?>>Proyectos de emprendimiento</option>
<option value="Proyectos de arte y cultura" <?= $proyecto['id_tipo'] == 'Proyectos de arte y cultura' ? 'selected' : '' ?>>Proyectos de arte y cultura</option>
<option value="Proyectos de salud pública" <?= $proyecto['id_tipo'] == 'Proyectos de salud pública' ? 'selected' : '' ?>>Proyectos de salud pública</option>
<option value="Proyectos de desarrollo rural" <?= $proyecto['id_tipo'] == 'Proyectos de desarrollo rural' ? 'selected' : '' ?>>Proyectos de desarrollo rural</option>
<option value="Proyectos de inteligencia artificial" <?= $proyecto['id_tipo'] == 'Proyectos de inteligencia artificial' ? 'selected' : '' ?>>Proyectos de inteligencia artificial</option>
<option value="Proyectos de realidad virtual" <?= $proyecto['id_tipo'] == 'Proyectos de realidad virtual' ? 'selected' : '' ?>>Proyectos de realidad virtual</option>
<option value="Proyectos de energías renovables" <?= $proyecto['id_tipo'] == 'Proyectos de energías renovables' ? 'selected' : '' ?>>Proyectos de energías renovables</option>
            </select>

            <label for="nom_proyecto">Nombre del Proyecto:</label>
            <input type="text" id="nom_proyecto" name="nom_proyecto"
                value="<?= htmlspecialchars($proyecto['nom_proyecto']) ?>" required>

            <label for="id_programa">Programa:</label>
            <select id="id_programa" name="id_programa" required>
                <option value="">Seleccione una opción</option>
            <option value="Facultad de Ciencias Económicas y Administrativas" <?= $proyecto['id_programa'] == 'Facultad de Ciencias Económicas y Administrativas' ? 'selected' : '' ?>>Facultad de Ciencias Económicas y Administrativas</option>
<option value="Facultad de Ciencias Sociales y Humanas" <?= $proyecto['id_programa'] == 'Facultad de Ciencias Sociales y Humanas' ? 'selected' : '' ?>>Facultad de Ciencias Sociales y Humanas</option>
<option value="Facultad de Ciencias de la Educación" <?= $proyecto['id_programa'] == 'Facultad de Ciencias de la Educación' ? 'selected' : '' ?>>Facultad de Ciencias de la Educación</option>
<option value="Facultad de Ingeniería en Sistemas" <?= $proyecto['id_programa'] == 'Facultad de Ingeniería en Sistemas' ? 'selected' : '' ?>>Facultad de Ingeniería en Sistemas</option>
<option value="Facultad de Medicina y Ciencias de la Salud" <?= $proyecto['id_programa'] == 'Facultad de Medicina y Ciencias de la Salud' ? 'selected' : '' ?>>Facultad de Medicina y Ciencias de la Salud</option>
<option value="Facultad de Derecho y Ciencias Políticas" <?= $proyecto['id_programa'] == 'Facultad de Derecho y Ciencias Políticas' ? 'selected' : '' ?>>Facultad de Derecho y Ciencias Políticas</option>
<option value="Facultad de Arquitectura y Diseño" <?= $proyecto['id_programa'] == 'Facultad de Arquitectura y Diseño' ? 'selected' : '' ?>>Facultad de Arquitectura y Diseño</option>
<option value="Facultad de Ingeniería Civil y Ambiental" <?= $proyecto['id_programa'] == 'Facultad de Ingeniería Civil y Ambiental' ? 'selected' : '' ?>>Facultad de Ingeniería Civil y Ambiental</option>
<option value="Facultad de Artes Visuales y Escénicas" <?= $proyecto['id_programa'] == 'Facultad de Artes Visuales y Escénicas' ? 'selected' : '' ?>>Facultad de Artes Visuales y Escénicas</option>
<option value="Facultad de Ciencias Agrarias y Veterinaria" <?= $proyecto['id_programa'] == 'Facultad de Ciencias Agrarias y Veterinaria' ? 'selected' : '' ?>>Facultad de Ciencias Agrarias y Veterinaria</option>
<option value="Facultad de Psicología y Neurociencias" <?= $proyecto['id_programa'] == 'Facultad de Psicología y Neurociencias' ? 'selected' : '' ?>>Facultad de Psicología y Neurociencias</option>
<option value="Facultad de Química y Farmacia" <?= $proyecto['id_programa'] == 'Facultad de Química y Farmacia' ? 'selected' : '' ?>>Facultad de Química y Farmacia</option>
<option value="Facultad de Ingeniería Electrónica y Telecomunicaciones" <?= $proyecto['id_programa'] == 'Facultad de Ingeniería Electrónica y Telecomunicaciones' ? 'selected' : '' ?>>Facultad de Ingeniería Electrónica y Telecomunicaciones</option>
<option value="Facultad de Matemáticas y Ciencias Físicas" <?= $proyecto['id_programa'] == 'Facultad de Matemáticas y Ciencias Físicas' ? 'selected' : '' ?>>Facultad de Matemáticas y Ciencias Físicas</option>
<option value="Facultad de Lenguas y Filología" <?= $proyecto['id_programa'] == 'Facultad de Lenguas y Filología' ? 'selected' : '' ?>>Facultad de Lenguas y Filología</option>
<option value="Facultad de Negocios Internacionales" <?= $proyecto['id_programa'] == 'Facultad de Negocios Internacionales' ? 'selected' : '' ?>>Facultad de Negocios Internacionales</option>
<option value="Facultad de Ciencias del Mar" <?= $proyecto['id_programa'] == 'Facultad de Ciencias del Mar' ? 'selected' : '' ?>>Facultad de Ciencias del Mar</option>
<option value="Facultad de Ingeniería Industrial" <?= $proyecto['id_programa'] == 'Facultad de Ingeniería Industrial' ? 'selected' : '' ?>>Facultad de Ingeniería Industrial</option>
<option value="Facultad de Música y Producción Audiovisual" <?= $proyecto['id_programa'] == 'Facultad de Música y Producción Audiovisual' ? 'selected' : '' ?>>Facultad de Música y Producción Audiovisual</option>

            </select>

            <label for="modalidad">Modalidad:</label>
            <select id="modalidad" name="modalidad" required>
                <option value="">Seleccione una modalidad</option>
<option value="Modalidad de desempeño académico" <?= $proyecto['modalidad'] == 'Modalidad de desempeño académico' ? 'selected' : '' ?>>Modalidad de desempeño académico</option>
<option value="Modalidad de prácticas empresariales" <?= $proyecto['modalidad'] == 'Modalidad de prácticas empresariales' ? 'selected' : '' ?>>Modalidad de prácticas empresariales</option>
<option value="Modalidad de pasantías" <?= $proyecto['modalidad'] == 'Modalidad de pasantías' ? 'selected' : '' ?>>Modalidad de pasantías</option>
<option value="Modalidad de trabajo de investigación" <?= $proyecto['modalidad'] == 'Modalidad de trabajo de investigación' ? 'selected' : '' ?>>Modalidad de trabajo de investigación</option>
<option value="Modalidad de semestre de posgrado" <?= $proyecto['modalidad'] == 'Modalidad de semestre de posgrado' ? 'selected' : '' ?>>Modalidad de semestre de posgrado</option>
<option value="Modalidad de investigación básica" <?= $proyecto['modalidad'] == 'Modalidad de investigación básica' ? 'selected' : '' ?>>Modalidad de investigación básica</option>
<option value="Modalidad de investigación aplicada" <?= $proyecto['modalidad'] == 'Modalidad de investigación aplicada' ? 'selected' : '' ?>>Modalidad de investigación aplicada</option>
<option value="Modalidad de desarrollo tecnológico" <?= $proyecto['modalidad'] == 'Modalidad de desarrollo tecnológico' ? 'selected' : '' ?>>Modalidad de desarrollo tecnológico</option>
<option value="Modalidad de innovación educativa" <?= $proyecto['modalidad'] == 'Modalidad de innovación educativa' ? 'selected' : '' ?>>Modalidad de innovación educativa</option>
<option value="Modalidad de proyectos interdisciplinarios" <?= $proyecto['modalidad'] == 'Modalidad de proyectos interdisciplinarios' ? 'selected' : '' ?>>Modalidad de proyectos interdisciplinarios</option>
<option value="Modalidad de cooperación internacional" <?= $proyecto['modalidad'] == 'Modalidad de cooperación internacional' ? 'selected' : '' ?>>Modalidad de cooperación internacional</option>
<option value="Modalidad de sostenibilidad ambiental" <?= $proyecto['modalidad'] == 'Modalidad de sostenibilidad ambiental' ? 'selected' : '' ?>>Modalidad de sostenibilidad ambiental</option>
<option value="Modalidad de impacto social" <?= $proyecto['modalidad'] == 'Modalidad de impacto social' ? 'selected' : '' ?>>Modalidad de impacto social</option>
<option value="Modalidad de emprendimiento" <?= $proyecto['modalidad'] == 'Modalidad de emprendimiento' ? 'selected' : '' ?>>Modalidad de emprendimiento</option>
<option value="Modalidad de arte y cultura" <?= $proyecto['modalidad'] == 'Modalidad de arte y cultura' ? 'selected' : '' ?>>Modalidad de arte y cultura</option>
<option value="Modalidad de salud pública" <?= $proyecto['modalidad'] == 'Modalidad de salud pública' ? 'selected' : '' ?>>Modalidad de salud pública</option>
<option value="Modalidad de desarrollo rural" <?= $proyecto['modalidad'] == 'Modalidad de desarrollo rural' ? 'selected' : '' ?>>Modalidad de desarrollo rural</option>
<option value="Modalidad de inteligencia artificial" <?= $proyecto['modalidad'] == 'Modalidad de inteligencia artificial' ? 'selected' : '' ?>>Modalidad de inteligencia artificial</option>
<option value="Modalidad de realidad virtual" <?= $proyecto['modalidad'] == 'Modalidad de realidad virtual' ? 'selected' : '' ?>>Modalidad de realidad virtual</option>
<option value="Modalidad de energías renovables" <?= $proyecto['modalidad'] == 'Modalidad de energías renovables' ? 'selected' : '' ?>>Modalidad de energías renovables</option>


            </select>

            <div class="flex-buttons">
                <input type="submit" name="submit_edicion" value="Actualizar Proyecto" class="btn">
                <a href="../registro.php" class="btn btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>
</body>

</html>