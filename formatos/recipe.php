<?php
require_once __DIR__ . '/../api/auth_check.php';
require_once __DIR__ . '/../api/conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo "Consulta no especificada.";
    exit;
}

// Obtener datos de la consulta, paciente y especialista
$stmt = $pdo->prepare("SELECT c.*, p.nombres AS p_nombres, p.apellidos AS p_apellidos, p.cedula AS p_cedula, p.telefono AS p_telefono, p.direccion AS p_direccion, p.fecha_nacimiento AS p_fecha_nac, u.nombre_completo AS esp_nombre, u.cedula AS esp_cedula, u.telefono AS esp_telefono FROM consultas c LEFT JOIN pacientes p ON c.paciente_id = p.id LEFT JOIN especialistas e ON c.especialista_id = e.id LEFT JOIN usuarios u ON e.usuario_id = u.id WHERE c.id = ? LIMIT 1");
$stmt->execute([$id]);
$consulta = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$consulta) {
    echo "Consulta no encontrada.";
    exit;
}

// Obtener especialidades
$stmt2 = $pdo->prepare("SELECT s.nombre FROM consulta_especialidades ce JOIN especialidades s ON ce.especialidad_id = s.id WHERE ce.consulta_id = ?");
$stmt2->execute([$id]);
$especialidades = $stmt2->fetchAll(PDO::FETCH_COLUMN);

// Formateos
$fecha_consulta = date('d-m-Y H:i', strtotime($consulta['fecha_consulta']));
$paciente_nombre = $consulta['p_nombres'] . ' ' . $consulta['p_apellidos'];
$medico_nombre = $consulta['esp_nombre'] ?? '';

// Helpers de formato
function formato_fecha_dmy($raw)
{
    if (!$raw) return '';
    // si ya viene dd-mm-yyyy
    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $raw)) return $raw;
    // si viene yyyy-mm-dd
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $raw, $m)) return $m[3] . '-' . $m[2] . '-' . $m[1];
    // intentar parsear
    $ts = strtotime($raw);
    if ($ts !== false) return date('d-m-Y', $ts);
    return $raw;
}

function formato_cedula($raw)
{
    if (!$raw) return '';
    $raw = trim($raw);
    // quitar espacios y guiones en el número
    // si tiene prefijo letra (V, E, etc.) mantenerla
    if (preg_match('/^\s*([VEve])[-\s]?(\d+)$/', $raw, $m)) {
        return strtoupper($m[1]) . '-' . $m[2];
    }
    // si viene con otros caracteres, extraer dígitos
    if (preg_match('/(\d{6,})/', $raw, $m2)) {
        return 'V-' . $m2[1];
    }
    // fallback
    return 'V-' . preg_replace('/[^0-9]/', '', $raw);
}

function formato_telefono($raw)
{
    if (!$raw) return '';
    $digits = preg_replace('/[^0-9]/', '', $raw);
    if (strlen($digits) <= 4) return $digits;
    $first = substr($digits, 0, 4);
    $rest = substr($digits, 4);
    return $first . '-' . $rest;
}

// Campos formateados para mostrar
$p_fecha_nac_fmt = formato_fecha_dmy($consulta['p_fecha_nac'] ?? '');
$p_cedula_fmt = formato_cedula($consulta['p_cedula'] ?? '');
$p_telefono_fmt = formato_telefono($consulta['p_telefono'] ?? '');
$esp_cedula_fmt = formato_cedula($consulta['esp_cedula'] ?? '');
$esp_telefono_fmt = formato_telefono($consulta['esp_telefono'] ?? '');

// Calcular edad a partir de la fecha (devuelve número entero de años o vacío)
function calcular_edad($raw)
{
    if (!$raw) return '';
    // intentar varios formatos
    $date = false;
    // yyyy-mm-dd
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $raw)) {
        $date = DateTime::createFromFormat('Y-m-d', substr($raw, 0, 10));
    }
    // dd-mm-yyyy
    if (!$date && preg_match('/^\d{2}-\d{2}-\d{4}$/', $raw)) {
        $parts = explode('-', $raw);
        $date = DateTime::createFromFormat('d-m-Y', $raw);
    }
    // intentar strtotime
    if (!$date) {
        $ts = strtotime($raw);
        if ($ts !== false) $date = (new DateTime())->setTimestamp($ts);
    }
    if (!$date) return '';
    $now = new DateTime();
    $diff = $now->diff($date);
    return $diff->y;
}

$p_edad = calcular_edad($consulta['p_fecha_nac'] ?? '');

// Plantilla HTML (hoja carta horizontal, dos columnas)
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ficha de Consulta - <?php echo htmlspecialchars($paciente_nombre); ?></title>
    <style>
        @page {
            size: Letter landscape;
            margin: 10mm;
            /* 1cm por todos lados */
        }

        /* usar box-sizing para evitar desbordes por padding/border */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            background: #fff;
        }

        .container {
            display: flex;
            gap: 20px;
            height: calc(100vh - 40px);
            align-items: stretch;
            position: relative;
            z-index: 2;
            /* sit above watermark wrapper */
        }

        /* Per-column watermark implemented with ::before pseudo-element (one logo per column) */

        .col {
            flex: 1;
            border: 1px solid #ddd;
            padding: 16px;
            border-radius: 6px;
            background: #fff;
            display: flex;
            flex-direction: column;
            position: relative;
            /* para poder anclar la firma dentro de la columna */
            overflow: hidden;
        }

        /* Pseudo-element watermark inside each column so it prints per column/page */
        .col::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-repeat: no-repeat;
            background-position: center center;
            background-size: 60%;
            width: 100%;
            height: 100%;
            opacity: 0.18;
            /* más clara (visible pero tenue) */
            pointer-events: none;
            z-index: 0;
            filter: grayscale(1);
        }

        /* Left and right variants: position the logo toward the column side and mirror right */
        .col.left::before {
            background-image: url('../logo.png');
            background-position: center center;
        }

        .col.right::before {
            background-image: url('../logo.png');
            background-position: center center;
            transform: translate(-50%, -50%) scaleX(-1);
        }

        /* Ensure actual content sits above the watermark */
        .col>* {
            position: relative;
            z-index: 1;
        }

        .col .col-body {
            flex: 1 1 auto;
            overflow: hidden;
            padding-bottom: 100px;
            /* espacio reservado para la firma anclada */
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo img {
            height: 60px;
            width: auto;
        }

        .clinic {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .meta {
            text-align: right;
            font-size: 0.9rem;
            color: #555;
        }

        .section-title {
            font-weight: 700;
            margin-top: 8px;
            margin-bottom: 6px;
            border-bottom: 1px dashed #e0e0e0;
            padding-bottom: 4px;
        }

        .field {
            margin-bottom: 6px;
        }

        .label {
            font-weight: 600;
            color: #333;
            display: inline-block;
            width: 120px;
        }

        .value {
            color: #111;
        }

        .prescription {
            white-space: pre-wrap;
            background: #fafafa;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #eee;
            max-height: 100%;
            overflow: auto;
        }

        /* Botón imprimir con estilo */
        .print-btn {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        }

        .print-btn:hover {
            opacity: 0.95;
        }

        .small {
            font-size: 0.85rem;
            color: #666;
        }

        @media print {

            html,
            body {
                height: 100%;
            }

            .no-print {
                display: none;
            }

            .container {
                height: 100%;
                gap: 12px;
            }

            .col {
                height: 100%;
                padding: 12px;
                border: none;
                position: relative;
                break-inside: avoid;
                -webkit-column-break-inside: avoid;
                -webkit-page-break-inside: avoid;
            }

            /* Per-column watermark should also print: bigger and slightly more visible */
            .col::before {
                background-size: 80%;
                opacity: 0.28;
                /* más visible en impresión */
            }

            .col.left::before {
                background-image: url('../logo.png');
                background-position: 15% center;
            }

            .col.right::before {
                background-image: url('../logo.png');
                background-position: 85% center;
                transform: translate(-50%, -50%) scaleX(-1);
            }

            .col .col-body {
                overflow: visible;
                padding-bottom: 100px;
            }

            /* Anclar la firma al pie de la columna para que no salte a otra página */
            .signature {
                page-break-inside: avoid;
                break-inside: avoid;
                position: absolute;
                left: 12px;
                right: 12px;
                bottom: 12px;
                text-align: center;
                background: transparent;
            }

            .prescription {
                max-height: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align:right; margin-bottom:8px;"><button class="print-btn" onclick="window.print()" aria-label="Imprimir ficha">Imprimir / Guardar PDF</button></div>
    <div class="container">
        <div class="col left">
            <div class="header">
                <div class="logo">
                    <img src="../logo.png" alt="Logo Clinica">
                    <div>
                        <div class="clinic">Clínica SaludSonrisa</div>
                        <div class="small">Ficha de Consulta</div>
                    </div>
                </div>
                <div class="meta">
                    <div><?php echo $fecha_consulta; ?></div>
                </div>
            </div>

            <div class="col-body">

                <div class="section-title">Datos del Paciente</div>
                <div class="field"><span class="label">Paciente:</span><span class="value"><?php echo htmlspecialchars($paciente_nombre); ?></span></div>
                <div class="field"><span class="label">Cédula:</span><span class="value"><?php echo htmlspecialchars($p_cedula_fmt); ?></span></div>
                <div class="field"><span class="label">Teléfono:</span><span class="value"><?php echo htmlspecialchars($p_telefono_fmt); ?></span></div>
                <div class="field"><span class="label">F. Nac:</span><span class="value"><?php echo htmlspecialchars($p_fecha_nac_fmt); ?><?php if ($p_edad !== '') {
                                                                                                                                                echo ' (' . intval($p_edad) . ' años)';
                                                                                                                                            } ?></span></div>

                <!-- Sección Datos del Médico eliminada según petición -->

                <div class="section-title">Diagnóstico</div>
                <div class="prescription"><?php echo nl2br(htmlspecialchars($consulta['diagnostico'])); ?></div>

            </div>

            <div class="signature" style="margin-top:12px; text-align:center;">
                <div style="border-top:1px solid #ddd; padding-top:10px;">Dr(a). <?php echo htmlspecialchars($medico_nombre); ?></div>
                <div class="small">Firma y Sello</div>
            </div>

        </div>

        <div class="col right">
            <div class="header">
                <div class="clinic">Tratamiento</div>
                <div class="small">Paciente: <?php echo htmlspecialchars($paciente_nombre); ?></div>
            </div>

            <div class="col-body">
                <div class="section-title">Especialidades</div>
                <div class="field"><?php echo htmlspecialchars(implode(', ', $especialidades)); ?></div>

                <div class="section-title">Tratamiento</div>
                <div class="prescription"><?php echo nl2br(htmlspecialchars($consulta['tratamiento'])); ?></div>

                <div class="section-title">Observaciones</div>
                <div class="prescription"><?php echo nl2br(htmlspecialchars($consulta['observaciones'])); ?></div>

            </div>

            <div class="signature" style="margin-top:12px; text-align:center;">
                <div style="border-top:1px solid #ddd; padding-top:10px;">Dr(a). <?php echo htmlspecialchars($medico_nombre); ?></div>
                <div class="small">Firma y Sello</div>
            </div>
        </div>
    </div>
</body>

</html>