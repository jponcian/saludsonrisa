<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
// Solo validar que el usuario haya iniciado sesión (auth_check.php lo hace)

$id_paciente = (int) ($_POST['id_paciente'] ?? 0);
$tipo = $_POST['tipo_pago'] ?? '';
$monto = isset($_POST['monto']) ? (float) $_POST['monto'] : 0.0;
$fecha = $_POST['fecha'] ?? '';
$modalidad = $_POST['modalidad_pago'] ?? '';
$referencia = trim($_POST['referencia'] ?? '');

if (!$id_paciente || !$tipo || !$monto || !$fecha || !$modalidad) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

if ($referencia === '') {
    echo json_encode(['status' => 'error', 'message' => 'Referencia requerida']);
    exit;
}

try {
    // Verificar existencia de plan_suscripciones y plan_pagos, tomar suscripción activa y validar referencia única
    $stSus = $pdo->prepare("SELECT ps.*, pl.cuota_afiliacion FROM plan_suscripciones ps INNER JOIN planes pl ON pl.id=ps.plan_id WHERE ps.paciente_id=? AND ps.activo=1 ORDER BY ps.id DESC LIMIT 1");
    $stSus->execute([$id_paciente]);
    $sus = $stSus->fetch(PDO::FETCH_ASSOC);
    if (!$sus) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }
    $plan_id = (int) $sus['plan_id'];
    $cuotaAfiliacion = isset($sus['cuota_afiliacion']) ? (float) $sus['cuota_afiliacion'] : 0.0;
    $colsPagos = $pdo->query("SHOW COLUMNS FROM plan_pagos")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('referencia', $colsPagos, true)) {
        $stRef = $pdo->prepare("SELECT 1 FROM plan_pagos WHERE UPPER(referencia)=UPPER(?) LIMIT 1");
        $stRef->execute([$referencia]);
        if ($stRef->fetchColumn()) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            exit;
        }
    }

    // Total inscripción ya pagado para este plan
    $stmtTot = $pdo->prepare("SELECT COALESCE(SUM(monto),0) FROM plan_pagos WHERE paciente_id=? AND plan_id=? AND tipo_pago IN ('inscripcion','inscripcion_diferencia')");
    $stmtTot->execute([$id_paciente, $plan_id]);
    $totalInscripcionPagada = (float) $stmtTot->fetchColumn();
    if ($tipo === 'inscripcion') {
        if ($cuotaAfiliacion <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            exit;
        }
        if ($totalInscripcionPagada + 0.009 >= $cuotaAfiliacion) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            exit;
        }
        if (($totalInscripcionPagada + $monto) > $cuotaAfiliacion + 0.009) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            exit;
        }
    } elseif ($tipo === 'mensualidad') {
        if ($cuotaAfiliacion > 0 && $totalInscripcionPagada + 0.009 < $cuotaAfiliacion) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    // Calcular periodo para mensualidad si las columnas existen
    $cols = $pdo->query("SHOW COLUMNS FROM plan_pagos")->fetchAll(PDO::FETCH_COLUMN);
    $tieneCreado = in_array('creado_en', $cols, true);
    $tienePeriodoDesde = in_array('periododesde', $cols, true);
    $tienePeriodoHasta = in_array('periodohasta', $cols, true);

    $periodoDesde = null;
    $periodoHasta = null;
    if ($tipo === 'mensualidad' && $tienePeriodoDesde && $tienePeriodoHasta) {
        // Obtener último periodo mensualidad para este paciente y plan
        $stmtUlt = $pdo->prepare("SELECT periododesde, periodohasta FROM plan_pagos WHERE paciente_id=? AND plan_id=? AND tipo_pago='mensualidad' AND periododesde IS NOT NULL ORDER BY periododesde DESC LIMIT 1");
        $stmtUlt->execute([$id_paciente, $plan_id]);
        $ultimo = $stmtUlt->fetch(PDO::FETCH_ASSOC);
        if ($ultimo && !empty($ultimo['periodohasta'])) {
            // Encadenar: nuevo desde = día siguiente al último hasta
            $dtDesde = DateTime::createFromFormat('Y-m-d', $ultimo['periodohasta']);
            if ($dtDesde) {
                $dtDesde->modify('+1 day');
                $periodoDesde = $dtDesde->format('Y-m-d');
            } else {
                $periodoDesde = $fecha; // fallback
            }
        } else {
            // Primera mensualidad: usar fecha de pago
            $periodoDesde = $fecha;
        }
        // Calcular hasta = (desde +1 mes) -1 día
        if ($periodoDesde) {
            $dtHasta = DateTime::createFromFormat('Y-m-d', $periodoDesde);
            if ($dtHasta) {
                $dtHasta->modify('+1 month');
                $dtHasta->modify('-1 day');
                $periodoHasta = $dtHasta->format('Y-m-d');
            }
        }
    }

    if ($tipo === 'inscripcion') {
        // Para inscripción no se registra periodo
        $periodoDesde = null;
        $periodoHasta = null;
    }

    $colsPagos = $pdo->query("SHOW COLUMNS FROM plan_pagos")->fetchAll(PDO::FETCH_COLUMN);
    $tieneModalidad = in_array('modalidad_pago', $colsPagos, true);
    if ($tienePeriodoDesde && $tienePeriodoHasta && $tieneModalidad) {
        $sqlIns = 'INSERT INTO plan_pagos (paciente_id, plan_id, tipo_pago, monto, fecha_pago, referencia, modalidad_pago, periododesde, periodohasta' . ($tieneCreado ? ', creado_en' : '') . ') VALUES (?,?,?,?,?,?,?,?,?' . ($tieneCreado ? ',NOW()' : '') . ')';
        $ins = $pdo->prepare($sqlIns);
        $ins->execute([$id_paciente, $plan_id, $tipo, $monto, $fecha, $referencia, $modalidad, $periodoDesde, $periodoHasta]);
    } else if ($tienePeriodoDesde && $tienePeriodoHasta) {
        $sqlIns = 'INSERT INTO plan_pagos (paciente_id, plan_id, tipo_pago, monto, fecha_pago, referencia, periododesde, periodohasta' . ($tieneCreado ? ', creado_en' : '') . ') VALUES (?,?,?,?,?,?,?,?' . ($tieneCreado ? ',NOW()' : '') . ')';
        $ins = $pdo->prepare($sqlIns);
        $ins->execute([$id_paciente, $plan_id, $tipo, $monto, $fecha, $referencia, $periodoDesde, $periodoHasta]);
    } else if ($tieneModalidad) {
        $sqlIns = 'INSERT INTO plan_pagos (paciente_id, plan_id, tipo_pago, monto, fecha_pago, referencia, modalidad_pago' . ($tieneCreado ? ', creado_en' : '') . ') VALUES (?,?,?,?,?,?,?' . ($tieneCreado ? ',NOW()' : '') . ')';
        $ins = $pdo->prepare($sqlIns);
        $ins->execute([$id_paciente, $plan_id, $tipo, $monto, $fecha, $referencia, $modalidad]);
    } else {
        $sqlIns = 'INSERT INTO plan_pagos (paciente_id, plan_id, tipo_pago, monto, fecha_pago, referencia' . ($tieneCreado ? ', creado_en' : '') . ') VALUES (?,?,?,?,?,?' . ($tieneCreado ? ',NOW()' : '') . ')';
        $ins = $pdo->prepare($sqlIns);
        $ins->execute([$id_paciente, $plan_id, $tipo, $monto, $fecha, $referencia]);
    }

    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al registrar pago', 'detail' => $e->getMessage()]);
}
