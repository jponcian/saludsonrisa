<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
if ($rol !== 'admin_usuarios') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}
$id_paciente = isset($_GET['id_paciente']) ? (int)$_GET['id_paciente'] : 0;
if (!$id_paciente) {
    echo json_encode(['status' => 'error', 'message' => 'Paciente requerido']);
    exit;
}
try {
    // Nuevo modelo: pagos referenciados por paciente_id (si la estructura lo permite)
    $chkCols = $pdo->query("SHOW COLUMNS FROM plan_pagos")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('paciente_id', $chkCols)) {
        $tienePeriodoDesde = in_array('periododesde', $chkCols, true);
        $tienePeriodoHasta = in_array('periodohasta', $chkCols, true);
        $extra = '';
        if ($tienePeriodoDesde) $extra .= ', periododesde';
        if ($tienePeriodoHasta) $extra .= ', periodohasta';
        // Ordenar descendente por periodohasta si existe, sino por fecha_pago
        if ($tienePeriodoHasta) {
            $stmt = $pdo->prepare('SELECT id, fecha_pago AS fecha, tipo_pago AS tipo, monto, referencia, plan_id' . $extra . ' FROM plan_pagos WHERE paciente_id=? ORDER BY periodohasta DESC, id DESC');
        } else {
            $stmt = $pdo->prepare('SELECT id, fecha_pago AS fecha, tipo_pago AS tipo, monto, referencia, plan_id' . $extra . ' FROM plan_pagos WHERE paciente_id=? ORDER BY fecha_pago DESC, id DESC');
        }
        $stmt->execute([$id_paciente]);
        echo json_encode(['status' => 'ok', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } elseif (in_array('suscripcion_id', $chkCols)) {
        // fallback si sigue esquema anterior pero no hay suscripción (retorna vacío)
        echo json_encode(['status' => 'ok', 'data' => []]);
    } else {
        echo json_encode(['status' => 'ok', 'data' => []]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al listar pagos', 'detail' => $e->getMessage()]);
}
