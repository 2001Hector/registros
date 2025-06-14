<?php
require_once 'conexionB.php';

if (isset($_POST['submit_proyecto'])) {
    $ano = $_POST['ano'];
    $docentes = $_POST['docentes'];
    $estudiantes = $_POST['estudiantes'];
    $id_programa = $_POST['id_programa'];
    $id_registrador = $_POST['id_registrador'];
    $id_registro = $_POST['id_registro'];
    $id_tipo = $_POST['id_tipo'];
    $nom_proyecto = $_POST['nom_proyecto'];

    // Validaciones de campos vacíos
    if (empty($ano)) {
        echo $ano;
        echo "<p class='error'>* Agregar el año del proyecto</p>";
    }
    if (empty($docentes)) {
        echo "<p class='error'>* Agregar los docentes del proyecto</p>";
    }
    if (empty($estudiantes)) {
        echo "<p class='error'>* Agregar los estudiantes del proyecto</p>";
    }
    if (empty($id_programa)) {
        echo "<p class='error'>* Agregar el ID del programa</p>";
    }
    if (empty($id_registrador)) {
        echo "<p class='error'>* Agregar el ID del registrador</p>";
    }
    if (empty($id_registro)) {
        echo "<p class='error'>* Agregar el ID del registro</p>";
    }
    if (empty($id_tipo)) {
        echo "<p class='error'>* Agregar el ID del tipo</p>";
    }
    if (empty($nom_proyecto)) {
        echo "<p class='error'>* Agregar el nombre del proyecto</p>";
    }

    // Si todos los campos están completos, insertar en la base de datos
    if (!empty($ano) && !empty($docentes) && !empty($estudiantes) && !empty($id_programa) && !empty($id_registrador) && !empty($id_registro) && !empty($id_tipo) && !empty($nom_proyecto)) {
        $sql = "INSERT INTO registro (ano, docentes, estudiantes, id_programa, id_registrador, id_registro, id_tipo, nom_proyecto)
                VALUES ('$ano', '$docentes', '$estudiantes', '$id_programa', '$id_registrador', '$id_registro', '$id_tipo', '$nom_proyecto')";

        if ($conn->query($sql) === TRUE) {
            echo "Proyecto registrado correctamente.";
        } else {
            echo "Error al registrar el proyecto: " . $conn->error;
        }
    }
}

if (isset($_POST['submit_usuario'])) {
    $constraseña = $_POST['constraseña'];
    $correo = $_POST['correo'];
    $estatuto = $_POST['estatuto'];
    $id_registrador = $_POST['id_registrador'];
    $nombre = $_POST['nombre'];
    $sexo = $_POST['sexo'];
    $tipo = $_POST['tipo'];

    // Validaciones de campos vacíos
    if (empty($constraseña)) {
        echo "<p class='error'>* Agregar la contraseña del usuario</p>";
    }
    if (empty($correo)) {
        echo "<p class='error'>* Agregar el correo del usuario</p>";
    }
    if (empty($estatuto)) {
        echo "<p class='error'>* Agregar el estatuto del usuario</p>";
    }
    if (empty($id_registrador)) {
        echo "<p class='error'>* Agregar el ID del registrador</p>";
    }
    if (empty($nombre)) {
        echo "<p class='error'>* Agregar el nombre del usuario</p>";
    }
    if (empty($sexo)) {
        echo "<p class='error'>* Agregar el sexo del usuario</p>";
    }
    if (empty($tipo)) {
        echo "<p class='error'>* Agregar el tipo del usuario</p>";
    }

    // Si todos los campos están completos, insertar en la base de datos
    if (!empty($constraseña) && !empty($correo) && !empty($estatuto) && !empty($id_registrador) && !empty($nombre) && !empty($sexo) && !empty($tipo)) {
        $sql = "INSERT INTO usuarios (constraseña, correo, estatuto, id_registrador, nombre, sexo, tipo)
                VALUES ('$constraseña', '$correo', '$estatuto', '$id_registrador', '$nombre', '$sexo', '$tipo')";

        if ($conn->query($sql) === TRUE) {
            echo "Usuario registrado correctamente.";
        } else {
            echo "Error al registrar el usuario: " . $conn->error;
        }
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <title>Registro de Proyectos</title>
    <link rel="icon" href="../imagenes/favicon.ico" type="image/x-icon">
</head>
<header>
    <form action="../index.php" method="get">
        <button type="submit">Volver al login</button>
    </form>
</header>
<body>
<body>
    <h2>REGISTRO DE PROYECTOS</h2>
    <form action="registro.php" method="post">
        <label for="id_registrador">ID Registrador:</label>
        <input type="number" id="id_registrador" name="id_registrador" required><br><br>
        
        <label for="id_registro">ID Registro:</label>
        <input type="number" id="id_registro" name="id_registro" required><br><br>
        
        <label for="docentes">Docentes:</label>
        <input type="text" id="docentes" name="docentes" required><br><br>
        
        <label for="estudiantes">Estudiantes:</label>
        <textarea id="estudiantes" name="estudiantes" required></textarea><br><br>
        
        <label for="ano">Año:</label>
        <input type="date" id="ano" name="ano" required><br><br>
         
        <label for="id_tipo">Tipo de Proyecto:</label>
        <select type="number" id="id_tipo" name="id_tipo" required>
            <option value="">Seleccione una opción</option>
            <option value="1">Con opcion a grado</option>
            <option value="2">Convocatoria interna</option>
            <option value="3">Convocatoria externa</option>
            <option value="4">Convocatoria de creacion de semilleros</option>
            <option value="5">Convocatoria de financiacion de semilleros</option>
            <option value="6">Convocatoria circulares</option>
        </select><br><br>

        <label for="nom_proyecto">Nombre del Proyecto:</label>
        <input type="text" id="nom_proyecto" name="nom_proyecto" required><br><br> 
        
        <label for="id_programa">Programa:</label>
        <select type="number" id="id_programa" name="id_programa" required>
            <option value="">Seleccione una opción</option>
            <option value="1">Facultad de Ciencias Economicas y Administrativas</option>
            <option value="2">Facultad de Ciencias Sociales y Humanas</option>
            <option value="3">Facultad de Ciencias de la educacion</option>
            <option value="4">Facultad de ingeneria en sistemas</option>
        </select><br><br>


        <input type="submit" name="submit_proyecto" value="Registrar Proyecto">
    </form>



    <h2>REGISTRO DE USUARIOS</h2>
    <form action="registro.php" method="post">
        <label for="constraseña">Contraseña:</label>
        <input type="password" id="constraseña" name="constraseña" required><br><br>

        <label for="correo">Correo:</label>
        <input type="email" id="correo" name="correo" required><br><br>

        <label for="estatuto">Estatuto:</label>
        <input type="text" id="estatuto" name="estatuto" required><br><br>

        <label for="id_registrador">ID Registrador:</label>
        <input type="number" id="id_registrador" name="id_registrador" required><br><br>

        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="sexo">Sexo:</label>
        <input type="text" id="sexo" name="sexo" required><br><br>

        <label for="tipo">Tipo:</label>
        <input type="text" id="tipo" name="tipo" required><br><br>

        <input type="submit" name="submit_usuario" value="Registrar Usuario">
    </form>



</body>
</html>