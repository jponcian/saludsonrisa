<?php
// Elimina una reunión demo y todo lo relacionado (participantes y asistencias)
require_once '../api/conexion.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: index.php?embed=1');
    exit;
}
// Eliminar asistencias
$stmt = $pdo->prepare('DELETE FROM demo_asistencias WHERE id_reunion = ?');
$stmt->execute([$id]);
// Eliminar participantes
$stmt = $pdo->prepare('DELETE FROM demo_participantes WHERE id_reunion = ?');
$stmt->execute([$id]);
// Eliminar la reunión
$stmt = $pdo->prepare('DELETE FROM demo_reuniones WHERE id = ?');
$stmt->execute([$id]);
header('Location: index.php?embed=1');
exit;
