<?php
// Recibe frame base64 (JPEG) y lo guarda como current.jpg para fallback de visualización
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
if (empty($_POST['frame'])) {
    http_response_code(400);
    exit;
}
$frame = $_POST['frame'];
if (!preg_match('/^data:image\/jpeg;base64,/', $frame)) {
    http_response_code(400);
    exit;
}
$base64 = substr($frame, strpos($frame, ',') + 1);
$bin = base64_decode($base64);
$dir = __DIR__ . '/frames';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}
$file = $dir . '/current.jpg';
file_put_contents($file, $bin);
// Opcional: podrías almacenar timestamp en un .txt para comprobar antigüedad
file_put_contents($dir . '/ts.txt', time());
echo 'OK';
