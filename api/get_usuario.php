<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');

// Solo validar que el usuario haya iniciado sesión (auth_check.php lo hace)

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado.']);
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT u.id, u.username, u.nombre_completo, u.cedula, u.telefono, u.rol AS rol_id, r.nombre AS rol_nombre
                             FROM usuarios u
                             LEFT JOIN roles r ON u.rol = r.id
                             WHERE u.id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $usuario['rol_slug'] = rol_to_slug($usuario['rol_nombre'] ?? '');
        echo json_encode(['status' => 'success', 'data' => $usuario]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener el usuario: ' . $e->getMessage()]);
}
