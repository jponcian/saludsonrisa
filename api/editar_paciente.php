<?php
require_once 'conexion.php';
header('Content-Type: application/json');

// Función para loguear errores
function log_error($message)
{
    file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - editar_paciente.php - ' . $message . PHP_EOL, FILE_APPEND);
}


$id = $_POST['id'] ?? null;
$nombres = $_POST['nombres'] ?? null;
$apellidos = $_POST['apellidos'] ?? null;
$cedula = $_POST['cedula'] ?? null;
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
$genero = $_POST['genero'] ?? null;
$telefono = $_POST['telefono'] ?? null;
$email = $_POST['email'] ?? null;
$direccion = $_POST['direccion'] ?? null;

if (!$id || !$nombres || !$apellidos || !$cedula || !$fecha_nacimiento || !$genero) {
    log_error('Datos incompletos para la actualización.');
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
    exit;
}

// Convertir fecha de dd-mm-yyyy a yyyy-mm-dd para la base de datos, o dejarla igual si ya está en yyyy-mm-dd
$fecha_nacimiento_db = null;
if ($fecha_nacimiento) {
    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $fecha_nacimiento)) {
        // dd-mm-yyyy
        $date_parts = explode('-', $fecha_nacimiento);
        $fecha_nacimiento_db = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
    } else if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nacimiento)) {
        // yyyy-mm-dd
        $fecha_nacimiento_db = $fecha_nacimiento;
    } else {
        log_error('Formato de fecha de nacimiento inválido: ' . $fecha_nacimiento);
        echo json_encode(['status' => 'error', 'message' => 'Formato de fecha de nacimiento inválido. Use dd-mm-yyyy o yyyy-mm-dd.']);
        exit;
    }
}

try {
    $foto_actualizada = false;
    $foto_url = null;
    // Procesar foto base64 (cámara)
    if (!empty($_POST['foto_capturada_editar'])) {
        $base64 = $_POST['foto_capturada_editar'];
        if (preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $base64)) {
            $base64 = preg_replace('/^data:image\/(png|jpg|jpeg);base64,/', '', $base64);
            $data = base64_decode($base64);
            $filename = 'paciente_' . $id . '_' . time() . '.png';
            $filepath = __DIR__ . '/../uploads/' . $filename;
            if (file_put_contents($filepath, $data) !== false) {
                $foto_url = $filename;
                $foto_actualizada = true;
            }
        }
    }
    // Procesar archivo subido
    elseif (!empty($_FILES['edit-foto']['name'])) {
        $file = $_FILES['edit-foto'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'paciente_' . $id . '_' . time() . '.' . $ext;
        $filepath = __DIR__ . '/../uploads/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $foto_url = $filename;
            $foto_actualizada = true;
        }
    }

    // Actualizar datos básicos
    $sql = "UPDATE pacientes SET nombres = ?, apellidos = ?, cedula = ?, fecha_nacimiento = ?, genero = ?, telefono = ?, email = ?, direccion = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombres, $apellidos, $cedula, $fecha_nacimiento_db, $genero, $telefono, $email, $direccion, $id]);

    // Si se actualizó la foto, guardar en la BD
    if ($foto_actualizada && $foto_url) {
        $sql_foto = "UPDATE pacientes SET foto_url = ? WHERE id = ?";
        $stmt_foto = $pdo->prepare($sql_foto);
        $stmt_foto->execute([$foto_url, $id]);
    }

    if ($stmt->rowCount() > 0 || $foto_actualizada) {
        echo json_encode(['status' => 'success', 'message' => 'Paciente actualizado correctamente.', 'foto_url' => $foto_url]);
    } else {
        log_error('No se encontró el paciente con ID: ' . $id . ' o no hubo cambios.');
        echo json_encode(['status' => 'info', 'message' => 'No se realizó ningún cambio o el paciente no fue encontrado.']);
    }
} catch (PDOException $e) {
    log_error('Error al actualizar el paciente en la DB: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el paciente: ' . $e->getMessage()]);
}
