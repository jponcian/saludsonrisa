<?php
$timezone = 'America/Caracas';
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set($timezone);
}
$host = 'localhost';
$dbname = 'javier_ponciano_5';
$user = 'ponciano';
$pass = 'Prueba016.';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Establecer zona horaria de MySQL
    $pdo->exec("SET time_zone = '-04:00'"); // Hora de Venezuela sin DST
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de conexión a la base de datos.'
    ]);
    exit;
}
?>