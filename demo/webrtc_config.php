<?php
// Configuración centralizada de servidores ICE (solo STUN para funcionamiento en misma red)

$ICE_SERVERS = [
    ['urls' => 'stun:stun.l.google.com:19302'],
    ['urls' => 'stun:stun1.l.google.com:19302'],
    ['urls' => 'stun:stun2.l.google.com:19302'],
];

// Si se llama directamente, devolver JSON
if (php_sapi_name() !== 'cli' && basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($ICE_SERVERS, JSON_UNESCAPED_SLASHES);
    exit;
}
