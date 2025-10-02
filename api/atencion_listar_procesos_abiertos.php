<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
// Especialistas pueden ver procesos abiertos
try {
    $hasSus = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='plan_suscripciones' LIMIT 1")->fetchColumn();
    $hasPlanes = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='planes' LIMIT 1")->fetchColumn();
    if ($hasSus && $hasPlanes) {
        $sql = "SELECT ap.id, ap.paciente_id, ap.motivo, ap.urgencia, ap.estado,
                       CONCAT(p.nombres,' ',p.apellidos) AS paciente,
                       pl.nombre AS plan,
                       CASE WHEN (SELECT DATE_ADD(MIN(pp.fecha_pago), INTERVAL 45 DAY) FROM plan_pagos pp WHERE pp.paciente_id = p.id AND pp.tipo_pago = 'mensualidad') IS NOT NULL AND (SELECT DATE_ADD(MIN(pp.fecha_pago), INTERVAL 45 DAY) FROM plan_pagos pp WHERE pp.paciente_id = p.id AND pp.tipo_pago = 'mensualidad') <= CURDATE() THEN 'Activa' ELSE 'En espera' END AS cobertura
                FROM atencion_procesos ap
                INNER JOIN pacientes p ON p.id=ap.paciente_id
                LEFT JOIN plan_suscripciones ps ON ps.paciente_id=p.id AND ps.activo=1
                LEFT JOIN planes pl ON pl.id=ps.plan_id
                WHERE ap.estado='abierto'
                ORDER BY ap.creado_en DESC";
    } else {
        $sql = "SELECT ap.id, ap.paciente_id, ap.motivo, ap.urgencia, ap.estado,
                       CONCAT(p.nombres,' ',p.apellidos) AS paciente,
                       NULL AS plan,
                       'Desconocida' AS cobertura
                FROM atencion_procesos ap
                INNER JOIN pacientes p ON p.id=ap.paciente_id
                WHERE ap.estado='abierto'
                ORDER BY ap.creado_en DESC";
    }
    $stmt = $pdo->query($sql);
    echo json_encode(['status' => 'ok', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al listar', 'detail' => $e->getMessage()]);
}
