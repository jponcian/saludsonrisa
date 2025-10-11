// Gestión de Facturación / Suscripciones
(function () {
  const selPaciente = $("#facSelPaciente");
  const planActual = $("#facPlanActual");
  const coberturaEstado = $("#facCoberturaEstado");
  const coberturaInicio = $("#facCoberturaInicio");
  const selPlan = $("#facSelPlan");
  const diasEspera = $("#facDiasEspera");
  const obsPlan = $("#facObsPlan");
  const btnAsignarPlan = $("#facBtnAsignarPlan");
  const msgPlan = $("#facMsgPlan");

  const tipoPago = $("#facTipoPago");
  const montoPago = $("#facMontoPago");
  const fechaPago = $("#facFechaPago");
  const refPago = $("#facRefPago");
  const btnRegistrarPago = $("#facBtnRegistrarPago");
  const msgPago = $("#facMsgPago");
  const tablaPagos = $("#facTablaPagos tbody");
  const inscripcionStatus = $("#facInscripcionStatus");

  let _pacientes = [];
  let _planes = [];
  let _pagos = [];
  let _suscripcion = null;
  let _pagosCargados = false;
  let _suscripcionLoaded = false;

  function formatNumber(num) {
    if (typeof num !== "number") {
      num = parseFloat(num);
    }
    if (isNaN(num)) return "0,00";
    const str = num.toFixed(2).replace(".", ",");
    const parts = str.split(",");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    return parts.join(",");
  }

  function toInputDecimal(num) {
    if (typeof num !== "number") num = parseFloat(num);
    if (isNaN(num)) return "";
    return num.toFixed(2);
  }

  // Remover evento on("input") para evitar conflicto con inputmask
  // montoPago.on("input", function () {
  //   const raw = this.value.replace(/\./g, "").replace(",", ".");
  //   if (raw === "") return;
  //   const n = parseFloat(raw);
  //   if (!isNaN(n)) this.value = toInputDecimal(n);
  // });

  // ---- Helpers de mensajería (SweetAlert + fallback) ----
  function hasSwal() {
    return typeof Swal !== "undefined";
  }
  function showLoadingPlan(txt) {
    if (hasSwal())
      Swal.fire({
        title: txt,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });
    else msgPlan.text(txt).removeClass("text-danger text-success");
  }
  function showLoadingPago(txt) {
    if (hasSwal())
      Swal.fire({
        title: txt,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });
    else msgPago.text(txt).removeClass("text-danger text-success");
  }
  function planOk(txt) {
    if (hasSwal()) Swal.fire(txt, "", "success");
    else msgPlan.text(txt).removeClass("text-danger").addClass("text-success");
  }
  function planErr(txt) {
    if (hasSwal()) Swal.fire(txt, "", "error");
    else msgPlan.text(txt).removeClass("text-success").addClass("text-danger");
  }
  function pagoOk(txt) {
    if (hasSwal()) Swal.fire(txt, "", "success");
    else msgPago.text(txt).removeClass("text-danger").addClass("text-success");
  }
  function pagoErr(txt) {
    if (hasSwal()) Swal.fire(txt, "", "error");
    else msgPago.text(txt).removeClass("text-success").addClass("text-danger");
  }

  function autocompletarPagoInicial() {
    if (!_suscripcion) return;
    const planId = _suscripcion.plan_id;
    const plan = _planes.find((pl) => pl.id == planId);
    if (!plan) return;
    const cuota = parseFloat(plan.cuota_afiliacion || 0);
    const mensual = parseFloat(plan.monto_mensual || 0);
    let totalIns = 0;
    if (_pagosCargados) {
      totalIns = _pagos
        .filter(
          (p) =>
            (p.tipo === "inscripcion" || p.tipo === "inscripcion_diferencia") &&
            p.plan_id == planId
        )
        .reduce((a, b) => a + parseFloat(b.monto || 0), 0);
    }
    if (cuota > 0 && totalIns + 0.009 < cuota) {
      const restante = Math.max(0, cuota - totalIns);
      tipoPago.val("inscripcion");
      tipoPago.find('option[value="inscripcion"]').prop("disabled", false);
      montoPago.val(formatNumber(restante));
    } else {
      tipoPago.val("mensualidad");
      montoPago.val(mensual > 0 ? formatNumber(mensual) : "");
      if (cuota <= 0 || totalIns + 0.009 >= cuota) {
        tipoPago.find('option[value="inscripcion"]').prop("disabled", true);
      }
    }
  }

  function api(url, data = {}, method = "POST") {
    return $.ajax({ url, data, method, dataType: "json" });
  }

  function cargarPlanes() {
    selPlan.html("<option>Cargando...</option>");
    api("api/facturacion_listar_planes.php", {}, "GET")
      .done((r) => {
        if (r.status === "ok") {
          _planes = r.data || [];
          const opts = ['<option value="">-- Seleccione --</option>'];
          _planes.forEach((pl) => {
            opts.push(
              '<option data-monto="' +
                (pl.monto_mensual || "") +
                '" data-afiliacion="' +
                (pl.cuota_afiliacion || "") +
                '" value="' +
                pl.id +
                '">' +
                pl.nombre +
                " (Mensual: " +
                formatNumber(pl.monto_mensual || 0) +
                " / Afiliación: " +
                formatNumber(pl.cuota_afiliacion || 0) +
                ")" +
                "</option>"
            );
          });
          selPlan.html(opts.join(""));
        } else selPlan.html("<option>Error</option>");
      })
      .fail(() => selPlan.html("<option>Error</option>"));
  }

  function cargarPacientes(selectedId = null) {
    selPaciente.html("<option>Cargando...</option>");
    api("api/atencion_listar_pacientes.php", {}, "GET")
      .done((r) => {
        if (r.status === "ok") {
          _pacientes = r.data || [];
          const opts = ['<option value="">-- Seleccione --</option>'];
          _pacientes.forEach((p) =>
            opts.push(
              '<option value="' +
                p.id +
                '">' +
                p.documento +
                " - " +
                p.nombre +
                "</option>"
            )
          );
          selPaciente.html(opts.join(""));
          if (selectedId) {
            selPaciente.val(selectedId);
          }
        } else selPaciente.html("<option>Error</option>");
      })
      .fail(() => selPaciente.html("<option>Error</option>"));
  }

  function mostrarPaciente(id) {
    const p = _pacientes.find((x) => x.id == id);
    if (!p) {
      limpiarPaciente();
      return;
    }
    tipoPago.prop("disabled", true);
    montoPago.prop("disabled", true);
    fechaPago.prop("disabled", true);
    refPago.prop("disabled", true);
    btnRegistrarPago.prop("disabled", true);
    planActual.text(p.plan_nombre ? p.plan_nombre : "Sin plan");
    coberturaEstado
      .text("-")
      .removeClass("badge-success badge-warning")
      .addClass("badge-secondary");
    coberturaInicio.val("");
    _suscripcionLoaded = false;
    _pagosCargados = false;
    cargarSuscripcion(id);
    cargarPagos(id);
  }

  function limpiarPaciente() {
    planActual.text("-");
    coberturaEstado
      .text("Sin Plan")
      .removeClass("badge-success badge-warning")
      .addClass("badge-secondary");
    coberturaInicio.val("");
    tablaPagos.empty();
    _suscripcion = null;
    _suscripcionLoaded = false;
    _pagosCargados = false;
    msgPlan.text("");
    msgPago.text("");
    inscripcionStatus.addClass("d-none").html("");
    toggleCardsForPlan(false);
  }

  function toggleCardsForPlan(tienePlan) {
    const cardPlan = $("#facCardPlan");
    const cardPagos = $("#facCardPagos");
    if (!cardPlan.length || !cardPagos.length) return;
    function ensureState($card, shouldCollapse) {
      const collapsed = $card.hasClass("collapsed-card");
      if (shouldCollapse && !collapsed) {
        $card
          .find('> .card-header [data-card-widget="collapse"]')
          .trigger("click");
      } else if (!shouldCollapse && collapsed) {
        $card
          .find('> .card-header [data-card-widget="collapse"]')
          .trigger("click");
      }
    }
    if (tienePlan) {
      ensureState(cardPlan, true);
      ensureState(cardPagos, false);
    } else {
      ensureState(cardPlan, false);
      ensureState(cardPagos, true);
    }
  }

  function cargarSuscripcion(idPaciente) {
    api(
      "api/facturacion_suscripcion_actual.php",
      { id_paciente: idPaciente },
      "GET"
    ).done((r) => {
      if (r.status === "ok") {
        _suscripcion = r.data;
        if (_suscripcion) {
          planActual.text(_suscripcion.plan_nombre || "Sin plan");
          const estado = (_suscripcion.estado || "").toLowerCase();
          if (estado === "activo") {
            coberturaEstado
              .text("Activa")
              .removeClass("badge-secondary badge-warning")
              .addClass("badge-success");
          } else if (estado === "pendiente") {
            coberturaEstado
              .text("En espera")
              .removeClass("badge-secondary badge-success")
              .addClass("badge-warning");
          } else {
            coberturaEstado
              .text(estado || "Sin Plan")
              .removeClass("badge-success badge-warning")
              .addClass("badge-secondary");
          }
          let fechaCob = _suscripcion.fecha_inicio_cobertura || "";
          if (/^\d{4}-\d{2}-\d{2}$/.test(fechaCob)) {
            const [y, m, d] = fechaCob.split("-");
            fechaCob = `${d}-${m}-${y}`;
          } else if (fechaCob) {
            const djs = new Date(fechaCob);
            if (!isNaN(djs)) {
              let day = ("0" + djs.getDate()).slice(-2);
              let month = ("0" + (djs.getMonth() + 1)).slice(-2);
              let year = djs.getFullYear();
              fechaCob = `${day}-${month}-${year}`;
            }
          }
          coberturaInicio.val(fechaCob);
          tipoPago.prop("disabled", false);
          montoPago.prop("disabled", false);
          fechaPago.prop("disabled", false);
          refPago.prop("disabled", false);
          btnRegistrarPago.prop("disabled", false);
          // Prefill inmediato (incluso antes de pagos) y luego ajuste
          autocompletarPagoInicial();
          toggleCardsForPlan(true);
        } else {
          planActual.text("Sin Plan");
          coberturaEstado
            .text("Sin Plan")
            .removeClass("badge-success badge-warning")
            .addClass("badge-secondary");
          coberturaInicio.val("");
          toggleCardsForPlan(false);
        }
        _suscripcionLoaded = true;
        actualizarEstadoInscripcion();
      }
    });
  }

  function actualizarEstadoInscripcion() {
    if (!_suscripcionLoaded || !_pagosCargados) return;

    if (_suscripcion && _suscripcion.plan_id) {
      const planId = _suscripcion.plan_id;
      const plan = _planes.find((p) => p.id == planId);
      if (!plan || parseFloat(plan.cuota_afiliacion || 0) <= 0) {
        inscripcionStatus.addClass("d-none").html("");
        return;
      }

      // Si ya existe al menos una mensualidad registrada, ocultar completamente el estado de inscripción
      const tieneMensualidad = _pagos.some((p) => p.tipo === "mensualidad");
      if (tieneMensualidad) {
        inscripcionStatus.addClass("d-none").html("");
        return;
      }

      const tieneInscripcion = inscripcionYaPagadaParaPlan(planId);

      if (tieneInscripcion) {
        inscripcionStatus
          .html(
            '<div class="alert alert-success"><h5><i class="icon fas fa-check"></i> ¡Inscripción Pagada!</h5></div>'
          )
          .removeClass("d-none");
      } else {
        inscripcionStatus
          .html(
            '<div class="alert alert-warning"><h5><i class="icon fas fa-exclamation-triangle"></i> Inscripción Pendiente</h5></div>'
          )
          .removeClass("d-none");
      }
    } else {
      inscripcionStatus.addClass("d-none").html("");
    }
  }

  function cargarPagos(idPaciente) {
    tablaPagos.html(
      '<tr><td colspan="7" class="text-center text-muted">Cargando...</td></tr>'
    );
    api("api/facturacion_listar_pagos.php", { id_paciente: idPaciente }, "GET")
      .done((r) => {
        if (r.status === "ok") {
          _pagos = r.data || [];
          _pagosCargados = true;
          actualizarEstadoInscripcion();

          const rows = _pagos.map((p, index) => {
            let fecha = p.fecha;
            if (/^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
              let [y, m, d] = fecha.split("-");
              fecha = `${d}-${m}-${y}`;
            } else {
              let djs = new Date(fecha);
              if (!isNaN(djs)) {
                let day = ("0" + djs.getDate()).slice(-2);
                let month = ("0" + (djs.getMonth() + 1)).slice(-2);
                let year = djs.getFullYear();
                fecha = `${day}-${month}-${year}`;
              }
            }
            let desde = p.periododesde || "";
            let hasta = p.periodohasta || "";
            if (/^\d{4}-\d{2}-\d{2}$/.test(desde)) {
              let [y, m, d] = desde.split("-");
              desde = `${d}-${m}-${y}`;
            }
            if (/^\d{4}-\d{2}-\d{2}$/.test(hasta)) {
              let [y, m, d] = hasta.split("-");
              hasta = `${d}-${m}-${y}`;
            }
            let tipoLabel = p.tipo;
            if (p.tipo === "inscripcion") {
              tipoLabel = '<span class="badge badge-info">Inscripción</span>';
            } else if (p.tipo === "inscripcion_diferencia") {
              tipoLabel =
                '<span class="badge badge-secondary">Dif. Inscripción</span>';
            } else if (p.tipo === "mensualidad") {
              tipoLabel =
                '<span class="badge badge-primary">Mensualidad</span>';
            }
            // Solo mostrar botón eliminar en el último pago (primero en lista ordenada)
            const btnEliminar =
              index === 0
                ? `<button class="btn btn-sm btn-danger facBtnEliminarPago" data-id="${p.id}"><i class="fas fa-trash"></i></button>`
                : "";
            return `<tr><td>${fecha}</td><td>${desde}</td><td>${hasta}</td><td>${tipoLabel}</td><td>${formatNumber(
              p.monto
            )}</td><td>${p.modalidad_pago || ""}</td><td>${
              p.referencia || ""
            }</td><td>${btnEliminar}</td></tr>`;
          });
          tablaPagos.html(
            rows.join("") ||
              '<tr><td colspan="7" class="text-center text-muted">Sin pagos</td></tr>'
          );
          tablaPagos.find(".facBtnEliminarPago").on("click", function () {
            const pagoId = $(this).data("id");
            if (!pagoId) return;
            if (window.Swal) {
              Swal.fire({
                title: "¿Eliminar pago?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
              }).then((result) => {
                if (result.isConfirmed) {
                  // Validar que sea el último pago
                  if (_pagos.length > 0 && pagoId != _pagos[0].id) {
                    pagoErr("Solo se puede eliminar el último pago registrado");
                    return;
                  }
                  eliminarPago(pagoId, idPaciente);
                }
              });
            } else {
              // Validar que sea el último pago
              if (_pagos.length > 0 && pagoId != _pagos[0].id) {
                pagoErr("Solo se puede eliminar el último pago registrado");
                return;
              }
              if (confirm("¿Eliminar este pago?"))
                eliminarPago(pagoId, idPaciente);
            }
          });
          function eliminarPago(pagoId, idPaciente) {
            if (!pagoId) return;
            showLoadingPago("Eliminando pago...");
            api("api/facturacion_eliminar_pago.php", { id: pagoId }, "POST")
              .done(function (r) {
                if (r.status === "ok") {
                  if (hasSwal()) {
                    Swal.close();
                    // Toast informativo inferior derecho
                    const Toast = Swal.mixin({
                      toast: true,
                      position: "bottom-end",
                      showConfirmButton: false,
                      timer: 3000,
                      timerProgressBar: true,
                      icon: "info",
                    });
                    Toast.fire({ title: "Pago eliminado correctamente" });
                  } else {
                    // Fallback simple
                    console.log("Pago eliminado correctamente");
                  }
                  cargarPagos(idPaciente);
                } else {
                  if (hasSwal()) Swal.close();
                  pagoErr(r.message || "Error al eliminar");
                }
              })
              .fail(function () {
                if (hasSwal()) Swal.close();
                pagoErr("Error de conexión");
              });
          }
          autocompletarPagoInicial();
        } else
          tablaPagos.html(
            '<tr><td colspan="7" class="text-center text-danger">Error</td></tr>'
          );
      })
      .fail(() =>
        tablaPagos.html(
          '<tr><td colspan="7" class="text-center text-danger">Error</td></tr>'
        )
      );
  }

  selPlan.on("change", function () {
    const opt = $(this).find("option:selected");
    if (tipoPago.val() === "inscripcion") {
      montoPago.val(formatNumber(parseFloat(opt.data("afiliacion") || 0)));
    } else if (tipoPago.val() === "mensualidad") {
      montoPago.val(formatNumber(parseFloat(opt.data("monto") || 0)));
    }
    const planId = opt.val();
    if (planId) {
      const restante = restanteInscripcion(planId);
      if (inscripcionYaPagadaParaPlan(planId)) {
        tipoPago.find('option[value="inscripcion"]').prop("disabled", true);
        if (tipoPago.val() === "inscripcion") {
          tipoPago.val("mensualidad").trigger("change");
        }
      } else {
        tipoPago.find('option[value="inscripcion"]').prop("disabled", false);
        if (tipoPago.val() === "inscripcion") {
          montoPago.val(formatNumber(restante));
        }
      }
    }
  });

  tipoPago.on("change", function () {
    const opt = selPlan.find("option:selected");
    if (!opt.length) return;
    if (this.value === "inscripcion") {
      montoPago.val(formatNumber(parseFloat(opt.data("afiliacion") || 0)));
    } else if (this.value === "mensualidad") {
      montoPago.val(formatNumber(parseFloat(opt.data("monto") || 0)));
    }
  });

  function inscripcionYaPagadaParaPlan(planId) {
    const plan = _planes.find((pl) => pl.id == planId);
    if (!plan) return false;
    const total = _pagos
      .filter(
        (p) => p.tipo === "inscripcion" || p.tipo === "inscripcion_diferencia"
      )
      .reduce((a, b) => a + parseFloat(b.monto || 0), 0);
    const cuota = parseFloat(plan.cuota_afiliacion || 0);
    if (cuota <= 0) return true;
    return total + 0.009 >= cuota;
  }

  function restanteInscripcion(planId) {
    const plan = _planes.find((pl) => pl.id == planId);
    if (!plan) return 0;
    const cuota = parseFloat(plan.cuota_afiliacion || 0);
    if (cuota <= 0) return 0;
    const total = _pagos
      .filter(
        (p) =>
          (p.tipo === "inscripcion" || p.tipo === "inscripcion_diferencia") &&
          p.plan_id == planId
      )
      .reduce((a, b) => a + parseFloat(b.monto || 0), 0);
    return Math.max(0, cuota - total);
  }

  const _origCargarPagos = cargarPagos;
  cargarPagos = function (idPaciente) {
    _origCargarPagos(idPaciente);
    setTimeout(() => {
      const currentPlanOpt = selPlan.find("option:selected");
      const planId = currentPlanOpt.val();
      if (planId) {
        if (inscripcionYaPagadaParaPlan(planId)) {
          tipoPago.find('option[value="inscripcion"]').prop("disabled", true);
          if (tipoPago.val() === "inscripcion") {
            tipoPago.val("mensualidad").trigger("change");
          }
        } else {
          tipoPago.find('option[value="inscripcion"]').prop("disabled", false);
        }
      }
    }, 300);
  };

  function asignarPlan() {
    const idPaciente = selPaciente.val();
    const planId = selPlan.val();
    if (!idPaciente || !planId) {
      planErr("Seleccione paciente y plan");
      return;
    }
    if (_suscripcion && _suscripcion.plan_id == planId) {
      planErr("El paciente ya tiene este plan asignado");
      return;
    }
    btnAsignarPlan.prop("disabled", true);
    showLoadingPlan("Asignando plan...");
    api(
      "api/facturacion_asignar_plan.php",
      { id_paciente: idPaciente, plan_id: planId, obs: obsPlan.val() },
      "POST"
    )
      .done((r) => {
        if (r.status === "ok") {
          if (hasSwal()) Swal.close();
          planOk("Plan asignado correctamente");
          cargarPacientes(idPaciente);
          mostrarPaciente(idPaciente);
        } else {
          if (hasSwal()) Swal.close();
          planErr(r.message || "Error");
        }
      })
      .fail(() => {
        if (hasSwal()) Swal.close();
        planErr("Error de conexión");
      })
      .always(() => btnAsignarPlan.prop("disabled", false));
  }

  function registrarPago() {
    const idPaciente = selPaciente.val();
    const tipo = tipoPago.val();
    const modalidad = $("#facModalidadPago").val();
    const monto = montoPago.inputmask("unmaskedvalue");
    const fecha = fechaPago.val();

    if (!idPaciente) {
      pagoErr("Seleccione un paciente");
      return;
    }
    if (!monto || !fecha) {
      pagoErr("Monto y fecha requeridos");
      return;
    }
    if (!refPago.val().trim()) {
      pagoErr("Referencia requerida");
      return;
    }
    if (!modalidad) {
      pagoErr("Seleccione modalidad de pago");
      return;
    }
    btnRegistrarPago.prop("disabled", true);
    showLoadingPago("Registrando pago...");
    api(
      "api/facturacion_registrar_pago.php",
      {
        id_paciente: idPaciente,
        tipo_pago: tipo,
        modalidad_pago: modalidad,
        monto: monto,
        fecha: fecha,
        referencia: refPago.val(),
      },
      "POST"
    )
      .done((r) => {
        if (r.status === "ok") {
          if (hasSwal()) {
            Swal.close();
            // Toast informativo inferior derecho
            const Toast = Swal.mixin({
              toast: true,
              position: "bottom-end",
              showConfirmButton: false,
              timer: 3000,
              timerProgressBar: true,
              icon: "success",
            });
            Toast.fire({ title: "Pago registrado correctamente" });
          } else {
            // Fallback simple
            console.log("Pago registrado correctamente");
          }
          cargarPagos(idPaciente);
        } else {
          if (hasSwal()) Swal.close();
          pagoErr(r.message || "Error");
        }
      })
      .fail(() => {
        if (hasSwal()) Swal.close();
        pagoErr("Error de conexión");
      })
      .always(() => btnRegistrarPago.prop("disabled", false));
  }

  selPaciente.on("change", () => mostrarPaciente(selPaciente.val()));
  btnAsignarPlan.on("click", function () {
    const idPaciente = selPaciente.val();
    const planId = selPlan.val();
    if (!idPaciente || !planId) {
      planErr("Seleccione paciente y plan");
      return;
    }
    if (window.Swal) {
      Swal.fire({
        title: "¿Confirmar asignación de plan?",
        text: "Esta acción actualizará el plan del paciente seleccionado.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, asignar",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          asignarPlan();
        }
      });
    } else {
      if (confirm("¿Confirmar asignación de plan?")) {
        asignarPlan();
      }
    }
  });
  btnRegistrarPago.on("click", registrarPago);

  cargarPacientes();
  cargarPlanes();

  selPaciente.select2({
    theme: "bootstrap4",
  });

  montoPago.inputmask({
    alias: "numeric",
    groupSeparator: ".",
    radixPoint: ",",
    autoGroup: true,
    digits: 2,
    digitsOptional: false,
    placeholder: "0",
    rightAlign: true,
  });
})();
