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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clínica SaludSonrisa | Facturación</title>

    <link rel="stylesheet" href="css/Source Sans Pro.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/ekko-lightbox/ekko-lightbox.css">
    <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <style>
        /* Centrar encabezados y celdas de la tabla de pagos; monto a la derecha */
        #facTablaPagos thead th {
            text-align: center;
            vertical-align: middle;
        }

        #facTablaPagos tbody td {
            text-align: center;
            vertical-align: middle;
        }

        /* Columna Monto (ajustada a nueva posición) */
        #facTablaPagos tbody td:nth-child(5) {
            text-align: right;
        }

        /* Mantener referencia/fecha/periodo centradas */
        #facTablaPagos tbody td:nth-child(1),
        #facTablaPagos tbody td:nth-child(2),
        #facTablaPagos tbody td:nth-child(3),
        #facTablaPagos tbody td:nth-child(4),
        #facTablaPagos tbody td:nth-child(6) {
            text-align: center;
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
                            <h1 class="m-0">Gestión de Facturación / Suscripciones</h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    <div class="alert alert-info p-2 mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Selecciona un paciente para asociar un plan, registrar pago de inscripción y controlar mensualidades.
                    </div>

                    <div class="card card-primary card-outline">
                        <div class="card-header p-2"><strong>1. Selección de Paciente</strong></div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-5">
                                    <label class="w-100 text-center">Paciente</label>
                                    <select id="facSelPaciente" class="form-control"></select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="w-100 text-center">Plan Actual</label>
                                    <div id="facPlanActual" class="form-control text-center font-weight-bold" style="min-height: 38px; background-color: #f4f6f9;">-</div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="w-100 text-center">Cobertura</label>
                                    <div class="text-center">
                                        <span id="facCoberturaEstado" class="badge" style="font-size: 1rem; padding: .5rem 1rem;">-</span>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="w-100 text-center">Inicio Cobertura</label>
                                    <input id="facCoberturaInicio" class="form-control text-center" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-info card-outline collapsed-card" id="facCardPlan">
                        <div class="card-header p-2">
                            <strong>2. Asignar / Cambiar Plan</strong>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" aria-label="Colapsar sección plan">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label class="w-100 text-center">Plan</label>
                                    <select id="facSelPlan" class="form-control"></select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="w-100 text-center">Días Espera</label>
                                    <input id="facDiasEspera" class="form-control text-center" value="45" readonly>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="w-100 text-center">Observaciones</label>
                                    <input id="facObsPlan" class="form-control" maxlength="140">
                                </div>
                            </div>
                            <button id="facBtnAsignarPlan" class="btn btn-primary"><i class="fas fa-link mr-1"></i>Asignar / Actualizar Plan</button>
                            <span id="facMsgPlan" class="ml-2 text-muted"></span>
                        </div>
                    </div>

                    <div class="card card-success card-outline collapsed-card" id="facCardPagos">
                        <div class="card-header p-2">
                            <strong>3. Pagos</strong>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" aria-label="Colapsar sección pagos">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="facInscripcionStatus" class="d-none mb-3">
                                <!-- El estado de la inscripción aparecerá aquí -->
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label class="w-100 text-center">Tipo Pago</label>
                                    <select id="facTipoPago" class="form-control">
                                        <option value="inscripcion">Inscripción</option>
                                        <option value="mensualidad">Mensualidad</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="w-100 text-center">Modalidad de Pago</label>
                                    <select id="facModalidadPago" class="form-control">
                                        <option value="Punto de Venta">Punto de Venta</option>
                                        <option value="Pago Movil">Pago Móvil</option>
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Divisas">Divisas</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="w-100 text-center">Monto</label>
                                    <input id="facMontoPago" type="text" class="form-control text-right">
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="w-100 text-center">Fecha</label>
                                    <input id="facFechaPago" type="date" class="form-control text-right" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="w-100 text-center">Referencia</label>
                                    <input id="facRefPago" class="form-control text-right" maxlength="60">
                                </div>
                            </div>
                            <button id="facBtnRegistrarPago" class="btn btn-success"><i class="fas fa-dollar-sign mr-1"></i>Registrar Pago</button>
                            <span id="facMsgPago" class="ml-2 text-muted"></span>
                            <hr>
                            <h6 class="mb-2"><i class="fas fa-list mr-1"></i>Historial de Pagos</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="facTablaPagos">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Fecha Pago</th>
                                            <th>Desde</th>
                                            <th>Hasta</th>
                                            <th>Tipo</th>
                                            <th>Monto</th>
                                            <th>Modalidad</th>
                                            <th>Ref</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
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
    <script src="plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
    <script src="plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="plugins/ekko-lightbox/ekko-lightbox.min.js"></script>
    <script src="plugins/select2/js/select2.full.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="plugins/inputmask/jquery.inputmask.min.js"></script>
    <script src="js/facturacion.js"></script>
    <script src="js/modal_cambiar_contrasena.js"></script>
</body>

</html>