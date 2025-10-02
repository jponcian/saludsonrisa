<?php
// Debug / hardening temporal para localizar 500 Internal Server Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Intentar preservar el control de acceso existente
require_once 'auth_check.php';

try {
    // Intentar incluir el archivo de conexión en varias rutas posibles
    $included = false;
    $candidates = [
        __DIR__ . '/conexion.php',
        __DIR__ . '/db.php',
        __DIR__ . '/connection.php',
        __DIR__ . '/../api/conexion.php',
        __DIR__ . '/../conexion.php',
        __DIR__ . '/../db.php',
        __DIR__ . '/../config.php',
    ];
    foreach ($candidates as $f) {
        if (file_exists($f)) {
            require_once $f;
            $included = true;
            break;
        }
    }

    if (!isset($rol) || $rol !== 'admin_usuarios') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
        exit;
    }

    if (!$included && !isset($pdo) && !isset($conn) && !isset($mysqli)) {
        throw new Exception('No se encontró archivo de conexión. Buscados: ' . implode(', ', $candidates));
    }

    // Normalizar conexión: preferir PDO
    if (isset($pdo)) {
        $db = $pdo;
        $isPDO = true;
    } elseif (isset($conn)) {
        $db = $conn;
        $isPDO = true; // muchos proyectos usan $conn como PDO
    } elseif (isset($mysqli)) {
        $db = $mysqli;
        $isPDO = false;
    } else {
        // Intentar crear PDO si existen constantes
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, defined('DB_PASS') ? DB_PASS : '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db = $pdo;
            $isPDO = true;
        } else {
            throw new Exception('La conexión a la base de datos no está inicializada ($pdo / $conn / $mysqli).');
        }
    }

    // Verificar existencia de la tabla atencion_procesos para evitar 500
    $tableExists = false;
    if ($isPDO) {
        $check = $db->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='atencion_procesos' LIMIT 1");
        $check->execute();
        $tableExists = (bool) $check->fetchColumn();
    } else {
        $res = $db->query("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='atencion_procesos' LIMIT 1");
        $tableExists = ($res && $res->num_rows > 0);
    }

    if (!$tableExists) {
        // Responder vacio pero sin 500 para que la UI no rompa
        echo json_encode(['status' => 'ok', 'data' => [], 'warning' => 'Tabla atencion_procesos no encontrada.']);
        exit;
    }

    // Construir la consulta principal (igual que antes)
    $sql = "SELECT ap.id, ap.motivo, ap.urgencia, ap.estado, DATE_FORMAT(ap.creado_en,'%Y-%m-%d %H:%i') AS creado,
                   CONCAT(p.nombres,' ',p.apellidos) AS paciente,
                   pl.nombre AS plan
            FROM atencion_procesos ap
            INNER JOIN pacientes p ON p.id=ap.paciente_id
            LEFT JOIN plan_suscripciones ps ON ps.paciente_id = p.id AND ps.activo = 1
            LEFT JOIN planes pl ON pl.id = ps.plan_id
            ORDER BY ap.id DESC LIMIT 200";

    if ($isPDO) {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'ok', 'data' => $rows]);
        exit;
    } else {
        $res = $db->query($sql);
        if ($res === false) {
            throw new Exception('Error MySQL: ' . $db->error);
        }
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['status' => 'ok', 'data' => $rows]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Exception en atencion_listar_procesos.php', 'detail' => $e->getMessage()]);
    exit;
}
