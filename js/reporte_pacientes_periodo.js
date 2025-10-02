$(document).ready(function() {
    function cargarPacientesPorPeriodo() {
        const fechaInicio = $('#fechaInicio').val();
        const fechaFin = $('#fechaFin').val();

        if (fechaInicio && fechaFin) {
            $.ajax({
                url: 'api/get_pacientes_periodo.php',
                method: 'GET',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin
                },
                dataType: 'json',
                success: function(data) {
                    let tableBody = '';
                    if (data.length > 0) {
                        data.forEach(function(paciente) {
                            tableBody += `<tr>
                                <td>${paciente.paciente_id}</td>
                                <td>${paciente.paciente_nombre}</td>
                                <td>${paciente.paciente_apellido}</td>
                                <td>${paciente.genero || ''}</td>
                                <td>${paciente.telefono || ''}</td>
                                <td>${paciente.email || ''}</td>
                                <td>${paciente.fecha_consulta}</td>
                                <td>${paciente.especialista_nombre}</td>
                                <td>${paciente.diagnostico}</td>
                            </tr>`;
                        });
                    } else {
                        tableBody = '<tr><td colspan="9">No se encontraron pacientes para el periodo seleccionado.</td></tr>';
                    }
                    $('#tablaPacientesPeriodo tbody').html(tableBody);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error al cargar los pacientes por periodo:", textStatus, errorThrown);
                    $('#tablaPacientesPeriodo tbody').html('<tr><td colspan="9">Error al cargar los datos. Intente de nuevo.</td></tr>');
                }
            });
        } else {
            $('#tablaPacientesPeriodo tbody').html('<tr><td colspan="9">Seleccione ambas fechas para filtrar.</td></tr>');
        }
    }

    // Cargar datos cuando cambian las fechas
    $('#fechaInicio, #fechaFin').on('change', cargarPacientesPorPeriodo);

    // Cargar datos inicialmente si ya hay fechas preseleccionadas (aunque no es el caso aquí)
    // cargarPacientesPorPeriodo();
});
