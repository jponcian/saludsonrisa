<?php
require_once 'auth_check.php';

// Validar permiso para gestionar usuarios (permiso_id = 4)
$puedeGestionarUsuarios = in_array(4, $permisos_usuario, true);
if (!$puedeGestionarUsuarios) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

header('Content-Type: application/json');
require 'conexion.php';

$response = ['status' => 'error', 'message' => 'Datos inválidos.'];

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['rol_id']) && isset($_POST['nombre_completo'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rol_id_input = (int) $_POST['rol_id'];
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

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
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

    if (empty($username) || empty($password) || empty($nombre_completo) || $rol_id_input <= 0) {
        $response['message'] = 'Todos los campos son requeridos.';
    } else {
        // Buscar datos del rol seleccionado
        $stmtRol = $pdo->prepare("SELECT id, nombre FROM roles WHERE id = ? LIMIT 1");
        $stmtRol->execute([$rol_id_input]);
        $rol_data = $stmtRol->fetch(PDO::FETCH_ASSOC);

        if (!$rol_data) {
            $response['message'] = 'Rol seleccionado no existe.';
            echo json_encode($response);
            exit;
        }

        $rol_nuevo_slug = rol_to_slug($rol_data['nombre']);

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $pdo->beginTransaction();

            $sql = "INSERT INTO usuarios (username, password_hash, rol, nombre_completo, cedula, telefono, foto) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $password_hash, $rol_data['id'], $nombre_completo, $cedula, $telefono, $foto_path]);
            $usuario_id = $pdo->lastInsertId();

            if ($rol_nuevo_slug === 'especialista') {
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
