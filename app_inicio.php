<?php require_once 'api/auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clínica SaludSonrisa | Inicio</title>
    <link rel="stylesheet" href="css/Source Sans Pro.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <style>
        /* Evita el cursor titilando en el área principal al hacer clic */
        .no-blink:focus {
            outline: none !important;
            box-shadow: none !important;
            caret-color: transparent !important;
        }
    </style>
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
                    <!-- Modal pregunta mostrar resumen -->
                    <div class="modal fade" id="mostrarResumenModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-body text-center">
                                    <h5 class="mb-3">¿Deseas ver el resumen de consultas?</h5>
                                    <div class="d-flex justify-content-center">
                                        <button id="btnResumenSi" type="button" class="btn btn-primary mr-2">Sí</button>
                                        <button id="btnResumenNo" type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal resumen con gráficos -->
                    <div class="modal fade" id="resumenModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Resumen de consultas</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="card">
                                                <div class="card-header">Consultas diarias (últimos 14 días)</div>
                                                <div class="card-body">
                                                    <canvas id="chartConsultasDiarias" style="width:100%;height:320px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="card">
                                                <div class="card-header">Consultas por especialidad</div>
                                                <div class="card-body">
                                                    <canvas id="chartPorEspecialidad" style="width:100%;height:320px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>

        <?php include 'sidebar.php'; ?>
        <?php include 'modal_cambiar_contrasena.php'; ?>
        <div class="content-wrapper">
            <section class="content pt-5">
                <div class="container-fluid">
                    <!-- Modal de bienvenida -->
                    <div class="modal fade" id="bienvenidaModal" tabindex="-1" role="dialog"
                        aria-labelledby="bienvenidaModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-body text-center">
                                    <img src="logo.png" alt="Logo Clínica SaludSonrisa"
                                        style="width:110px; margin-bottom:18px;">
                                    <h4 class="mb-2" style="color:#007bff;">¡Bienvenido!</h4>
                                    <div style="font-size:1.1rem; color:#333;">Has iniciado sesión en <b>Clínica
                                            SaludSonrisa</b>.<br>¡Que tengas un excelente día!</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal Cambiar Contraseña -->
                    <?php // El modal se incluye externamente arriba 
                    ?>
                    <!-- Contenido principal aquí -->
                    <div class="row justify-content-center">
                        <div class="col-12 text-center mt-5 no-blink" tabindex="-1"
                            style="position:relative; min-height:350px;">
                            <img src="logo.png" alt="Logo Clínica SaludSonrisa"
                                style="opacity:0.08; position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); max-width:350px; width:80vw; pointer-events:none; z-index:0;">
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- (Eliminado Chart.js y scripts de resumen) -->
    <script src="js/modal_cambiar_contrasena.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script>
        // Solo mostrar el modal de bienvenida y ocultarlo automáticamente
        $(document).ready(function() {
            // Ensure modals are direct children of body to avoid z-index and backdrop issues
            $('#bienvenidaModal, #mostrarResumenModal, #resumenModal').appendTo('body');

            // Helper to clean modal/backdrop state
            function _cleanModalState() {
                // hide any stray visible modals (except none)
                $('.modal.show').each(function() {
                    try {
                        $(this).modal('hide');
                    } catch (e) {}
                });
                // remove all backdrops
                $('.modal-backdrop').remove();
                // remove body class if no modal visible
                $('body').removeClass('modal-open');
            }
            $(function() {
                // Mostrar solo el modal de bienvenida y cerrarlo a los 3s
                $('#bienvenidaModal').appendTo('body').modal('show');
                setTimeout(function() {
                    $('#bienvenidaModal').modal('hide');
                }, 3000);
            });
        });
    </script>