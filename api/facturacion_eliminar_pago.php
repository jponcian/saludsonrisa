<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
$puedeGestionarFacturacion = in_array(2, $permisos_usuario, true);
if (!$puedeGestionarFacturacion) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}
$id = (int) ($_POST['id'] ?? 0);
if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID de pago requerido']);
    exit;
}
try {
    $stmt = $pdo->prepare('DELETE FROM plan_pagos WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar pago', 'detail' => $e->getMessage()]);
}
