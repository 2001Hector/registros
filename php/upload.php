<?php
require_once 'conexionB.php';

// Verificar sesión activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar autenticación
if (!isset($_SESSION['id_registrador']) || $_SESSION['id_registrador'] != 3) {
    header("Location: ../index.php");
    exit();
}

$usuarioId = $_SESSION['id_registrador'];

// Obtener filtros de fecha
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

if ($fechaFin && !$fechaInicio) {
    $fechaInicio = date('Y-m-d');
}

// Consulta para proyectos por tipo
$query = "SELECT r.id_tipo as nombre_tipo, COUNT(*) as total 
          FROM registro r
          GROUP BY r.id_tipo";
$result = $GLOBALS['conn']->query($query);

$proyectosPorTipo = [];
$totalProyectos = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $proyectosPorTipo[$row['nombre_tipo']] = $row['total'];
        $totalProyectos += $row['total'];
    }
}

// Consultar archivos en la base de datos
$queryFiles = "SELECT * FROM archivos WHERE 1=1";
$params = [];
$paramTypes = "";

// Filtros de fecha
if ($fechaInicio) {
    $queryFiles .= " AND DATE(fecha_subida) >= ?";
    $params[] = $fechaInicio;
    $paramTypes .= "s";
}
if ($fechaFin) {
    $queryFiles .= " AND DATE(fecha_subida) <= ?";
    $params[] = $fechaFin;
    $paramTypes .= "s";
}

// Orden descendente por fecha
$queryFiles .= " ORDER BY fecha_subida DESC";
$stmtFiles = $GLOBALS['conn']->prepare($queryFiles);

if (!empty($params)) {
    $stmtFiles->bind_param($paramTypes, ...$params);
}

$stmtFiles->execute();
$resultFiles = $stmtFiles->get_result();
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
        alert("<?= htmlspecialchars($_SESSION['mensaje']) ?>");
    </script>
    <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <script>
        alert("<?= htmlspecialchars($_SESSION['error']) ?>");
    </script>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <header>
        <aside class="topbar">
            <div class="navbar">
                <button class="hamburger" onclick="toggleMenu()"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAACGElEQVR4nO2aT08TQRiH90TLAYIJUG/4OWy5EQgJRI8in8HU+C20HDCaGK58BCgBv0FDPNuW9oR6MeoZ0PiQXzKHxozbnXa2O9P0SZps0u7s/DrvvvP+mSSZMaUAD4CnQAM4AzrAL+DWfHTdNt/pN0+ApSQEgBKwD3wE/uCO7jkHnmuspAAB88Ar4Bv++Aq8BMqTErEN9MmPHrDFt5m9ZXIca+V9i6gAn5g8l8CqLxGPzHIXxZXmMK6IFeNGi6YPPBxVRLkgc0ozM3cXDXwgPN6N4mJDZdPFzXYJl16mTdPs2KFTz7IaChVC50vqi28CwFjYSxOiKDYWztLyCZdQ/HHiGaDq8PzfwKJtECVFLtRyELLuOIdd2yAHxMcbmxCloLFxYhOiKDM2OjYhP4mP7zYhqnQQkdcSNz6EhOC1bqbatLrER3uq3W+D+HhtE6JabGxea8c2yJJj0FjNQUjN4fl31qDRDHRBPDTT/hFVxWPh2bSkunPD7FSl/dB5keWFKwVSJv0fV5krjupPECZ/gY1MIgbEvCc8Dp1EDJiYCseh0Br6gqeIWQ6orVAZScQ/jZ4i0+AusDaWiAExqwWZWUvNJi8iLM1QeY5JcJRr7139iZxNrePsYsdcnboJFXxxrR27qBMQErRnskvVYl3RPU0FgCO7Vt8oN1AtVmVM4NQcoPkxcKhG15+VniqzU1IELHifyIwkDO4By8bd8bsJ49QAAAAASUVORK5CYII=" alt="xbox-menu"> </button>

                <div class="menu" id="menu">
                    <form action="totales.php" method="get">
                        <button type="submit"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAACBUlEQVR4nO2Z20sbQRiH90TLAYIJUG/4OWy5EQgJRI8in8HU+C20HDCaGK58BCgBv0FDPNuW9oR6MeoZ0PiQXzKHxozbnXa2O9P0SZps0u7s/DrvvvP+mSSZMaUAD4GnQAM4AzrAL+DWfHTdNt/pN0+ApSQEgBKwD3wE/uCO7jkHnmuspAAB88Ar4Bv++Aq8BMqTErEN9MmPHrCVtxm9ZXIca+V9i6gAn5g8l8CqLxGPzHImROxZXmMK6IFeNGi6YPPBxVRLkgc0ozM3cXDXwgPN6N4mJDZdPFzXYJl16mTdPs2KFTz7IaChVC50vqi28CwFjYSxOiKDYWztLyCZdQ/HHiGaDq8PzfwKJtECVFLtRyELLuOIdd2yAHxMcbmxCloLFxYhOiKDM2OjYhP4mP7zYhqnQQkdcSNz6EhOC1bqbatLrER3uq3W+D+HhtE6JabGxea8c2yJJj0FjNQUjN4fl31qDRDHRBPDTT/hFVxWPh2bSkunPD7FSl/dB5keWFKwVSJv0fV5krjupPECZ/gY1MIgbEvCc8Dp1EDJiYCseh0Br6gqeIWQ6orVAZScQ/jZ4i0+AusDaWiAExqwWZWUvNJi8iLM1QeY5JcJRr7139iZxNrePsYsdcnboJFXxxrR27qBMQErRnskvVYl3RPU0FgCO7Vt8oN1AtVmVM4NQcoPkxcKhG15+VniqzU1IELHifyIwkDO4By8bd8bsJ49QAAAAASUVORK5CYII=" alt="pdf--v2"></button>
                    </form>
                    <form action="../index.php" method="get">
                        <button type="submit"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEbElEQVR4nO2YX0xbVRzHTyECG0VGRrnnNCAtMOaWOcPDMp3JjIsaI0MHFXzwwanL3LinQzeK+mAw/nk1miVT1LeZGHnQqNPZbsZoC86FaMgIRkaJ05ioXQv0D90W7dfctrfcXvqHlpW2Sb/J76X33N7v55zv+d3TElLs8nS1v0yKWVfLyjF/V+skKVa5CIFU7ttY0NO35U5SrAAuqapr4Otsf50ULQAhKLpIuVQAsUgZiiRSriQAiki9RooWgEQitXBPy0+kUOU1M7gqqlJCxLpU79Y7SKEpcJ7A/4EWHqpLC+HSakOLncZXSKEBBKQ6W4b5vU1pIQquSwVkgGh5+SojZWBBd9/tOwoOICBF6n0t3KuM1ML+luFcebtyBnUYRXnGAPmO1Mw5dMxaMem0AbM2BJw2vApAkxlALFJ6uCoq04L4ug5ZPb0v1K7V/J+fY6PTht8l83FlxTNZAYQj9Z4WbqE+JYB/P4ffZPnVZxrcuRaAORvuXWE+AnA2a4AwxJlyLOxuSgkQMA3B32MJBnosA9kCXD6HuxMC2PDpmgBikepnCSMlA8Sqx3IavcPaTAGmRlHhtGFKDTBrRfdNAQivxru1cDfoUgNEVmMsneFjDRDMenTtqfrFwsjERumzuW/QPGvDF04rfE4rZpLmP1uARJFSA/hNQ6FA9+CKWTPrsUekGOQMo5zhN86Ag7olGIkDBmJfNBLHiIHYV38KzhZgOVKRLpUA4C31s/oF9EmG1bWvei4MoCwDcUwYiP2wvCo5A5DKd7IW/m7ztCL/E3jIXKl8zlGKbZzBpzZ/hP67uEUzfkMNIFerZtx1lN7YlVOAcJ1ua/T3WN4OmIa81w682B438zpoOcO0bFpkuMQFHDRTbG8hY08lMy/V3g0z0j3TzzdiQ04BvF8RnfR9S48eb1I/gzN8HJt1Co9Zh1b5mpE4fkgF8ES9NwJNcXJdANR6shlVnOKT6Mz/10/x8LL573emMr+17Mc/FHELcQGdaQFcL6WuTAEigoYLsHCKuD/RDMR+KhVAM3Ec4RSfKSD+5hTPWjajZp0BVmo7+VYbbZsJzRuIw9dGLtxqptCJDH/FbX6KYLgN63F/3gB2VV56M9XsG4n9HXmsqMcjIsW1BC34Yt4AHq/zXN5dOY0W4ggljs9Yh3L8QDM2iQyHOcOUDGDW4+m8AIgMD8gmDjVcx4PaK75t5ReVIBeS3w2NFB2R4sMTAqrzAsApvlbHQWQhPFZ3FR23TM5L74bVfte6AxwTsCPcEpdfav9wBmccDMXxggSIHie+VHWUYbOA++KgKJZEPdqzArjZLzIuwCgyDIgUjhVdhCIoHafD4xhOqa6N95I0P+hzDcAZRhKdPhU1Io+VNqTIMBu/LzCQVwCpzSUyLh0pOMN3/Y1oU63WPjlK0TE/izXYnDeAcO+muC4bkmIUjlMT9Mn8cIqPRMV+kCKYNwBJIsUbogDxuXqwlEai4hQn4lYs3wCZipcAzpdWYH0jVFJJJZGi1v86J6M6MUxmgQAAAABJRU5ErkJggg==" alt="leave-house"></button>
                    </form>
                </div>
            </div>
            <img src="../imagenes/logo.ico" alt="Logo de la empresa"  style="width: 90px; height: px; object-fit: cover; border-radius: 10px;">
        </aside>
    </header>
<br><br>
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
    <h2>TIPO DE FILTRADO</h2>

    <div class="toggle-container">
        <label class="switch">
            <input type="checkbox" id="toggleFiltro" onchange="cambiarFiltro()">
            <span class="slider"></span>
        </label>
        <span id="modoFiltro">Fecha</span>
    </div>

    <div id="contenedorFiltros">
        <div id="form-fecha" class="filtro-activo">
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

        <div id="form-nombre" class="filtro-oculto">
            <div class="busqueda-contenedor">
                <label for="buscarNombre">Buscar por nombre del archivo:</label><br>
                <input type="text" id="buscarNombre" placeholder="Escriba parte del nombre...">
            </div>
        </div>
    </div>

    <h2>RESULTADOS DE BUSQUEDA</h2>
    <div class="tabla-contenedor">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre del Archivo</th>
                    <th>Ver archivo</th>
                    <th>Fecha de Subida</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($resultFiles->num_rows > 0) {
                    while ($row = $resultFiles->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";

                        echo "<td><a href='view.php?file=" . urlencode($row['ruta']) . "' target='_blank'>
                                <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAACWUlEQVR4nO3XT0/TcBzH8e/BR2B8Ah68bFETIx7gAMYDLAYOoge5iJpowhPwZGI0GqIRjWYKog7mjaPePEgCdBv7l6UMFFAGbVecY9nmhAVm+zFoZMmv/R3EstKk3+R17uedJk1K5J577rnnnntW3eobOqkGSVGDhN3IjpKuBmleHaVLZMcpAZKVEYIV5ADdaniAHPj9YMtIAepvaMDKK4LVll/SgDRMBxsSkBkm7HNyZoh83IAvLwj73hBJ3IDFQYITEe/mnxOcgHj3yU9gSe98qJYy2NrabqhqcQnS23bDnm3cgLmnBFZlbRHV6oYtKmsLhj3buAHpJwTW+voPW6VNNnEDxMcEVqXy3VaiySZuQHKAwCoW87ZKmGziBsQfEFj5fM5WMZNN3IDJewSWFeO/Lk1iM+qzZLj+N0DwxPY8ILssojT3CFrohKXj9T9vYGxnuCZ4nln9gL2mTXnr/9ua4Plg9yD9H0HwXKsHhI759dBxOAmEo2d2AhA+2adHmuAkCJ86XA+ItbTp0RY4hRZtrmG89UA9IN56SI+fhlNosbYvhk+olmj/pic74ARaou2bIQCpznGkuuAMnYPGAJFbD/E8nKH7hjFg5mIf0j1whNkrxs9Zs5f7kO6BI8xe6TUEmL3Uh3QPHKH3iDFg5kIf0j1whN4jZHaV0Lmx2sxVHR+vwx+qiVf00kTXiOl499xzzz33aBf3C8Ako+nZG9acAAAAAElFTkSuQmCC' alt='Ver archivo' width='24' height='24' style='cursor:pointer;'>
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

                $stmtFiles->close();
                $GLOBALS['conn']->close();
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
            window.location.href = 'upload.php';
        }
    </script>

    <script>
        document.getElementById('buscarNombre').addEventListener('input', function () {
            const filtro = this.value.toLowerCase();
            const filas = document.querySelectorAll('.styled-table tbody tr');

            filas.forEach(fila => {
                const nombreArchivo = fila.querySelector('td')?.textContent.toLowerCase() || "";
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

                const url = new URL(window.location.href);
                if (url.searchParams.has('fecha_inicio') || url.searchParams.has('fecha_fin')) {
                    window.location.href = 'upload.php';
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