<?php
require_once 'conexionB.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_registrador'])) {
    header("Location: index.php");
    exit;
}

$usuarioId = $_SESSION['id_registrador'];
$errors = [];
$proyecto_success = '';
$archivo_success = '';

// Registro de proyecto
if (isset($_POST['submit_proyecto'])) {
    $ano = $_POST['ano'];
    $docentes = $_POST['docentes'];
    $estudiantes = $_POST['estudiantes'];
    $ids_estudiantes = isset($_POST['ids_estudiantes']) ? $_POST['ids_estudiantes'] : '';
    $id_programa = $_POST['id_programa'];
    $id_tipo = $_POST['id_tipo'];
    $modalidad = $_POST['modalidad']; // ✅ Asignar correctamente
    $nom_proyecto = $_POST['nom_proyecto'];

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
    // Validaciones
    if (empty($modalidad))
        $errors[] = "* Seleccionar modalidad de proyecto";
    if (empty($nom_proyecto))
        $errors[] = "* Agregar el nombre del proyecto";

    if (empty($errors)) {
        $sql = "INSERT INTO registro (ano, docentes, estudiantes, ids_estudiantes, id_programa, id_registrador, id_tipo, modalidad, nom_proyecto)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssisss", // ahora hay 9 parámetros
            $ano,
            $docentes,
            $estudiantes,
            $ids_estudiantes,
            $id_programa,
            $usuarioId,
            $id_tipo,
            $modalidad,
            $nom_proyecto
        );


        if ($stmt->execute()) {
            $proyecto_success = "Proyecto registrado correctamente.";
        } else {
            $errors[] = "Error al registrar el proyecto: " . $conn->error;
        }
    }
}

// Subida de archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $nombreArchivo = $_FILES['archivo']['name'];
    $rutaTemporal = $_FILES['archivo']['tmp_name'];
    $rutaDestino = '../uploads/' . basename($nombreArchivo);
    $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);

    if (!in_array($extension, ['pdf', 'docx'])) {
        $errors[] = "Solo se permiten archivos PDF o DOCX.";
    } else {
        if (move_uploaded_file($rutaTemporal, $rutaDestino)) {
            $stmt = $conn->prepare("INSERT INTO archivos (nombre, ruta, fecha_subida, id_registrador, autorizado) VALUES (?, ?, NOW(), ?, 0)");
            $stmt->bind_param("ssi", $nombreArchivo, $rutaDestino, $usuarioId);
            if ($stmt->execute()) {
                $archivo_success = "Archivo subido exitosamente.";
            } else {
                $errors[] = "Error al registrar el archivo en la base de datos.";
            }
        } else {
            $errors[] = "No se pudo mover el archivo.";
        }
    }
    header("Location: registro.php");
    exit;
}

// Obtener proyectos del usuario
$proyectos = $conn->query("SELECT * FROM registro WHERE id_registrador = $usuarioId ORDER BY id_registro DESC");
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro de Proyectos</title>
    <link rel="icon" href="../imagenes/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style_reg_usuar.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <header>
        <img src="../imagenes/logo.ico" alt="Logo"
            style="width: 150px; height: 150px; object-fit: cover; border-radius: 20px;">

        <nav class="menu-horizontal">
            <ul>
                <li><a href="#" onclick="mostrarFormulario()"><img
                            src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAABgklEQVR4nO2VzUrDQBSFZ+EjiJtKV+YHQSj4DL6KP2/hO4huFBrQjRR3uhO6s+1YhaqbdiFaTMFiobWRhmBzJMZVME2mpswU7wdnE7I439w7CWMEQRAEEQHHbBlFVoLF3mExpEqR7TJlylusl7q4ahIIT168vCoSEFkbFSUQFDhZAC5zQFUHuCk3Vd1HOW/jYmkjnUBQvqLJL84jqWg+zhfXkwWCk5ddlseknG8nC6iwNjxuCrqfLCC7JJ8cEgBNwJyTFaoXAPsAcNuA7wHuM2Dvh8+VF6gXAKeBXxne/kmCzUTgeg3oHAFeF/Bew5KTeNlTTKBzCCFGT4oJeF0xgbGriEBrB3DuAfhiAsH7zh3Q2pIoEJTPguamJAHnIRsBpyFJYPyRjcDnkCYw5R3YnvM7wH8kRo9TfIUQ/vCaYuWzF5AQRgKcJoBZr9BA9ikjLjWznyxQM84UFiilEDB1cONNelkejdHDzepKosC3xJWWAzdPFVmnQXDyqcsTBEEQ/4ovKzce5J6fFEIAAAAASUVORK5CYII="
                            alt="cloud-folder"></a></li>
                <li><a href="#" onclick="mostrarProyectos()">
                        <section
                            class="relative group flex flex-col items-start justify-start ml-2 mt-1 w-fit scale-50">
                            <div class="file relative w-20 h-12 cursor-pointer origin-bottom [perspective:1500px] z-50">
                                <div
                                    class="work-5 bg-amber-600 w-full h-full origin-top rounded-md rounded-tl-none group-hover:shadow-[0_4px_8px_rgba(0,0,0,.2)] transition-all ease duration-300 relative after:absolute after:content-[''] after:bottom-[99%] after:left-0 after:w-8 after:h-2 after:bg-amber-600 after:rounded-t-md before:absolute before:content-[''] before:-top-[6px] before:left-[24px] before:w-1.5 before:h-1.5 before:bg-amber-600 before:[clip-path:polygon(0_35%,0%_100%,50%_100%);]">
                                </div>
                                <div
                                    class="work-4 absolute inset-0.5 bg-zinc-400 rounded-md transition-all ease duration-300 origin-bottom select-none group-hover:[transform:rotateX(-20deg)]">
                                </div>
                                <div
                                    class="work-3 absolute inset-0.5 bg-zinc-300 rounded-md transition-all ease duration-300 origin-bottom group-hover:[transform:rotateX(-30deg)]">
                                </div>
                                <div
                                    class="work-2 absolute inset-0.5 bg-zinc-200 rounded-md transition-all ease duration-300 origin-bottom group-hover:[transform:rotateX(-38deg)]">
                                </div>
                                <div
                                    class="work-1 absolute bottom-0 bg-gradient-to-t from-amber-500 to-amber-400 w-full h-[50px] rounded-md rounded-tr-none after:absolute after:content-[''] after:bottom-[99%] after:right-0 after:w-[56px] after:h-[6px] after:bg-amber-400 after:rounded-t-md before:absolute before:content-[''] before:-top-[4px] before:right-[52px] before:w-1.5 before:h-1.5 before:bg-amber-400 before:[clip-path:polygon(100%_14%,50%_100%,100%_100%);] transition-all ease duration-300 origin-bottom flex items-end group-hover:shadow-[inset_0_6px_12px_#fbbf24,_inset_0_-6px_12px_#d97706] group-hover:[transform:rotateX(-46deg)_translateY(1px)]">
                                </div>
                            </div>
                            <p class="text-[0.5rem] pt-0.5 opacity-30"></p>
                        </section>
                    </a></li>
                <li>
                    <form action="../index.php" method="get">
                        <button type="submit"><img
                                src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAABUUlEQVR4nO2ZsUoDQRCG9w2CzdxeLjaCiJUvkbcI+ArWvojPIBKsLRNui7yDlc1tEoxctFC8gZFJFAsxBm7XzYT54a8Ojvl2/9nbY4xRqeKJxt1DLPNhU+bP6HKK5Wb1fntLrnsStPimtIuYheMPELugSdELAsAr/5/F47dvggDEjs2GOC2DACRafWIrAEt3wGmE2kkj5PY4QvUgIxwJBvAA9NjP6P3OygVgz88yersWDOABaHaU0euVlQvg2Rbo5dIKBoC1v5pbLID/bO6HTucgOsCmItq6ArivrD0VC+DXfpoC9CUDUAWAU4ALBUCNEOxeE6P0YxT1Qwb7cZUQfZmbS75Oi/6hqc8zwvHvz82uA/xlowBGd4A0Qm2lTewSn0JNuhFTHQSAx55JYlTmwyAAPLNNMmYdFcdBAFYQk6LHY0+eHEaOzZJXPmjxKpXZSh9uWuw1BxqXFQAAAABJRU5ErkJggg=="
                                alt="exit"></button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p class="error"><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($proyecto_success): ?>
        <p class="success"><?= $proyecto_success ?></p>
    <?php endif; ?>

    <?php if ($archivo_success): ?>
        <p class="success"><?= $archivo_success ?></p>
    <?php endif; ?>

    <div class="form-container" id="formulario">
        <h2>REGISTRO DE PROYECTOS</h2>
        <form action="registro.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_registrador" value="<?= htmlspecialchars($usuarioId) ?>">

            <label for="docentes">Docentes:</label>
            <input type="text" id="docentes" name="docentes" required><br><br>

            <label for="estudiantes">Estudiantes:</label>
            <textarea id="estudiantes" name="estudiantes" required></textarea><br><br>

            <label for="ids_estudiantes">IDs de Estudiantes (separados por comas):</label>
            <input type="text" id="ids_estudiantes" name="ids_estudiantes"
                placeholder="Ej: 12345, 67890, 54321"><br><br>

            <label for="ano">Fecha:</label>
            <input type="date" id="ano" name="ano" value="<?= date('Y-m-d'); ?>" readonly required><br><br>

            <label for="id_tipo">Tipo de Proyecto:</label>
            <select id="id_tipo" name="id_tipo" required>
                <option value="">Seleccione una opción</option>
        <option value="Proyectos de investigación básica">Proyectos de investigación básica</option>
        <option value="Proyectos de investigación aplicada">Proyectos de investigación aplicada</option>
        <option value="Proyectos de desarrollo tecnológico">Proyectos de desarrollo tecnológico</option>
        <option value="Proyectos de innovación educativa">Proyectos de innovación educativa</option>
        <option value="Proyectos interdisciplinarios">Proyectos interdisciplinarios</option>
        <option value="Proyectos de cooperación internacional">Proyectos de cooperación internacional</option>
        <option value="Proyectos de sostenibilidad ambiental">Proyectos de sostenibilidad ambiental</option>
        <option value="Proyectos de impacto social">Proyectos de impacto social</option>
        <option value="Proyectos de emprendimiento">Proyectos de emprendimiento</option>
        <option value="Proyectos de arte y cultura">Proyectos de arte y cultura</option>
        <option value="Proyectos de salud pública">Proyectos de salud pública</option>
        <option value="Proyectos de desarrollo rural">Proyectos de desarrollo rural</option>
        <option value="Proyectos de inteligencia artificial">Proyectos de inteligencia artificial</option>
        <option value="Proyectos de realidad virtual">Proyectos de realidad virtual</option>
        <option value="Proyectos de energías renovables">Proyectos de energías renovables</option>
            </select><br><br>

            <label for="nom_proyecto">Nombre del Proyecto:</label>
            <input type="text" id="nom_proyecto" name="nom_proyecto" required><br><br>

            <label for="id_programa">Programa:</label>
            <select id="id_programa" name="id_programa" required>
                <option value="">Seleccione una opción</option>
        <option value="Facultad de Ciencias Económicas y Administrativas">Facultad de Ciencias Económicas y Administrativas</option>
        <option value="Facultad de Ciencias Sociales y Humanas">Facultad de Ciencias Sociales y Humanas</option>
        <option value="Facultad de Ciencias de la Educación">Facultad de Ciencias de la Educación</option>
        <option value="Facultad de Ingeniería en Sistemas">Facultad de Ingeniería en Sistemas</option>
        <option value="Facultad de Medicina y Ciencias de la Salud">Facultad de Medicina y Ciencias de la Salud</option>
        <option value="Facultad de Derecho y Ciencias Políticas">Facultad de Derecho y Ciencias Políticas</option>
        <option value="Facultad de Arquitectura y Diseño">Facultad de Arquitectura y Diseño</option>
        <option value="Facultad de Ingeniería Civil y Ambiental">Facultad de Ingeniería Civil y Ambiental</option>
        <option value="Facultad de Artes Visuales y Escénicas">Facultad de Artes Visuales y Escénicas</option>
        <option value="Facultad de Ciencias Agrarias y Veterinaria">Facultad de Ciencias Agrarias y Veterinaria</option>
        <option value="Facultad de Psicología y Neurociencias">Facultad de Psicología y Neurociencias</option>
        <option value="Facultad de Química y Farmacia">Facultad de Química y Farmacia</option>
        <option value="Facultad de Ingeniería Electrónica y Telecomunicaciones">Facultad de Ingeniería Electrónica y Telecomunicaciones</option>
        <option value="Facultad de Matemáticas y Ciencias Físicas">Facultad de Matemáticas y Ciencias Físicas</option>
        <option value="Facultad de Lenguas y Filología">Facultad de Lenguas y Filología</option>
        <option value="Facultad de Negocios Internacionales">Facultad de Negocios Internacionales</option>
        <option value="Facultad de Ciencias del Mar">Facultad de Ciencias del Mar</option>
        <option value="Facultad de Ingeniería Industrial">Facultad de Ingeniería Industrial</option>
        <option value="Facultad de Música y Producción Audiovisual">Facultad de Música y Producción Audiovisual</option>
            </select><br><br>

            <label for="modalidad">Modalidad:</label>
            <select id="modalidad" name="modalidad" required>
                <option value="">Seleccione una opción</option>
<option value="Trabajo de grado en grupo">Trabajo de grado en grupo</option>
<option value="Proyecto de investigación">Proyecto de investigación</option>
<option value="Proyecto de extensión">Proyecto de extensión</option>
<option value="Proyecto de innovación">Proyecto de innovación</option>
<option value="Práctica profesional">Práctica profesional</option>
<option value="Pasantía investigativa">Pasantía investigativa</option>
<option value="Semillero de investigación">Semillero de investigación</option>
<option value="Proyecto interdisciplinario">Proyecto interdisciplinario</option>
<option value="Proyecto transdisciplinario">Proyecto transdisciplinario</option>
<option value="Proyecto de impacto social">Proyecto de impacto social</option>
<option value="Proyecto de aula">Proyecto de aula</option>
<option value="Investigación formativa">Investigación formativa</option>
<option value="Proyecto institucional">Proyecto institucional</option>
<option value="Proyecto colaborativo internacional">Proyecto colaborativo internacional</option>

            </select>
            
            <br><br>

            <label for="archivo">Documento del Proyecto:</label>
            <input type="file" name="archivo" required><br><br>

            <input type="submit" name="submit_proyecto" value="Registrar Proyecto y Subir Documento">
        </form>
    </div>
    <!-- ######################################################################################## -->

    <div class="proyectos-container" id="proyectos" style="display: none;">
        <h2 class="text-2xl font-bold mb-4">Mis Proyectos</h2>

        <?php if ($proyectos->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 border-b text-left">Nombre del Proyecto</th>
                            <th class="py-3 px-4 border-b text-left">Modalidades</th>
                            <th class="py-3 px-4 border-b text-left">Año</th>
                            <th class="py-3 px-4 border-b text-left">Tipo</th>
                            <th class="py-3 px-4 border-b text-left">Programa</th>
                            <th class="py-3 px-4 border-b text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $proyectos->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['nom_proyecto']) ?></td>
                                <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['modalidad']) ?></td>
                                <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['ano']) ?></td>
                                <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['id_tipo']) ?></td>
                                <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['id_programa']) ?></td>
                                <td class="py-3 px-4 border-b">
                                    <div class="flex space-x-2">
                                        <!-- Botón Editar -->
                                        <a href="crud_reg/editar_proyecto.php?id=<?= $row['id_registro'] ?>"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Editar
                                        </a>

                                        <!-- Formulario Eliminar -->
                                        <form action="crud_reg/eliminar_proyecto.php" method="post" class="m-0">
                                            <input type="hidden" name="id" value="<?= $row['id_registro'] ?>">
                                            <button type="submit"
                                                onclick="return confirm('¿Estás seguro de eliminar este proyecto?');"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                <p>No hay proyectos registrados aún.</p>
            </div>
        <?php endif; ?>
    </div>





    <!-- ########################################################################################## -->
    <script>
        function mostrarFormulario() {
            document.getElementById("formulario").style.display = "block";
            document.getElementById("proyectos").style.display = "none";
        }
        function mostrarProyectos() {
            document.getElementById("formulario").style.display = "none";
            document.getElementById("proyectos").style.display = "block";
        }
    </script>

    <br><br>

    <body class="flex flex-col min-h-screen">
        <!-- Contenido principal -->
        <main class="flex-grow">
            <div class="proyectos-container" id="proyectos" style="display: none;">
                <h2 class="text-2xl font-bold mb-4">Mis Proyectos</h2>

                <?php if ($proyectos->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full bg-white border border-gray-200 rounded-lg">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-3 px-4 border-b text-left w-1/4">Nombre del Proyecto</th>
                                    <th class="py-3 px-4 border-b text-left w-1/5">Modalidades</th>
                                    <th class="py-3 px-4 border-b text-left w-1/6">Año</th>
                                    <th class="py-3 px-4 border-b text-left w-1/5">Tipo</th>
                                    <th class="py-3 px-4 border-b text-left w-1/4">Programa</th>
                                    <th class="py-3 px-4 border-b text-left w-1/5">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $proyectos->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-4 border-b truncate max-w-xs"
                                            title="<?= htmlspecialchars($row['nom_proyecto']) ?>">
                                            <?= htmlspecialchars($row['nom_proyecto']) ?>
                                        </td>
                                        <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['modalidad']) ?></td>
                                        <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['ano']) ?></td>
                                        <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['id_tipo']) ?></td>
                                        <td class="py-3 px-4 border-b truncate max-w-xs"
                                            title="<?= htmlspecialchars($row['id_programa']) ?>">
                                            <?= htmlspecialchars($row['id_programa']) ?>
                                        </td>
                                        <td class="py-3 px-4 border-b">
                                            <div class="flex space-x-2">
                                                <!-- Formulario para Editar -->
                                                <form action="crud_reg/editar_proyecto.php" method="get" class="m-0">
                                                    <input type="hidden" name="id" value="<?= $row['id_registro'] ?>">
                                                    <button type="submit"
                                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Editar
                                                    </button>
                                                </form>

                                                <!-- Formulario para Eliminar -->
                                                <form action="crud_reg/eliminar_proyecto.php" method="post" class="m-0"
                                                    onsubmit="return confirm('¿Estás seguro de eliminar este proyecto?');">
                                                    <input type="hidden" name="id" value="<?= $row['id_registro'] ?>">
                                                    <button type="submit"
                                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                        <p>No hay proyectos registrados aún.</p>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Tu contenido aquí -->
        </main>

        <!-- Footer -->
        <footer class="bg-[#015C63] text-white">

            <div class="mx-auto w-full max-w-screen-xl">

                <div class="grid grid-cols-2 gap-8 px-6 py-8 md:grid-cols-4">
                    <div>

                        <h2 class="mb-6 text-sm font-semibold uppercase">UNIVERSIDAD DE LA GUAJIRA</h2>
                        <ul class="font-medium">
                            <li class="mb-4"><a
                                    href="https://www.google.com/maps/place/Universidad+De+La+Guajira/@11.5140532,-72.8693311,400m/data=!3m1!1e3!4m6!3m5!1s0x8e8b7d3e96883e99:0x71a81967d2f730c4!8m2!3d11.5140459!4d-72.8691971!16s%2Fm%2F03yltmk?hl=es&entry=ttu&g_ep=EgoyMDI1MDUyMS4wIKXMDSoJLDEwMjExNDUzSAFQAw%3D%3D"
                                    class="hover:underline"> <img
                                        src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAADYklEQVR4nO2Z3UtUQRjGD0FQXfZJEXSZ4IwQ3QRFH/9ARfcZXdZNF4FEIHUlSBrkzApBdhMkCRHdFXZpRbAXgenMmhpKrq0fkfvh2fMx+8Qcs3b1nHVdzXOE88LDLJyXnec3857Z5R3DiEgAOASgD0p9A5AF4EIpPY4DeKGfG1ENAJ0A8iiVECil8gAeGFELKPUaSpWgFGqQzntlRCWgVMc6zJdDtEehbA7CdfO+JitLZ7VcNwfgQNgAvQErPAbgmgeoR9edCMh7Hi6A62ZXmQKuBuReX5XrONmtd11uynEcH4DDfrkFFI74ADhGmAHXnfUpC98dKNiZGz4As0aYAccZhOOgXMq2Jv1ybSs7tzq3+KXuye/B2NElG09zQdqZJJJJYvMUGewStK17tOlgTQC23brSlJZj59LzudTt8cX3x9LZZKtp/VzwzXMW7q/L9OPkyT1c0ktM0h4mSIZLCj8xPQo6xyV5nRgilwMBgF3KtvJ+5taScszFSUzuXtM0G27Yl0g1NXNJ+5ik2SDT1eTtjqTDTJCHz4YbKl7SwmKmvx6AXDH9NtD0oxF6tEvSFi7oABdU1WO6pt0ZbLzycqL5omUulGDbqFVOMW9LJPcHr7qk6c00XQ2GpSiG5/tqNq81Vxh6U7VstsK8J7E0dssTMM2ZmszrvCSSO6MB4EEQMNmEd1N3UbKtqub18/7vd1DV/FYDsLLPP7JJwLIClV745OVFCoAvgwiKJ6NnUTTnfM1b5k/0jJ5DIqoAPOUdt/g4/dAHoIgP0x3gI0u50QSQS+WUEBSzucEKgJnsZw9wOS+yAPwPRM/XM39LyTLn8XT0fEVOpAH4Hwh92tjmL29cPm63BQD7+z74jNsBgJeBJFYY31YAvIpiAB7vAI1LqGqEXSI8foll+KvM42NUhr/SPP4hk9GUsVaEbZDHADL8VeaBIlObU0K65SjogG5Bdgt6PCEaL3BBepmg037/4Tck8W8u3fasH0CQRS5oPxP01uMVTdry6Jw8tbdrhLZwST4ySQr1mGaCmstzMXHiyJqmKwGI8++LSIYL+kS31XV73agjat0dJkhGt+43MtfShJLe5JK26QsMfZFhbGJ4uyNJK5f0M5PU1pcj+pLkf8wVRxxGbfEbCreaWsJ6bqYAAAAASUVORK5CYII="
                                        alt="map-marker"> Sede Principal: Km 3+354 vía Maicao, La Guajira (Colombia):
                                    Código Postal: 440002 >>Como llegar</a></li>
                            <li class="mb-4"><a
                                    href="https://www.google.com/maps/place/Universidad+de+La+Guajira/@11.3804754,-72.2559974,872m/data=!3m1!1e3!4m6!3m5!1s0x8e8bf234ee343b79:0xc093c9aea4e92545!8m2!3d11.380999!4d-72.2561913!16s%2Fm%2F0cv2rx2?hl=es"
                                    class="hover:underline">-Sede Maicao- Clle 16 No. 28a - 80 salida a Riohacha, La
                                    Guajira (Colombia): >>Cómo llegar</a></li>
                            <li class="mb-4"><a
                                    href="https://www.google.com/maps/place/Universidad+de+La+Guajira+Extension+Villanueva/@10.6165884,-72.964734,803m/data=!3m1!1e3!4m6!3m5!1s0x8e8aee933f0e7bad:0x70a34cae3ea2cb0d!8m2!3d10.6166305!4d-72.9633413!16s%2Fg%2F113gg22t7?hl=es&entry=ttu&g_ep=EgoyMDI1MDUyMS4wIKXMDSoJLDEwMjExNDUzSAFQAw%3D%3D"
                                    class="hover:underline">-Sede Villanueva- Km 1 vía San Juan del Cesar, La Guajira
                                    (Colombia): >>Cómo llegar</a></li>
                            <li class="mb-4"><a
                                    href="https://www.google.com/maps/place/Universidad+De+La+Guajira/@10.8974684,-72.826849,16z/data=!4m7!3m6!1s0x8e8b03ca73121289:0x925943791b13c2e2!8m2!3d10.8981676!4d-72.8275756!15sCiF1bml2ZXJzaWRhZCBkZSBMYSBHdWFqaXJhIGZvbnNlY2GSAQZzY2hvb2zgAQA!16s%2Fm%2F0cv2rvh?hl=es&coh=164777&entry=tt&shorturl=1"
                                    class="hover:underline">-Sede Fonseca- Km 2 vía Barrancas, La Guajira (Colombia):
                                    >>Cómo llegar</a></li>
                        </ul>
                    </div>
                    <div>
                        <h2 class="mb-6 text-sm font-semibold uppercase">CONTÁCTANOS </h2>
                        <ul class="font-medium">
                            <li class="mb-4"><a href="#" class="hover:underline"> <img
                                        src="data:image/png;base64,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"
                                        alt="messaging-">+57 3048577649</a></li>
                            <li class="mb-4"><a href="#" class="hover:underline">universidad@uniguajira.edu.co</a></li>
                            <li class="mb-4"><a href="#" class="hover:underline">Facebook</a></li>
                            <li class="mb-4"><a href="#" class="hover:underline">twitter</a></li>
                            <li class="mb-4"><a href="#" class="hover:underline">Canales físicos y electrónicos para
                                    atención al público</a></li>
                        </ul>
                    </div>
                    <div>
                        <h2 class="mb-6 text-sm font-semibold uppercase">POLITICAS</h2>
                        <ul class="font-medium">
                            <li class="mb-4"><a href="#" class="hover:underline"> <img
                                        src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAAB+ElEQVR4nO2Yy04UQRSGK76OVWOCBkPACCojl2GGBS/h3jdww76qcbjMMLc3YM0eTAyJMdaNrehOJiYmQPQ3VXhbjDPTPVDQUH/yJ510J32+PufUnzQhUVFRUbmTkGyFS3bAFT0ViuGyzb3du+hb/uFuaeTiQxQt/gcj2Q8uWTkzgPvyVwkgPATdyw4QaGxEP0v6LTPAlRevzh0BRJBRYas9rQovctEBchm6sQCfum+Ak5PUPuruXw8AV0gWgI/dvesBkPsREhGAxSUeSnGJVVzi0SRuexIf9UlekYck7pe8IgbZbd4BkRsAzTCK3T+eRNE8d4Ai0cNBkDTikn1eU2Oo6ofY0FPYMtPYNs/QsM/RtPNomRI6dgntwwo6h8vo2Mo/LqNtl9C0i/7Zhimibp72dMMWfXGuE+IiAULNdd08Ob92I6UiwF8JRc9CdKBmp5Hoe+6XycAxImnEJTsOAbBpHuG1Hhv8rGTddACPvg8BUFUPsKEnhwCguyk7UHgZapHd6TZoiRNNF0laCUXfhQBYVxPY1FM+D3rd54q+IlkEkDtCsh0h6Rcu2fdEFbCm7mPdZYOZxJZ5jJqe+ZUPRTTtHBpmwZ//LVtCy7gc+O0FNM28z5G6mfXHZ83M/HHblH1eVPU4EllwC/3VjU2mLx8VFRUVRTLqJ5TdUVqmwQm0AAAAAElFTkSuQII="
                                        alt="scroll">Politica de privacidad</a></li>
                            <li class="mb-4"><a href="#" class="hover:underline">Politica de autor </a></li>
                            <li class="mb-4"><a href="#" class="hover:underline">Terminos &amp; Condiciones</a></li>
                        </ul>
                    </div>

                    <div>

                        <img src="../imagenes/universdad.jpg" alt="Logo"
                            style="width: 250px; height: 150px; object-fit: cover; border-radius: 20px;">

                    </div>

                </div>

                <div
                    class="flex flex-col md:flex-row items-center justify-between px-6 py-4 bg-[#01494F] text-sm font-medium">
                    <span>© 2025 <a href="https://uniguajira.edu.co/universidad-de-la-guajira/estudiantes/"
                            class="underline">Micao</a>. Universidad de la guajira.</span>
                    <div class="flex mt-4 md:mt-0 space-x-6">
                        <a href="#" class="hover:text-gray-200" aria-label="Facebook">
                            <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 19">
                                <path fill-rule="evenodd"
                                    d="M6.135 3H8V0H6.135a4.147 4.147 0 0 0-4.142 4.142V6H0v3h2v9.938h3V9h2.021l.592-3H5V3.591A.6.6 0 0 1 5.592 3h.543Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>

                        <a href="#" class="hover:text-gray-200" aria-label="Twitter">
                            <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 17">
                                <path fill-rule="evenodd"
                                    d="M20 1.892a8.178 8.178 0 0 1-2.355.635 4.074 4.074 0 0 0 1.8-2.235 8.344 8.344 0 0 1-2.605.98A4.13 4.13 0 0 0 13.85 0a4.068 4.068 0 0 0-4.1 4.038 4 4 0 0 0 .105.919A11.705 11.705 0 0 1 1.4.734a4.006 4.006 0 0 0 1.268 5.392 4.165 4.165 0 0 1-1.859-.5v.05A4.057 4.057 0 0 0 4.1 9.635a4.19 4.19 0 0 1-1.856.07 4.108 4.108 0 0 0 3.831 2.807A8.36 8.36 0 0 1 0 14.184 11.732 11.732 0 0 0 6.291 16 11.502 11.502 0 0 0 17.964 4.5c0-.177 0-.35-.012-.523A8.143 8.143 0 0 0 20 1.892Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>

                    </div>
                </div>
            </div>
        </footer>
    </body>

</body>

</html>