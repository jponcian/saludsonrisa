// Gestión de Validación / Apertura de Atención 24/7 (Administración)
(function () {
  const selPaciente = $("#selPaciente");
  const txtPlan = $("#txtPlan");
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
    txtPlan.val(p.plan_nombre || "");
    const estado = (p.estado_plan || "").toLowerCase();
    let estadoTexto = p.estado_plan || "Sin Plan";
    if (estado === "activo") {
      txtEstadoPlan
        .text("Activo")
        .removeClass("badge-secondary badge-warning")
        .addClass("badge-success");
      estadoTexto = "Activo";
    } else if (estado === "pendiente") {
      txtEstadoPlan
        .text("En espera")
        .removeClass("badge-secondary badge-success")
        .addClass("badge-warning");
      estadoTexto = "En espera";
    } else {
      txtEstadoPlan
        .text(estadoTexto)
        .removeClass("badge-success badge-warning")
        .addClass("badge-secondary");
    }
    txtInscripcion.val(formatDate(p.fecha_inscripcion) || "");
    txtMensualidad.val(formatNumber(p.monto_mensual) || "");
    txtInicioCobertura.val(formatDate(p.fecha_inicio_cobertura) || "");
    txtDiasRestantes.val(
      p.dias_para_cobertura >= 0 ? p.dias_para_cobertura : ""
    );
    if (p.cobertura_activa === "si") btnAbrir.prop("disabled", false);
    else btnAbrir.prop("disabled", true);
    cargarConsumos(id);

    let iconType = 'info';
    if (estado === 'activo') {
        iconType = 'success';
    } else if (estado === 'pendiente') {
        iconType = 'warning';
    }

    Swal.fire({
      position: 'center',
      icon: iconType,
      title: `Estado del Plan: ${estadoTexto}`,
      showConfirmButton: false,
      timer: 1500
    });
  }

  function limpiarPaciente() {
    txtPlan.val("");
    txtEstadoPlan
      .text("-")
      .removeClass("badge-success badge-warning")
      .addClass("badge-secondary");
    txtInscripcion.val("");
    txtMensualidad.val("");
    txtInicioCobertura.val("");
    txtDiasRestantes.val("");
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
          const htmlKPIs = kpis
            .map((k) => {
              const pct =
                k.max > 0
                  ? Math.min(100, Math.round((k.usado / k.max) * 100))
                  : 0;
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
                '   <div class="icon"><i class="fas fa-puzzle-piece"></i></div>\n' +
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
    const ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    const strTime = String(hours).padStart(2, '0') + ':' + minutes + ' ' + ampm;

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
              "<td>" + p.id + "</td>" +
              "<td>" + p.paciente + "</td>" +
              "<td>" + p.estado + "</td>" +
              "<td>" + formatDateTime(p.creado) + "</td>" +
              '<td class="text-center">' +
              (p.estado === "abierto"
                ? '<button class="btn btn-xs btn-outline-danger btn-cerrar" data-id="' + p.id + '">' +
                  '<i class="fas fa-trash-alt"></i>' +
                  '</button>'
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
            icon: 'success',
            title: 'Proceso Abierto',
            text: 'El proceso de atención ha sido abierto con el ID ' + resp.id,
            timer: 2000,
            showConfirmButton: false
          });
          txtObs.val("");
          cargarProcesos();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error al Abrir Proceso',
            text: resp.message || 'Ocurrió un error inesperado.'
          });
        }
      })
      .fail(() => {
        Swal.fire({
          icon: 'error',
          title: 'Error de Conexión',
          text: 'No se pudo comunicar con el servidor.'
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
      title: '¿Estás seguro?',
      text: "Esta acción cerrará el proceso de atención #" + id,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, ¡Cerrar!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        api("api/atencion_cerrar_proceso.php", { id })
          .done((resp) => {
            if (resp.status === "ok") {
              Swal.fire(
                'Cerrado',
                'El proceso ha sido cerrado con éxito.',
                'success'
              );
              cargarProcesos();
            } else {
              Swal.fire(
                'Error',
                resp.message || 'No se pudo cerrar el proceso.',
                'error'
              );
            }
          })
          .fail(() => {
            Swal.fire(
              'Error de Conexión',
              'No se pudo comunicar con el servidor.',
              'error'
            );
          });
      }
    });
  });

  // Init
  cargarPacientes();
  cargarProcesos();
})();
