<?php require_once 'api/auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte | Pacientes por Periodo</title>
    <link rel="stylesheet" href="css/Source Sans Pro.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <?php include 'modal_cambiar_contrasena.php'; ?>
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Pacientes por Periodo</h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="fechaInicio">Fecha Inicio:</label>
                                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio">
                                </div>
                                <div class="col-md-6">
                                    <label for="fechaFin">Fecha Fin:</label>
                                    <input type="date" class="form-control" id="fechaFin" name="fechaFin">
                                </div>
                            </div>
                            <table id="tablaPacientesPeriodo" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID Paciente</th>
                                        <th>Nombre Paciente</th>
                                        <th>Apellido Paciente</th>
                                        <th>Género</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>Fecha Consulta</th>
                                        <th>Especialista</th>
                                        <th>Diagnóstico</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="main-footer"
            style="position:fixed;left:0;bottom:0;width:100%;z-index:1030;background:#fff;border-top:1px solid #dee2e6;">
            <strong>&copy; 2024-2025 <a href="#">Clínica SaludSonrisa</a>.</strong>
            <div class="float-right d-none d-sm-inline">Innovando la Gestión Médica</div>
        </footer>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="js/reporte_pacientes_periodo.js"></script>
</body>

</html>