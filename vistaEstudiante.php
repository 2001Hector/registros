<?php
require_once 'php/conexionB.php';

$proyectos = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $id_estudiante = trim($_POST['id_estudiante']);

    // Consulta mejorada para buscar IDs en listas separadas por comas
    $sql = "SELECT * FROM registro WHERE FIND_IN_SET(?, ids_estudiantes) > 0";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $id_estudiante);
        $stmt->execute();
        $result = $stmt->get_result();

        $proyectos = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Estudiante</title>
    <link rel="icon" href="imagenes/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="css/styless.css">
    <link rel="stylesheet" href="css/vistaEstudiante.css">
    <style>
        .botones {
            display: flex;
            gap: 1rem;
        }

        .btn {
            background-color: rgb(236, 163, 19);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s;
            width: auto;
            /* Opcional: ajusta al contenido */
            display: inline-block;
            /* Reemplaza block para que no se apilen */
            margin-top: 0;
            /* Elimina el margen superior que causaba separación */
        }

        .btn:hover {
            background-color: rgb(223, 243, 4);
        }

        .danger {
            background-color: rgb(223, 15, 36);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s;
            width: auto;
            /* Opcional: ajusta al contenido */
            display: inline-block;
            /* Reemplaza block para que no se apilen */
            margin-top: 0;
            /* Elimina el margen superior que causaba separación */
        }

        .danger:hover {
            background-color: rgb(244, 6, 6);
        }
    </style>
</head>

<body>
    <div class="contenedor-formulario">
        <h1>Buscar Proyectos del Estudiante</h1>

        <!-- Formulario de búsqueda -->
        <form method="POST" class="search-form">
            <label for="id_estudiante">ID del Estudiante:</label>
            <input type="text" id="id_estudiante" name="id_estudiante" required
                placeholder="Ingrese el ID del estudiante (ej: 12345)">
            <div class="botones">
    <button type="submit" name="buscar" class="btn">Buscar</button>
    <button type="button" onclick="window.location.href='index.php'" class="danger">Volver</button>
</div>

        </form>



    </div>
    <!-- Resultados de la búsqueda -->
    <div class="resultados-derecha">
        <?php if (!empty($proyectos)): ?>
            <h2>Proyectos encontrados:</h2>
            <?php foreach ($proyectos as $proyecto): ?>
                <div class="proyecto-card">
                    <h3><?= htmlspecialchars($proyecto['nom_proyecto']) ?></h3>
                    <p><strong>Tipo:</strong> <?= htmlspecialchars($proyecto['id_tipo']) ?></p>
                    <p><strong>Programa:</strong> <?= htmlspecialchars($proyecto['id_programa']) ?></p>
                    <p><strong>Docentes:</strong> <?= htmlspecialchars($proyecto['docentes']) ?></p>
                    <p><strong>modalidad:</strong> <?= htmlspecialchars($proyecto['modalidad']) ?></p>
                    <p><strong>Estudiantes:</strong> <?= htmlspecialchars($proyecto['estudiantes']) ?></p>
                    <p><strong>IDs de Estudiantes:</strong> <?= htmlspecialchars($proyecto['ids_estudiantes']) ?></p>
                    <p><strong>Fecha:</strong> <?= htmlspecialchars($proyecto['ano']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <p>No se encontraron proyectos para este estudiante.</p>
        <?php endif; ?>
    </div>
</body>

</html>