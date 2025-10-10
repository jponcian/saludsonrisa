<?php
require_once __DIR__ . '/../api/conexion.php';
// Desencriptar el id recibido
$id = 0;
if (isset($_GET['id'])) {
    $raw = base64_decode($_GET['id']);
    $parts = explode('|', $raw);
    if (count($parts) === 2 && $parts[1] === 'saludsonrisa2025') {
        $id = (int)$parts[0];
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
$numero_afiliado = $id;

// Formatear cédula: X-00.000.000
function formato_cedula($cedula)
{
    $cedula = preg_replace('/\D/', '', $cedula);
    if (strlen($cedula) >= 8) {
        return substr($cedula, 0, 2) . '.' . substr($cedula, 2, 3) . '.' . substr($cedula, 5);
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
$colorTitulo = imagecolorallocate($img, 0, 123, 255);
$colorTexto = imagecolorallocate($img, 255, 255, 255);
$colorInfo = imagecolorallocate($img, 120, 120, 120);
$font = __DIR__ . '/../plugins/fpdf/tutorial/CevicheOne-Regular.ttf'; // Fuente TTF disponible

// Número de afiliado
imagettftext($img, 32, 0, 100, 355, $colorTexto, $font, $numero_afiliado);
// Nombre
imagettftext($img, 32, 0, 50, 480, $colorTexto, $font,  $nombre);
// Cédula y teléfono
imagettftext($img, 32, 0, 50, 520, $colorTexto, $font,  $cedula_fmt . '    ' . $telefono_fmt);

// Info legal
imagettftext($img, 24, 0, 50, 600, $colorInfo, $font, 'Válido solo para afiliados activos');

// Salida PNG
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="Carnet_' . $cedula . '.png"');
imagepng($img);
imagedestroy($img);
exit;
