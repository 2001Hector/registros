<?php
session_start();
require 'database.php';

$user = null;
if (isset($_SESSION['user_id'])) {
    $records = $conn->prepare('SELECT id, email, password FROM users WHERE id = :id');
    $records->bindParam(':id', $_SESSION['user_id']);
    $records->execute();

    // Obtener el resultado como un array asociativo
    $results = $records->fetch(PDO::FETCH_ASSOC);

    // Verifica si hay resultados
    if ($results !== false) { // Verifica si results no es false
        $user = $results; // Asigna el resultado a $user
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to your WebApp</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require 'partials/header.php'; ?>

    <?php if (!empty($user)): ?>
        <br> Welcome. <?= htmlspecialchars($user['email']); ?>
        <br>You are Successfully Logged In
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <h1>Proyectos Uniguajira</h1>
        <a href="login.php">Iniciar sesión</a> o
        <a href="signup.php">Registrarse</a>
    <?php endif; ?>
</body>
</html>
