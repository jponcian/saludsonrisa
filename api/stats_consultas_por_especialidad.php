<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // contar las consultas por especialidad en los últimos 180 días (ajustable)
    $days = 180;
    $sql = "SELECT s.nombre, COUNT(*) as cnt FROM consulta_especialidades ce JOIN especialidades s ON ce.especialidad_id = s.id JOIN consultas c ON ce.consulta_id = c.id WHERE c.fecha_consulta >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY s.nombre ORDER BY cnt DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$days]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $data = [];
    foreach ($rows as $r) {
        $labels[] = $r['nombre'];
        $data[] = intval($r['cnt']);
    }

    echo json_encode(['labels' => $labels, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
