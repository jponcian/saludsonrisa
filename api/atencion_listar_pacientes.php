<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');

function tableExists($pdo, $name)
{
    try {
        $q = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=? LIMIT 1");
        $q->execute([$name]);
        return (bool)$q->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

try {
    $hasPlanes = tableExists($pdo, 'planes');
    $pacCols = $pdo->query("SHOW COLUMNS FROM pacientes")->fetchAll(PDO::FETCH_COLUMN);
    $planesCols = $hasPlanes ? $pdo->query("SHOW COLUMNS FROM planes")->fetchAll(PDO::FETCH_COLUMN) : [];

    // Helpers para elegir la primera columna existente
    $pick = function (array $candidatos, $cols) {
        foreach ($candidatos as $c) if (in_array($c, $cols, true)) return $c;
        return null;
    };

    $colId = in_array('id', $pacCols, true) ? 'p.id' : null;
    $colNombres = $pick(['nombres', 'nombre'], $pacCols);
    $colApellidos = $pick(['apellidos', 'apellido'], $pacCols);
    $colCedula = $pick(['cedula', 'documento', 'dni'], $pacCols);
    $colFechaRegistro = $pick(['fecha_registro', 'created_at', 'creado_en'], $pacCols);
    $colPlanId = in_array('plan_id', $pacCols, true) ? 'p.plan_id' : null;

    $colPlanNombre = $hasPlanes && in_array('nombre', $planesCols, true) ? 'pl.nombre' : null;
    $colPlanClave = $hasPlanes && in_array('clave', $planesCols, true) ? 'pl.clave' : null;
    $colCostoMensualReal = $hasPlanes && in_array('costo_mensual', $planesCols, true) ? 'pl.costo_mensual' : ($hasPlanes && in_array('monto_mensual', $planesCols, true) ? 'pl.monto_mensual' : null);
    $colCuotaAfiliacion = $hasPlanes && in_array('cuota_afiliacion', $planesCols, true) ? 'pl.cuota_afiliacion' : null;
    // Si ya no existe plan_id en pacientes, no podemos hacer JOIN ni seleccionar columnas de pl
    if (!$colPlanId) {
        $colPlanNombre = null;
        $colPlanClave = null;
        $colCostoMensualReal = null;
        $colCuotaAfiliacion = null;
    }

    // Construcción dinámica principal
    $select = [];
    $select[] = $colId ?: 'NULL AS id';
    if ($colNombres && $colApellidos) {
        $select[] = "CONCAT(p.$colNombres,' ',p.$colApellidos) AS nombre";
    } elseif ($colNombres) {
        $select[] = "p.$colNombres AS nombre";
    } elseif ($colApellidos) {
        $select[] = "p.$colApellidos AS nombre";
    } else {
        $select[] = "'' AS nombre";
    }
    $select[] = $colCedula ? "p.$colCedula AS documento" : "'' AS documento";
    $select[] = 'pl.nombre AS plan_nombre';
    $select[] = 'pl.clave AS plan_clave';
    $select[] = "(SELECT MIN(pp.fecha_pago) FROM plan_pagos pp WHERE pp.paciente_id = p.id AND pp.tipo_pago IN ('inscripcion', 'inscripcion_diferencia')) AS fecha_inscripcion";
    // cobertura inicio = fecha del primer pago de mensualidad + 45 días si existe
    $select[] = "(SELECT DATE_ADD(MIN(pp.fecha_pago), INTERVAL 45 DAY) FROM plan_pagos pp WHERE pp.paciente_id = p.id AND pp.tipo_pago = 'mensualidad') AS fecha_inicio_cobertura";
    $select[] = "(SELECT DATEDIFF(DATE_ADD(MIN(pp.fecha_pago), INTERVAL 45 DAY), CURDATE()) FROM plan_pagos pp WHERE pp.paciente_id = p.id AND pp.tipo_pago = 'mensualidad') AS dias_para_cobertura";
    $select[] = "CASE WHEN (SELECT DATE_ADD(MIN(pp.fecha_pago), INTERVAL 45 DAY) FROM plan_pagos pp WHERE pp.paciente_id = p.id AND pp.tipo_pago = 'mensualidad') <= CURDATE() AND ps.id IS NOT NULL THEN 'si' ELSE 'no' END AS cobertura_activa";
    $select[] = "CASE WHEN ps.id IS NULL THEN 'sin_plan' ELSE (CASE WHEN (SELECT DATE_ADD(MIN(pp.fecha_pago), INTERVAL 45 DAY) FROM plan_pagos pp WHERE pp.paciente_id = p.id AND pp.tipo_pago = 'mensualidad') <= CURDATE() THEN 'activo' ELSE 'pendiente' END) END AS estado_plan";
    $select[] = 'pl.costo_mensual AS monto_mensual';
    $select[] = 'pl.cuota_afiliacion AS cuota_afiliacion';

    $from = ' FROM pacientes p LEFT JOIN plan_suscripciones ps ON ps.paciente_id = p.id AND ps.activo = 1 LEFT JOIN planes pl ON pl.id = ps.plan_id';

    // Orden: si existen apellidos/nombres usar, si no por id
    $order = '';
    if (in_array('apellidos', $pacCols, true)) {
        $order = ' ORDER BY p.apellidos, nombre';
    } elseif (in_array('nombres', $pacCols, true)) {
        $order = ' ORDER BY nombre';
    } else {
        $order = ' ORDER BY id';
    }

    $sql = 'SELECT ' . implode(',', $select) . $from . $order;
    $debug = isset($_GET['debug']);
    try {
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $inner) {
        if ($debug) {
            echo json_encode(['status' => 'error', 'message' => 'SQL fail', 'sql' => $sql, 'detail' => $inner->getMessage()]);
            return;
        }
        throw $inner;
    }
    // Limpiar campos de plan si no existe plan_id en pacientes
    // Ya no necesario, ahora se hace JOIN con plan_suscripciones
    echo json_encode(['status' => 'ok', 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    $debug = isset($_GET['debug']);
    $out = ['status' => 'error', 'message' => 'Error al listar pacientes'];
    if ($debug) {
        $out['detail'] = $e->getMessage();
        if (isset($sql)) $out['sql'] = $sql;
    }
    echo json_encode($out);
}
