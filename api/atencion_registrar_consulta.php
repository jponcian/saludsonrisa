<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
// Solo especialistas pueden registrar
if ($rol !== 'especialista') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}
$id_paciente = isset($_POST['id_paciente']) ? (int)$_POST['id_paciente'] : 0;
$diagnostico = trim($_POST['diagnostico'] ?? '');
$tratamiento = trim($_POST['procedimiento'] ?? ''); // Mantener compatibilidad con front (usa 'procedimiento')
$observaciones = trim($_POST['indicaciones'] ?? ''); // Front envía 'indicaciones'
$proceso_id = isset($_POST['proceso_id']) ? (int)$_POST['proceso_id'] : null; // futuro uso si se asocia consulta a proceso
if (!$id_paciente || $diagnostico === '') {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}
try {
    // Insert básico (la tabla existente usa campos paciente_id / especialista_id / tratamiento / observaciones)
    $sql = "INSERT INTO consultas (paciente_id, especialista_id, fecha_consulta, diagnostico, tratamiento, observaciones) VALUES (?, ?, NOW(), ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_paciente, $id_usuario, $diagnostico, $tratamiento, $observaciones]);
    $id_consulta = $pdo->lastInsertId();

    // (Opcional) Si en el futuro se crea tabla intermedia proceso_consultas se podría enlazar aquí.

    echo json_encode(['status' => 'ok', 'id' => $id_consulta]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al registrar']);
}
