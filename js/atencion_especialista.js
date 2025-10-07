// Gestión de Atención 24/7 (Especialista)
(function () {
  const selProceso = $("#selProceso");
  const txtPacienteNombre = $("#txtPacienteNombre");
  const txtPlanE = $("#txtPlanE");
  const txtCoberturaE = $("#txtCoberturaE");
  const txtEstadoProcesoE = $("#txtEstadoProcesoE");
  const tablaHistorial = $("#tablaHistorialPaciente tbody");
  const divResumenPlan = $("#resumen-plan-paciente");
  const txtDiagnostico = $("#txtDiagnostico");
  const txtProcedimiento = $("#txtProcedimiento");
  const txtIndicaciones = $("#txtIndicaciones");
  const btnGuardar = $("#btnGuardarConsulta");

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
    _pacienteActual = pr.paciente_id;
    txtPacienteNombre.val(pr.paciente || "");
    txtPlanE.val(pr.plan || "");
    txtCoberturaE.val(pr.cobertura || "");
    txtEstadoProcesoE.val(pr.estado || "");
    btnGuardar.prop("disabled", false);
    cargarHistorial(pr.paciente_id);
    cargarResumenPlan(pr.paciente_id, pr.plan);
  }

  function limpiarProceso() {
    _pacienteActual = null;
    txtPacienteNombre.val("");
    txtPlanE.val("");
    txtCoberturaE.val("");
    txtEstadoProcesoE.val("");
    tablaHistorial.empty();
    divResumenPlan.html(
      '<p class="text-muted text-center">Seleccione un proceso para ver la cobertura.</p>'
    );
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

  function cargarResumenPlan(idPaciente, planNombre) {
    if (!idPaciente) {
      divResumenPlan.html(
        '<p class="text-warning text-center">No se pudo determinar el paciente para el plan seleccionado.</p>'
      );
      return;
    }

    divResumenPlan.html(
      '<p class="text-muted text-center">Cargando resumen...</p>'
    );
    api("api/atencion_resumen_consumos.php", { id_paciente: idPaciente }, "GET")
      .done((resp) => {
        if (resp.status !== "ok" || !resp.kpis) {
          divResumenPlan.html(
            '<p class="text-danger text-center">Error al cargar resumen.</p>'
          );
          return;
        }
        if (!resp.kpis.length) {
          divResumenPlan.html(
            `<h6 class='text-center'>Plan: <strong>${
              planNombre || "N/A"
            }</strong></h6><p class='text-muted text-center mt-3'>Este plan no tiene cobertura de servicios por cantidad.</p>`
          );
          return;
        }

        let html = `<h6 class='text-center'>Plan: <strong>${
          planNombre || "N/A"
        }</strong></h6>`;
        resp.kpis.forEach((kpi) => {
          const perc = kpi.max > 0 ? (kpi.usado / kpi.max) * 100 : 0;
          let color = "bg-success";
          if (perc >= 50) color = "bg-warning";
          if (perc >= 90) color = "bg-danger";

          html += `
                <div class="progress-group">
                    ${kpi.nombre}
                    <span class="float-right"><b>${kpi.usado}</b>/${kpi.max}</span>
                    <div class="progress progress-sm">
                        <div class="progress-bar ${color}" style="width: ${perc}%"></div>
                    </div>
                </div>`;
        });
        divResumenPlan.html(html);
      })
      .fail(() => {
        divResumenPlan.html(
          '<p class="text-danger text-center">Error al cargar resumen.</p>'
        );
      });
  }

  function guardarConsulta() {
    const procesoId = selProceso.val();
    const diagnostico = txtDiagnostico.val().trim();
    const procedimiento = txtProcedimiento.val().trim();
    const indicaciones = txtIndicaciones.val().trim();
    if (!procesoId) {
      Swal.fire({
        icon: "warning",
        title: "No hay proceso seleccionado",
        text: "Debes seleccionar un proceso abierto antes de guardar la consulta.",
      });
      return;
    }

    const proceso = _procesos.find((x) => String(x.id) === String(procesoId));
    if (!proceso) {
      Swal.fire({
        icon: "error",
        title: "Proceso no encontrado",
        text: "El proceso seleccionado ya no está disponible. Recarga la lista e inténtalo nuevamente.",
      });
      limpiarProceso();
      cargarProcesos();
      return;
    }

    if (!diagnostico) {
      Swal.fire(
        "Dato Requerido",
        "El campo diagnóstico es obligatorio.",
        "warning"
      );
      return;
    }
    btnGuardar.prop("disabled", true);

    api("api/atencion_registrar_consulta.php", {
      id_paciente: proceso.paciente_id,
      proceso_id: procesoId,
      diagnostico,
      procedimiento,
      indicaciones,
    })
      .done((resp) => {
        if (resp.status === "ok") {
          Swal.fire("Éxito", resp.message, "success");
          txtDiagnostico.val("");
          txtProcedimiento.val("");
          txtIndicaciones.val("");
          cargarHistorial(proceso.paciente_id);
          cargarResumenPlan(proceso.paciente_id, txtPlanE.val()); // Recargar resumen
        } else {
          Swal.fire(
            "Error",
            resp.message || "Ocurrió un error no especificado",
            "error"
          );
        }
      })
      .fail(() =>
        Swal.fire(
          "Error de Conexión",
          "No se pudo comunicar con el servidor.",
          "error"
        )
      )
      .always(() => btnGuardar.prop("disabled", false));
  }

  // Eventos
  selProceso.on("change", () => seleccionarProceso(selProceso.val()));
  btnGuardar.on("click", guardarConsulta);

  cargarProcesos();
})();
