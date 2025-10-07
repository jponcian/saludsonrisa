(function () {
  const appContainer = document.getElementById("historiaClinicaApp");
  if (!appContainer) {
    return;
  }

  const pacienteId = parseInt(appContainer.dataset.pacienteId, 10);
  const seccionesContainer = document.getElementById("historia-secciones");
  const loadingIndicator = document.getElementById("historia-loading");
  const emptyMessage = document.getElementById("historia-empty");
  const badgeEstado = document.getElementById("historia-estado");
  const ultimaActualizacion = document.getElementById("historia-ultima");
  const completadoPor = document.getElementById("historia-completado-por");
  const btnGuardar = document.getElementById("btn-guardar-historia");
  const btnCompletar = document.getElementById("btn-completar-historia");

  let definicion = null;

  const ESTADOS_BADGE = {
    pendiente: "badge badge-warning badge-estado",
    en_progreso: "badge badge-info badge-estado",
    completado: "badge badge-success badge-estado",
  };

  function setEstado(estado) {
    const clase = ESTADOS_BADGE[estado] || "badge badge-secondary badge-estado";
    badgeEstado.className = clase;
    const textos = {
      pendiente: "Pendiente",
      en_progreso: "En progreso",
      completado: "Completada",
    };
    badgeEstado.textContent = textos[estado] || "Sin estado";
  }

  function formatoFechaLarga(fechaISO) {
    if (!fechaISO) {
      return "—";
    }
    const date = new Date(fechaISO.replace(" ", "T"));
    if (Number.isNaN(date.getTime())) {
      return "—";
    }
    return date.toLocaleString("es-VE", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  function crearElementoPregunta(pregunta) {
    const wrapper = document.createElement("div");
    wrapper.className = "historia-question";
    wrapper.dataset.preguntaId = String(pregunta.id);

    const labelId = `preg-${pregunta.id}`;

    const label = document.createElement("label");
    label.setAttribute("for", labelId);
    label.textContent = pregunta.pregunta + (pregunta.requerida ? " *" : "");
    wrapper.appendChild(label);

    let input;
    const valor = pregunta.respuesta;

    const crearOpcion = (option, index, multiple = false) => {
      const opt = document.createElement("option");
      opt.value = option.value;
      opt.textContent = option.label;
      if (multiple && Array.isArray(valor) && valor.includes(option.value)) {
        opt.selected = true;
      } else if (!multiple && valor === option.value) {
        opt.selected = true;
      }
      return opt;
    };

    switch (pregunta.tipo) {
      case "texto_largo":
        input = document.createElement("textarea");
        input.className = "form-control";
        input.id = labelId;
        input.rows = 3;
        input.value = valor ? String(valor) : "";
        break;
      case "numero":
        input = document.createElement("input");
        input.type = "number";
        input.className = "form-control";
        input.id = labelId;
        input.value =
          valor !== null && valor !== undefined ? String(valor) : "";
        break;
      case "fecha":
        input = document.createElement("input");
        input.type = "date";
        input.className = "form-control";
        input.id = labelId;
        if (valor) {
          input.value = String(valor).substring(0, 10);
        }
        break;
      case "si_no":
        {
          const values = [
            { value: "si", label: "Sí" },
            { value: "no", label: "No" },
          ];
          const radioGroup = document.createElement("div");
          radioGroup.className = "d-flex align-items-center";
          values.forEach((opt) => {
            const radioId = `${labelId}-${opt.value}`;
            const radioWrapper = document.createElement("div");
            radioWrapper.className = "custom-control custom-radio mr-3";

            const radio = document.createElement("input");
            radio.type = "radio";
            radio.className = "custom-control-input";
            radio.id = radioId;
            radio.name = `preg_${pregunta.id}`;
            radio.value = opt.value;
            if (valor === opt.value) {
              radio.checked = true;
            }

            const radioLabel = document.createElement("label");
            radioLabel.className = "custom-control-label";
            radioLabel.setAttribute("for", radioId);
            radioLabel.textContent = opt.label;

            radioWrapper.appendChild(radio);
            radioWrapper.appendChild(radioLabel);
            radioGroup.appendChild(radioWrapper);
          });
          input = radioGroup;
        }
        break;
      case "seleccion_unica":
        input = document.createElement("select");
        input.className = "form-control";
        input.id = labelId;
        const opcionPlaceholder = document.createElement("option");
        opcionPlaceholder.value = "";
        opcionPlaceholder.textContent = "Seleccione una opción";
        input.appendChild(opcionPlaceholder);
        (pregunta.opciones || []).forEach((opt) => {
          input.appendChild(crearOpcion(opt));
        });
        break;
      case "seleccion_multiple": {
        const contenedor = document.createElement("div");
        contenedor.className = "historia-multiopciones";
        contenedor.id = labelId;
        (pregunta.opciones || []).forEach((opt, index) => {
          const checkboxId = `${labelId}-${index}`;
          const item = document.createElement("div");
          item.className = "custom-control custom-checkbox";

          const checkbox = document.createElement("input");
          checkbox.type = "checkbox";
          checkbox.className = "custom-control-input";
          checkbox.id = checkboxId;
          checkbox.value = opt.value;
          checkbox.dataset.checkboxPregunta = String(pregunta.id);
          if (Array.isArray(valor) && valor.includes(opt.value)) {
            checkbox.checked = true;
          }

          const checkboxLabel = document.createElement("label");
          checkboxLabel.className = "custom-control-label";
          checkboxLabel.setAttribute("for", checkboxId);
          checkboxLabel.textContent = opt.label;

          checkbox.addEventListener("change", () => {
            const exclusivas = ["ninguna", "ninguno"];
            const esExclusiva = exclusivas.includes(checkbox.value);
            const checkboxes = contenedor.querySelectorAll(
              `[data-checkbox-pregunta="${pregunta.id}"]`
            );

            if (esExclusiva && checkbox.checked) {
              checkboxes.forEach((other) => {
                if (other !== checkbox) {
                  other.checked = false;
                }
              });
            } else if (!esExclusiva && checkbox.checked) {
              checkboxes.forEach((other) => {
                if (other !== checkbox && exclusivas.includes(other.value)) {
                  other.checked = false;
                }
              });
            }
          });

          item.appendChild(checkbox);
          item.appendChild(checkboxLabel);
          contenedor.appendChild(item);
        });

        if (!contenedor.childElementCount) {
          const aviso = document.createElement("p");
          aviso.className = "text-muted mb-0";
          aviso.textContent = "No hay opciones disponibles.";
          contenedor.appendChild(aviso);
        }

        input = contenedor;
        break;
      }
      default:
        input = document.createElement("input");
        input.type = "text";
        input.className = "form-control";
        input.id = labelId;
        input.value = valor ? String(valor) : "";
        break;
    }

    if (pregunta.tipo !== "si_no") {
      input.dataset.inputPregunta = String(pregunta.id);
    }

    if (pregunta.tipo === "si_no") {
      wrapper.appendChild(input);
    } else {
      wrapper.appendChild(input);
    }

    if (pregunta.ayuda) {
      const ayuda = document.createElement("small");
      ayuda.className = "form-text";
      ayuda.textContent = pregunta.ayuda;
      wrapper.appendChild(ayuda);
    }

    return wrapper;
  }

  function renderSecciones(data) {
    definicion = data;
    seccionesContainer.innerHTML = "";

    if (!Array.isArray(data.secciones) || data.secciones.length === 0) {
      seccionesContainer.style.display = "none";
      emptyMessage.style.display = "block";
      btnGuardar.disabled = true;
      btnCompletar.disabled = true;
      return;
    }

    emptyMessage.style.display = "none";
    seccionesContainer.style.display = "block";

    data.secciones.forEach((seccion) => {
      const card = document.createElement("div");
      card.className = "historia-section-card card";

      const header = document.createElement("div");
      header.className = "card-header";

      const title = document.createElement("h3");
      title.className = "card-title";
      title.textContent = seccion.nombre;
      header.appendChild(title);

      if (seccion.descripcion) {
        const desc = document.createElement("p");
        desc.className = "mb-0 mt-1 text-muted";
        desc.textContent = seccion.descripcion;
        header.appendChild(desc);
      }

      const body = document.createElement("div");
      body.className = "card-body";

      (seccion.preguntas || []).forEach((pregunta) => {
        body.appendChild(crearElementoPregunta(pregunta));
      });

      card.appendChild(header);
      card.appendChild(body);
      seccionesContainer.appendChild(card);
    });

    btnGuardar.disabled = false;
    btnCompletar.disabled = false;
  }

  function actualizarEstado(historia) {
    const estado = historia.estado || "pendiente";
    setEstado(estado);
    ultimaActualizacion.textContent = formatoFechaLarga(
      historia.ultima_actualizacion
    );
    completadoPor.textContent = historia.completado_por || "—";
  }

  function cargarHistoria() {
    loadingIndicator.style.display = "block";
    fetch(`api/historia_obtener.php?paciente_id=${pacienteId}`, {
      credentials: "same-origin",
    })
      .then((res) => {
        if (!res.ok) {
          throw new Error("No se pudo cargar la historia clínica.");
        }
        return res.json();
      })
      .then((json) => {
        if (json.status !== "success") {
          throw new Error(json.message || "Respuesta inválida.");
        }
        renderSecciones({
          secciones: json.secciones || [],
        });
        actualizarEstado(json.historia || {});
      })
      .catch((err) => {
        console.error(err);
        if (window.Swal) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: err.message || "No se pudo cargar la historia clínica.",
          });
        } else {
          alert(err.message || "No se pudo cargar la historia clínica.");
        }
        btnGuardar.disabled = true;
        btnCompletar.disabled = true;
      })
      .finally(() => {
        loadingIndicator.style.display = "none";
      });
  }

  function obtenerRespuestaPregunta(pregunta) {
    const id = pregunta.id;
    switch (pregunta.tipo) {
      case "si_no": {
        const seleccionado = document.querySelector(
          `input[name="preg_${id}"]:checked`
        );
        return seleccionado ? seleccionado.value : null;
      }
      case "seleccion_multiple": {
        const checkboxes = document.querySelectorAll(
          `[data-checkbox-pregunta="${id}"]`
        );
        if (!checkboxes.length) {
          return [];
        }
        return Array.from(checkboxes)
          .filter((checkbox) => checkbox.checked)
          .map((checkbox) => checkbox.value);
      }
      default: {
        const input = document.querySelector(`[data-input-pregunta="${id}"]`);
        if (!input) return null;
        if (input.type === "date" && input.value) {
          return input.value;
        }
        return input.value !== "" ? input.value : null;
      }
    }
  }

  function recolectarRespuestas() {
    if (!definicion || !Array.isArray(definicion.secciones)) {
      return [];
    }
    const respuestas = [];
    definicion.secciones.forEach((seccion) => {
      (seccion.preguntas || []).forEach((pregunta) => {
        const valor = obtenerRespuestaPregunta(pregunta);
        respuestas.push({
          pregunta_id: pregunta.id,
          valor,
        });
      });
    });
    return respuestas;
  }

  function validarObligatorios(estadoObjetivo) {
    if (estadoObjetivo !== "completado") {
      return {
        valido: true,
        faltantes: [],
      };
    }

    const faltantes = [];
    definicion.secciones.forEach((seccion) => {
      (seccion.preguntas || []).forEach((pregunta) => {
        if (!pregunta.requerida) {
          return;
        }
        const valor = obtenerRespuestaPregunta(pregunta);
        const vacio =
          valor === null ||
          valor === "" ||
          (Array.isArray(valor) && valor.length === 0);
        if (vacio) {
          faltantes.push(pregunta.pregunta);
        }
      });
    });

    return {
      valido: faltantes.length === 0,
      faltantes,
    };
  }

  function guardarHistoria(estadoObjetivo) {
    const validacion = validarObligatorios(estadoObjetivo);
    if (!validacion.valido) {
      if (window.Swal) {
        Swal.fire({
          icon: "warning",
          title: "Faltan respuestas",
          html: `<p>Debes completar las siguientes preguntas obligatorias:</p><ul class="text-left">${validacion.faltantes
            .map((q) => `<li>${q}</li>`)
            .join("")}</ul>`,
        });
      }
      return;
    }

    const payload = {
      paciente_id: pacienteId,
      estado: estadoObjetivo,
      respuestas: recolectarRespuestas(),
    };

    btnGuardar.disabled = true;
    btnCompletar.disabled = true;

    fetch("api/historia_guardar.php", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    })
      .then((res) => {
        if (!res.ok) {
          throw new Error("No se pudo guardar la historia clínica.");
        }
        return res.json();
      })
      .then((json) => {
        if (json.status !== "success") {
          throw new Error(
            json.message || "No se pudo guardar la historia clínica."
          );
        }
        const estadoRespuesta = json.estado || "en_progreso";
        setEstado(estadoRespuesta);
        ultimaActualizacion.textContent = formatoFechaLarga(
          json.ultima_actualizacion || new Date().toISOString()
        );
        if (estadoRespuesta === "completado") {
          completadoPor.textContent =
            json.completado_por ||
            (window.AuthUsuario ? window.AuthUsuario.nombre : "—");
        } else {
          completadoPor.textContent = json.completado_por || "—";
        }
        if (window.Swal) {
          Swal.fire({
            icon: "success",
            title: "Cambios guardados",
            text:
              estadoRespuesta === "completado"
                ? "La historia clínica quedó marcada como completada."
                : "Progreso guardado correctamente.",
            timer: 2000,
          });
        }
      })
      .catch((err) => {
        console.error(err);
        if (window.Swal) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: err.message || "No se pudo guardar la historia clínica.",
          });
        } else {
          alert(err.message || "No se pudo guardar la historia clínica.");
        }
      })
      .finally(() => {
        btnGuardar.disabled = false;
        btnCompletar.disabled = false;
      });
  }

  btnGuardar.addEventListener("click", function () {
    guardarHistoria("en_progreso");
  });

  btnCompletar.addEventListener("click", function () {
    guardarHistoria("completado");
  });

  cargarHistoria();
})();
