<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
if ($rol !== 'admin_usuarios') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}
try {
    // fallback si tabla no existe
    $chk = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='planes'")->fetchColumn();
    if (!$chk) {
        echo json_encode(['status' => 'ok', 'data' => []]);
        return;
    }
    // Ordenamos de mayor a menor según costo mensual (y por nombre como secundario)
    $stmt = $pdo->query("SELECT id, nombre, costo_mensual AS monto_mensual, cuota_afiliacion FROM planes ORDER BY costo_mensual DESC, nombre ASC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'ok', 'data' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al listar planes']);
}
