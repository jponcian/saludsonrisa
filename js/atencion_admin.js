// Gestión de Validación / Apertura de Atención 24/7 (Administración)
(function () {
  const selPaciente = $("#selPaciente");
  // const txtPlan = $("#txtPlan"); // Eliminado, solo se usa el badge
  const txtEstadoPlan = $("#estadoPlanBadge");
  const txtInscripcion = $("#txtInscripcion");
  const txtInicioCobertura = $("#txtInicioCobertura");
  const txtMensualidad = $("#txtMensualidad");
  const txtDiasRestantes = $("#txtDiasRestantes");
  const resumenConsumos = $("#resumenConsumos");
  const tablaConsumos = $("#tablaConsumos tbody");
  const txtObs = $("#txtObs");
  const btnAbrir = $("#btnAbrirProceso");
  const tablaProcesos = $("#tablaProcesos tbody");
  const lblProcesoMsg = $("#lblProcesoMsg");

  let _pacientesCache = [];
  let _procesosCache = [];

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

  function formatDate(dateStr) {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    if (isNaN(date)) return dateStr;
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
  }

  function api(url, data, method = "POST") {
    return $.ajax({ url, method, data, dataType: "json" });
  }

  function cargarPacientes() {
    selPaciente.html('<option value="">Cargando...</option>');
    api("api/atencion_listar_pacientes.php", {}, "GET")
      .done((resp) => {
        if (resp.status === "ok") {
          _pacientesCache = resp.data || [];
          const opts = ['<option value="">-- Seleccione --</option>'];
          _pacientesCache.forEach((p) => {
            opts.push(
              '<option value="' +
                p.id +
                '">' +
                p.documento +
                " - " +
                p.nombre +
                "</option>"
            );
          });
          selPaciente.html(opts.join(""));
          // Inicializar Select2 después de cargar las opciones
          if (selPaciente.data("select2")) {
            selPaciente.select2("destroy");
          }
          selPaciente.select2({
            theme: "bootstrap4",
            placeholder: "-- Seleccione --",
            width: "100%",
          });
          // Select2 inicializado sólo para paciente (urgencia removida en formulario)
        } else {
          selPaciente.html('<option value="">Sin datos</option>');
        }
      })
      .fail(() => selPaciente.html('<option value="">Error</option>'));
  }

  function mostrarPaciente(id) {
    const p = _pacientesCache.find((x) => x.id == id);
    if (!p) {
      limpiarPaciente();
      return;
    }
    // txtPlan eliminado, solo badge
    // Badge llamativo con icono y color
    const planNombreRaw = (p.plan_nombre || "").toLowerCase();
    let badgeHtml = "";
    let badgeClass = "badge-secondary";
    let icon = "fa-puzzle-piece";
    let planColor = "";
    if (planNombreRaw.includes("premium")) {
      badgeClass = "badge-warning";
      icon = "fa-crown";
      planColor = "#FFD700";
    } else if (planNombreRaw.includes("plus")) {
      badgeClass = "badge-info";
      icon = "fa-star";
      planColor = "#C0C0C0";
    } else if (planNombreRaw.includes("salud")) {
      badgeClass = "badge-success";
      icon = "fa-heartbeat";
      planColor = "#CD7F32";
    }
    if (p.plan_nombre) {
      badgeHtml = `<span class=\"badge ${badgeClass}\" style=\"font-size:1.1em;padding:0.6em 1.2em;border-radius:1.5em;box-shadow:0 2px 8px rgba(0,0,0,0.10);background:${planColor};color:#fff;font-weight:600;display:inline-flex;align-items:center;gap:0.5em;\"><i class=\"fas ${icon}\" style=\"font-size:1.2em;color:#fff;\"></i> ${p.plan_nombre}</span>`;
    } else {
      badgeHtml = `<span class=\"badge badge-secondary\" style=\"color:#fff;\">Sin Plan</span>`;
    }
    $("#planBadge").html(badgeHtml);
    // txtPlan eliminado, solo badge
    const estado = (p.estado_plan || "").toLowerCase();
    let estadoTexto = p.estado_plan || "Sin Plan";
    let estadoBadge = "";
    let estadoClass = "badge-secondary";
    let estadoIcon = "fa-question-circle";
    let estadoColor = "#adb5bd";
    if (estado === "activo") {
      estadoClass = "badge-success";
      estadoIcon = "fa-check-circle";
      estadoColor = "#28a745";
      estadoTexto = "Activo";
    } else if (estado === "pendiente") {
      estadoClass = "badge-warning";
      estadoIcon = "fa-hourglass-half";
      estadoColor = "#ffc107";
      estadoTexto = "En espera";
    }
    estadoBadge = `<span class=\"badge ${estadoClass}\" style=\"font-size:1.1em;padding:0.6em 1.2em;border-radius:1.5em;box-shadow:0 2px 8px rgba(0,0,0,0.10);background:${estadoColor};color:#fff;font-weight:600;display:inline-flex;align-items:center;gap:0.5em;\"><i class=\"fas ${estadoIcon}\" style=\"font-size:1.2em;color:#fff;\"></i> ${estadoTexto}</span>`;
    $("#estadoPlanBadge").html(estadoBadge);
    txtInscripcion.val(formatDate(p.fecha_inscripcion) || "");
    txtMensualidad.val(formatNumber(p.monto_mensual) || "");
    txtInicioCobertura.val(formatDate(p.fecha_inicio_cobertura) || "");
    if (estado === "activo") {
      $("#grupoDiasRestantes").hide();
      txtDiasRestantes.val("");
    } else {
      $("#grupoDiasRestantes").show();
      txtDiasRestantes.val(
        p.dias_para_cobertura >= 0 ? p.dias_para_cobertura : ""
      );
    }
    if (p.cobertura_activa === "si") btnAbrir.prop("disabled", false);
    else btnAbrir.prop("disabled", true);
    cargarConsumos(id);

    let iconType = "info";
    if (estado === "activo") {
      iconType = "success";
    } else if (estado === "pendiente") {
      iconType = "warning";
    }

    Swal.fire({
      position: "center",
      icon: iconType,
      title: `Estado del Plan: ${estadoTexto}`,
      showConfirmButton: false,
      timer: 1500,
    });
  }

  function limpiarPaciente() {
    // txtPlan eliminado, solo badge
    txtEstadoPlan
      .text("-")
      .removeClass("badge-success badge-warning")
      .addClass("badge-secondary");
    txtInscripcion.val("");
    txtMensualidad.val("");
    txtInicioCobertura.val("");
    txtDiasRestantes.val("");
    $("#grupoDiasRestantes").show();
    resumenConsumos.empty();
    tablaConsumos.empty();
    btnAbrir.prop("disabled", true);
  }

  function cargarConsumos(idPaciente) {
    resumenConsumos.html(
      '<div class="col-12 text-muted">Cargando consumos...</div>'
    );
    tablaConsumos.html(
      '<tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>'
    );
    api("api/atencion_resumen_consumos.php", { id_paciente: idPaciente }, "GET")
      .done((resp) => {
        if (resp.status === "ok") {
          // KPIs
          const kpis = resp.kpis || [];
          const iconMap = {
            "Atenciones Primarias": "fa-user-md",
            Laboratorios: "fa-vials",
            "Consultas por Emergencia": "fa-ambulance",
            "Observaciones por emergencia": "fa-notes-medical",
            "Cirugías menores": "fa-syringe",
            "Consultas con Especialistas": "fa-user-nurse",
            Inmovilizaciones: "fa-crutch",
            Ecografías: "fa-wave-square",
            "Rayos X": "fa-x-ray",
            "Consulta Odontológica": "fa-tooth",
            "Limpieza Profunda": "fa-tooth",
          };
          const htmlKPIs = kpis
            .map((k) => {
              const pct =
                k.max > 0
                  ? Math.min(100, Math.round((k.usado / k.max) * 100))
                  : 0;
              const icon = iconMap[k.nombre] || "fa-puzzle-piece";
              return (
                '<div class="col-sm-6 col-lg-3 mb-3">\n' +
                ' <div class="small-box ' +
                (pct >= 90
                  ? "bg-danger"
                  : pct >= 70
                  ? "bg-warning"
                  : "bg-success") +
                '">\n' +
                '   <div class="inner">\n' +
                '     <h5 style="font-weight:600">' +
                k.nombre +
                "</h5>\n" +
                "     <p>" +
                k.usado +
                " / " +
                k.max +
                " (" +
                pct +
                "%)</p>\n" +
                "   </div>\n" +
                '   <div class="icon"><i class="fas ' +
                icon +
                '"></i></div>\n' +
                " </div>\n" +
                "</div>"
              );
            })
            .join("");
          resumenConsumos.html(
            htmlKPIs || '<div class="col-12 text-muted">Sin KPIs</div>'
          );
          // tabla consumos
          const rows = (resp.consumos || []).map(
            (c) =>
              "<tr>" +
              "<td>" +
              formatDate(c.fecha) +
              "</td><td>" +
              c.tipo +
              "</td><td>" +
              (c.detalle || "") +
              "</td><td>" +
              c.cantidad +
              "</td><td>" +
              (c.especialista || "") +
              "</td></tr>"
          );
          tablaConsumos.html(
            rows.join("") ||
              '<tr><td colspan="5" class="text-center text-muted">Sin consumos</td></tr>'
          );
        } else {
          resumenConsumos.html(
            '<div class="col-12 text-danger">Error al cargar</div>'
          );
          tablaConsumos.html(
            '<tr><td colspan="5" class="text-center text-danger">Error</td></tr>'
          );
        }
      })
      .fail(() => {
        resumenConsumos.html(
          '<div class="col-12 text-danger">Error de conexión</div>'
        );
        tablaConsumos.html(
          '<tr><td colspan="5" class="text-center text-danger">Error</td></tr>'
        );
      });
  }

  function formatDateTime(dateTimeStr) {
    if (!dateTimeStr) return "";
    const date = new Date(dateTimeStr);
    if (isNaN(date)) return dateTimeStr;

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();

    let hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, "0");
    const ampm = hours >= 12 ? "pm" : "am";
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    const strTime = String(hours).padStart(2, "0") + ":" + minutes + " " + ampm;

    return `${day}-${month}-${year} ${strTime}`;
  }

  function cargarProcesos() {
    tablaProcesos.html(
      '<tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>'
    );
    api("api/atencion_listar_procesos.php", {}, "GET")
      .done((resp) => {
        if (resp.status === "ok") {
          _procesosCache = resp.data || [];
          const rows = _procesosCache.map(
            (p) =>
              "<tr>" +
              "<td>" +
              p.id +
              "</td>" +
              "<td>" +
              p.paciente +
              "</td>" +
              "<td>" +
              (p.observaciones ? p.observaciones : "-") +
              "</td>" +
              "<td>" +
              p.estado +
              "</td>" +
              "<td>" +
              formatDateTime(p.creado) +
              "</td>" +
              '<td class="text-center">' +
              (p.estado === "abierto"
                ? '<button class="btn btn-sm btn-warning btn-cerrar" data-id="' +
                  p.id +
                  '"><i class="fas fa-door-closed mr-1"></i>Cerrar proceso</button>'
                : "-") +
              "</td>" +
              "</tr>"
          );
          tablaProcesos.html(
            rows.join("") ||
              '<tr><td colspan="5" class="text-center text-muted">Sin procesos</td></tr>'
          );
        } else {
          tablaProcesos.html(
            '<tr><td colspan="5" class="text-center text-danger">Error</td></tr>'
          );
        }
      })
      .fail(() =>
        tablaProcesos.html(
          '<tr><td colspan="5" class="text-center text-danger">Error</td></tr>'
        )
      );
  }

  function abrirProceso() {
    const id_paciente = selPaciente.val();
    if (!id_paciente) return;
    const p = _pacientesCache.find((x) => x.id == id_paciente);
    const obs = (txtObs.val() || "").trim();
    btnAbrir.prop("disabled", true);
    api("api/atencion_crear_proceso.php", {
      id_paciente,
      obs,
      suscripcion_id: p ? p.suscripcion_id : null,
      plan_id: p ? p.plan_id : null,
    })
      .done((resp) => {
        if (resp.status === "ok") {
          Swal.fire({
            icon: "success",
            title: "Proceso Abierto",
            text: "El proceso de atención ha sido abierto con el ID " + resp.id,
            timer: 2000,
            showConfirmButton: false,
          });
          txtObs.val("");
          cargarProcesos();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error al Abrir Proceso",
            text: resp.message || "Ocurrió un error inesperado.",
          });
        }
      })
      .fail(() => {
        Swal.fire({
          icon: "error",
          title: "Error de Conexión",
          text: "No se pudo comunicar con el servidor.",
        });
      })
      .always(() => btnAbrir.prop("disabled", false));
  }

  // Eventos
  selPaciente.on("change", () => mostrarPaciente(selPaciente.val()));
  btnAbrir.on("click", abrirProceso);
  tablaProcesos.on("click", ".btn-cerrar", function () {
    const id = $(this).data("id");
    Swal.fire({
      title: "¿Cerrar proceso?",
      text:
        "Se marcará como cerrado el proceso de atención #" +
        id +
        ". Esta acción no se puede deshacer.",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Sí, cerrar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        api("api/atencion_cerrar_proceso.php", { id })
          .done((resp) => {
            if (resp.status === "ok") {
              Swal.fire({
                icon: "success",
                title: "Proceso cerrado",
                text: "El proceso se cerró correctamente.",
                showConfirmButton: false,
                timer: 1800,
              });
              cargarProcesos();
            } else {
              Swal.fire(
                "Error",
                resp.message || "No se pudo cerrar el proceso.",
                "error"
              );
            }
          })
          .fail(() => {
            Swal.fire(
              "Error de Conexión",
              "No se pudo comunicar con el servidor.",
              "error"
            );
          });
      }
    });
  });

  // Init
  cargarPacientes();
  cargarProcesos();
})();
