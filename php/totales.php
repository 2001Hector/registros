<?php
require_once 'conexionB.php';

// Consulta para obtener archivos (si es necesaria)
$sql = "SELECT * FROM archivos";
$resultado = $conn->query($sql);

// Consulta para obtener la cantidad de proyectos por tipo (usando nombres directamente)
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

// Obtener detalles si se ha seleccionado un tipo
$tipoSeleccionado = $_GET['tipo'] ?? null;
$detallesProyectos = [];
$nombreTipoSeleccionado = '';

if ($tipoSeleccionado) {
    $nombreTipoSeleccionado = $tipoSeleccionado;
    
    $queryDetalles = "SELECT 
                        r.id_registro,
                        r.nom_proyecto,
                        r.ano,
                        r.docentes,
                        r.estudiantes,
                        r.modalidad,
                        r.id_programa,
                        r.id_tipo
                      FROM registro r 
                      WHERE r.id_tipo = ?";
    $stmt = $conn->prepare($queryDetalles);
    $stmt->bind_param("s", $nombreTipoSeleccionado); // "s" para string
    $stmt->execute();
    $resultDetalles = $stmt->get_result();
    
    while ($row = $resultDetalles->fetch_assoc()) {
        $detallesProyectos[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style_reg_usuar.css">
    <title>Estadísticas de Proyectos</title>
    <link rel="icon" href="../imagenes/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        header {
            margin-bottom: 30px;
        }
        h2 {
            color: #333;
            text-align: center;
        }
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
            background-color:orange;
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
        .volver-btn, .descargar-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
           background-color:rgb(236, 166, 13);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .volver-btn:hover, .descargar-btn:hover {
            background-color:rgb(243, 183, 17);
        }
        .detalles-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 1000px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color:orange;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .titulo-detalle {
            color:rgb(0, 0, 0);
            text-align: center;
            margin-bottom: 20px;
        }
        .estudiantes-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<header>
       <img src="../imagenes/logo.ico" alt="Logo de la empresa"  style="width: 130px; height: 150px; object-fit: cover; border-radius: 10px;">
         
    </header>
<body>

<h2>Resumen de Proyectos por Tipo</h2>
<div class="proyectos-container">
    <?php foreach ($proyectosPorTipo as $tipo => $cantidad): ?>
        <a href="?tipo=<?= urlencode($tipo) ?>" class="proyecto-bolita">
            <div class="proyecto-tipo"><?= htmlspecialchars($tipo) ?></div>
            <div class="proyecto-cantidad"><?= $cantidad ?></div>
        </a>
    <?php endforeach; ?>
    
    <div class="proyecto-bolita total-proyectos">
        <div class="proyecto-tipo">TOTAL</div>
        <div class="proyecto-cantidad"><?= $totalProyectos ?></div>
    </div>
</div>

<?php if ($tipoSeleccionado && !empty($detallesProyectos)): ?>
    <div class="detalles-container">
        <h3 class="titulo-detalle">Detalles de <?= htmlspecialchars($nombreTipoSeleccionado) ?></h3>
        <table>
            <thead>
                <tr>
                    <th>ID Registro</th>
                    <th>Nombre Proyecto</th>
                    <th>Año</th>
                    <th>Docentes</th>
                    <th>Estudiantes</th>
                    <th>Modalidad</th>
                    <th>ID Programa</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detallesProyectos as $proyecto): ?>
                    <tr>
                        <td><?= htmlspecialchars($proyecto['id_registro'] ?? '') ?></td>
                        <td><?= htmlspecialchars($proyecto['nom_proyecto'] ?? '') ?></td>
                        <td><?= htmlspecialchars($proyecto['ano'] ?? '') ?></td>
                        <td><?= htmlspecialchars($proyecto['docentes'] ?? '') ?></td>
                        <td class="estudiantes-cell" title="<?= htmlspecialchars($proyecto['estudiantes'] ?? '') ?>">
                            <?= htmlspecialchars($proyecto['estudiantes'] ?? '') ?>
                        </td>
                        <td><?= htmlspecialchars($proyecto['modalidad'] ?? '') ?></td>
                        <td><?= htmlspecialchars($proyecto['id_programa'] ?? '') ?></td>
                        <td><?= htmlspecialchars($proyecto['id_tipo'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<BR><BR>
<!-- Centrar el título y el botón de PDF -->
<div style="text-align: center;">
  <h2 class="titulo-detalle">Generar PDF</h2>
  <a href="generarPDF.php">
    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAACBUlEQVR4nO2Z20sbQRjF96/Rin2wBS+tF6qIV6rBK1jyUkgrVRER8UItYqWgiDcUX1pR6Ism09c+9FXc/B0qjDGJ8RITjZYj860uUosXsju7yhw4sPvNLHw/5szswmqakpLS0xdYsBYsuA0WRLreq3+/Lh8goG9Z0bwwz8iTDwGLmr8CIAiPT3/UAFIhYBMAlwVhJwCXAWE3ALcbQgYAtxNCFgC3C8JKgN3cijshYk3t664FOPm2JB8CFgI8xJoCuJRaAaYilJ6ebISiNV5EShvJ0Vovkl8WqJ4YmjXrkfJWxFo+4WTsu/nc/rtuhIvrTR/3TjgDEHpRht1XNYh3jiJcVAeemY/UzCriXV/pLN9v68KBt4fmifvj/ikDvNqLnexCHH78TD4dX3EOQDQjrpMji0aTfZMmwNk8o7HzH7+x87wI4cK3JkAop9T5CAmAyJsGeruKOPHMAqTm/DcAqOnKNvBnBYBfN1Yg6zViDT5yam7NOQDRqGhGZDk5PE/1/wGESzw034wQAXwgOwpwFaHr/hcgMThtfN80t7svQtFbAMTGDr0sN65LPPi7/MddAEcdI0gMTN+on07+xKFviBzvHqM9Av+GOZ4YnKWTC04D2G1NAVxKrQBTEUpPKkJMRSg9qQgxFSH3/GK6twP6pnUAv/RqqRABfRNso8oyACUlJc21ugAgO/p50x6cFgAAAABJRU5ErkJggg==" alt="pdf icon">
  </a>
</div>
<BR><BR>
<a href="upload.php" >
  <img src="data:image/png;base64,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" alt="Volver">
</a>
<BR><BR>

</body>
</html>