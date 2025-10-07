-- Semilla de cuestionario para historia clínica
-- Fecha: 2025-10-06
START TRANSACTION;

-- Elimina secciones y preguntas previas con los mismos nombres (opcional)
DELETE FROM historia_preguntas
WHERE seccion_id IN (
    SELECT id FROM (
        SELECT id FROM historia_secciones
        WHERE nombre IN (
            'Información General del Paciente',
            'Hábitos y Estilo de Vida',
            'Antecedentes Médicos Personales',
            'Medicamentos, Alergias y Vacunas',
            'Antecedentes Familiares',
            'Salud Reproductiva',
            'Factores Psicosociales y Bienestar'
        )
    ) AS tmp
);

DELETE FROM historia_secciones
WHERE nombre IN (
    'Información General del Paciente',
    'Hábitos y Estilo de Vida',
    'Antecedentes Médicos Personales',
    'Medicamentos, Alergias y Vacunas',
    'Antecedentes Familiares',
    'Salud Reproductiva',
    'Factores Psicosociales y Bienestar'
);

-- Inserta las secciones principales del cuestionario
INSERT INTO historia_secciones (nombre, descripcion, orden, activo)
VALUES
('Información General del Paciente', 'Datos básicos para la valoración inicial y planificación de la consulta.', 1, 1),
('Hábitos y Estilo de Vida', 'Información sobre hábitos cotidianos que impactan en la salud del paciente.', 2, 1),
('Antecedentes Médicos Personales', 'Condiciones previas, hospitalizaciones y diagnósticos en seguimiento.', 3, 1),
('Medicamentos, Alergias y Vacunas', 'Registro de fármacos, alergias y esquemas de vacunación.', 4, 1),
('Antecedentes Familiares', 'Historia clínica relevante de familiares directos.', 5, 1),
('Salud Reproductiva', 'Aspectos relacionados con salud sexual y reproductiva, cuando aplique.', 6, 1),
('Factores Psicosociales y Bienestar', 'Evaluación de factores emocionales y soporte social.', 7, 1);

-- Preguntas: Información General del Paciente
INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Peso actual (kg)', 'numero', NULL, 'Ingrese el peso en kilogramos. Utilice punto decimal si es necesario.', 1, 1, 1
FROM historia_secciones WHERE nombre = 'Información General del Paciente';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Estatura (cm)', 'numero', NULL, 'Ingrese la estatura en centímetros.', 1, 2, 1
FROM historia_secciones WHERE nombre = 'Información General del Paciente';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Motivo principal de la consulta', 'texto_largo', NULL, 'Describa brevemente la razón principal por la que acude hoy.', 1, 3, 1
FROM historia_secciones WHERE nombre = 'Información General del Paciente';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Síntomas principales y tiempo de evolución', 'texto_largo', NULL, 'Incluya inicio, evolución y factores que mejoran o empeoran.', 1, 4, 1
FROM historia_secciones WHERE nombre = 'Información General del Paciente';

-- Preguntas: Hábitos y Estilo de Vida
INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, '¿Realiza actividad física regularmente?', 'si_no', NULL, NULL, 1, 1, 1
FROM historia_secciones WHERE nombre = 'Hábitos y Estilo de Vida';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Frecuencia de actividad física', 'seleccion_unica',
       '[{"value":"diaria","label":"Diaria"},{"value":"3_5_semana","label":"3-5 veces por semana"},{"value":"1_2_semana","label":"1-2 veces por semana"},{"value":"ocasional","label":"Ocasional"},{"value":"ninguna","label":"No realiza"}]',
       'Seleccione la opción que mejor describa la frecuencia habitual.', 0, 2, 1
FROM historia_secciones WHERE nombre = 'Hábitos y Estilo de Vida';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Tipo de actividad física habitual', 'texto_corto', NULL, 'Por ejemplo: caminar, correr, gimnasio, yoga.', 0, 3, 1
FROM historia_secciones WHERE nombre = 'Hábitos y Estilo de Vida';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Consumo de tabaco', 'seleccion_unica',
       '[{"value":"nunca","label":"Nunca"},{"value":"exfumador","label":"Ex fumador"},{"value":"menos_5","label":"Menos de 5 cigarrillos al día"},{"value":"5_10","label":"5-10 cigarrillos al día"},{"value":"mas_10","label":"Más de 10 cigarrillos al día"}]',
       NULL, 1, 4, 1
FROM historia_secciones WHERE nombre = 'Hábitos y Estilo de Vida';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Consumo de alcohol', 'seleccion_unica',
       '[{"value":"nunca","label":"Nunca"},{"value":"social","label":"Ocasional/social"},{"value":"moderado","label":"De 1 a 3 veces por semana"},{"value":"frecuente","label":"Más de 3 veces por semana"}]',
       NULL, 1, 5, 1
FROM historia_secciones WHERE nombre = 'Hábitos y Estilo de Vida';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Horas de sueño promedio por noche', 'seleccion_unica',
       '[{"value":"menos_5","label":"Menos de 5 horas"},{"value":"5_6","label":"5-6 horas"},{"value":"7_8","label":"7-8 horas"},{"value":"mas_8","label":"Más de 8 horas"}]',
       NULL, 0, 6, 1
FROM historia_secciones WHERE nombre = 'Hábitos y Estilo de Vida';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Calidad percibida de la alimentación', 'seleccion_unica',
       '[{"value":"excelente","label":"Excelente"},{"value":"buena","label":"Buena"},{"value":"regular","label":"Regular"},{"value":"deficiente","label":"Deficiente"}]',
       NULL, 0, 7, 1
FROM historia_secciones WHERE nombre = 'Hábitos y Estilo de Vida';

-- Preguntas: Antecedentes Médicos Personales
INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Enfermedades diagnosticadas previamente', 'seleccion_multiple',
       '[{"value":"hipertension","label":"Hipertensión arterial"},{"value":"diabetes","label":"Diabetes"},{"value":"dislipidemia","label":"Dislipidemia / colesterol alto"},{"value":"asma","label":"Asma o enfermedad respiratoria crónica"},{"value":"cardiopatia","label":"Cardiopatía"},{"value":"renal","label":"Enfermedad renal"},{"value":"hepatica","label":"Enfermedad hepática"},{"value":"endocrina","label":"Trastorno endocrino (tiroides, suprarrenales, etc.)"},{"value":"neurologica","label":"Trastorno neurológico"},{"value":"psiquiatrica","label":"Trastorno psiquiátrico"},{"value":"cancer","label":"Cáncer"},{"value":"otras","label":"Otras"}]',
       'Seleccione todas las opciones que apliquen y detalle al final si es necesario.', 0, 1, 1
FROM historia_secciones WHERE nombre = 'Antecedentes Médicos Personales';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Detalle de otras enfermedades o condiciones no listadas', 'texto_largo', NULL, 'Incluya fecha de diagnóstico y profesional tratante si aplica.', 0, 2, 1
FROM historia_secciones WHERE nombre = 'Antecedentes Médicos Personales';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, '¿Ha sido hospitalizado o intervenido quirúrgicamente en los últimos 12 meses?', 'si_no', NULL, NULL, 1, 3, 1
FROM historia_secciones WHERE nombre = 'Antecedentes Médicos Personales';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Detalle de hospitalizaciones o cirugías recientes (fecha, diagnóstico, centro)', 'texto_largo', NULL, 'Indique fecha, procedimiento y centro médico.', 0, 4, 1
FROM historia_secciones WHERE nombre = 'Antecedentes Médicos Personales';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, '¿Presenta actualmente dolor o limitaciones funcionales?', 'si_no', NULL, NULL, 0, 5, 1
FROM historia_secciones WHERE nombre = 'Antecedentes Médicos Personales';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Detalle del dolor o limitaciones (localización, intensidad, desencadenantes)', 'texto_largo', NULL, NULL, 0, 6, 1
FROM historia_secciones WHERE nombre = 'Antecedentes Médicos Personales';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, '¿Cuenta con diagnósticos crónicos en seguimiento?', 'si_no', NULL, NULL, 0, 7, 1
FROM historia_secciones WHERE nombre = 'Antecedentes Médicos Personales';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Detalle de diagnósticos crónicos y profesional tratante', 'texto_largo', NULL, NULL, 0, 8, 1
FROM historia_secciones WHERE nombre = 'Antecedentes Médicos Personales';

-- Preguntas: Medicamentos, Alergias y Vacunas
INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, '¿Toma medicamentos de forma regular?', 'si_no', NULL, NULL, 1, 1, 1
FROM historia_secciones WHERE nombre = 'Medicamentos, Alergias y Vacunas';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Listado de medicamentos actuales (nombre, dosis, frecuencia)', 'texto_largo', NULL, 'Incluya suplementos y tratamientos de venta libre si aplica.', 0, 2, 1
FROM historia_secciones WHERE nombre = 'Medicamentos, Alergias y Vacunas';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Alergias conocidas', 'seleccion_multiple',
       '[{"value":"ninguna","label":"Ninguna conocida"},{"value":"medicamentos","label":"Medicamentos"},{"value":"alimentos","label":"Alimentos"},{"value":"latex","label":"Látex"},{"value":"ambientales","label":"Ambientales (polvo, polen, etc.)"},{"value":"picaduras","label":"Picaduras de insectos"},{"value":"otros","label":"Otros"}]',
       'Seleccione todas las alergias conocidas. Si marca "Ninguna" no seleccione otras opciones.', 0, 3, 1
FROM historia_secciones WHERE nombre = 'Medicamentos, Alergias y Vacunas';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Detalle adicional sobre alergias o reacciones adversas', 'texto_largo', NULL, NULL, 0, 4, 1
FROM historia_secciones WHERE nombre = 'Medicamentos, Alergias y Vacunas';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Esquema de vacunación al día', 'si_no', NULL, NULL, 0, 5, 1
FROM historia_secciones WHERE nombre = 'Medicamentos, Alergias y Vacunas';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Vacunas pendientes o recomendaciones previas', 'texto_largo', NULL, NULL, 0, 6, 1
FROM historia_secciones WHERE nombre = 'Medicamentos, Alergias y Vacunas';

-- Preguntas: Antecedentes Familiares
INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Antecedentes familiares relevantes', 'seleccion_multiple',
       '[{"value":"hipertension","label":"Hipertensión"},{"value":"cardiopatia","label":"Cardiopatía / infarto"},{"value":"acv","label":"Accidente cerebrovascular"},{"value":"diabetes","label":"Diabetes"},{"value":"cancer","label":"Cáncer"},{"value":"autoinmune","label":"Enfermedad autoinmune"},{"value":"renal","label":"Enfermedad renal"},{"value":"salud_mental","label":"Trastorno de salud mental"},{"value":"ninguno","label":"Ninguno"},{"value":"otros","label":"Otros"}]',
       'Seleccione todas las condiciones que se presentan en familiares directos.', 0, 1, 1
FROM historia_secciones WHERE nombre = 'Antecedentes Familiares';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Detalle de antecedentes familiares (familiar, edad de inicio, estado actual)', 'texto_largo', NULL, NULL, 0, 2, 1
FROM historia_secciones WHERE nombre = 'Antecedentes Familiares';

-- Preguntas: Salud Reproductiva
INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, '¿Aplica seguimiento de salud reproductiva para el paciente?', 'si_no', NULL, 'Seleccione "No" si el tema no aplica (por ejemplo, paciente pediátrico).', 0, 1, 1
FROM historia_secciones WHERE nombre = 'Salud Reproductiva';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Última revisión ginecológica o urológica', 'fecha', NULL, 'Indique la fecha de la última evaluación. Deje en blanco si no aplica.', 0, 2, 1
FROM historia_secciones WHERE nombre = 'Salud Reproductiva';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Detalle de embarazos, partos o procedimientos reproductivos relevantes', 'texto_largo', NULL, NULL, 0, 3, 1
FROM historia_secciones WHERE nombre = 'Salud Reproductiva';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Método anticonceptivo actual', 'seleccion_unica',
       '[{"value":"ninguno","label":"Ninguno"},{"value":"barrera","label":"Método de barrera"},{"value":"hormonal","label":"Método hormonal"},{"value":"diu","label":"Dispositivo intrauterino"},{"value":"permanente","label":"Método permanente (ligadura, vasectomía)"},{"value":"otros","label":"Otros"}]',
       NULL, 0, 4, 1
FROM historia_secciones WHERE nombre = 'Salud Reproductiva';

-- Preguntas: Factores Psicosociales y Bienestar
INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Nivel de estrés percibido en las últimas cuatro semanas', 'seleccion_unica',
       '[{"value":"bajo","label":"Bajo"},{"value":"moderado","label":"Moderado"},{"value":"alto","label":"Alto"},{"value":"muy_alto","label":"Muy alto"}]',
       NULL, 0, 1, 1
FROM historia_secciones WHERE nombre = 'Factores Psicosociales y Bienestar';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Calidad del apoyo familiar o social', 'seleccion_unica',
       '[{"value":"adecuado","label":"Adecuado"},{"value":"limitado","label":"Limitado"},{"value":"inexistente","label":"Inexistente"}]',
       NULL, 0, 2, 1
FROM historia_secciones WHERE nombre = 'Factores Psicosociales y Bienestar';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Estado de ánimo predominante', 'seleccion_unica',
       '[{"value":"estable","label":"Estable"},{"value":"ansioso","label":"Ansioso"},{"value":"decaido","label":"Decaído"},{"value":"irritable","label":"Irritable"},{"value":"otro","label":"Otro"}]',
       NULL, 0, 3, 1
FROM historia_secciones WHERE nombre = 'Factores Psicosociales y Bienestar';

INSERT INTO historia_preguntas (seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden, activo)
SELECT id, 'Comentarios adicionales relevantes para el plan de cuidado', 'texto_largo', NULL, 'Espacio libre para notas del paciente o del profesional.', 0, 4, 1
FROM historia_secciones WHERE nombre = 'Factores Psicosociales y Bienestar';

COMMIT;
