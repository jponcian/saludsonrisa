<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
if ($rol !== 'admin_usuarios') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}
$id_paciente = (int)($_POST['id_paciente'] ?? 0);
$plan_id = (int)($_POST['plan_id'] ?? 0);
$obs = trim($_POST['obs'] ?? ''); // se ignora en este modelo simplificado
if (!$id_paciente || !$plan_id) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}
try {
    // validar existencia plan
    $havePlan = $pdo->prepare('SELECT * FROM planes WHERE id=?');
    $havePlan->execute([$plan_id]);
    $plan = $havePlan->fetch(PDO::FETCH_ASSOC);
    if (!$plan) {
        echo json_encode(['status' => 'error', 'message' => 'Plan no existe']);
        exit;
    }
    // Buscar suscripción activa actual (cualquier plan)
    $cur = $pdo->prepare('SELECT * FROM plan_suscripciones WHERE paciente_id=? AND activo=1 LIMIT 1');
    $cur->execute([$id_paciente]);
    $susActual = $cur->fetch(PDO::FETCH_ASSOC);
    if ($susActual) {
        if ((int)$susActual['plan_id'] === $plan_id) {
            echo json_encode(['status' => 'error', 'message' => 'El paciente ya tiene este plan asignado']);
            exit;
        }
        // Respaldar en historial (crear tabla si no existe)
        $pdo->exec('CREATE TABLE IF NOT EXISTS plan_suscripciones_historial (
            id INT AUTO_INCREMENT PRIMARY KEY,
            suscripcion_id INT,
            paciente_id INT,
            plan_id INT,
            fecha_inscripcion DATE,
            cobertura_inicio DATE,
            dias_espera INT,
            estado VARCHAR(30),
            activo TINYINT,
            monto_mensual DECIMAL(10,2),
            monto_afiliacion DECIMAL(10,2),
            notas TEXT,
            respaldado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $insHist = $pdo->prepare('INSERT INTO plan_suscripciones_historial (suscripcion_id, paciente_id, plan_id, fecha_inscripcion, cobertura_inicio, dias_espera, estado, activo, monto_mensual, monto_afiliacion, notas) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $insHist->execute([
            $susActual['id'],
            $susActual['paciente_id'],
            $susActual['plan_id'],
            $susActual['fecha_inscripcion'],
            $susActual['cobertura_inicio'],
            $susActual['dias_espera'],
            $susActual['estado'],
            $susActual['activo'],
            $susActual['monto_mensual'],
            $susActual['monto_afiliacion'],
            $susActual['notas']
        ]);
        // Actualizar suscripción existente con nuevo plan
        $fecha_inscripcion = date('Y-m-d');
        $dias_espera = 45;
        $cobertura_inicio = date('Y-m-d', strtotime("+$dias_espera days"));
        $estado = 'pendiente';
        $monto_mensual = $plan['costo_mensual'] ?? null;
        $monto_afiliacion = $plan['cuota_afiliacion'] ?? null;
        $upd = $pdo->prepare('UPDATE plan_suscripciones SET plan_id=?, fecha_inscripcion=?, cobertura_inicio=?, dias_espera=?, estado=?, monto_mensual=?, monto_afiliacion=?, notas=?, actualizado_en=NOW() WHERE id=?');
        // Intentar agregar columna actualizado_en si no existe
        try {
            $pdo->exec("ALTER TABLE plan_suscripciones ADD COLUMN actualizado_en TIMESTAMP NULL AFTER notas");
        } catch (Exception $e) { /* ignorar si ya existe */
        }
        $upd->execute([$plan_id, $fecha_inscripcion, $cobertura_inicio, $dias_espera, $estado, $monto_mensual, $monto_afiliacion, $obs, $susActual['id']]);
        echo json_encode(['status' => 'ok', 'cambio' => true]);
    } else {
        // No había suscripción activa: crear nueva
        $fecha_inscripcion = date('Y-m-d');
        $dias_espera = 45;
        $cobertura_inicio = date('Y-m-d', strtotime("+$dias_espera days"));
        $estado = 'pendiente';
        $activo = 1;
        $monto_mensual = $plan['costo_mensual'] ?? null;
        $monto_afiliacion = $plan['cuota_afiliacion'] ?? null;
        // asegurar columna actualizado_en
        try {
            $pdo->exec("ALTER TABLE plan_suscripciones ADD COLUMN actualizado_en TIMESTAMP NULL AFTER notas");
        } catch (Exception $e) {
        }
        $ins = $pdo->prepare('INSERT INTO plan_suscripciones (paciente_id, plan_id, fecha_inscripcion, cobertura_inicio, dias_espera, estado, activo, monto_mensual, monto_afiliacion, notas, actualizado_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $ins->execute([$id_paciente, $plan_id, $fecha_inscripcion, $cobertura_inicio, $dias_espera, $estado, $activo, $monto_mensual, $monto_afiliacion, $obs]);
        echo json_encode(['status' => 'ok', 'cambio' => false]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al asignar', 'detail' => $e->getMessage()]);
}
