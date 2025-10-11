<?php
// qr_info.php
// Procesa el QR y redirige al carnet con el id codificado

if (!isset($_GET['id'])) {
    die('ID no especificado.');
}

$id_codificado = $_GET['id'];
$raw = base64_decode($id_codificado);
$parts = explode('|', $raw);
if (count($parts) !== 2 || $parts[1] !== 'saludsonrisa2025') {
    die('ID inválido.');
}
$id = (int) $parts[0];
if (!$id) {
    die('ID inválido.');
}
// Redirigir al dashboard
header('Location: dashboard_paciente.php?id=' . urlencode($id_codificado));
exit;
