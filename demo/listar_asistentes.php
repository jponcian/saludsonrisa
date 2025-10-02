<?php
// Devuelve la lista de asistentes registrados para una reunión
require_once '../api/conexion.php';
header('Content-Type: application/json; charset=utf-8');
$id_reunion = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_reunion <= 0) {
    echo json_encode([]);
    exit;
}
$sql = "SELECT p.nombre, a.fecha_hora FROM demo_asistencias a JOIN demo_participantes p ON a.id_participante = p.id WHERE a.id_reunion = ? ORDER BY a.fecha_hora DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_reunion]);
$asistentes = $stmt->fetchAll();
echo json_encode($asistentes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>