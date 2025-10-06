// Gestión de Atención 24/7 (Especialista)
(function () {
  const selProceso = $("#selProceso");
  const txtPacienteNombre = $("#txtPacienteNombre");
  const txtPlanE = $("#txtPlanE");
  const txtCoberturaE = $("#txtCoberturaE");
  const txtEstadoProcesoE = $("#txtEstadoProcesoE");
  const tablaHistorial = $("#tablaHistorialPaciente tbody");
  const txtDiagnostico = $("#txtDiagnostico");
  const txtProcedimiento = $("#txtProcedimiento");
  const txtIndicaciones = $("#txtIndicaciones");
  const btnGuardar = $("#btnGuardarConsulta");
  const lblConsultaMsg = $("#lblConsultaMsg");

  let _procesos = [];
  let _pacienteActual = null;

  function api(url, data, method = "POST") {
    return $.ajax({ url, method, data, dataType: "json" });
  }

  function cargarProcesos() {
    selProceso.html('<option value="">Cargando...</option>');
    api("api/atencion_listar_procesos_abiertos.php", {}, "GET")
      .done((resp) => {
        if (resp.status === "ok") {
          _procesos = resp.data || [];
          const opts = ['<option value="">-- Seleccione --</option>'];
          _procesos.forEach((p) =>
            opts.push(
              '<option value="' +
                p.id +
                '">#' +
                p.id +
                " - " +
                p.paciente +
                "</option>"
            )
          );
          selProceso.html(opts.join(""));
        } else selProceso.html('<option value="">Sin datos</option>');
      })
      .fail(() => selProceso.html('<option value="">Error</option>'));
  }

  function seleccionarProceso(id) {
    const pr = _procesos.find((x) => x.id == id);
    if (!pr) {
      limpiarProceso();
      return;
    }
    _pacienteActual = pr.id_paciente;
    txtPacienteNombre.val(pr.paciente || "");
    txtPlanE.val(pr.plan || "");
    txtCoberturaE.val(pr.cobertura || "");
    txtEstadoProcesoE.val(pr.estado || "");
    btnGuardar.prop("disabled", false);
    cargarHistorial(pr.id_paciente);
  }

  function limpiarProceso() {
    _pacienteActual = null;
    txtPacienteNombre.val("");
    txtPlanE.val("");
    txtCoberturaE.val("");
    txtEstadoProcesoE.val("");
    tablaHistorial.empty();
    btnGuardar.prop("disabled", true);
  }

  function cargarHistorial(idPaciente) {
    tablaHistorial.html(
      '<tr><td colspan="4" class="text-center text-muted">Cargando...</td></tr>'
    );
    api(
      "api/atencion_historial_paciente.php",
      { id_paciente: idPaciente },
      "GET"
    )
      .done((resp) => {
        if (resp.status === "ok") {
          const rows = (resp.data || []).map(
            (r) =>
              "<tr>" +
              "<td>" +
              r.fecha +
              "</td><td>" +
              r.tipo +
              "</td><td>" +
              (r.detalle || "") +
              "</td><td>" +
              (r.especialista || "") +
              "</td></tr>"
          );
          tablaHistorial.html(
            rows.join("") ||
              '<tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>'
          );
        } else
          tablaHistorial.html(
            '<tr><td colspan="4" class="text-center text-danger">Error</td></tr>'
          );
      })
      .fail(() =>
        tablaHistorial.html(
          '<tr><td colspan="4" class="text-center text-danger">Error</td></tr>'
        )
      );
  }

  function guardarConsulta() {
    if (!_pacienteActual) return;
    const diagnostico = txtDiagnostico.val().trim();
    const procedimiento = txtProcedimiento.val().trim();
    const indicaciones = txtIndicaciones.val().trim();
    if (!diagnostico) {
      lblConsultaMsg.text("Diagnóstico requerido").addClass("text-danger");
      return;
    }
    btnGuardar.prop("disabled", true);
    lblConsultaMsg.removeClass("text-danger text-success").text("Guardando...");
    api("api/atencion_registrar_consulta.php", {
      id_paciente: _pacienteActual,
      diagnostico,
      procedimiento,
      indicaciones,
    })
      .done((resp) => {
        if (resp.status === "ok") {
          lblConsultaMsg.text("Consulta registrada").addClass("text-success");
          txtDiagnostico.val("");
          txtProcedimiento.val("");
          txtIndicaciones.val("");
          cargarHistorial(_pacienteActual);
        } else {
          lblConsultaMsg.text(resp.message || "Error").addClass("text-danger");
        }
      })
      .fail(() =>
        lblConsultaMsg.text("Error de conexión").addClass("text-danger")
      )
      .always(() => btnGuardar.prop("disabled", false));
  }

  // Eventos
  selProceso.on("change", () => seleccionarProceso(selProceso.val()));
  btnGuardar.on("click", guardarConsulta);

  cargarProcesos();
})();
