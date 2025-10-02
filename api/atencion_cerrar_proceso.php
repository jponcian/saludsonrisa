<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
if ($rol !== 'admin_usuarios') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
    exit;
}
try {
    $stmt = $pdo->prepare("UPDATE atencion_procesos SET estado='cerrado', cerrado_en=NOW() WHERE id=? AND estado='abierto'");
    $stmt->execute([$id]);
    if ($stmt->rowCount() == 0) {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo cerrar (ya cerrado?)']);
        exit;
    }
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al cerrar proceso']);
}
