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
                <option value="">Seleccione una opción</option>
                <option value="Con opción a grado" <?= $proyecto['id_tipo'] == 'Con opción a grado' ? 'selected' : '' ?>>
                    Con opción a grado</option>
                <option value="Convocatoria interna" <?= $proyecto['id_tipo'] == 'Convocatoria interna' ? 'selected' : '' ?>>Convocatoria interna</option>
                <option value="Convocatoria externa" <?= $proyecto['id_tipo'] == 'Convocatoria externa' ? 'selected' : '' ?>>Convocatoria externa</option>
                <option value="Convocatoria de creación de semilleros" <?= $proyecto['id_tipo'] == 'Convocatoria de creación de semilleros' ? 'selected' : '' ?>>Convocatoria de creación de semilleros</option>
                <option value="Convocatoria de financiación de semilleros" <?= $proyecto['id_tipo'] == 'Convocatoria de financiación de semilleros' ? 'selected' : '' ?>>Convocatoria de financiación de semilleros</option>
                <option value="Convocatoria circulares" <?= $proyecto['id_tipo'] == 'Convocatoria circulares' ? 'selected' : '' ?>>Convocatoria circulares</option>
            </select>

            <label for="nom_proyecto">Nombre del Proyecto:</label>
            <input type="text" id="nom_proyecto" name="nom_proyecto"
                value="<?= htmlspecialchars($proyecto['nom_proyecto']) ?>" required>

            <label for="id_programa">Programa:</label>
            <select id="id_programa" name="id_programa" required>
                <option value="">Seleccione una opción</option>
                <option value="Facultad de Ciencias Económicas y Administrativas" <?= $proyecto['id_programa'] == 'Facultad de Ciencias Económicas y Administrativas' ? 'selected' : '' ?>>Facultad de Ciencias Económicas y
                    Administrativas</option>
                <option value="Facultad de Ciencias Sociales y Humanas" <?= $proyecto['id_programa'] == 'Facultad de Ciencias Sociales y Humanas' ? 'selected' : '' ?>>Facultad de Ciencias Sociales y Humanas</option>
                <option value="Facultad de Ciencias de la Educación" <?= $proyecto['id_programa'] == 'Facultad de Ciencias de la Educación' ? 'selected' : '' ?>>Facultad de Ciencias de la Educación</option>
                <option value="Facultad de Ingeniería en Sistemas" <?= $proyecto['id_programa'] == 'Facultad de Ingeniería en Sistemas' ? 'selected' : '' ?>>Facultad de Ingeniería en Sistemas</option>
            </select>

            <label for="modalidad">Tipo de modalidad:</label>
            <select id="modalidad" name="modalidad" required>
                <option value="">Seleccione una modalidad</option>
                <option value="desempeño academico" <?= $proyecto['modalidad'] == 'desempeño academico' ? 'selected' : '' ?>>desempeño academico</option>
                <option value="practicas empresariales" <?= $proyecto['modalidad'] == 'practicas empresariales' ? 'selected' : '' ?>>practicas empresariales</option>
                <option value="pasantias" <?= $proyecto['modalidad'] == 'pasantias' ? 'selected' : '' ?>>pasantias</option>
                <option value="trabajo de investigacion" <?= $proyecto['modalidad'] == 'trabajo de investigacion' ? 'selected' : '' ?>>trabajo de investigacion</option>
                <option value="semestre de posgrado" <?= $proyecto['modalidad'] == 'semestre de posgrado' ? 'selected' : '' ?>>semestre de posgrado</option>
            </select>

            <div class="flex-buttons">
                <input type="submit" name="submit_edicion" value="Actualizar Proyecto" class="btn">
                <a href="../registro.php" class="btn btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>
</body>

</html>