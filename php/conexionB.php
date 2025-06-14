<?php
session_start(); // Iniciar la sesión
$host = 'mysql.hostinger.com';
$user = 'u648222299_hector';
$password = 'Proyectou2025';
$dbname = 'u648222299_base_hector';

// Crear conexión
$pdo = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($pdo->connect_error) {
    die("Conexión fallida: " . $pdo->connect_error);
}