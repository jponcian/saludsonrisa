<?php require_once 'api/auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte | Ficha de Control del Paciente</title>
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
                            <h1 class="m-0">Ficha de Control del Paciente</h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="pacienteSelect">Seleccionar Paciente:</label>
                                <select class="form-control" id="pacienteSelect" name="paciente_id">
                                    <option value="">Seleccione un paciente</option>
                                    <?php
                                    require_once 'api/conexion.php';
                                    try {
                                        $stmt = $pdo->query('SELECT id, nombres, apellidos FROM pacientes ORDER BY apellidos, nombres');
                                        while ($row = $stmt->fetch()) {
                                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['apellidos']) . ', ' . htmlspecialchars($row['nombres']) . '</option>';
                                        }
                                    } catch (PDOException $e) {
                                        echo '<option value="">Error al cargar pacientes</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div id="fichaPacienteContent">
                                <!-- Aquí se cargará la información del paciente y sus consultas -->
                                <div class="alert alert-info">Seleccione un paciente para ver su ficha de control.</div>
                            </div>
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
    <script>
        $(document).ready(function () {
            $('#pacienteSelect').change(function () {
                var pacienteId = $(this).val();
                if (pacienteId) {
                    // Aquí se haría una llamada AJAX para cargar la ficha del paciente
                    // Por ahora, un placeholder
                    $('#fichaPacienteContent').html('<div class="alert alert-warning">Cargando ficha para el paciente ID: ' + pacienteId + '... (Implementación AJAX pendiente)</div>');
                } else {
                    $('#fichaPacienteContent').html('<div class="alert alert-info">Seleccione un paciente para ver su ficha de control.</div>');
                }
            });
        });
    </script>
</body>

</html>
</div>
</div>
</div>
</div>
</div>
<footer class="main-footer">
    <div class="float-right d-none d-sm-inline">Innovando la Gestión Médica</div>
    <strong>Copyright &copy; 2024-2025 <a href="#">Clínica SaludSonrisa</a>.</strong>
</footer>
</div>
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
</body>

</html>