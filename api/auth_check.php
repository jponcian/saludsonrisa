<?php
session_start();
require_once __DIR__ . '/rol_utils.php';

// Si no existe la variable de sesión, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit();
}

// Guardar datos del usuario en variables para fácil acceso
$usuario_id = $_SESSION['usuario_id'];
// Compatibilidad PHP <7: usar isset en vez de ??
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$rol_display = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$rol_id = isset($_SESSION['rol_id']) ? $_SESSION['rol_id'] : null;
$rol_slug = isset($_SESSION['rol_slug']) ? $_SESSION['rol_slug'] : null;
$permisos_sesion = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : array();
if (!is_array($permisos_sesion)) {
    $permisos_sesion = [];
}
$permisos_usuario = array_map('intval', $permisos_sesion);
$nombre_completo = isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : '';

if (!$rol_slug && $rol_display) {
    $rol_slug = rol_to_slug($rol_display);
    $_SESSION['rol_slug'] = $rol_slug;
}

// Compatibilidad: si no hubo nombre almacenado pero sí slug, generarlo
if (!$rol_display && $rol_slug) {
    $rol_display = rol_from_slug($rol_slug);
    $_SESSION['rol'] = $rol_display;
}

// Alias de compatibilidad
$rol = $rol_display;

// Asegurar que la sesión guarde los permisos normalizados
$_SESSION['permisos'] = $permisos_usuario;
?>