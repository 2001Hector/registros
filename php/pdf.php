<?php
require_once 'conexionB.php';
require __DIR__ . '/../librerias/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tu_pagina_principal.html');
    exit;
}

$tipo = $_POST['tipo'] ?? null;
$programa = $_POST['programa'] ?? null;
$proyecto_seleccionado = $_POST['proyecto_seleccionado'] ?? null;

$sql = "SELECT * FROM registro WHERE 1=1";
$params = [];
$types = "";

// Filtro por tipo (si está seleccionado y no hay proyecto específico)
if ($tipo && $tipo !== "" && !$proyecto_seleccionado) {
    $sql .= " AND id_tipo = ?";
    $params[] = $tipo;
    $types .= "s";
}

// Filtro por programa (si está seleccionado y no hay proyecto específico)
if ($programa && $programa !== "" && !$proyecto_seleccionado) {
    $sql .= " AND id_programa = ?";
    $params[] = $programa;
    $types .= "s";
}

// Filtro por nombre de proyecto (tiene prioridad)
if ($proyecto_seleccionado && $proyecto_seleccionado !== "") {
    $sql .= " AND nom_proyecto = ?";
    $params = [$proyecto_seleccionado]; // Sobrescribe otros filtros
    $types = "s";
}

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error en la preparación: " . $conn->error);
}

if (!empty($params)) {
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

$stmt->execute();
$result = $stmt->get_result();
$registros = $result->fetch_all(MYSQLI_ASSOC);

if (empty($registros)) {
    echo '
    <div class="mensaje-contenedor">
        <div class="mensaje-box">
            <h2>⚠ Sin resultados encontrados</h2>
            <p>No se encontraron proyectos con los filtros seleccionados.</p>
            <a href="generarPDF.php" class="boton-volver">🔙 Volver</a>
        </div>
    </div>

    <style>
        .mensaje-contenedor {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
            background-color: #f9f9f9;
        }

        .mensaje-box {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            animation: aparecer 0.4s ease-in-out;
        }

        .mensaje-box h2 {
            margin-top: 0;
        }

        .boton-volver {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #ffc107;
            color: #212529;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .boton-volver:hover {
            background-color: #e0a800;
        }

        @keyframes aparecer {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    ';
    exit;
}



// Convertir imagen a base64 para el logo
$logoPath = __DIR__ . '/../imagenes/logo.jpg';
$logoData = base64_encode(file_get_contents($logoPath));
$logoMime = mime_content_type($logoPath);
$logoImg = '<img src="data:'.$logoMime.';base64,'.$logoData.'" alt="Logo de la empresa">';

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Proyectos</title>
    <link rel="icon" href="imagenes/favicon.ico" type="image/x-icon">
    <style>
        body { font-family: Arial, sans-serif; }
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }
        .header h1 { color:rgb(0, 0, 0); margin-bottom: 5px; }
        .logo-container {
            position: absolute;
            top: 0;
            right: 0;
        }
        .logo-container img {
            width: 150px;
            height: auto;
            max-width: 100%;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: auto;
        }
        th {
            background-color: orange;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            vertical-align: top;
            font-size: 12px; 
        }
        th:nth-child(7), td:nth-child(7) {
            width: 20%;
        }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 12px;
            color: #7f8c8d;
        }
        .no-results {
            text-align: center;
            padding: 20px;
            color: #e74c3c;
        }
        .project-highlight {
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-container">
            '.$logoImg.'
        </div>
        <h1>Reportes de proyectos </h1>
        <p>Universidad de La Guajira -Sd Maicao</p>
    </div>';

$html .= '<div class="filters"><strong>Resumen:</strong><ul>';
if ($proyecto_seleccionado && $proyecto_seleccionado !== "") {
    $html .= "<li>Proyecto específico: <span class='project-highlight'>".htmlspecialchars($proyecto_seleccionado)."</span></li>";
} else {
    if ($tipo && $tipo !== "") $html .= "<li>Tipo de proyecto: ".htmlspecialchars($tipo)."</li>";
    if ($programa && $programa !== "") $html .= "<li>Facultad/Programa: ".htmlspecialchars($programa)."</li>";
    if ((!$tipo || $tipo === "") && (!$programa || $programa === "")) $html .= "<li>Sin filtros - Todos los registros</li>";
}
$html .= '</ul></div>';

$html .= '<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Año</th>
            <th>modalidad</th>
            <th>Tipo</th>
            <th>Programa</th>
            <th>Proyecto</th>
            <th>Docentes</th>
            <th>Estudiantes</th>
        </tr>
    </thead>
    <tbody>';

foreach ($registros as $registro) {
    $html .= '<tr>
        <td>'.htmlspecialchars($registro['id_registro']).'</td>
        <td>'.htmlspecialchars($registro['ano']).'</td>
        <td>'.htmlspecialchars($registro['modalidad']).'</td>
        <td>'.htmlspecialchars($registro['id_tipo']).'</td>
        <td>'.htmlspecialchars($registro['id_programa']).'</td>
        <td>'.htmlspecialchars($registro['nom_proyecto']).'</td>
        <td>'.htmlspecialchars($registro['docentes']).'</td>
        <td>'.htmlspecialchars($registro['estudiantes']).'</td>
    </tr>';
}

$html .= '</tbody></table>';

$html .= '<div class="footer">
    <p>Reporte generado el '.date('d/m/Y H:i:s').'</p>
</div>
</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Reporte_Proyectos_';
if ($proyecto_seleccionado && $proyecto_seleccionado !== "") {
    $filename .= 'Proyecto_'.preg_replace('/[^a-zA-Z0-9]/', '_', substr($proyecto_seleccionado, 0, 20)).'_';
} else {
    if ($tipo && $tipo !== "") $filename .= preg_replace('/[^a-zA-Z0-9]/', '_', $tipo).'_';
    if ($programa && $programa !== "") $filename .= preg_replace('/[^a-zA-Z0-9]/', '_', $programa).'_';
}
$filename .= date('Ymd_His').'.pdf';

$dompdf->stream($filename, array("Attachment" => true));
?>