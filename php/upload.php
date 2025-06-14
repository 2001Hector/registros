<?php
require_once 'conexionB.php';
session_start(); // Iniciar la sesión

// Comprobar si el usuario está autenticado
if (!isset($_SESSION['id_registrador'])) {
    header("Location: index.php"); // Redirigir a login si no está autenticado
    exit;
}

$usuarioId = $_SESSION['id_registrador']; // Obtener el ID del usuario



// Obtener los filtros de fecha
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

// Validar que si se especifica fecha fin, también se especifique fecha inicio
if ($fechaFin && !$fechaInicio) {
    $fechaInicio = date('Y-m-d'); // Si solo hay fecha fin, establecer fecha inicio como hoy
}

// Consulta para obtener la cantidad de proyectos por tipo
$query = "SELECT r.id_tipo as nombre_tipo, COUNT(*) as total 
          FROM registro r
          GROUP BY r.id_tipo";
$result = $conn->query($query);

$proyectosPorTipo = [];
$totalProyectos = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $proyectosPorTipo[$row['nombre_tipo']] = $row['total'];
        $totalProyectos += $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles_upload.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Archivos</title>
    <link rel="icon" href="../imagenes/favicon.ico" type="image/x-icon">
    <style>
        .proyectos-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
        }
        .proyecto-bolita {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background-color: orange;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
            cursor: pointer;
            text-decoration: none;
        }
        .proyecto-bolita:hover {
            transform: scale(1.05);
        }
        .proyecto-tipo {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .proyecto-cantidad {
            font-size: 24px;
        }
        .total-proyectos {
            background-color: #333;
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['mensaje'])): ?>
    <script>
        alert("<?= $_SESSION['mensaje'] ?>");
    </script>
    <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <script>
        alert("<?= $_SESSION['error'] ?>");
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
    <header>
        <aside class="topbar">
            <div class="navbar">
                <!-- Botón hamburguesa -->
                  <button class="hamburger" onclick="toggleMenu()"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAACGElEQVR4nO2aT08TQRiH90TLAYIJUG/4OWy5EQgJRI8in8HU+C20HDCaGK58BCgBv0FDPNuW9oR6MeoZ0PiQXzKHxozbnXa2O9P0SZps0u7s/DrvvvP+mSSZMaUAD4CnQAM4AzrAL+DWfHTdNt/pN0+ApSQEgBKwD3wE/uCO7jkHnmuspAAB88Ar4Bv++Aq8BMqTErEN9MmPHrCVtxm9ZXIca+V9i6gAn5g8l8CqLxGPzHIXxZXmMK6IFeNGi6YPPBxVRLkgc0ozM3cXDXwgPN6N4mJDZdPFzXYJl16mTdPs2KFTz7IaChVC50vqi28CwFjYSxOiKDYWztLyCZdQ/HHiGaDq8PzfwKJtECVFLtRyELLuOIdd2yAHxMcbmxCloLFxYhOiKDM2OjYhP4mP7zYhqnQQkdcSNz6EhOC1bqbatLrER3uq3W+D+HhtE6JabGxea8c2yJJj0FjNQUjN4fl31qDRDHRBPDTT/hFVxWPh2bSkunPD7FSl/dB5keWFKwVSJv0fV5krjupPECZ/gY1MIgbEvCc8Dp1EDJiYCseh0Br6gqeIWQ6orVAZScQ/jZ4i0+AusDaWiAExqwWZWUvNJi8iLM1QeY5JcJRr7139iZxNrePsYsdcnboJFXxxrR27qBMQErRnskvVYl3RPU0FgCO7Vt8oN1AtVmVM4NQcoPkxcKhG15+VniqzU1IELHifyIwkDO4By8bd8bsJ49QAAAAASUVORK5CYII=" alt="xbox-menu"> </button>

                <!-- Menú desplegable -->
                <div class="menu" id="menu">
                    <form action="totales.php" method="get">
                        <button type="submit"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAACBUlEQVR4nO2Z20sbQRjF96/Rin2wBS+tF6qIV6rBK1jyUkgrVRER8UItYqWgiDcUX1pR6Ism09c+9FXc/B0qjDGJ8RITjZYj860uUosXsju7yhw4sPvNLHw/5szswmqakpLS0xdYsBYsuA0WRLreq3+/Lh8goG9Z0bwwz8iTDwGLmr8CIAiPT3/UAFIhYBMAlwVhJwCXAWE3ALcbQgYAtxNCFgC3C8JKgN3cijshYk3t664FOPm2JB8CFgI8xJoCuJRaAaYilJ6ebISiNV5EShvJ0Vovkl8WqJ4YmjXrkfJWxFo+4WTsu/nc/rtuhIvrTR/3TjgDEHpRht1XNYh3jiJcVAeemY/UzCriXV/pLN9v68KBt4fmifvj/ikDvNqLnexCHH78TD4dX3EOQDQjrpMji0aTfZMmwNk8o7HzH7+x87wI4cK3JkAop9T5CAmAyJsGeruKOPHMAqTm/DcAqOnKNvBnBYBfN1Yg6zViDT5yam7NOQDRqGhGZDk5PE/1/wGESzw034wQAXwgOwpwFaHr/hcgMThtfN80t7svQtFbAMTGDr0sN65LPPi7/MddAEcdI0gMTN+on07+xKFviBzvHqM9Av+GOZ4YnKWTC04D2G1NAVxKrQBTEUpPKkJMRSg9qQgxFSH3/GK6twP6pnUAv/RqqRABfRNso8oyACUlJc21ugAgO/p50x6cFgAAAABJRU5ErkJggg==" alt="pdf--v2"></button>
                    </form>
                    <form action="../index.php" method="get">
                        <button type="submit"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEbElEQVR4nO2YX0xbVRzHTyECG0VGRrnnNCAtMOaWOcPDMp3JjIsaI0MHFXzwwanL3LinQzeK+mAw/nk1miVT1LeZGHnQqNPZbsZoC86FaMgIRkaJ05ioXQv0D90W7dfctrfcXvqHlpW2Sb/J76X33N7v55zv+d3TElLs8nS1v0yKWVfLyjF/V+skKVa5CIFU7ttY0NO35U5SrAAuqapr4Otsf50ULQAhKLpIuVQAsUgZiiRSriQAiki9RooWgEQitXBPy0+kUOU1M7gqqlJCxLpU79Y7SKEpcJ7A/4EWHqpLC+HSakOLncZXSKEBBKQ6W4b5vU1pIQquSwVkgGh5+SojZWBBd9/tOwoOICBF6n0t3KuM1ML+luFcebtyBnUYRXnGAPmO1Mw5dMxaMem0AbM2BJw2vApAkxlALFJ6uCoq04L4ug5ZPb0v1K7V/J+fY6PTht8l83FlxTNZAYQj9Z4WbqE+JYB/P4ffZPnVZxrcuRaAORvuXWE+AnA2a4AwxJlyLOxuSgkQMA3B32MJBnosA9kCXD6HuxMC2PDpmgBikepnCSMlA8Sqx3IavcPaTAGmRlHhtGFKDTBrRfdNAQivxru1cDfoUgNEVmMsneFjDRDMenTtqfrFwsjERumzuW/QPGvDF04rfE4rZpLmP1uARJFSA/hNQ6FA9+CKWTPrsUekGOQMo5zhN86Ag7olGIkDBmJfNBLHiIHYV38KzhZgOVKRLpUA4C31s/oF9EmG1bWvei4MoCwDcUwYiP2wvCo5A5DKd7IW/m7ztCL/E3jIXKl8zlGKbZzBpzZ/hP67uEUzfkMNIFerZtx1lN7YlVOAcJ1ua/T3WN4OmIa81w682B438zpoOcO0bFpkuMQFHDRTbG8hY08lMy/V3g0z0j3TzzdiQ04BvF8RnfR9S48eb1I/gzN8HJt1Co9Zh1b5mpE4fkgF8ES9NwJNcXJdANR6shlVnOKT6Mz/10/x8LL573emMr+17Mc/FHELcQGdaQFcL6WuTAEigoYLsHCKuD/RDMR+KhVAM3Ec4RSfKSD+5hTPWjajZp0BVmo7+VYbbZsJzRuIw9dGLtxqptCJDH/FbX6KYLgN63F/3gB2VV56M9XsG4n9HXmsqMcjIsW1BC34Yt4AHq/zXN5dOY0W4ggljs9Yh3L8QDM2iQyHOcOUDGDW4+m8AIgMD8gmDjVcx4PaK75t5ReVIBeS3w2NFB2R4sMTAqrzAsApvlbHQWQhPFZ3FR23TM5L74bVfte6AxwTsCPcEpdfav9wBmccDMXxggSIHie+VHWUYbOA++KgKJZEPdqzArjZLzIuwCgyDIgUjhVdhCIoHafD4xhOqa6N95I0P+hzDcAZRhKdPhU1Io+VNqTIMBu/LzCQVwCpzSUyLh0pOMN3/Y1oU63WPjlK0TE/izXYnDeAcO+muC4bkmIUjlMT9Mn8cIqPRMV+kCKYNwBJIsUbogDxuXqwlEai4hQn4lYs3wCZipcAzpdWYH0jVFJJJZGi1v86J6M6MUxmgQAAAABJRU5ErkJggg==" alt="leave-house"></button>
                    </form>
                </div>
            </div>
            <!-- Logo a la derecha -->
            <img src="../imagenes/logo.ico" alt="Logo de la empresa"  style="width: 90px; height: px; object-fit: cover; border-radius: 10px;">
             
        </aside>
    </header>
<br><br>
    <!-- Bolas de estadísticas -->
    <h2 style="text-align: center;">Totales de proyecto actuales</h2>
    <div class="proyectos-container">
        <?php foreach ($proyectosPorTipo as $tipo => $cantidad): ?>
            <div class="proyecto-bolita">
                <div class="proyecto-tipo"><?= htmlspecialchars($tipo) ?></div>
                <div class="proyecto-cantidad"><?= $cantidad ?></div>
            </div>
        <?php endforeach; ?>
        
        <div class="proyecto-bolita total-proyectos">
            <div class="proyecto-tipo">TOTAL</div>
            <div class="proyecto-cantidad"><?= $totalProyectos ?></div>
        </div>
    </div>
<br><br>
    <!-- Título -->
    <h2>TIPO DE FILTRADO</h2>

    <!-- Toggle Switch -->
    <div class="toggle-container">
        <label class="switch">
            <input type="checkbox" id="toggleFiltro" onchange="cambiarFiltro()">
            <span class="slider"></span>
        </label>
        <span id="modoFiltro">Fecha</span>
    </div>

    <!-- Contenedor para ambos formularios -->
    <div id="contenedorFiltros">
        <!-- Este se mostrará por defecto -->
        <div id="form-fecha" class="filtro-activo">
            <!-- Aquí insertas tu formulario por fecha -->
            <form method="get" action="upload.php" class="form-fechas">
                <h2>POR RANGO DE FECHA</h2>
                <div class="form-group">
                    <label for="fecha_inicio">Fecha de inicio:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio"
                        value="<?= htmlspecialchars($fechaInicio) ?>">
                </div>
                <div class="form-group">
                    <label for="fecha_fin">Fecha de fin:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="<?= htmlspecialchars($fechaFin) ?>">
                </div>
                <button type="submit" class="btn-buscar">FILTRAR</button>
                <button type="button" class="btn-limpiar" onclick="limpiarFiltros()">LIMPIAR FILTRO</button>
            </form>
        </div>

        <!-- Este se oculta al inicio -->
        <div id="form-nombre" class="filtro-oculto">
            <!-- Aquí insertas tu formulario por nombre -->
            <div class="busqueda-contenedor">
                <label for="buscarNombre">Buscar por nombre del archivo:</label><br>
                <input type="text" id="buscarNombre" placeholder="Escriba parte del nombre...">
            </div>
        </div>
    </div>

    <h2>RESULTADOS DE BUSQUEDA</h2>
    <!-- Campo de búsqueda por nombre -->
    <div class="tabla-contenedor">
        <table class="styled-table">
    <thead>
        <tr>
            <th>Nombre del Archivo</th>
            <th>Ver archivo</th>
            <th>Fecha de Subida</th>
            <th>Eliminar</th> <!-- ← Añadido correctamente -->
        </tr>
    </thead>
    <tbody>
        <?php
        // Consultar archivos en la base de datos
        $query = "SELECT * FROM archivos WHERE 1=1";
        $params = [];
        $paramTypes = "";

        // Filtros de fecha
        if ($fechaInicio) {
            $query .= " AND DATE(fecha_subida) >= ?";
            $params[] = $fechaInicio;
            $paramTypes .= "s";
        }
        if ($fechaFin) {
            $query .= " AND DATE(fecha_subida) <= ?";
            $params[] = $fechaFin;
            $paramTypes .= "s";
        }

        // Orden descendente por fecha
        $query .= " ORDER BY fecha_subida DESC";
        $stmt = $conn->prepare($query);

        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";

                echo "<td><a href='view.php?file=" . urlencode($row['ruta']) . "' target='_blank'>
                        <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAACWUlEQVR4nO3XT0/TcBzH8e/BR2B8Ah68bFETIx7gAMYDLAYOoge5iJpowhPwZGI0GqIRjWYKog7mjaPePEgCdBv7l6UMFFAGbVecY9nmhAVm+zFoZMmv/R3EstKk3+R17uedJk1K5J577rnnnlW3+obOqkFS1CBhN7KjpKtBmldH6RLZcUqAZGWEYAU5QLcaHiAHfj/YMlKA+hsasPKKYLXllzQgDdPBhgRkhgn7nJwZIh834MsLwr43RBI3YHGQ4ATEu/nnBCcg3n3yE1jSOx+qpQy2tjYbqlpcgvS23bBnGzdg7imBVVlbRLW6YYvK2oJhzzZuQPoJgbW+/sNWaZNN3ADxMYFVqXy3lWiyiRuQGiCwyuWSrVImm7gByYcEVrFYsFXSZBM3IPGAwCoU8rZKmGziBsTuE1j5fM5WMZNN3IDpfgIrl1u11bTJJm5A+B6BpapZW4VNNnEDhLsElqJIthJMNnEDJu8QWLK8YglFWkBZvImfkWbogve/aVPe14aAidsElhXjvy5NYjPqs2S4/jdA8MT2PCC7LKI09wha6ISl4/U/b2BsZ7gmeJ5Z/YC9pk156//bmuD5YPcg/R9B8FyrB4SO+fXQcTgJhKNndgIQPtmnR5rgJIicOlwPiLW06dEWOIUWba5hvPVAPSDeekiPn4ZTaPG2z4ZPqJZo/6YnO+AEWqLjvSEAqc5xpLrgDJ2DxgCx2w/xPJyh+4YxYOZiH9I9cITZngsmAb1NmL0MZ+g9QmZXCZ0bq81c1fHxOvajmnhFL010jZiOd88999xzj3ZxvwBMMpqevWHNKQAAAABJRU5ErkJggg==' alt='Ver archivo' width='24' height='24' style='cursor:pointer;'>
                      </a></td>";

                echo "<td>" . htmlspecialchars($row['fecha_subida']) . "</td>";

                echo "<td>
                        <form method='post' action='eliminar.php' 
                              onsubmit=\"return confirm('¿Eliminar ESTE archivo y SUS registros exactamente relacionados?');\">
                            <input type='hidden' name='id' value='" . htmlspecialchars($row['id'], ENT_QUOTES) . "'>
                            <button type='submit' style='
                                background-color: #dc3545;
                                color: white;
                                border: none;
                                padding: 6px 12px;
                                border-radius: 4px;
                                cursor: pointer;
                                white-space: nowrap;
                            '>Eliminar</button>
                        </form>
                      </td>";

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No se encontraron archivos para el rango de fechas seleccionado.</td></tr>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </tbody>
</table>

    </div>

    <script>
        function toggleMenu() {
            const menu = document.getElementById("menu");
            menu.classList.toggle("open");
        }
    </script>

    <script>
        function limpiarFiltros() {
            document.getElementById('fecha_inicio').value = '';
            document.getElementById('fecha_fin').value = '';
            window.location.href = 'upload.php'; // Recargar sin parámetros
        }
    </script>

    <script>
        document.getElementById('buscarNombre').addEventListener('input', function () {
            const filtro = this.value.toLowerCase();
            const filas = document.querySelectorAll('.styled-table tbody tr');

            filas.forEach(fila => {
                const nombreArchivo = fila.querySelector('td')?.textContent.toLowerCase() || "";
                // Mostrar fila solo si contiene el texto buscado
                fila.style.display = nombreArchivo.includes(filtro) ? '' : 'none';
            });
        });
    </script>

    <script>
        function cambiarFiltro() {
            const checkbox = document.getElementById("toggleFiltro");
            const textoModo = document.getElementById("modoFiltro");
            const formFecha = document.getElementById("form-fecha");
            const formNombre = document.getElementById("form-nombre");

            if (checkbox.checked) {
                textoModo.textContent = "Nombre";
                formFecha.classList.replace("filtro-activo", "filtro-oculto");
                formNombre.classList.replace("filtro-oculto", "filtro-activo");

                // Si hay parámetros de fecha en la URL, recargar sin ellos
                const url = new URL(window.location.href);
                if (url.searchParams.has('fecha_inicio') || url.searchParams.has('fecha_fin')) {
                    window.location.href = 'upload.php';  // o el nombre de tu archivo principal
                }

            } else {
                textoModo.textContent = "Fecha";
                formNombre.classList.replace("filtro-activo", "filtro-oculto");
                formFecha.classList.replace("filtro-oculto", "filtro-activo");
            }
        }
    </script>
</body>
</html>