<?php
// Endpoint para registrar asistencia desde el lector QR
require_once '../api/conexion.php';
header('Content-Type: application/json');
$id_reunion = isset($_POST['reunion']) ? intval($_POST['reunion']) : 0;
$id_participante = isset($_POST['participante']) ? intval($_POST['participante']) : 0;
$token = isset($_POST['token']) ? trim($_POST['token']) : '';
if ($id_reunion <= 0 || $id_participante <= 0) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}
// Verificar que el participante pertenece a la reunión y validar token
$stmt = $pdo->prepare("SELECT nombre, token FROM demo_participantes WHERE id = ? AND id_reunion = ?");
$stmt->execute([$id_participante, $id_reunion]);
$row = $stmt->fetch();
if ($row) {
    $nombre = $row['nombre'];
    $token_db = $row['token'];
    if (!$token_db || !hash_equals($token_db, $token)) {
        echo json_encode(['success' => false, 'error' => 'Token inválido']);
        exit;
    }
    // Registrar asistencia solo si no existe
    $stmt2 = $pdo->prepare("SELECT id FROM demo_asistencias WHERE id_participante = ? AND id_reunion = ?");
    $stmt2->execute([$id_participante, $id_reunion]);
    if (!$stmt2->fetch()) {
        $stmt3 = $pdo->prepare("INSERT INTO demo_asistencias (id_participante, id_reunion) VALUES (?, ?)");
        $stmt3->execute([$id_participante, $id_reunion]);
        echo json_encode(['success' => true, 'nombre' => $nombre]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Asistencia ya registrada', 'nombre' => $nombre]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Participante no válido']);
}
