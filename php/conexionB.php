<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'mysql.hostinger.com';
$user = 'u648222299_hector';
$password = 'Proyectou2025';
$dbname = 'u648222299_base_hector';

// Crear conexión como variable global
$GLOBALS['conn'] = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($GLOBALS['conn']->connect_error) {
    die("Conexión fallida: " . $GLOBALS['conn']->connect_error);
}
?>