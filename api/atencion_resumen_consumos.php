<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');

$id_paciente = isset($_REQUEST['id_paciente']) ? (int)$_REQUEST['id_paciente'] : 0;
if (!$id_paciente) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID de Paciente requerido']);
    exit;
}

try {
    // 1. Obtener los KPIs (límites y consumos) usando la vista optimizada
    $stmt_kpis = $pdo->prepare(
        "SELECT codigo, nombre_limite AS nombre, usado, maximo AS max
         FROM vw_paciente_limites
         WHERE paciente_id = ?
           AND maximo > 0
           AND nombre_limite NOT LIKE '%(%)%'"
    );
    $stmt_kpis->execute([$id_paciente]);
    $kpis = $stmt_kpis->fetchAll(PDO::FETCH_ASSOC);

    // Convertir valores numéricos a enteros para consistencia
    foreach ($kpis as &$kpi) {
        $kpi['usado'] = (int)$kpi['usado'];
        $kpi['max'] = (int)$kpi['max'];
    }

    // 2. Obtener el historial de consumos (lógica original mantenida)
    $stmt_consumos = $pdo->prepare(
        "SELECT sc.created_at AS fecha, sc.tipo_servicio AS tipo, sc.notas AS detalle, sc.cantidad, u.nombre_completo AS especialista
                FROM servicios_consumidos sc
                LEFT JOIN usuarios u ON u.id = sc.realizado_por
                WHERE sc.paciente_id = ?
                    AND sc.estado = 'registrado'
                    AND NOT EXISTS (
                                SELECT 1
                                FROM seguros_limites sl
                                WHERE sl.codigo = sc.codigo_limite
                                    AND sl.nombre LIKE '%(%)%'
                    )
                ORDER BY sc.created_at DESC
                LIMIT 200"
    );
    $stmt_consumos->execute([$id_paciente]);
    $consumos = $stmt_consumos->fetchAll(PDO::FETCH_ASSOC);

    // 3. Enviar respuesta
    echo json_encode(['status' => 'ok', 'kpis' => $kpis, 'consumos' => $consumos]);
} catch (Exception $e) {
    http_response_code(500);
    // Para depuración, puedes registrar el error: error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error en el servidor al obtener el resumen de consumos.']);
}
