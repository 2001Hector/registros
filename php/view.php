<?php
// Asegúrate de que el archivo se recibe
if (isset($_GET['file'])) {
    $file = $_GET['file'];

    // Verifica si el archivo existe
    if (file_exists($file)) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        // Establecer el tipo de contenido correcto según la extensión
        if ($extension === 'pdf') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($file) . '"');
        } elseif ($extension === 'docx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: inline; filename="' . basename($file) . '"');
        } else {
            die("Tipo de archivo no soportado.");
        }

        // Leer el archivo y enviar su contenido al navegador
        readfile($file);
        exit;
    } else {
        die("El archivo no existe.");
    }
} else {
    die("No se ha especificado ningún archivo.");
}
?>

