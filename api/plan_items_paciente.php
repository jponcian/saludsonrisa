<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');

$id_paciente = isset($_GET['id_paciente']) ? (int)$_GET['id_paciente'] : 0;
if (!$id_paciente) {
    echo json_encode(['status' => 'error', 'message' => 'ID de paciente requerido']);
    exit;
}

// Obtener los ítems del plan activo del paciente
$stmt = $pdo->prepare("SELECT codigo, nombre_limite AS nombre, usado, maximo AS max FROM vw_paciente_limites WHERE paciente_id = ? AND maximo > 0");
$stmt->execute([$id_paciente]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($items as &$item) {
    $item['usado'] = (int)$item['usado'];
    $item['max'] = (int)$item['max'];
    $item['restante'] = $item['max'] - $item['usado'];
}

echo json_encode(['status' => 'ok', 'items' => $items]);
