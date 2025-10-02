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
    // Detectar tablas y columnas disponibles
    $tableExists = function ($name) use ($pdo) {
        $q = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=? LIMIT 1");
        $q->execute([$name]);
        return (bool)$q->fetchColumn();
    };
    $colExists = function ($table, $col) use ($pdo) {
        try {
            $q = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=? LIMIT 1");
            $q->execute([$table, $col]);
            return (bool)$q->fetchColumn();
        } catch (Exception $e) {
            return false;
        }
    };

    $hasPlanes = $tableExists('planes');
    $hasSus = $tableExists('plan_suscripciones');
    $hasPac = $tableExists('pacientes');
    if (!$hasPac) {
        echo json_encode(['status' => 'ok', 'data' => null]);
        return;
    }

    // Preferir plan_suscripciones activa si existe
    if ($hasSus && $hasPlanes) {
        $sql = "SELECT ps.plan_id, pl.nombre AS plan_nombre, 
                       (SELECT MIN(pp.fecha_pago) FROM plan_pagos pp WHERE pp.paciente_id = ps.paciente_id AND pp.tipo_pago IN ('inscripcion', 'inscripcion_diferencia')) AS fecha_inscripcion, 
                       (SELECT DATE_ADD(MIN(pp.fecha_pago), INTERVAL 45 DAY) FROM plan_pagos pp WHERE pp.paciente_id = ps.paciente_id AND pp.tipo_pago = 'mensualidad') AS fecha_inicio_cobertura,
                       ps.monto_mensual, CASE WHEN ((SELECT DATE_ADD(MIN(pp.fecha_pago), INTERVAL 45 DAY) FROM plan_pagos pp WHERE pp.paciente_id = ps.paciente_id AND pp.tipo_pago = 'mensualidad') IS NOT NULL AND (SELECT DATE_ADD(MIN(pp.fecha_pago), INTERVAL 45 DAY) FROM plan_pagos pp WHERE pp.paciente_id = ps.paciente_id AND pp.tipo_pago = 'mensualidad') <= CURDATE()) THEN 'activo' ELSE ps.estado END AS estado
                FROM plan_suscripciones ps
                INNER JOIN planes pl ON pl.id=ps.plan_id
                WHERE ps.paciente_id=? AND ps.activo=1
                ORDER BY ps.id DESC LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute([$id_paciente]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo json_encode(['status' => 'ok', 'data' => $row]);
            return;
        }
        // Si no hay suscripción activa, continuar a fallback paciente
    }

    if ($hasPlanes && $colExists('pacientes', 'plan_id')) {
        // columnas opcionales
        $fechaCol = $colExists('pacientes', 'fecha_registro') ? 'fecha_registro' : ($colExists('pacientes', 'creado_en') ? 'creado_en' : null);
        $selFecha = "(SELECT MIN(pp.fecha_pago) FROM plan_pagos pp WHERE pp.paciente_id = p.id AND pp.tipo_pago IN ('inscripcion', 'inscripcion_diferencia'))";
        $add45 = "(SELECT DATE_ADD(MIN(pp.fecha_pago), INTERVAL 45 DAY) FROM plan_pagos pp WHERE pp.paciente_id = p.id AND pp.tipo_pago = 'mensualidad')";
        $costoCol = $colExists('planes', 'costo_mensual') ? 'pl.costo_mensual' : ($colExists('planes', 'monto_mensual') ? 'pl.monto_mensual' : 'NULL');
        $estadoExpr = "CASE WHEN $add45 IS NOT NULL AND $add45 <= CURDATE() THEN 'activo' ELSE 'pendiente' END";
        $sql2 = "SELECT p.plan_id, pl.nombre AS plan_nombre, $selFecha AS fecha_inscripcion, $add45 AS fecha_inicio_cobertura, $costoCol AS monto_mensual, $estadoExpr AS estado FROM pacientes p LEFT JOIN planes pl ON pl.id=p.plan_id WHERE p.id=? LIMIT 1";
        $st2 = $pdo->prepare($sql2);
        $st2->execute([$id_paciente]);
        $row2 = $st2->fetch(PDO::FETCH_ASSOC);
        if (!$row2 || !$row2['plan_id']) {
            echo json_encode(['status' => 'ok', 'data' => null]);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $row2]);
        return;
    }
    echo json_encode(['status' => 'ok', 'data' => null]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error', 'detail' => $e->getMessage()]);
}
