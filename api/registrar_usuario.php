<?php
require_once 'auth_check.php';

// Solo los administradores de usuarios pueden crear nuevos usuarios
if ($rol !== 'admin_usuarios') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

header('Content-Type: application/json');
require 'conexion.php';

$response = ['status' => 'error', 'message' => 'Datos inválidos.'];

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['rol']) && isset($_POST['nombre_completo'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rol = $_POST['rol'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $cedula = trim($_POST['cedula'] ?? null);
    $telefono = trim($_POST['telefono'] ?? null);
    $foto_path = null;

    // Manejo de la subida de la foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $fileSize = $_FILES['foto']['size'];
        $fileType = $_FILES['foto']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $foto_path = $newFileName;
            } else {
                $response['message'] = 'Hubo un error al mover el archivo de la foto.';
                echo json_encode($response);
                exit;
            }
        } else {
            $response['message'] = 'Tipo de archivo de foto no permitido. Solo JPG, JPEG, PNG, GIF.';
            echo json_encode($response);
            exit;
        }
    }

    if (empty($username) || empty($password) || empty($rol) || empty($nombre_completo)) {
        $response['message'] = 'Todos los campos son requeridos.';
    } elseif (!in_array($rol, ['admin_usuarios', 'especialista'])) {
        $response['message'] = 'Rol no válido.';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $pdo->beginTransaction();

            $sql = "INSERT INTO usuarios (username, password_hash, rol, nombre_completo, cedula, telefono, foto) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $password_hash, $rol, $nombre_completo, $cedula, $telefono, $foto_path]);
            $usuario_id = $pdo->lastInsertId();

            if ($rol === 'especialista') {
                $sql_especialista = "INSERT INTO especialistas (usuario_id) VALUES (?)";
                $stmt_especialista = $pdo->prepare($sql_especialista);
                $stmt_especialista->execute([$usuario_id]);
            }

            $pdo->commit();
            $response = ['status' => 'success', 'message' => 'Usuario registrado con éxito.'];
        } catch (\PDOException $e) {
            $pdo->rollBack();
            if ($e->errorInfo[1] == 1062) { // Error de entrada duplicada
                $response['message'] = 'El nombre de usuario ya existe.';
            } else {
                $response['message'] = 'Error de base de datos al registrar el usuario: ' . $e->getMessage();
            }
            http_response_code(400);
        }
    }
}

echo json_encode($response);
?>