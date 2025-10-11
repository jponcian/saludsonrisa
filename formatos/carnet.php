<?php
require_once __DIR__ . '/../api/conexion.php';
require_once __DIR__ . '/../plugins/phpqrcode/qrlib.php'; // Librería QR
// Desencriptar el id recibido
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
$nombre = ($paciente['nombres'] ?? '') . ' ' . ($paciente['apellidos'] ?? '');
$cedula = $paciente['cedula'] ?? '';
$telefono = $paciente['telefono'] ?? '';
// Obtener el número correlativo de afiliado
$stmtSus = $pdo->prepare('SELECT * FROM plan_suscripciones WHERE paciente_id = ? ORDER BY id DESC LIMIT 1');
$stmtSus->execute([$id]);
$suscripcion = $stmtSus->fetch(PDO::FETCH_ASSOC);
if (!$suscripcion) {
    die('No existe suscripción activa para este paciente.');
}
if (empty($suscripcion['numero']) || $suscripcion['numero'] == 0) {
    $stmtUlt = $pdo->query('SELECT MAX(numero) as max_num FROM plan_suscripciones');
    $maxNum = $stmtUlt->fetchColumn();
    $nuevo_numero = ($maxNum ? intval($maxNum) : 0) + 1;
    $stmtUpdate = $pdo->prepare('UPDATE plan_suscripciones SET numero = ? WHERE id = ?');
    $stmtUpdate->execute([$nuevo_numero, $suscripcion['id']]);
    $numero_afiliado = str_pad($nuevo_numero, 5, '0', STR_PAD_LEFT);
} else {
    $numero_afiliado = str_pad($suscripcion['numero'], 5, '0', STR_PAD_LEFT);
}
// Formatear cédula: 00.000.000
function formato_cedula($cedula)
{
    $cedula = preg_replace('/\D/', '', $cedula);
    if (strlen($cedula) == 8) {
        // 8 dígitos: 00.000.000
        return substr($cedula, 0, 2) . '.' . substr($cedula, 2, 3) . '.' . substr($cedula, 5);
    } elseif (strlen($cedula) == 7) {
        // 7 dígitos: 0.000.000
        return substr($cedula, 0, 1) . '.' . substr($cedula, 1, 3) . '.' . substr($cedula, 4);
    } elseif (strlen($cedula) == 6) {
        // 6 dígitos: 000.000
        return substr($cedula, 0, 3) . '.' . substr($cedula, 3);
    }
    return $cedula;
}
// Formatear teléfono: (0000)000.00.00
function formato_telefono($telefono)
{
    $telefono = preg_replace('/\D/', '', $telefono);
    if (strlen($telefono) == 11) {
        return '(' . substr($telefono, 0, 4) . ')' . substr($telefono, 4, 3) . '.' . substr($telefono, 7, 2) . '.' . substr($telefono, 9, 2);
    }
    return $telefono;
}
$cedula_fmt = formato_cedula($cedula);
$telefono_fmt = formato_telefono($telefono);
// Crear imagen base 1000x650 px
$width = 1000;
$height = 650;
$img = imagecreatetruecolor($width, $height);
// Fondo anverso
$img_frontal = __DIR__ . '/../multimedia/carnet_anverso.png';
if (file_exists($img_frontal)) {
    $fondo = imagecreatefrompng($img_frontal);
    imagecopyresampled($img, $fondo, 0, 0, 0, 0, $width, $height, imagesx($fondo), imagesy($fondo));
    imagedestroy($fondo);
} else {
    $bg = imagecolorallocate($img, 240, 248, 255);
    imagefilledrectangle($img, 0, 0, $width, $height, $bg);
}
// Colores y fuentes
// Colores y fuentes actualizados para texto blanco y negrita
$colorTitulo = imagecolorallocate($img, 0, 123, 255);
$colorTexto = imagecolorallocate($img, 255, 255, 255); // Blanco
$colorInfo = imagecolorallocate($img, 120, 120, 120);
$font = __DIR__ . '/../plugins/fonts/static/OpenSans-Bold.ttf'; // Fuente TTF en negrita
// Número de afiliado
imagettftext($img, 32, 0, 100, 357, $colorTexto, $font, $numero_afiliado);
// Nombre
imagettftext($img, 32, 0, 50, 480, $colorTexto, $font, $nombre);
// Cédula y teléfono
imagettftext($img, 32, 0, 50, 540, $colorTexto, $font, $cedula_fmt . '    ' . $telefono_fmt);
// Info legal
imagettftext($img, 24, 0, 50, 600, $colorInfo, $font, 'Válido solo para afiliados activos');
// Generar QR
// --- Parámetros QR editables ---
$qr_size = 190; // Tamaño del QR (ajusta aquí)
$qr_offset_x = 50; // Más a la izquierda: aumenta este valor
$qr_offset_y = 49; // Más arriba: aumenta este valor
// --- Fin parámetros QR ---
$raw_id = $id . '|saludsonrisa2025';
$id_codificado = base64_encode($raw_id);
$qr_url = 'https://clinicasaludsonrisa.zz.com.ve/qr_info.php?id=' . urlencode($id_codificado);
$qr_temp = tempnam(sys_get_temp_dir(), 'qr_');
QRcode::png($qr_url, $qr_temp, QR_ECLEVEL_L, 4, 0); // cuadros internos más grandes
$qr_img = imagecreatefrompng($qr_temp);
$qr_x = $width - $qr_size - $qr_offset_x;
$qr_y = $height - $qr_size - $qr_offset_y;
imagecopyresampled($img, $qr_img, $qr_x, $qr_y, 0, 0, $qr_size, $qr_size, imagesx($qr_img), imagesy($qr_img));
imagedestroy($qr_img);
unlink($qr_temp);
// Salida PNG
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="Carnet_' . $cedula . '.png"');
imagepng($img);
imagedestroy($img);
exit;
