<?php
// Debug / hardening temporal para localizar 500 Internal Server Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

// Intentar preservar el control de acceso existente
require_once 'auth_check.php';

try {
    // Incluir solo la ruta principal de conexion.php
    require_once __DIR__ . '/conexion.php';
    $included = true;

    // Solo validar que el usuario haya iniciado sesión (auth_check.php lo hace)

    if (!isset($pdo)) {
        throw new Exception('No se encontró conexión PDO en conexion.php');
    }

    // Usar solo PDO
    $db = $pdo;
    $isPDO = true;

    // Verificar existencia de la tabla atencion_procesos para evitar 500
    $check = $db->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='atencion_procesos' LIMIT 1");
    $check->execute();
    $tableExists = (bool) $check->fetchColumn();

    if (!$tableExists) {
        // Responder vacio pero sin 500 para que la UI no rompa
        echo json_encode(['status' => 'ok', 'data' => [], 'warning' => 'Tabla atencion_procesos no encontrada.']);
        exit;
    }

    // Construir la consulta principal (solo PDO)
    $sql = "SELECT ap.id, ap.motivo, ap.urgencia, ap.estado, DATE_FORMAT(ap.creado_en,'%Y-%m-%d %H:%i') AS creado,
                   CONCAT(p.nombres,' ',p.apellidos) AS paciente,
                   pl.nombre AS plan
            FROM atencion_procesos ap
            INNER JOIN pacientes p ON p.id=ap.paciente_id
            LEFT JOIN plan_suscripciones ps ON ps.paciente_id = p.id AND ps.activo = 1
            LEFT JOIN planes pl ON pl.id = ps.plan_id
            ORDER BY ap.id DESC LIMIT 200";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'ok', 'data' => $rows]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Exception en atencion_listar_procesos.php', 'detail' => $e->getMessage()]);
    exit;
}
