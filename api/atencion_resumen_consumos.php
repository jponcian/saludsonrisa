<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
$id_paciente = isset($_REQUEST['id_paciente']) ? (int)$_REQUEST['id_paciente'] : 0;
if (!$id_paciente) {
    echo json_encode(['status' => 'error', 'message' => 'Paciente requerido']);
    exit;
}

// Nuevo modelo: plan_suscripciones, planes_limites, servicios_consumidos.
// Salida: kpis[] => {codigo, nombre, usado, max}, consumos[] => {fecha, tipo, detalle, cantidad, especialista}
try {
    // Verificar existencia de tablas para evitar errores tempranos
    $chk = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('plan_suscripciones','planes','planes_limites','servicios_consumidos')")->fetchAll(PDO::FETCH_COLUMN);
    $has = array_flip($chk);

    $kpis = [];
    $consumos = [];
    if (isset($has['plan_suscripciones']) && isset($has['planes'])) {
        $stmt = $pdo->prepare("SELECT ps.id, ps.plan_id, pl.nombre AS plan_nombre FROM plan_suscripciones ps INNER JOIN planes pl ON pl.id=ps.plan_id WHERE ps.paciente_id=? AND ps.activo=1 LIMIT 1");
        $stmt->execute([$id_paciente]);
        $sus = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($sus && isset($has['planes_limites'])) {
            $stmt = $pdo->prepare("SELECT codigo, nombre, maximo FROM seguros_limites WHERE plan_nombre=? AND nombre NOT LIKE '%(%)%'");
            $stmt->execute([$sus['plan_nombre']]);
            $limites = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($limites as $lim) {
                if (isset($has['servicios_consumidos'])) {
                    $stmtU = $pdo->prepare("SELECT COALESCE(SUM(cantidad),0) FROM servicios_consumidos WHERE paciente_id=? AND codigo_limite=?");
                    $stmtU->execute([$id_paciente, $lim['codigo']]);
                    $usado = (int)$stmtU->fetchColumn();
                } else {
                    $usado = 0;
                }
                $kpis[] = [
                    'codigo' => $lim['codigo'],
                    'nombre' => $lim['nombre'],
                    'usado' => $usado,
                    'max' => (int)$lim['maximo']
                ];
            }
        }
    }
    if (isset($has['servicios_consumidos'])) {
        $stmt = $pdo->prepare("SELECT sc.created_at AS fecha, sc.tipo_servicio AS tipo, sc.notas AS detalle, sc.cantidad, u.nombre_completo AS especialista
                                FROM servicios_consumidos sc
                                LEFT JOIN usuarios u ON u.id = sc.realizado_por
                                WHERE sc.paciente_id=? AND sc.estado='registrado'
                                AND NOT EXISTS (SELECT 1 FROM seguros_limites sl WHERE sl.codigo = sc.codigo_limite AND sl.nombre LIKE '%(%)%')
                                ORDER BY sc.created_at DESC LIMIT 200");
        $stmt->execute([$id_paciente]);
        $consumos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode(['status' => 'ok', 'kpis' => $kpis, 'consumos' => $consumos]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener consumos', 'detail' => $e->getMessage()]);
}
