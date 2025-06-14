<?php
session_start(); // Iniciar la sesión
$host = 'mysql.hostinger.com';
$user = 'u648222299_hector';
$password = 'Proyectou2025';
$dbname = 'u648222299_base_hector';

// Crear conexión
$conn = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}