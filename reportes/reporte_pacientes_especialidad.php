<?php require_once 'api/auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte | Pacientes por Especialidad</title>
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
                            <h1 class="m-0">Pacientes por Especialidad</h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="especialidadSelect">Seleccionar Especialidad:</label>
                                <select class="form-control" id="especialidadSelect" name="especialidad">
                                    <option value="">Todas las especialidades</option>
                                    <?php
                                    require_once 'api/conexion.php';
                                    try {
                                        $stmt = $pdo->query('SELECT id, nombre FROM especialidades ORDER BY nombre');
                                        while ($row = $stmt->fetch()) {
                                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['nombre']) . '</option>';
                                        }
                                    } catch (PDOException $e) {
                                        echo '<option value="">Error al cargar especialidades</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <table id="tablaPacientesEspecialidad" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID Paciente</th>
                                        <th>Nombre Paciente</th>
                                        <th>Apellido Paciente</th>
                                        <th>Género</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>Especialidad</th>
                                        <th>Fecha Consulta</th>
                                        <th>Diagnóstico</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- El contenido de la tabla se llenará por AJAX -->
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
    <script>
        $(document).ready(function () {
            function cargarPacientes(especialidadId = '') {
                $.ajax({
                    url: 'api/get_pacientes_especialidad.php',
                    method: 'GET',
                    data: {
                        especialidad_id: especialidadId
                    },
                    dataType: 'json',
                    success: function (res) {
                        var tbody = '';
                        if (res.data && res.data.length > 0) {
                            res.data.forEach(function (row) {
                                tbody += '<tr>' +
                                    '<td>' + (row.paciente_id ?? '') + '</td>' +
                                    '<td>' + (row.paciente_nombre ?? '') + '</td>' +
                                    '<td>' + (row.paciente_apellido ?? '') + '</td>' +
                                    '<td>' + (row.genero ?? '') + '</td>' +
                                    '<td>' + (row.telefono ?? '') + '</td>' +
                                    '<td>' + (row.email ?? '') + '</td>' +
                                    '<td>' + (row.especialidad ?? '') + '</td>' +
                                    '<td>' + (row.fecha_consulta ?? '') + '</td>' +
                                    '<td>' + (row.diagnostico ?? '') + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            tbody = '<tr><td colspan="9">No hay datos para mostrar.</td></tr>';
                        }
                        $('#tablaPacientesEspecialidad tbody').html(tbody);
                    },
                    error: function () {
                        $('#tablaPacientesEspecialidad tbody').html('<tr><td colspan="9">Error al cargar los datos.</td></tr>');
                    }
                });
            }
            // Cargar todos al inicio
            cargarPacientes();
            // Filtrar al cambiar el select
            $('#especialidadSelect').on('change', function () {
                cargarPacientes($(this).val());
            });
        });
    </script>
</body>

</html>