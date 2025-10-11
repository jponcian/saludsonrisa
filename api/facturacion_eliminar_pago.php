<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
// Solo validar que el usuario haya iniciado sesión (auth_check.php lo hace)
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
