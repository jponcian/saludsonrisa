<?php
// carnet_reverso.php
// Genera la imagen del reverso del carnet
require_once __DIR__ . '/../api/conexion.php';

$id = 0;
if (isset($_GET['id'])) {
    $raw = base64_decode($_GET['id']);
    $parts = explode('|', $raw);
    if (count($parts) === 2 && $parts[1] === 'saludsonrisa2025') {
        $id = (int) $parts[0];
    }
}
if (!$id) {
    die('Paciente no especificado o id inválido.');
}
$stmt = $pdo->prepare('SELECT * FROM pacientes WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$paciente) {
    die('Paciente no encontrado.');
}
// Obtener la suscripción más antigua (primer pago)
$stmtSus = $pdo->prepare('SELECT * FROM plan_suscripciones WHERE paciente_id = ? ORDER BY fecha_inscripcion ASC LIMIT 1');
$stmtSus->execute([$id]);
$suscripcion = $stmtSus->fetch(PDO::FETCH_ASSOC);
if (!$suscripcion) {
    die('No existe suscripción para este paciente.');
}
$fecha_emision = date('m/y', strtotime($suscripcion['fecha_inscripcion']));
$fecha_vencimiento = date('m/y', strtotime('+1 year', strtotime($suscripcion['fecha_inscripcion'])));

// Crear imagen base
$width = 1000;
$height = 650;
$img = imagecreatetruecolor($width, $height);
$img_reverso = __DIR__ . '/../multimedia/carnet_reverso.jpg';
if (file_exists($img_reverso)) {
    $fondo = imagecreatefromjpeg($img_reverso);
    imagecopyresampled($img, $fondo, 0, 0, 0, 0, $width, $height, imagesx($fondo), imagesy($fondo));
    imagedestroy($fondo);
} else {
    $bg = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, $width, $height, $bg);
}
// Colores y fuente
$colorTexto = imagecolorallocate($img, 255, 255, 255);
$colorInfo = imagecolorallocate($img, 40, 40, 40);
$font = __DIR__ . '/../plugins/fonts/static/OpenSans-Bold.ttf';
// Emisión y vencimiento
imagettftext($img, 28, 0, 850, 95, $colorTexto, $font, $fecha_emision);
imagettftext($img, 28, 0, 850, 160, $colorTexto, $font, $fecha_vencimiento);
// Salida PNG
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="CarnetReverso_' . ($paciente['cedula'] ?? 'sincedula') . '.png"');
imagepng($img);
imagedestroy($img);
exit;
