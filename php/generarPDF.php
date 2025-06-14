<?php
require_once 'conexionB.php';

// Consulta para obtener los nombres de proyectos
$sql_proyectos = "SELECT DISTINCT nom_proyecto FROM registro";
$resultado_proyectos = $conn->query($sql_proyectos);
$proyectos = [];
while ($row = $resultado_proyectos->fetch_assoc()) {
    $proyectos[] = $row['nom_proyecto'];
}
$proyectos_json = json_encode($proyectos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="../css/style_reg_usuar.css">
    <meta charset="UTF-8">
    <title>Generador de Reportes PDF</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../imagenes/favicon.ico" type="image/x-icon">
    
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .filter-container { 
            background: #f5f5f5; 
            padding: 20px; 
            border-radius: 5px; 
            max-width: 600px;
            margin: 0 auto;
        }
        .filter-group { margin-bottom: 15px; }
        label { display: inline-block; width: 200px; }
        select, input { 
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button { 
            padding: 10px 20px; 
            background:rgba(236, 155, 34, 0.95); 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px;
            display: block;
            margin: 20px auto;
        }
        button:hover { background:rgb(207, 160, 30); }
        .confirmation-dialog {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .dialog-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            max-width: 400px;
            text-align: center;
        }
        .dialog-buttons {
            margin-top: 20px;
        }
        .dialog-buttons button {
            margin: 0 10px;
            padding: 8px 15px;
        }
        .title {
            text-align: center;
            margin-bottom: 20px;
            color:rgb(12, 12, 12);
        }
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .hidden-id {
            display: none;
        }
        .separator {
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }
        .search-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<header>
    <img src="../imagenes/logo.ico" alt="Logo de la empresa" width="150" height="auto">
</header>
<body>
    <br><br>
    <div class="filter-container">
        <h2 style="text-align: center;">Generar Reporte de Proyectos</h2>
        
        <form id="reportForm" action="pdf.php" method="post">
            <div class="form-container">
    <label for="id_tipo">Tipo de Proyecto:</label>
    <select id="id_tipo" name="tipo">
        <option value="">Todos </option>
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
    </select>
</div>

            <div class="form-container">
    <label for="id_programa">Facultad/Programa:</label>
    <select id="id_programa" name="programa">
        <option value="">Todos </option>
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
    </select>
</div>

            <div class="separator"></div>

            <div class="search-title">Búsqueda específica por nombre de proyecto:</div>
            
            <div class="form-container">
                <label for="nombre_proyecto">Nombre del Proyecto:</label>
                <input type="text" id="nombre_proyecto" name="nombre_proyecto" placeholder="Escribe para buscar proyectos">
                <input type="hidden" id="proyecto_seleccionado" name="proyecto_seleccionado">
                <small style="display: block; margin-top: 5px; color: #666;">Este filtro funciona independientemente de los filtros superiores</small>
            </div>

            <button type="button" onclick="validateForm()">
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAACBUlEQVR4nO2Z20sbQRjF96/Rin2wBS+tF6qIV6rBK1jyUkgrVRER8UItYqWgiDcUX1pR6Ism09c+9FXc/B0qjDGJ8RITjZYj860uUosXsju7yhw4sPvNLHw/5szswmqakpLS0xdYsBYsuA0WRLreq3+/Lh8goG9Z0bwwz8iTDwGLmr8CIAiPT3/UAFIhYBMAlwVhJwCXAWE3ALcbQgYAtxNCFgC3C8JKgN3cijshYk3t664FOPm2JB8CFgI8xJoCuJRaAaYilJ6ebISiNV5EShvJ0Vovkl8WqJ4YmjXrkfJWxFo+4WTsu/nc/rtuhIvrTR/3TjgDEHpRht1XNYh3jiJcVAeemY/UzCriXV/pLN9v68KBt4fmifvj/ikDvNqLnexCHH78TD4dX3EOQDQjrpMji0aTfZMmwNk8o7HzH7+x87wI4cK3JkAop9T5CAmAyJsGeruKOPHMAqTm/DcAqOnKNvBnBYBfN1Yg6zViDT5yam7NOQDRqGhGZDk5PE/1/wGESzw034wQAXwgOwpwFaHr/hcgMThtfN80t7svQtFbAMTGDr0sN65LPPi7/MddAEcdI0gMTN+on07+xKFviBzvHqM9Av+GOZ4YnKWTC04D2G1NAVxKrQBTEUpPKkJMRSg9qQgxFaH0pCLEVITc84vp3g7om9YB/NKrpUIE9E2wjSrLAJSUlDTX6gKA7OjnTXtwWgAAAABJRU5ErkJggg==" alt="pdf icon">
            </button>
        </form>
    </div>

    <!-- Diálogo de confirmación -->
    <div id="confirmationDialog" class="confirmation-dialog">
        <div class="dialog-content">
           <i> <h3>Confirmación</h3>
            <p id="confirmationMessage">¿Estás seguro que deseas generar el reporte con los filtros seleccionados?</p><br>
            <div class="title" >
               <button onclick="submitForm()">Sí, Generar PDF</button>
                <button onclick="hideDialog()">Cancelar</button> <i>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        var proyectos = <?php echo $proyectos_json; ?>;
        
        $(function() {
            $("#nombre_proyecto").autocomplete({
                source: proyectos,
                select: function(event, ui) {
                    $("#nombre_proyecto").val(ui.item.value);
                    $("#proyecto_seleccionado").val(ui.item.value);
                    // Limpiamos los otros filtros cuando se selecciona un proyecto
                    $("#id_tipo").val("");
                    $("#id_programa").val("");
                    return false;
                }
            });
            
            // Si se modifica cualquier otro filtro, limpiamos el proyecto seleccionado
            $("#id_tipo, #id_programa").change(function() {
                $("#nombre_proyecto").val("");
                $("#proyecto_seleccionado").val("");
            });
        });
        
        function validateForm() {
            const tipo = document.getElementById('id_tipo').value;
            const programa = document.getElementById('id_programa').value;
            const proyecto = document.getElementById('proyecto_seleccionado').value;
            
            let message = "¿Estás seguro que deseas generar el reporte con los siguientes filtros?";
            
            if (proyecto) {
                message += "\nProyecto específico: " + proyecto;
            } else {
                if (tipo) message += "\nTipo: " + tipo;
                if (programa) message += "\nFacultad/Programa: " + programa;
                if (!tipo && !programa) message += "\nSin filtros (todos los proyectos)";
            }
            
            showConfirmation(message);
        }
        
        function showConfirmation(message) {
            document.getElementById('confirmationMessage').innerText = message;
            document.getElementById('confirmationDialog').style.display = 'flex';
        }
        
        function hideDialog() {
            document.getElementById('confirmationDialog').style.display = 'none';
        }
        
        function submitForm() {
            hideDialog();
            document.getElementById('reportForm').submit();
        }
    </script>
   
    <a href="totales.php" >
  <img src="data:image/png;base64,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" alt="Volver">
</a>
<BR><BR>
</body>

</html>