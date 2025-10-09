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
  const btnCerrarProceso = $("#btnCerrarProceso");
  const contenedorItemsPlan = $("#plan-items-checks");

  let procesos = [];
  let pacienteActual = null;

  const MENSAJE_ITEMS_DEFAULT =
    '<p class="text-muted">Seleccione un proceso para ver los ítems del plan.</p>';

  function api(url, data, method = "POST") {
    return $.ajax({ url: url, method: method, data: data, dataType: "json" });
  }

  function cargarProcesos() {
    selProceso.html('<option value="">Cargando...</option>');
    api("api/atencion_listar_procesos_abiertos.php", {}, "GET")
      .done(function (resp) {
        if (resp.status === "ok") {
          procesos = resp.data || [];
          var opciones = ['<option value="">-- Seleccione --</option>'];
          procesos.forEach(function (p) {
            opciones.push(
              '<option value="' +
                p.id +
                '">#' +
                p.id +
                " - " +
                p.paciente +
                "</option>"
            );
          });
          selProceso.html(opciones.join(""));
        } else {
          selProceso.html('<option value="">Sin datos</option>');
        }
      })
      .fail(function () {
        selProceso.html('<option value="">Error</option>');
      });
  }

  function seleccionarProceso(idProceso) {
    var proceso = procesos.find(function (p) {
      return String(p.id) === String(idProceso);
    });

    if (!proceso) {
      limpiarProceso();
      return;
    }

    pacienteActual = proceso.paciente_id;
    txtPacienteNombre.val(proceso.paciente || "");
    txtPlanE.val(proceso.plan || "");
    txtCoberturaE.val(proceso.cobertura || "");
    txtEstadoProcesoE.val(proceso.estado || "");
    btnGuardar.prop("disabled", false);
    btnCerrarProceso.prop("disabled", false).data("procesoId", proceso.id);

    cargarHistorial(proceso.paciente_id);
    cargarResumenPlan(proceso.paciente_id, proceso.plan);
    cargarItemsPlan(proceso.paciente_id);
  }

  function limpiarProceso() {
    pacienteActual = null;
    txtPacienteNombre.val("");
    txtPlanE.val("");
    txtCoberturaE.val("");
    txtEstadoProcesoE.val("");
    tablaHistorial.empty();
    divResumenPlan.html(
      '<p class="text-muted text-center">Seleccione un proceso para ver la cobertura.</p>'
    );
    contenedorItemsPlan.html(MENSAJE_ITEMS_DEFAULT);
    btnGuardar.prop("disabled", true);
    btnCerrarProceso.prop("disabled", true).removeData("procesoId");
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
      .done(function (resp) {
        if (resp.status === "ok") {
          var filas = (resp.data || []).map(function (r) {
            return (
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
          });
          tablaHistorial.html(
            filas.join("") ||
              '<tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>'
          );
        } else {
          tablaHistorial.html(
            '<tr><td colspan="4" class="text-center text-danger">Error</td></tr>'
          );
        }
      })
      .fail(function () {
        tablaHistorial.html(
          '<tr><td colspan="4" class="text-center text-danger">Error</td></tr>'
        );
      });
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
      .done(function (resp) {
        if (resp.status !== "ok" || !resp.kpis) {
          divResumenPlan.html(
            '<p class="text-danger text-center">Error al cargar resumen.</p>'
          );
          return;
        }

        if (!resp.kpis.length) {
          divResumenPlan.html(
            "<h6 class='text-center'>Plan: <strong>" +
              (planNombre || "N/A") +
              "</strong></h6><p class='text-muted text-center mt-3'>Este plan no tiene cobertura de servicios por cantidad.</p>"
          );
          return;
        }

        var html =
          "<h6 class='text-center'>Plan: <strong>" +
          (planNombre || "N/A") +
          "</strong></h6>";

        resp.kpis.forEach(function (kpi) {
          var porcentaje = kpi.max > 0 ? (kpi.usado / kpi.max) * 100 : 0;
          var color = "bg-success";
          if (porcentaje >= 50) {
            color = "bg-warning";
          }
          if (porcentaje >= 90) {
            color = "bg-danger";
          }

          html +=
            '<div class="progress-group">' +
            kpi.nombre +
            '<span class="float-right"><b>' +
            kpi.usado +
            "</b>/" +
            kpi.max +
            "</span>" +
            '<div class="progress progress-sm">' +
            '<div class="progress-bar ' +
            color +
            '" style="width: ' +
            porcentaje +
            '%"></div>' +
            "</div>" +
            "</div>";
        });

        divResumenPlan.html(html);
      })
      .fail(function () {
        divResumenPlan.html(
          '<p class="text-danger text-center">Error al cargar resumen.</p>'
        );
      });
  }

  function renderItemsPlan(items) {
    var itemsVisibles = (items || []).filter(function (item) {
      var etiqueta = (item.nombre || item.codigo || "").toString();
      return etiqueta.indexOf("%") === -1;
    });

    if (!itemsVisibles.length) {
      contenedorItemsPlan.html(
        '<p class="text-muted">Este plan no tiene ítems configurados para descontar.</p>'
      );
      return;
    }

    var html = itemsVisibles
      .map(function (item, index) {
        var restante =
          typeof item.restante === "number"
            ? item.restante
            : item.max - item.usado;
        var agotado = restante <= 0;
        var inputId = "plan-item-" + index;
        var badge = agotado
          ? '<span class="badge badge-danger ml-2">Agotado</span>'
          : '<span class="badge badge-info ml-2">Disponibles: ' +
            restante +
            "</span>";

        return (
          '<div class="custom-control custom-checkbox mb-2">' +
          '<input type="checkbox" class="custom-control-input plan-item-check" id="' +
          inputId +
          '" value="' +
          item.codigo +
          '" ' +
          (agotado ? "disabled" : "") +
          ">" +
          '<label class="custom-control-label" for="' +
          inputId +
          '"><strong>' +
          (item.nombre || item.codigo) +
          "</strong>" +
          badge +
          "</label>" +
          "</div>"
        );
      })
      .join("");

    contenedorItemsPlan.html(html);
  }

  function cargarItemsPlan(idPaciente) {
    if (!idPaciente) {
      contenedorItemsPlan.html(
        '<p class="text-warning">No se localizaron ítems para este paciente.</p>'
      );
      return;
    }

    contenedorItemsPlan.html(
      '<p class="text-muted">Cargando ítems del plan...</p>'
    );
    api("api/plan_items_paciente.php", { id_paciente: idPaciente }, "GET")
      .done(function (resp) {
        if (resp.status !== "ok") {
          contenedorItemsPlan.html(
            '<p class="text-danger">No fue posible obtener los ítems del plan.</p>'
          );
          return;
        }

        var items = resp.items || [];
        renderItemsPlan(items);
      })
      .fail(function () {
        contenedorItemsPlan.html(
          '<p class="text-danger">Error al cargar los ítems del plan.</p>'
        );
      });
  }

  function obtenerItemsSeleccionados() {
    var seleccionados = [];
    contenedorItemsPlan.find("input.plan-item-check:checked").each(function () {
      seleccionados.push($(this).val());
    });
    return seleccionados;
  }

  function cerrarProcesoActual() {
    var procesoId = btnCerrarProceso.data("procesoId") || selProceso.val();

    if (!procesoId) {
      Swal.fire({
        icon: "warning",
        title: "Proceso no seleccionado",
        text: "Selecciona un proceso antes de intentar cerrarlo.",
      });
      return;
    }

    Swal.fire({
      title: "¿Cerrar proceso?",
      text:
        "Se marcará como cerrado el proceso de atención #" +
        procesoId +
        ". Esta acción no se puede deshacer.",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Sí, cerrar",
      cancelButtonText: "Cancelar",
    }).then(function (result) {
      if (!result.isConfirmed) {
        return;
      }

      btnCerrarProceso.prop("disabled", true);

      api("api/atencion_cerrar_proceso.php", { id: procesoId })
        .done(function (resp) {
          if (resp.status === "ok") {
            Swal.fire({
              icon: "success",
              title: "Proceso cerrado",
              text: "El proceso se cerró correctamente.",
              showConfirmButton: false,
              timer: 1800,
            });
            selProceso.val("");
            limpiarProceso();
            cargarProcesos();
          } else {
            Swal.fire(
              "Error",
              resp.message || "No se pudo cerrar el proceso.",
              "error"
            );
            btnCerrarProceso.prop("disabled", false);
          }
        })
        .fail(function () {
          Swal.fire(
            "Error de Conexión",
            "No se pudo comunicar con el servidor.",
            "error"
          );
          btnCerrarProceso.prop("disabled", false);
        });
    });
  }

  function guardarConsulta() {
    var procesoId = selProceso.val();
    var diagnostico = txtDiagnostico.val().trim();
    var procedimiento = txtProcedimiento.val().trim();
    var indicaciones = txtIndicaciones.val().trim();

    if (!procesoId) {
      Swal.fire({
        icon: "warning",
        title: "No hay proceso seleccionado",
        text: "Debes seleccionar un proceso abierto antes de guardar la consulta.",
      });
      return;
    }

    var proceso = procesos.find(function (p) {
      return String(p.id) === String(procesoId);
    });

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

    var itemsPlanSeleccionados = obtenerItemsSeleccionados();

    api("api/atencion_registrar_consulta.php", {
      id_paciente: proceso.paciente_id,
      proceso_id: procesoId,
      diagnostico: diagnostico,
      procedimiento: procedimiento,
      indicaciones: indicaciones,
      items_plan: itemsPlanSeleccionados,
    })
      .done(function (resp) {
        if (resp.status === "ok") {
          Swal.fire("Éxito", resp.message, "success");
          txtDiagnostico.val("");
          txtProcedimiento.val("");
          txtIndicaciones.val("");
          contenedorItemsPlan
            .find("input.plan-item-check")
            .prop("checked", false);
          cargarHistorial(proceso.paciente_id);
          cargarResumenPlan(proceso.paciente_id, txtPlanE.val());
          cargarItemsPlan(proceso.paciente_id);
        } else {
          Swal.fire(
            "Error",
            resp.message || "Ocurrió un error no especificado",
            "error"
          );
        }
      })
      .fail(function () {
        Swal.fire(
          "Error de Conexión",
          "No se pudo comunicar con el servidor.",
          "error"
        );
      })
      .always(function () {
        btnGuardar.prop("disabled", false);
      });
  }

  selProceso.on("change", function () {
    seleccionarProceso(selProceso.val());
  });

  btnGuardar.on("click", guardarConsulta);
  btnCerrarProceso.on("click", cerrarProcesoActual);

  btnCerrarProceso.prop("disabled", true);
  contenedorItemsPlan.html(MENSAJE_ITEMS_DEFAULT);
  cargarProcesos();
})();
