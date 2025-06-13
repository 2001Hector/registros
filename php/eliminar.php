<?php
session_start();
require_once 'conexionB.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $archivo_id = intval($_POST['id']);

    // Obtener info del archivo
    $sql_archivo = "SELECT ruta, id_registrador FROM archivos WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql_archivo);
    $stmt->bind_param("i", $archivo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "❌ El archivo no existe.";
        header("Location: upload.php");
        exit;
    }

    $archivo = $result->fetch_assoc();
    $ruta = $archivo['ruta'];
    $id_registrador = $archivo['id_registrador'];
    $stmt->close();

    // Eliminar archivo físico si existe
    if (!empty($ruta) && file_exists($ruta)) {
        unlink($ruta);
    }

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Eliminar un registro del mismo id_registrador (el más antiguo por ejemplo)
        $sql_registro = "DELETE FROM registro 
                         WHERE id_registrador = ? 
                         AND id_registro = (
                             SELECT id_registro FROM (
                                 SELECT id_registro FROM registro 
                                 WHERE id_registrador = ? 
                                 ORDER BY id_registro ASC LIMIT 1
                             ) AS sub
                         )";
        $stmt = $conn->prepare($sql_registro);
        $stmt->bind_param("ii", $id_registrador, $id_registrador);
        $stmt->execute();
        $stmt->close();

        // Eliminar de archivos
        $sql_archivo_delete = "DELETE FROM archivos WHERE id = ?";
        $stmt = $conn->prepare($sql_archivo_delete);
        $stmt->bind_param("i", $archivo_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $_SESSION['mensaje'] = "✅ Archivo y registro eliminados correctamente.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "❌ Error al eliminar: " . $e->getMessage();
    }

    $conn->close();
    header("Location: upload.php");
    exit;
}
?>
