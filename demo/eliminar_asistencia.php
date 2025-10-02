<?php
require_once '../api/conexion.php';
$id_reunion = isset($_GET['id_reunion']) ? intval($_GET['id_reunion']) : 0;
$nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : '';
if ($id_reunion <= 0 || $nombre === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}
$stmt = $pdo->prepare('DELETE FROM demo_asistencias WHERE id_reunion = ? AND id_participante = (SELECT id FROM demo_participantes WHERE nombre = ? AND id_reunion = ? LIMIT 1)');
$stmt->execute([$id_reunion, $nombre, $id_reunion]);
echo json_encode(['success' => true]);
