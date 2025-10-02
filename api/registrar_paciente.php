<?php
header('Content-Type: application/json');
require 'conexion.php';

// Función para loguear errores
function log_error($message)
{
    file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - registrar_paciente.php - ' . $message . PHP_EOL, FILE_APPEND);
}

$response = ['status' => 'error', 'message' => 'Datos inválidos.'];

if (!isset($_POST['cedula'])) {
    log_error('Falta el campo cédula.');
    echo json_encode(['status' => 'error', 'message' => 'Falta el campo cédula.']);
    exit;
}
if (!isset($_POST['nombres'])) {
    log_error('Falta el campo nombres.');
    echo json_encode(['status' => 'error', 'message' => 'Falta el campo nombres.']);
    exit;
}
if (!isset($_POST['apellidos'])) {
    log_error('Falta el campo apellidos.');
    echo json_encode(['status' => 'error', 'message' => 'Falta el campo apellidos.']);
    exit;
}
if (!isset($_POST['fecha_nacimiento'])) {
    log_error('Falta el campo fecha_nacimiento.');
    echo json_encode(['status' => 'error', 'message' => 'Falta el campo fecha_nacimiento.']);
    exit;
}
if (!isset($_POST['genero'])) {
    log_error('Falta el campo genero.');
    echo json_encode(['status' => 'error', 'message' => 'Falta el campo genero.']);
    exit;
}

// Directorio de subida
$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        log_error('Error al crear el directorio de subida: ' . $uploadDir);
        echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor al crear directorio de subida.']);
        exit;
    }
}

$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$cedula = $_POST['cedula'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$genero = $_POST['genero'];
$telefono = $_POST['telefono'] ?? null;
$email = $_POST['email'] ?? null;
$direccion = $_POST['direccion'] ?? null;
$foto_url = null;

// Manejo de la subida de la foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $fileName = uniqid() . '-' . basename($_FILES['foto']['name']);
    $targetFilePath = $uploadDir . $fileName;
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    if (in_array($imageFileType, $allowTypes)) {
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFilePath)) {
            $foto_url = $fileName;
        } else {
            log_error('Error al mover el archivo subido.');
            $response['message'] = 'Error al subir la imagen.';
            echo json_encode($response);
            exit;
        }
    } else {
        log_error('Tipo de archivo no permitido: ' . $imageFileType);
        $response['message'] = 'Solo se permiten imágenes JPG, JPEG, PNG y GIF.';
        echo json_encode($response);
        exit;
    }
} elseif (!empty($_POST['foto_capturada'])) {
    // Si viene una imagen capturada desde la cámara (base64)
    $data = $_POST['foto_capturada'];
    if (preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $data, $matches)) {
        $type = $matches[1];
        $data = substr($data, strpos($data, ',') + 1);
        $data = base64_decode($data);
        $fileName = uniqid() . '-captura.' . $type;
        $targetFilePath = $uploadDir . $fileName;
        if (file_put_contents($targetFilePath, $data)) {
            $foto_url = $fileName;
        } else {
            log_error('Error al guardar la imagen capturada.');
            $response['message'] = 'Error al guardar la foto capturada.';
            echo json_encode($response);
            exit;
        }
    } else {
        $response['message'] = 'Formato de imagen capturada no válido.';
        echo json_encode($response);
        exit;
    }
}

try {
    $sql = "INSERT INTO pacientes (nombres, apellidos, cedula, fecha_nacimiento, genero, telefono, direccion, foto_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombres, $apellidos, $cedula, $fecha_nacimiento, $genero, $telefono, $direccion, $foto_url]);

    $response = ['status' => 'success', 'message' => 'Paciente registrado con éxito.'];
} catch (\PDOException $e) {
    log_error('Error al registrar el paciente en la DB: ' . $e->getMessage());
    $response['message'] = 'Error al registrar el paciente: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
