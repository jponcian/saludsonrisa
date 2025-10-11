<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
// Solo validar que el usuario haya iniciado sesión (auth_check.php lo hace)
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
    exit;
}
$stmt = $pdo->prepare('DELETE FROM consultas WHERE id = ?');
if ($stmt->execute([$id])) {
    echo json_encode(['status' => 'success', 'message' => 'Consulta eliminada correctamente.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar consulta.']);
}
