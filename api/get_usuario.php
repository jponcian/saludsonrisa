<?php
require_once 'conexion.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado.']);
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT id, username, nombre_completo, cedula, telefono, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        echo json_encode(['status' => 'success', 'data' => $usuario]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener el usuario: ' . $e->getMessage()]);
}
?>