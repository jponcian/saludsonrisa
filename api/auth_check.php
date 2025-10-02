<?php
session_start();

// Si no existe la variable de sesión, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit();
}

// Guardar datos del usuario en variables para fácil acceso
$usuario_id = $_SESSION['usuario_id'];
$username = $_SESSION['username'];
$rol = $_SESSION['rol'];
$nombre_completo = $_SESSION['nombre_completo'];
?>