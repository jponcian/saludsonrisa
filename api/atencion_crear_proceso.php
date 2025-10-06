<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
if ($rol !== 'admin_usuarios') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}
$id_paciente = isset($_POST['id_paciente']) ? (int)$_POST['id_paciente'] : 0;
if ($rol === 'admin_usuarios') {
    $motivo = 'APERTURA ADMIN';
    $urgencia = 'programada';
} else {
    $motivo = trim($_POST['motivo'] ?? '');
    $urgencia = trim($_POST['urgencia'] ?? '');
}

$obs = trim($_POST['obs'] ?? '');

if (!$id_paciente || $motivo === '' || $urgencia === '') {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}
try {
    // Verificar si ya tiene proceso abierto
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM atencion_procesos WHERE paciente_id=? AND estado='abierto'");
    $stmt->execute([$id_paciente]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Ya existe un proceso abierto']);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO atencion_procesos (paciente_id, motivo, urgencia, observaciones, estado, creado_en) VALUES (?,?,?,?, 'abierto', NOW())");
    $stmt->execute([$id_paciente, $motivo, $urgencia, $obs]);
    $id = $pdo->lastInsertId();
    echo json_encode(['status' => 'ok', 'id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al abrir proceso']);
}
