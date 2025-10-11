<?php
require_once 'auth_check.php';

// Validar permiso para gestionar usuarios (permiso_id = 4)
// Solo validar que el usuario haya iniciado sesión (auth_check.php lo hace)

header('Content-Type: application/json');
require 'conexion.php';

try {
    $stmt = $pdo->query("SELECT u.id, u.username, u.foto, u.rol AS rol_id, r.nombre AS rol_nombre, u.nombre_completo, u.cedula, u.telefono, u.fecha_creacion
                         FROM usuarios u
                         LEFT JOIN roles r ON u.rol = r.id
                         ORDER BY u.id DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as &$usuario) {
        $usuario['rol_slug'] = rol_to_slug($usuario['rol_nombre'] ?? '');
    }
    unset($usuario);

    echo json_encode(['data' => $usuarios]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener los usuarios.']);
}
