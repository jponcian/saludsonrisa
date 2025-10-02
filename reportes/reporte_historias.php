<?php require_once 'api/auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte | Historias Médicas</title>
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
                            <h1 class="m-0">Historias Médicas</h1>
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
                            <table id="tablaHistoriasMedicas" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Especialista</th>
                                        <th>Diagnóstico</th>
                                        <th>Tratamiento</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // La lógica para filtrar por paciente se agregará aquí con JavaScript/AJAX o al enviar el formulario
                                    // Por ahora, se mostrarán todas las consultas para demostrar la estructura
                                    require_once 'api/conexion.php';
                                    try {
                                        $sql = "SELECT c.fecha_consulta, u.nombre_completo as especialista_nombre, c.diagnostico, c.tratamiento, c.observaciones ";
                                        $sql .= "FROM consultas c ";
                                        $sql .= "JOIN especialistas e ON c.especialista_id = e.id ";
                                        $sql .= "JOIN usuarios u ON e.usuario_id = u.id ";
                                        $sql .= "ORDER BY c.fecha_consulta DESC";

                                        $stmt = $pdo->query($sql);
                                        while ($row = $stmt->fetch()) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($row['fecha_consulta']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['especialista_nombre']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['diagnostico']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['tratamiento']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['observaciones']) . '</td>';
                                            echo '</tr>';
                                        }
                                    } catch (PDOException $e) {
                                        echo '<tr><td colspan="5">Error al cargar las historias médicas: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                                    }
                                    ?>
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
            function cargarHistorias(pacienteId = '') {
                if (!pacienteId) {
                    $('#tablaHistoriasMedicas tbody').html('<tr><td colspan="5">Seleccione un paciente para ver sus historias médicas.</td></tr>');
                    return;
                }
                $.ajax({
                    url: 'api/get_historias_paciente.php',
                    method: 'GET',
                    data: {
                        paciente_id: pacienteId
                    },
                    dataType: 'json',
                    success: function (res) {
                        var tbody = '';
                        if (res.data && res.data.length > 0) {
                            res.data.forEach(function (row) {
                                let fecha = row.fecha_consulta ?? '';
                                let fechaFormateada = '';
                                if (fecha) {
                                    // Formato Venezuela: d/m/Y h:i a
                                    let d = new Date(fecha.replace(' ', 'T'));
                                    if (!isNaN(d.getTime())) {
                                        let dia = ('0' + d.getDate()).slice(-2);
                                        let mes = ('0' + (d.getMonth() + 1)).slice(-2);
                                        let anio = d.getFullYear();
                                        let horas = d.getHours();
                                        let minutos = ('0' + d.getMinutes()).slice(-2);
                                        let ampm = horas >= 12 ? 'pm' : 'am';
                                        horas = horas % 12;
                                        horas = horas ? horas : 12;
                                        fechaFormateada = `${dia}/${mes}/${anio} ${horas}:${minutos} ${ampm}`;
                                    } else {
                                        fechaFormateada = fecha;
                                    }
                                }
                                tbody += '<tr>' +
                                    '<td>' + fechaFormateada + '</td>' +
                                    '<td>' + (row.especialista_nombre ?? '') + '</td>' +
                                    '<td>' + (row.diagnostico ?? '') + '</td>' +
                                    '<td>' + (row.tratamiento ?? '') + '</td>' +
                                    '<td>' + (row.observaciones ?? '') + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            tbody = '<tr><td colspan="5">No hay historias médicas para este paciente.</td></tr>';
                        }
                        $('#tablaHistoriasMedicas tbody').html(tbody);
                    },
                    error: function () {
                        $('#tablaHistoriasMedicas tbody').html('<tr><td colspan="5">Error al cargar los datos.</td></tr>');
                    }
                });
            }
            // Al inicio, no mostrar registros
            cargarHistorias('');
            // Filtrar al cambiar el select
            $('#pacienteSelect').on('change', function () {
                cargarHistorias($(this).val());
            });
        });
    </script>
</body>

</html>