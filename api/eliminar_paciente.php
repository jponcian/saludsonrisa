<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
$puedeEliminarPaciente = in_array(1, $permisos_usuario, true);
if (!$puedeEliminarPaciente) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para eliminar pacientes.']);
    exit;
}
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
    exit;
}
$stmt = $pdo->prepare('DELETE FROM pacientes WHERE id = ?');
if ($stmt->execute([$id])) {
    echo json_encode(['status' => 'success', 'message' => 'Paciente eliminado correctamente.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar paciente.']);
}
