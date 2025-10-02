<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID de especialista no proporcionado.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM especialistas WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount()) {
        echo json_encode(['status' => 'success', 'message' => 'Especialista eliminado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'El especialista no existe o ya fue eliminado.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el especialista: ' . $e->getMessage()]);
}
?>