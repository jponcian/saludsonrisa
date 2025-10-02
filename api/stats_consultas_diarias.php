<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $days = 14;
    $labels = [];
    $data = [];

    $stmt = $pdo->prepare("SELECT DATE(fecha_consulta) as d, COUNT(*) as cnt FROM consultas WHERE fecha_consulta >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY DATE(fecha_consulta) ORDER BY DATE(fecha_consulta) ASC");
    $stmt->execute([$days]);
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = date('d-m', strtotime($d));
        $data[] = isset($rows[$d]) ? intval($rows[$d]) : 0;
    }

    echo json_encode(['labels' => $labels, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
