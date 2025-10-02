<?php
require_once 'auth_check.php';

// Solo los administradores de usuarios pueden ver la lista
if ($rol !== 'admin_usuarios') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

header('Content-Type: application/json');
require 'conexion.php';

try {
    $stmt = $pdo->query("SELECT id, username, foto, rol, nombre_completo, cedula, telefono, fecha_creacion FROM usuarios ORDER BY id DESC");
    $usuarios = $stmt->fetchAll();
    echo json_encode(['data' => $usuarios]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener los usuarios.']);
}
?>