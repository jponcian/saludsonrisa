<?php
require_once 'api/auth_check.php';
require_once 'api/conexion.php';

// $paginaRuta = basename(__FILE__);
// $stmtPagina = $pdo->prepare('SELECT id FROM paginas WHERE ruta = ? LIMIT 1');
// $stmtPagina->execute([$paginaRuta]);
// $paginaId = $stmtPagina->fetchColumn();

// if (!$paginaId || !in_array((int) $paginaId, $permisos_usuario, true)) {
//     header('Location: app_inicio.php');
//     exit;
// }

$paciente_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($paciente_id <= 0) {
    header('Location: app_pacientes.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nombres, apellidos, cedula, genero, fecha_nacimiento, telefono, email, direccion, foto_url FROM pacientes WHERE id = ? LIMIT 1');
$stmt->execute([$paciente_id]);
$paciente = $stmt->fetch();

if (!$paciente) {
    header('Location: app_pacientes.php');
    exit;
}

$pacienteNombre = trim($paciente['nombres'] . ' ' . $paciente['apellidos']);
$pacienteCedula = $paciente['cedula'];
$pacienteFoto = $paciente['foto_url'];
$fechaNacimiento = $paciente['fecha_nacimiento'] ? date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) : null;
$edad = null;
if (!empty($paciente['fecha_nacimiento'])) {
    $fechaNacimientoDate = new DateTime($paciente['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fechaNacimientoDate)->y;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historia Clínica | <?php echo htmlspecialchars($pacienteNombre); ?></title>
    <link rel="stylesheet" href="css/Source Sans Pro.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <style>
        .historia-section-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .historia-section-card .card-header {
            background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
            color: #1f2d3d;
            border-bottom: none;
        }

        .historia-section-card .card-header h3 {
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .historia-section-card .card-body {
            background-color: #ffffff;
        }

        .historia-question {
            margin-bottom: 1.25rem;
        }

        .historia-question label {
            font-weight: 600;
        }

        .historia-question small.form-text {
            color: #6c757d;
        }

        .badge-estado {
            font-size: 0.85rem;
            padding: 0.45rem 0.75rem;
        }

        .historia-toolbar {
            position: sticky;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            z-index: 10;
        }

        .historia-empty {
            text-align: center;
            padding: 2rem 1rem;
            color: #6c757d;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($nombre_completo); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">Opciones de Usuario</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-cambiar-contrasena">
                            <i class="fas fa-key mr-2"></i> Cambiar Contraseña
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="api/logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <?php include 'sidebar.php'; ?>
        <?php include 'modal_cambiar_contrasena.php'; ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Historia Clínica</h1>
                            <p class="text-muted mb-0">Paciente: <?php echo htmlspecialchars($pacienteNombre); ?> <?php if ($pacienteCedula) {
                                                                                                                        echo '• C.I. ' . htmlspecialchars($pacienteCedula);
                                                                                                                    } ?></p>
                        </div>
                        <div class="col-sm-6 text-right">
                            <span id="historia-estado" class="badge badge-secondary badge-estado">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body d-flex">
                                    <div class="mr-3">
                                        <?php if (!empty($pacienteFoto)) : ?>
                                            <img src="uploads/<?php echo htmlspecialchars($pacienteFoto); ?>" alt="Foto del paciente" class="img-thumbnail" style="width: 110px; height: 110px; object-fit: cover; border-radius: 12px;">
                                        <?php else : ?>
                                            <div class="d-flex align-items-center justify-content-center bg-light" style="width: 110px; height: 110px; border-radius: 12px;">
                                                <i class="fas fa-user-md fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 class="mb-1" style="font-size:1.25rem;"><?php echo htmlspecialchars($pacienteNombre); ?></h3>
                                        <p class="mb-1 text-muted">C.I.: <?php echo htmlspecialchars($pacienteCedula); ?></p>
                                        <?php if ($edad !== null) : ?><p class="mb-1"><strong>Edad:</strong> <?php echo (int) $edad; ?> años</p><?php endif; ?>
                                        <?php if ($fechaNacimiento) : ?><p class="mb-1"><strong>Nac.:</strong> <?php echo htmlspecialchars($fechaNacimiento); ?></p><?php endif; ?>
                                        <?php if (!empty($paciente['telefono'])) : ?><p class="mb-1"><strong>Tel.:</strong> <?php echo htmlspecialchars($paciente['telefono']); ?></p><?php endif; ?>
                                        <?php if (!empty($paciente['email'])) : ?><p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($paciente['email']); ?></p><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Estado de la historia</h3>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2"><strong>Última actualización:</strong> <span id="historia-ultima"></span></p>
                                    <p class="mb-2"><strong>Marcado como completado por:</strong> <span id="historia-completado-por">—</span></p>
                                    <p class="mb-0 text-muted">Los cambios se guardan manualmente. Asegúrate de presionar "Guardar" antes de salir.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body" id="historiaClinicaApp" data-paciente-id="<?php echo (int) $paciente_id; ?>">
                                    <div id="historia-loading" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Cargando...</span>
                                        </div>
                                        <p class="mt-3 mb-0 text-muted">Cargando cuestionario de historia clínica...</p>
                                    </div>
                                    <div id="historia-secciones" style="display:none;"></div>
                                    <div id="historia-empty" class="historia-empty" style="display:none;">
                                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                                        <p class="mb-1">Aún no se han configurado secciones o preguntas para la historia clínica.</p>
                                        <p class="text-muted">Contacta al administrador para definir el cuestionario.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="historia-toolbar">
                                <button id="btn-guardar-historia" class="btn btn-outline-primary" disabled><i class="fas fa-save mr-2"></i>Guardar progreso</button>
                                <button id="btn-completar-historia" class="btn btn-success" disabled><i class="fas fa-check mr-2"></i>Guardar y marcar como completada</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <strong>&copy; 2024-2025 <a href="#">Clínica SaludSonrisa</a>.</strong>
            <div class="float-right d-none d-sm-inline">Innovando la Gestión Médica</div>
        </footer>
    </div>

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <script>
        window.HistoriaPaciente = {
            id: <?php echo (int) $paciente_id; ?>,
            nombre: <?php echo json_encode($pacienteNombre, JSON_UNESCAPED_UNICODE); ?>
        };
        window.AuthUsuario = {
            id: <?php echo (int) $usuario_id; ?>,
            nombre: <?php echo json_encode($nombre_completo, JSON_UNESCAPED_UNICODE); ?>
        };
    </script>
    <script src="js/historia_clinica.js"></script>
</body>

</html>