<?php
require_once 'api/auth_check.php';
require_once 'api/conexion.php';

$paginaRuta = basename(__FILE__);
$stmtPagina = $pdo->prepare('SELECT id FROM paginas WHERE ruta = ? LIMIT 1');
$stmtPagina->execute([$paginaRuta]);
$paginaId = $stmtPagina->fetchColumn();

if (!$paginaId || !in_array((int) $paginaId, $permisos_usuario, true)) {
    header('Location: app_inicio.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atención 24/7 - Validación</title>
    <link rel="stylesheet" href="css/Source Sans Pro.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a></li>
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

        <div class="content-wrapper p-3">
            <section class="content">
                <div class="container-fluid">
                    <h4><i class="fas fa-check-circle text-success mr-2"></i>Validación / Apertura de Atención 24/7</h4>
                    <div class="card card-primary card-outline">
                        <div class="card-header p-2">
                            <strong>1. Selección de Paciente</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="w-100 text-center">Paciente</label>
                                    <select id="selPaciente" class="form-control text-center">
                                        <option value="">Cargando...</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="w-100 text-center">Plan</label>
                                    <div id="planBadge" class="form-control text-center d-flex align-items-center justify-content-center" style="height:38px;padding:0;background:transparent;border:none;"></div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="w-100 text-center">Estado del Plan</label>
                                    <div id="estadoPlanBadge" class="form-control text-center d-flex align-items-center justify-content-center" style="height:38px;padding:0;background:transparent;border:none;"></div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label class="w-100 text-center">Inscripción</label>
                                    <input id="txtInscripcion" class="form-control text-center" readonly>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="w-100 text-center">Inicio Cobertura (45 días)</label>
                                    <input id="txtInicioCobertura" class="form-control text-center" readonly>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="w-100 text-center">Mensualidad</label>
                                    <input id="txtMensualidad" class="form-control text-right" readonly>
                                </div>
                                <div class="form-group col-md-3" id="grupoDiasRestantes">
                                    <label class="w-100 text-center">Días Restantes para Cobertura</label>
                                    <input id="txtDiasRestantes" class="form-control text-center" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-info card-outline">
                        <div class="card-header p-2">
                            <strong>2. Consumos del Plan</strong>
                        </div>
                        <div class="card-body">
                            <div class="row" id="resumenConsumos">
                                <!-- KPIs dinámicos -->
                            </div>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered" id="tablaConsumos">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Detalle</th>
                                            <th>Cantidad</th>
                                            <th>Especialista</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card card-success card-outline">
                        <div class="card-header p-2">
                            <strong>3. Apertura de Proceso de Atención</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Observaciones</label>
                                    <input id="txtObs" class="form-control" maxlength="240" placeholder="Observaciones adicionales (solo validación de seguro)">
                                </div>
                            </div>
                            <button id="btnAbrirProceso" class="btn btn-success" disabled><i class="fas fa-play mr-1"></i>Abrir Proceso</button>
                            <span id="lblProcesoMsg" class="ml-2 text-muted"></span>
                        </div>
                    </div>

                    <div class="card card-secondary card-outline">
                        <div class="card-header p-2">
                            <strong>4. Procesos Abiertos</strong>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="tablaProcesos">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Paciente</th>
                                            <th>Estado</th>
                                            <th>Creado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">Innovando la Gestión Médica</div><strong>Copyright &copy; 2024-2025 <a
                    href="#">Clínica SaludSonrisa</a>.</strong>
        </footer>

    </div>

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="js/modal_cambiar_contrasena.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/select2/js/select2.full.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="js/atencion_admin.js"></script>
</body>

</html>