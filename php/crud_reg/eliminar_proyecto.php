<?php
require_once __DIR__ . '/../conexionB.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['id_registrador'])) {
    $_SESSION['error'] = "Debes iniciar sesión primero";
    header("Location: ../login.php");
    exit();
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header("Location: ../registro.php");
    exit();
}

// Validar ID del proyecto
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    $_SESSION['error'] = "ID de proyecto inválido";
    header("Location: ../registro.php");
    exit();
}

$usuarioId = $_SESSION['id_registrador'];

try {
    // Verificar que el proyecto le pertenece al usuario
    $stmt = $conn->prepare("SELECT id_registro FROM registro WHERE id_registro = ? AND id_registrador = ?");
    $stmt->bind_param("ii", $id, $usuarioId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "No tienes permiso para eliminar este proyecto o no existe";
        header("Location: ../registro.php");
        exit();
    }

    // Eliminar el proyecto (solo registro de base de datos)
    $deleteStmt = $conn->prepare("DELETE FROM registro WHERE id_registro = ?");
    $deleteStmt->bind_param("i", $id);

    if ($deleteStmt->execute()) {
        $_SESSION['success'] = "Proyecto eliminado correctamente de la base de datos";
    } else {
        $_SESSION['error'] = "Error al eliminar el proyecto: " . $conn->error;
    }

    $deleteStmt->close();
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "Error en el servidor: " . $e->getMessage();
}

header("Location: ../registro.php");
exit();
?>