<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');

// Validar permisos (especialista o administrador de atención)
$puedeRegistrarConsulta = in_array(6, $permisos_usuario, true) || in_array(5, $permisos_usuario, true);
if (!$puedeRegistrarConsulta) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado para esta acción.']);
    exit;
}

// Recoger y validar datos de entrada
$id_paciente = isset($_POST['id_paciente']) ? (int) $_POST['id_paciente'] : 0;
$diagnostico = trim($_POST['diagnostico'] ?? '');
$tratamiento = trim($_POST['procedimiento'] ?? '');
$observaciones = trim($_POST['indicaciones'] ?? '');
$proceso_id = isset($_POST['proceso_id']) ? (int) $_POST['proceso_id'] : null;
$items_plan = isset($_POST['items_plan']) && is_array($_POST['items_plan']) ? $_POST['items_plan'] : [];

$debug_post = print_r($_POST, true);
$debug_items_plan = print_r($items_plan, true);
file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " POST: " . $debug_post . " items_plan: " . $debug_items_plan . "\n", FILE_APPEND);

if (!$id_paciente || $diagnostico === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos: Paciente y diagnóstico son requeridos.']);
    exit;
}

$pdo->beginTransaction();
try {
    // 1. Insertar la consulta médica
    $sqlConsulta = "INSERT INTO consultas (paciente_id, especialista_id, fecha_consulta, diagnostico, tratamiento, observaciones, proceso_id) VALUES (?, ?, NOW(), ?, ?, ?, ?)";
    $stmtConsulta = $pdo->prepare($sqlConsulta);
    $stmtConsulta->execute([$id_paciente, $usuario_id, $diagnostico, $tratamiento, $observaciones, $proceso_id]);
    $id_consulta = $pdo->lastInsertId();

    $response_message = 'Consulta registrada correctamente.';

    // 2. Lógica de consumo de servicios del plan
    $stmtSuscripcion = $pdo->prepare("SELECT id, plan_id FROM plan_suscripciones WHERE paciente_id = ? AND estado IN ('activa', 'pendiente', 'espera') AND activo = 1 LIMIT 1");
    $stmtSuscripcion->execute([$id_paciente]);
    $suscripcion = $stmtSuscripcion->fetch(PDO::FETCH_ASSOC);

    if ($suscripcion && is_array($items_plan) && count($items_plan) > 0) {
        // Descontar cada ítem marcado
        foreach ($items_plan as $codigo_limite) {
            file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Descontando código: " . $codigo_limite . "\n", FILE_APPEND);
            $stmtLimite = $pdo->prepare("SELECT nombre_limite FROM vw_paciente_limites WHERE paciente_id = ? AND codigo = ? LIMIT 1");
            $stmtLimite->execute([$id_paciente, $codigo_limite]);
            $nombre_servicio = $stmtLimite->fetchColumn() ?: $codigo_limite;
            $sqlConsumo = "INSERT INTO servicios_consumidos (paciente_id, tipo_servicio, codigo_limite, cantidad, realizado_por, created_at, estado) VALUES (?, ?, ?, 1, ?, NOW(), 'registrado')";
            $stmtConsumo = $pdo->prepare($sqlConsumo);
            $stmtConsumo->execute([$id_paciente, $nombre_servicio, $codigo_limite, $usuario_id]);
        }
        $response_message = 'Consulta registrada y servicios descontados del plan.';
    }

    // 3. Confirmar la transacción
    $pdo->commit();

    echo json_encode(['status' => 'ok', 'id' => $id_consulta, 'message' => $response_message]);
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $pdo->rollBack();
    http_response_code(500);
    // Para depuración: error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error en el servidor al registrar la consulta.']);
}
