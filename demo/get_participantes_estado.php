<?php
require_once '../api/conexion.php';
header('Content-Type: application/json; charset=utf-8');
$id_reunion = isset($_GET['reunion']) ? intval($_GET['reunion']) : 0;
if ($id_reunion <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

$stmt = $pdo->prepare('SELECT p.id, CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END as asistio FROM demo_participantes p LEFT JOIN demo_asistencias a ON p.id = a.id_participante AND a.id_reunion = ? WHERE p.id_reunion = ? ORDER BY p.nombre ASC');
$stmt->execute([$id_reunion, $id_reunion]);
$rows = $stmt->fetchAll();

$out = [];
foreach ($rows as $r) {
    $out[] = ['id' => (int)$r['id'], 'asistio' => (int)$r['asistio']];
}

echo json_encode(['success' => true, 'data' => $out]);
