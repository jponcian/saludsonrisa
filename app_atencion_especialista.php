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
    <title>Atención 24/7 - Emergencia</title>
    <link rel="stylesheet" href="css/Source Sans Pro.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
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
                    <h4><i class="fas fa-ambulance text-danger mr-2"></i>Atención 24/7 - Emergencia</h4>
                    <div class="card card-danger card-outline">
                        <div class="card-header p-2">
                            <strong>1. Seleccionar Proceso Abierto</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Proceso</label>
                                    <select id="selProceso" class="form-control">
                                        <option value="">Cargando...</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Paciente</label>
                                    <input id="txtPacienteNombre" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Plan</label>
                                    <input id="txtPlanE" class="form-control" readonly>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Cobertura</label>
                                    <input id="txtCoberturaE" class="form-control" readonly>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Estado Proceso</label>
                                    <input id="txtEstadoProcesoE" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card card-info card-outline">
                                <div class="card-header p-2">
                                    <strong>2. Historial del Paciente</strong>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive" style="max-height:260px;overflow:auto;">
                                        <table class="table table-sm table-hover" id="tablaHistorialPaciente">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Tipo</th>
                                                    <th>Detalle</th>
                                                    <th>Especialista</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-warning card-outline">
                                <div class="card-header p-2">
                                    <strong>Resumen de Cobertura del Plan</strong>
                                </div>
                                <div id="resumen-plan-paciente" class="card-body" style="max-height:325px;overflow:auto;">
                                    <p class="text-muted text-center">Seleccione un proceso para ver la cobertura.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-success card-outline">
                        <div class="card-header p-2">
                            <strong>3. Registrar Consulta</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Columna izquierda: Campos de la consulta -->
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="txtDiagnostico" class="form-label">Diagnóstico <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="txtDiagnostico" rows="3" placeholder="Ingrese el diagnóstico" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="txtProcedimiento" class="form-label">Procedimiento / Tratamiento</label>
                                        <textarea class="form-control" id="txtProcedimiento" rows="3" placeholder="Ingrese el procedimiento o tratamiento"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="txtIndicaciones" class="form-label">Indicaciones</label>
                                        <textarea class="form-control" id="txtIndicaciones" rows="3" placeholder="Ingrese las indicaciones"></textarea>
                                    </div>
                                </div>
                                <!-- Columna derecha: Ítems del plan a descontar -->
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Ítems del plan a descontar</label>
                                        <div id="plan-items-checks">
                                            <p class="text-muted">Seleccione un proceso para ver los ítems del plan.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-warning mr-2" id="btnCerrarProceso" disabled>
                                    <i class="fas fa-door-closed mr-1"></i>Cerrar Proceso
                                </button>
                                <button type="button" class="btn btn-success" id="btnGuardarConsulta" disabled>
                                    <i class="fas fa-save mr-1"></i>Guardar Consulta
                                </button>
                                <span id="lblConsultaMsg" class="ml-2 text-muted"></span>
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
    <script>
        // ...el JS de gestión de atención y consulta se maneja en js/atencion_especialista.js...
    </script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="js/atencion_especialista.js"></script>
</body>

</html>