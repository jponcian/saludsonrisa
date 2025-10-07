<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');

// Validar rol de especialista o administrador
if ($rol !== 'especialista' && $rol !== 'admin' && $rol !== 'admin_usuarios') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado para esta acción.']);
    exit;
}

// Recoger y validar datos de entrada
$id_paciente = isset($_POST['id_paciente']) ? (int)$_POST['id_paciente'] : 0;
$diagnostico = trim($_POST['diagnostico'] ?? '');
$tratamiento = trim($_POST['procedimiento'] ?? '');
$observaciones = trim($_POST['indicaciones'] ?? '');
$proceso_id = isset($_POST['proceso_id']) ? (int)$_POST['proceso_id'] : null;

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
    $stmtSuscripcion = $pdo->prepare("SELECT id, plan_id FROM plan_suscripciones WHERE paciente_id = ? AND estado = 'activa' AND activo = 1 LIMIT 1");
    $stmtSuscripcion->execute([$id_paciente]);
    $suscripcion = $stmtSuscripcion->fetch(PDO::FETCH_ASSOC);

    if ($suscripcion) {
        // El paciente tiene un plan activo, se procede a verificar y consumir el servicio
        $codigo_limite = 'consultas_con_especialistas';
        $nombre_servicio = 'Consulta con Especialistas';

        // Verificar si le quedan servicios disponibles usando la vista
        $stmtLimites = $pdo->prepare("SELECT restante FROM vw_paciente_limites WHERE paciente_id = ? AND codigo = ?");
        $stmtLimites->execute([$id_paciente, $codigo_limite]);
        $limite = $stmtLimites->fetch(PDO::FETCH_ASSOC);

        if ($limite && $limite['restante'] > 0) {
            // Sí tiene servicios, se registra el consumo
            $sqlConsumo = "INSERT INTO servicios_consumidos (paciente_id, tipo_servicio, codigo_limite, cantidad, realizado_por, created_at, estado) VALUES (?, ?, ?, 1, ?, NOW(), 'registrado')";
            $stmtConsumo = $pdo->prepare($sqlConsumo);
            $stmtConsumo->execute([$id_paciente, $nombre_servicio, $codigo_limite, $usuario_id]);

            $response_message = 'Consulta registrada y 1 servicio "' . $nombre_servicio . '" ha sido descontado del plan.';
        } else {
            // No le quedan servicios de este tipo
            $response_message = 'Consulta registrada. ADVERTENCIA: El paciente no tiene más servicios de "' . $nombre_servicio . '" disponibles en su plan.';
        }
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
