<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
$id_paciente = isset($_REQUEST['id_paciente']) ? (int)$_REQUEST['id_paciente'] : 0;
if (!$id_paciente) {
    echo json_encode(['status' => 'error', 'message' => 'Paciente requerido']);
    exit;
}
try {
    // Historial: consultas + consumos (servicios_consumidos)
    $sql = "SELECT c.fecha_consulta AS fecha, 'consulta' AS tipo, c.diagnostico AS detalle,
                   u.nombre_completo AS especialista
            FROM consultas c 
            LEFT JOIN usuarios u ON u.id = c.especialista_id
            WHERE c.paciente_id=?
            UNION ALL
            SELECT sc.created_at AS fecha, sc.tipo_servicio AS tipo, sc.notas AS detalle,
                   u2.nombre_completo AS especialista
            FROM servicios_consumidos sc 
            LEFT JOIN usuarios u2 ON u2.id = sc.realizado_por
            WHERE sc.paciente_id=? AND sc.estado='registrado'
            ORDER BY fecha DESC LIMIT 300";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_paciente, $id_paciente]);
    echo json_encode(['status' => 'ok', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error historial']);
}
