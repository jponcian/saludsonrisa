/*
 Navicat Premium Data Transfer

 Source Server         : Local
 Source Server Type    : MySQL
 Source Server Version : 80300
 Source Host           : localhost:3306
 Source Schema         : javier_ponciano_5

 Target Server Type    : MySQL
 Target Server Version : 80300
 File Encoding         : 65001

 Date: 09/10/2025 20:38:54
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for atencion_eventos
-- ----------------------------
DROP TABLE IF EXISTS `atencion_eventos`;
CREATE TABLE `atencion_eventos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `proceso_id` int NOT NULL,
  `tipo` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `creado_por` int NULL DEFAULT NULL,
  `creado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_proceso`(`proceso_id`) USING BTREE,
  INDEX `idx_tipo`(`tipo`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of atencion_eventos
-- ----------------------------

-- ----------------------------
-- Table structure for atencion_procesos
-- ----------------------------
DROP TABLE IF EXISTS `atencion_procesos`;
CREATE TABLE `atencion_procesos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `suscripcion_id` int NULL DEFAULT NULL,
  `plan_id` int NULL DEFAULT NULL,
  `motivo` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `urgencia` enum('baja','media','alta','critica') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `observaciones` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `estado` enum('abierto','en_curso','cerrado','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'abierto',
  `origen` enum('guardia','emergencia','validacion') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'guardia',
  `cobertura_validada` tinyint(1) NULL DEFAULT 0,
  `cobertura_validada_por` int NULL DEFAULT NULL,
  `cobertura_validada_en` datetime NULL DEFAULT NULL,
  `creado_por` int NULL DEFAULT NULL,
  `cerrado_por` int NULL DEFAULT NULL,
  `creado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `cerrado_en` datetime NULL DEFAULT NULL,
  `actualizado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_paciente`(`paciente_id`) USING BTREE,
  INDEX `idx_estado`(`estado`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of atencion_procesos
-- ----------------------------
INSERT INTO `atencion_procesos` VALUES (6, 6, NULL, NULL, 'APERTURA ADMIN', '', 'Observaciones', 'cerrado', 'guardia', 0, NULL, NULL, NULL, NULL, '2025-10-08 23:27:59', '2025-10-08 23:44:40', '2025-10-08 23:44:40');
INSERT INTO `atencion_procesos` VALUES (7, 6, NULL, NULL, 'APERTURA ADMIN', '', 'Observaciones', 'cerrado', 'guardia', 0, NULL, NULL, NULL, NULL, '2025-10-08 23:45:08', '2025-10-08 23:48:51', '2025-10-08 23:48:51');

-- ----------------------------
-- Table structure for consulta_especialidades
-- ----------------------------
DROP TABLE IF EXISTS `consulta_especialidades`;
CREATE TABLE `consulta_especialidades`  (
  `consulta_id` int NOT NULL,
  `especialidad_id` int NOT NULL,
  PRIMARY KEY (`consulta_id`, `especialidad_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of consulta_especialidades
-- ----------------------------

-- ----------------------------
-- Table structure for consulta_fotos
-- ----------------------------
DROP TABLE IF EXISTS `consulta_fotos`;
CREATE TABLE `consulta_fotos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `consulta_id` int NOT NULL,
  `foto_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `consulta_id`(`consulta_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of consulta_fotos
-- ----------------------------

-- ----------------------------
-- Table structure for consultas
-- ----------------------------
DROP TABLE IF EXISTS `consultas`;
CREATE TABLE `consultas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `especialista_id` int NOT NULL,
  `fecha_consulta` datetime NULL DEFAULT NULL,
  `diagnostico` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `tratamiento` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `proceso_id` int NULL DEFAULT NULL,
  `duracion_min` int NULL DEFAULT NULL,
  `costo_estimado` decimal(10, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `paciente_id`(`paciente_id`) USING BTREE,
  INDEX `especialista_id`(`especialista_id`) USING BTREE,
  INDEX `idx_consulta_proceso`(`proceso_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of consultas
-- ----------------------------
INSERT INTO `consultas` VALUES (21, 6, 1, '2025-10-08 23:37:58', 'Diagnóstico', 'Procedimiento / Tratamiento', 'Indicaciones', 6, NULL, NULL);
INSERT INTO `consultas` VALUES (22, 6, 1, '2025-10-08 23:48:46', 'Diagnóstico *', 'Tratamiento', 'Indicaciones', 7, NULL, NULL);

-- ----------------------------
-- Table structure for demo_asistencias
-- ----------------------------
DROP TABLE IF EXISTS `demo_asistencias`;
CREATE TABLE `demo_asistencias`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_participante` int NOT NULL,
  `id_reunion` int NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_participante`(`id_participante`) USING BTREE,
  INDEX `id_reunion`(`id_reunion`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of demo_asistencias
-- ----------------------------

-- ----------------------------
-- Table structure for demo_participantes
-- ----------------------------
DROP TABLE IF EXISTS `demo_participantes`;
CREATE TABLE `demo_participantes`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_reunion` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_reunion`(`id_reunion`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of demo_participantes
-- ----------------------------

-- ----------------------------
-- Table structure for demo_reuniones
-- ----------------------------
DROP TABLE IF EXISTS `demo_reuniones`;
CREATE TABLE `demo_reuniones`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of demo_reuniones
-- ----------------------------

-- ----------------------------
-- Table structure for empresas
-- ----------------------------
DROP TABLE IF EXISTS `empresas`;
CREATE TABLE `empresas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contacto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of empresas
-- ----------------------------
INSERT INTO `empresas` VALUES (1, 'SOMOS Salud', '(0414) 149.04.01', '');

-- ----------------------------
-- Table structure for especialidades
-- ----------------------------
DROP TABLE IF EXISTS `especialidades`;
CREATE TABLE `especialidades`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 28 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of especialidades
-- ----------------------------
INSERT INTO `especialidades` VALUES (1, 'Anestesiología');
INSERT INTO `especialidades` VALUES (2, 'Cardiología');
INSERT INTO `especialidades` VALUES (3, 'Cirugía');
INSERT INTO `especialidades` VALUES (4, 'Dermatología');
INSERT INTO `especialidades` VALUES (5, 'Ecografía');
INSERT INTO `especialidades` VALUES (6, 'Enfermería');
INSERT INTO `especialidades` VALUES (7, 'Fisioterapia');
INSERT INTO `especialidades` VALUES (8, 'Fisitría');
INSERT INTO `especialidades` VALUES (9, 'Gastrología');
INSERT INTO `especialidades` VALUES (10, 'Ginecología');
INSERT INTO `especialidades` VALUES (11, 'Laboratorio');
INSERT INTO `especialidades` VALUES (12, 'Medicina Interna');
INSERT INTO `especialidades` VALUES (13, 'Nefrología');
INSERT INTO `especialidades` VALUES (14, 'Neumología');
INSERT INTO `especialidades` VALUES (15, 'Neurología');
INSERT INTO `especialidades` VALUES (16, 'Nutrición');
INSERT INTO `especialidades` VALUES (17, 'Odontología');
INSERT INTO `especialidades` VALUES (18, 'Oftalmología');
INSERT INTO `especialidades` VALUES (19, 'Oncología');
INSERT INTO `especialidades` VALUES (20, 'Ortodoncia');
INSERT INTO `especialidades` VALUES (21, 'Otorrinología');
INSERT INTO `especialidades` VALUES (22, 'Pediatría');
INSERT INTO `especialidades` VALUES (23, 'Psicología');
INSERT INTO `especialidades` VALUES (24, 'Psiquiatría');
INSERT INTO `especialidades` VALUES (25, 'Quirofano');
INSERT INTO `especialidades` VALUES (26, 'Traumatología');
INSERT INTO `especialidades` VALUES (27, 'Urología');

-- ----------------------------
-- Table structure for especialista_especialidades
-- ----------------------------
DROP TABLE IF EXISTS `especialista_especialidades`;
CREATE TABLE `especialista_especialidades`  (
  `especialista_id` int NOT NULL,
  `especialidad_id` int NOT NULL,
  PRIMARY KEY (`especialista_id`, `especialidad_id`) USING BTREE,
  INDEX `especialidad_id`(`especialidad_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of especialista_especialidades
-- ----------------------------
INSERT INTO `especialista_especialidades` VALUES (1, 1);
INSERT INTO `especialista_especialidades` VALUES (2, 1);
INSERT INTO `especialista_especialidades` VALUES (3, 2);
INSERT INTO `especialista_especialidades` VALUES (4, 4);
INSERT INTO `especialista_especialidades` VALUES (5, 3);
INSERT INTO `especialista_especialidades` VALUES (6, 5);
INSERT INTO `especialista_especialidades` VALUES (7, 5);
INSERT INTO `especialista_especialidades` VALUES (8, 7);
INSERT INTO `especialista_especialidades` VALUES (9, 8);
INSERT INTO `especialista_especialidades` VALUES (10, 9);
INSERT INTO `especialista_especialidades` VALUES (11, 10);
INSERT INTO `especialista_especialidades` VALUES (12, 10);
INSERT INTO `especialista_especialidades` VALUES (13, 12);
INSERT INTO `especialista_especialidades` VALUES (14, 5);

-- ----------------------------
-- Table structure for especialistas
-- ----------------------------
DROP TABLE IF EXISTS `especialistas`;
CREATE TABLE `especialistas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `usuario_id`(`usuario_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of especialistas
-- ----------------------------
INSERT INTO `especialistas` VALUES (2, 3);
INSERT INTO `especialistas` VALUES (3, 4);
INSERT INTO `especialistas` VALUES (4, 5);
INSERT INTO `especialistas` VALUES (5, 6);
INSERT INTO `especialistas` VALUES (6, 7);
INSERT INTO `especialistas` VALUES (7, 8);
INSERT INTO `especialistas` VALUES (8, 9);
INSERT INTO `especialistas` VALUES (12, 13);
INSERT INTO `especialistas` VALUES (13, 14);
INSERT INTO `especialistas` VALUES (14, 15);

-- ----------------------------
-- Table structure for historia_paciente_estados
-- ----------------------------
DROP TABLE IF EXISTS `historia_paciente_estados`;
CREATE TABLE `historia_paciente_estados`  (
  `paciente_id` int NOT NULL,
  `estado` enum('pendiente','en_progreso','completado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendiente',
  `ultima_actualizacion` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completado_por` int NULL DEFAULT NULL,
  `completado_en` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`paciente_id`) USING BTREE,
  INDEX `idx_historia_estado_estado`(`estado`) USING BTREE,
  INDEX `idx_historia_estado_completado`(`completado_en`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of historia_paciente_estados
-- ----------------------------
INSERT INTO `historia_paciente_estados` VALUES (1, 'completado', '2025-10-07 07:02:58', 1, '2025-10-07 07:02:58');

-- ----------------------------
-- Table structure for historia_preguntas
-- ----------------------------
DROP TABLE IF EXISTS `historia_preguntas`;
CREATE TABLE `historia_preguntas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `seccion_id` int NOT NULL,
  `pregunta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipo` enum('texto_corto','texto_largo','numero','fecha','si_no','seleccion_unica','seleccion_multiple') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'texto_corto',
  `opciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `ayuda` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `requerida` tinyint(1) NOT NULL DEFAULT 0,
  `orden` int NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_historia_preguntas_seccion`(`seccion_id`) USING BTREE,
  INDEX `idx_historia_preguntas_activo`(`activo`) USING BTREE,
  INDEX `idx_historia_preguntas_tipo`(`tipo`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 36 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of historia_preguntas
-- ----------------------------
INSERT INTO `historia_preguntas` VALUES (1, 1, 'Peso actual (kg)', 'numero', NULL, 'Ingrese el peso en kilogramos. Utilice punto decimal si es necesario.', 1, 1, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (2, 1, 'Estatura (cm)', 'numero', NULL, 'Ingrese la estatura en centímetros.', 1, 2, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (5, 2, '¿Realiza actividad física regularmente?', 'si_no', NULL, NULL, 1, 1, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (6, 2, 'Frecuencia de actividad física', 'seleccion_unica', '[{\"value\":\"diaria\",\"label\":\"Diaria\"},{\"value\":\"3_5_semana\",\"label\":\"3-5 veces por semana\"},{\"value\":\"1_2_semana\",\"label\":\"1-2 veces por semana\"},{\"value\":\"ocasional\",\"label\":\"Ocasional\"},{\"value\":\"ninguna\",\"label\":\"No realiza\"}]', 'Seleccione la opción que mejor describa la frecuencia habitual.', 0, 2, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (7, 2, 'Tipo de actividad física habitual', 'texto_corto', NULL, 'Por ejemplo: caminar, correr, gimnasio, yoga.', 0, 3, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (8, 2, 'Consumo de tabaco', 'seleccion_unica', '[{\"value\":\"nunca\",\"label\":\"Nunca\"},{\"value\":\"exfumador\",\"label\":\"Ex fumador\"},{\"value\":\"menos_5\",\"label\":\"Menos de 5 cigarrillos al día\"},{\"value\":\"5_10\",\"label\":\"5-10 cigarrillos al día\"},{\"value\":\"mas_10\",\"label\":\"Más de 10 cigarrillos al día\"}]', NULL, 1, 4, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (9, 2, 'Consumo de alcohol', 'seleccion_unica', '[{\"value\":\"nunca\",\"label\":\"Nunca\"},{\"value\":\"social\",\"label\":\"Ocasional/social\"},{\"value\":\"moderado\",\"label\":\"De 1 a 3 veces por semana\"},{\"value\":\"frecuente\",\"label\":\"Más de 3 veces por semana\"}]', NULL, 1, 5, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (10, 2, 'Horas de sueño promedio por noche', 'seleccion_unica', '[{\"value\":\"menos_5\",\"label\":\"Menos de 5 horas\"},{\"value\":\"5_6\",\"label\":\"5-6 horas\"},{\"value\":\"7_8\",\"label\":\"7-8 horas\"},{\"value\":\"mas_8\",\"label\":\"Más de 8 horas\"}]', NULL, 0, 6, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (11, 2, 'Calidad percibida de la alimentación', 'seleccion_unica', '[{\"value\":\"excelente\",\"label\":\"Excelente\"},{\"value\":\"buena\",\"label\":\"Buena\"},{\"value\":\"regular\",\"label\":\"Regular\"},{\"value\":\"deficiente\",\"label\":\"Deficiente\"}]', NULL, 0, 7, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (12, 3, 'Enfermedades diagnosticadas previamente', 'seleccion_multiple', '[{\"value\":\"hipertension\",\"label\":\"Hipertensión arterial\"},{\"value\":\"diabetes\",\"label\":\"Diabetes\"},{\"value\":\"dislipidemia\",\"label\":\"Dislipidemia / colesterol alto\"},{\"value\":\"asma\",\"label\":\"Asma o enfermedad respiratoria crónica\"},{\"value\":\"cardiopatia\",\"label\":\"Cardiopatía\"},{\"value\":\"renal\",\"label\":\"Enfermedad renal\"},{\"value\":\"hepatica\",\"label\":\"Enfermedad hepática\"},{\"value\":\"endocrina\",\"label\":\"Trastorno endocrino (tiroides, suprarrenales, etc.)\"},{\"value\":\"neurologica\",\"label\":\"Trastorno neurológico\"},{\"value\":\"psiquiatrica\",\"label\":\"Trastorno psiquiátrico\"},{\"value\":\"cancer\",\"label\":\"Cáncer\"},{\"value\":\"otras\",\"label\":\"Otras\"}]', 'Seleccione todas las opciones que apliquen y detalle al final si es necesario.', 0, 1, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (13, 3, 'Detalle de otras enfermedades o condiciones no listadas', 'texto_largo', NULL, 'Incluya fecha de diagnóstico y profesional tratante si aplica.', 0, 2, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (14, 3, '¿Ha sido hospitalizado o intervenido quirúrgicamente en los últimos 12 meses?', 'si_no', NULL, NULL, 1, 3, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (15, 3, 'Detalle de hospitalizaciones o cirugías recientes (fecha, diagnóstico, centro)', 'texto_largo', NULL, 'Indique fecha, procedimiento y centro médico.', 0, 4, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (16, 3, '¿Presenta actualmente dolor o limitaciones funcionales?', 'si_no', NULL, NULL, 0, 5, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (17, 3, 'Detalle del dolor o limitaciones (localización, intensidad, desencadenantes)', 'texto_largo', NULL, NULL, 0, 6, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (18, 3, '¿Cuenta con diagnósticos crónicos en seguimiento?', 'si_no', NULL, NULL, 0, 7, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (19, 3, 'Detalle de diagnósticos crónicos y profesional tratante', 'texto_largo', NULL, NULL, 0, 8, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (20, 4, '¿Toma medicamentos de forma regular?', 'si_no', NULL, NULL, 1, 1, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (21, 4, 'Listado de medicamentos actuales (nombre, dosis, frecuencia)', 'texto_largo', NULL, 'Incluya suplementos y tratamientos de venta libre si aplica.', 0, 2, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (22, 4, 'Alergias conocidas', 'seleccion_multiple', '[{\"value\":\"ninguna\",\"label\":\"Ninguna conocida\"},{\"value\":\"medicamentos\",\"label\":\"Medicamentos\"},{\"value\":\"alimentos\",\"label\":\"Alimentos\"},{\"value\":\"latex\",\"label\":\"Látex\"},{\"value\":\"ambientales\",\"label\":\"Ambientales (polvo, polen, etc.)\"},{\"value\":\"picaduras\",\"label\":\"Picaduras de insectos\"},{\"value\":\"otros\",\"label\":\"Otros\"}]', 'Seleccione todas las alergias conocidas. Si marca \"Ninguna\" no seleccione otras opciones.', 0, 3, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (23, 4, 'Detalle adicional sobre alergias o reacciones adversas', 'texto_largo', NULL, NULL, 0, 4, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (24, 4, 'Esquema de vacunación al día', 'si_no', NULL, NULL, 0, 5, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (25, 4, 'Vacunas pendientes o recomendaciones previas', 'texto_largo', NULL, NULL, 0, 6, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (26, 5, 'Antecedentes familiares relevantes', 'seleccion_multiple', '[{\"value\":\"hipertension\",\"label\":\"Hipertensión\"},{\"value\":\"cardiopatia\",\"label\":\"Cardiopatía / infarto\"},{\"value\":\"acv\",\"label\":\"Accidente cerebrovascular\"},{\"value\":\"diabetes\",\"label\":\"Diabetes\"},{\"value\":\"cancer\",\"label\":\"Cáncer\"},{\"value\":\"autoinmune\",\"label\":\"Enfermedad autoinmune\"},{\"value\":\"renal\",\"label\":\"Enfermedad renal\"},{\"value\":\"salud_mental\",\"label\":\"Trastorno de salud mental\"},{\"value\":\"ninguno\",\"label\":\"Ninguno\"},{\"value\":\"otros\",\"label\":\"Otros\"}]', 'Seleccione todas las condiciones que se presentan en familiares directos.', 0, 1, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (27, 5, 'Detalle de antecedentes familiares (familiar, edad de inicio, estado actual)', 'texto_largo', NULL, NULL, 0, 2, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (28, 6, '¿Aplica seguimiento de salud reproductiva para el paciente?', 'si_no', NULL, 'Seleccione \"No\" si el tema no aplica (por ejemplo, paciente pediátrico).', 0, 1, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (29, 6, 'Última revisión ginecológica o urológica', 'fecha', NULL, 'Indique la fecha de la última evaluación. Deje en blanco si no aplica.', 0, 2, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (30, 6, 'Detalle de embarazos, partos o procedimientos reproductivos relevantes', 'texto_largo', NULL, NULL, 0, 3, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (31, 6, 'Método anticonceptivo actual', 'seleccion_unica', '[{\"value\":\"ninguno\",\"label\":\"Ninguno\"},{\"value\":\"barrera\",\"label\":\"Método de barrera\"},{\"value\":\"hormonal\",\"label\":\"Método hormonal\"},{\"value\":\"diu\",\"label\":\"Dispositivo intrauterino\"},{\"value\":\"permanente\",\"label\":\"Método permanente (ligadura, vasectomía)\"},{\"value\":\"otros\",\"label\":\"Otros\"}]', NULL, 0, 4, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (32, 7, 'Nivel de estrés percibido en las últimas cuatro semanas', 'seleccion_unica', '[{\"value\":\"bajo\",\"label\":\"Bajo\"},{\"value\":\"moderado\",\"label\":\"Moderado\"},{\"value\":\"alto\",\"label\":\"Alto\"},{\"value\":\"muy_alto\",\"label\":\"Muy alto\"}]', NULL, 0, 1, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (33, 7, 'Calidad del apoyo familiar o social', 'seleccion_unica', '[{\"value\":\"adecuado\",\"label\":\"Adecuado\"},{\"value\":\"limitado\",\"label\":\"Limitado\"},{\"value\":\"inexistente\",\"label\":\"Inexistente\"}]', NULL, 0, 2, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (34, 7, 'Estado de ánimo predominante', 'seleccion_unica', '[{\"value\":\"estable\",\"label\":\"Estable\"},{\"value\":\"ansioso\",\"label\":\"Ansioso\"},{\"value\":\"decaido\",\"label\":\"Decaído\"},{\"value\":\"irritable\",\"label\":\"Irritable\"},{\"value\":\"otro\",\"label\":\"Otro\"}]', NULL, 0, 3, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_preguntas` VALUES (35, 7, 'Comentarios adicionales relevantes para el plan de cuidado', 'texto_largo', NULL, 'Espacio libre para notas del paciente o del profesional.', 0, 4, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');

-- ----------------------------
-- Table structure for historia_respuestas
-- ----------------------------
DROP TABLE IF EXISTS `historia_respuestas`;
CREATE TABLE `historia_respuestas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `pregunta_id` int NOT NULL,
  `respuesta` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `respondido_por` int NULL DEFAULT NULL,
  `respondido_en` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_historia_respuestas_unica`(`paciente_id`, `pregunta_id`) USING BTREE,
  INDEX `idx_historia_respuestas_paciente`(`paciente_id`) USING BTREE,
  INDEX `idx_historia_respuestas_pregunta`(`pregunta_id`) USING BTREE,
  INDEX `idx_historia_respuestas_por`(`respondido_por`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 34 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of historia_respuestas
-- ----------------------------
INSERT INTO `historia_respuestas` VALUES (1, 1, 1, '120', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (2, 1, 2, '183', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (3, 1, 5, 'si', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (4, 1, 6, '1_2_semana', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (5, 1, 7, 'volibol', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (6, 1, 8, 'nunca', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (7, 1, 9, 'social', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (8, 1, 10, '5_6', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (9, 1, 11, 'buena', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (10, 1, 12, '[\"asma\",\"renal\",\"otras\"]', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (11, 1, 13, 'ninguna', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (12, 1, 14, 'no', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (13, 1, 15, 'apendice\nvesicula', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (14, 1, 16, 'no', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (15, 1, 17, 'Detalle del dolor', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (16, 1, 18, 'no', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (17, 1, 19, 'Detalle de diagnósticos', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (18, 1, 20, 'no', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (19, 1, 21, 'Listado de medicamentos actuales', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (20, 1, 22, '[\"medicamentos\",\"alimentos\"]', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (21, 1, 23, 'Detalle adicional sobre alergias', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (22, 1, 24, 'si', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (23, 1, 25, 'Vacunas', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (24, 1, 26, '[\"cardiopatia\",\"diabetes\",\"cancer\"]', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (25, 1, 27, 'Detalle de antecedentes familiares', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (26, 1, 28, 'no', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (27, 1, 29, '2025-10-07', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (28, 1, 30, 'Detalle de embarazos', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (29, 1, 31, 'ninguno', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (30, 1, 32, 'bajo', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (31, 1, 33, 'adecuado', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (32, 1, 34, 'estable', 1, '2025-10-07 07:02:58');
INSERT INTO `historia_respuestas` VALUES (33, 1, 35, 'Comentarios adicionales', 1, '2025-10-07 07:02:58');

-- ----------------------------
-- Table structure for historia_secciones
-- ----------------------------
DROP TABLE IF EXISTS `historia_secciones`;
CREATE TABLE `historia_secciones`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `orden` int NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_historia_secciones_orden`(`orden`) USING BTREE,
  INDEX `idx_historia_secciones_activo`(`activo`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of historia_secciones
-- ----------------------------
INSERT INTO `historia_secciones` VALUES (1, 'Información General del Paciente', 'Datos básicos para la valoración inicial y planificación de la consulta.', 1, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_secciones` VALUES (2, 'Hábitos y Estilo de Vida', 'Información sobre hábitos cotidianos que impactan en la salud del paciente.', 2, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_secciones` VALUES (3, 'Antecedentes Médicos Personales', 'Condiciones previas, hospitalizaciones y diagnósticos en seguimiento.', 3, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_secciones` VALUES (4, 'Medicamentos, Alergias y Vacunas', 'Registro de fármacos, alergias y esquemas de vacunación.', 4, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_secciones` VALUES (5, 'Antecedentes Familiares', 'Historia clínica relevante de familiares directos.', 5, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_secciones` VALUES (6, 'Salud Reproductiva', 'Aspectos relacionados con salud sexual y reproductiva, cuando aplique.', 6, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');
INSERT INTO `historia_secciones` VALUES (7, 'Factores Psicosociales y Bienestar', 'Evaluación de factores emocionales y soporte social.', 7, 1, '2025-10-06 23:58:09', '2025-10-06 23:58:09');

-- ----------------------------
-- Table structure for pacientes
-- ----------------------------
DROP TABLE IF EXISTS `pacientes`;
CREATE TABLE `pacientes`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombres` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `apellidos` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `email` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `foto_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `empresa_id` int NULL DEFAULT NULL,
  `somos` tinyint(1) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pacientes
-- ----------------------------
INSERT INTO `pacientes` VALUES (1, '16912337', 'JAVIER ALEJANDRO', 'PONCIANO', '1984-10-07', 'Masculino', '04144679693', 'MISION ABAJO', 'JPONCIANG@GMAIL.COM', NULL, '2025-09-01 04:14:11', NULL, NULL);
INSERT INTO `pacientes` VALUES (6, '6922095', 'Vicente Emilio', 'Alfonzo Marcano', '1966-10-12', 'Masculino', '04243081205', 'Urbanización Francisco Lazo Marti\r\nAvenida 07', 'telefonodevicente@gmail.com', '68b74e96675e7-17568435794756952897789118965955.jpg', '2025-09-02 20:07:50', NULL, NULL);
INSERT INTO `pacientes` VALUES (7, '16075887', 'Hector', 'Corro', '1984-07-12', 'Masculino', '04128710617', 'Ciudadela', NULL, '68b897a7bfb9c-17569278299038582488441905856490.jpg', '2025-09-03 19:31:51', NULL, NULL);
INSERT INTO `pacientes` VALUES (8, '26752296', 'Willian Isaac', 'Guzmán Madera', '1995-11-27', 'Masculino', '04121316123', 'misión arriba calle 8 con carrera 7', NULL, '68b9ab6086801-17569984838993059449144085255784.jpg', '2025-09-04 15:08:16', NULL, NULL);
INSERT INTO `pacientes` VALUES (9, '12345678', 'Noris', 'Barrios', '2025-09-12', 'Femenino', '', 'Calabozo', NULL, NULL, '2025-09-12 15:36:33', NULL, NULL);

-- ----------------------------
-- Table structure for paginas
-- ----------------------------
DROP TABLE IF EXISTS `paginas`;
CREATE TABLE `paginas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ruta` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `activo` tinyint(1) NULL DEFAULT 1,
  `grupo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'Otros',
  `orden` int NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of paginas
-- ----------------------------
INSERT INTO `paginas` VALUES (1, 'Pacientes', 'app_pacientes.php', 1, 'Administración', 1);
INSERT INTO `paginas` VALUES (3, 'Usuarios', 'app_usuarios.php', 1, 'Sistemas', 1);
INSERT INTO `paginas` VALUES (4, 'Reunión QR', 'app_demo.php', 1, 'Otros', 1);
INSERT INTO `paginas` VALUES (5, 'Validacion', 'app_atencion_admin.php', 1, 'Atención 24/7', 1);
INSERT INTO `paginas` VALUES (6, 'Atención', 'app_atencion_especialista.php', 1, 'Atención 24/7', 2);
INSERT INTO `paginas` VALUES (8, 'Gestion Roles', 'app_acceso_admin.php', 1, 'Sistemas', 2);
INSERT INTO `paginas` VALUES (9, 'Facturación', 'app_facturacion.php', 1, 'Administración', 2);
INSERT INTO `paginas` VALUES (10, 'Especialistas', 'app_especialistas.php', 1, 'Administración', 3);

-- ----------------------------
-- Table structure for plan_pagos
-- ----------------------------
DROP TABLE IF EXISTS `plan_pagos`;
CREATE TABLE `plan_pagos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `tipo_pago` enum('inscripcion','mensualidad') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `monto` decimal(10, 2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `referencia` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `periododesde` date NULL DEFAULT NULL,
  `periodohasta` date NULL DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_plan_pagos_paciente`(`paciente_id`) USING BTREE,
  INDEX `idx_plan_pagos_plan`(`plan_id`) USING BTREE,
  INDEX `idx_plan_pagos_tipo`(`tipo_pago`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plan_pagos
-- ----------------------------
INSERT INTO `plan_pagos` VALUES (5, 6, 3, 'inscripcion', 60.00, '2025-08-08', '123456', NULL, NULL, '2025-10-08 15:51:01');
INSERT INTO `plan_pagos` VALUES (6, 6, 3, 'mensualidad', 20.00, '2025-08-08', '1234567', '2025-08-08', '2025-09-07', '2025-10-08 15:51:14');

-- ----------------------------
-- Table structure for plan_suscripciones
-- ----------------------------
DROP TABLE IF EXISTS `plan_suscripciones`;
CREATE TABLE `plan_suscripciones`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `empresa_id` int NULL DEFAULT NULL,
  `fecha_inscripcion` date NOT NULL,
  `cobertura_inicio` date NULL DEFAULT NULL,
  `dias_espera` int NULL DEFAULT 45,
  `estado` enum('pendiente','espera','activa','suspendida','vencida','cancelada') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'pendiente',
  `activo` tinyint(1) NULL DEFAULT 1,
  `monto_mensual` decimal(10, 2) NULL DEFAULT NULL,
  `monto_afiliacion` decimal(10, 2) NULL DEFAULT NULL,
  `notas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `creado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_paciente`(`paciente_id`) USING BTREE,
  INDEX `idx_plan`(`plan_id`) USING BTREE,
  INDEX `idx_estado`(`estado`) USING BTREE,
  INDEX `idx_activo`(`activo`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plan_suscripciones
-- ----------------------------
INSERT INTO `plan_suscripciones` VALUES (2, 1, 2, NULL, '2025-10-01', '2025-11-15', 45, 'pendiente', 1, 40.00, 120.00, '', '2025-10-01 21:46:23', '2025-10-01 21:46:23');
INSERT INTO `plan_suscripciones` VALUES (3, 6, 3, NULL, '2025-10-05', '2025-11-19', 45, 'pendiente', 1, 20.00, 60.00, '', '2025-10-05 23:41:22', '2025-10-05 23:41:22');

-- ----------------------------
-- Table structure for plan_suscripciones_historial
-- ----------------------------
DROP TABLE IF EXISTS `plan_suscripciones_historial`;
CREATE TABLE `plan_suscripciones_historial`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `empresa_id` int NULL DEFAULT NULL,
  `fecha_inscripcion` date NOT NULL,
  `cobertura_inicio` date NULL DEFAULT NULL,
  `dias_espera` int NULL DEFAULT 45,
  `estado` enum('pendiente','espera','activa','suspendida','vencida','cancelada') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'pendiente',
  `activo` tinyint(1) NULL DEFAULT 1,
  `monto_mensual` decimal(10, 2) NULL DEFAULT NULL,
  `monto_afiliacion` decimal(10, 2) NULL DEFAULT NULL,
  `notas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `creado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_paciente`(`paciente_id`) USING BTREE,
  INDEX `idx_plan`(`plan_id`) USING BTREE,
  INDEX `idx_estado`(`estado`) USING BTREE,
  INDEX `idx_activo`(`activo`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plan_suscripciones_historial
-- ----------------------------

-- ----------------------------
-- Table structure for planes
-- ----------------------------
DROP TABLE IF EXISTS `planes`;
CREATE TABLE `planes`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa_id` int NOT NULL,
  `clave` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cuota_afiliacion` decimal(10, 2) NULL DEFAULT 0.00,
  `costo_mensual` decimal(10, 2) NULL DEFAULT 0.00,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `empresa_id`(`empresa_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of planes
-- ----------------------------
INSERT INTO `planes` VALUES (1, 1, 'premium', 'Premium', 60.00, 180.00);
INSERT INTO `planes` VALUES (2, 1, 'plus', 'Plus', 60.00, 36.00);
INSERT INTO `planes` VALUES (3, 1, 'salud', 'Salud', 60.00, 21.00);

-- ----------------------------
-- Table structure for planes_limites
-- ----------------------------
DROP TABLE IF EXISTS `planes_limites`;
CREATE TABLE `planes_limites`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `plan_premium` int NULL DEFAULT 0,
  `plan_plus` int NULL DEFAULT 0,
  `plan_salud` int NULL DEFAULT 0,
  `fecha_actualizacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of planes_limites
-- ----------------------------
INSERT INTO `planes_limites` VALUES (1, 'Atenciones Primarias', 10, 7, 5, '2025-09-28 12:53:56');
INSERT INTO `planes_limites` VALUES (2, 'Laboratorios', 5, 2, 2, '2025-09-28 12:53:56');
INSERT INTO `planes_limites` VALUES (3, 'Consultas por Emergencia', 10, 7, 5, '2025-09-28 12:53:56');
INSERT INTO `planes_limites` VALUES (4, 'Observaciones por emergencia', 7, 5, 5, '2025-09-28 12:53:56');
INSERT INTO `planes_limites` VALUES (5, 'Cirugías menores', 4, 2, 1, '2025-09-28 12:53:56');
INSERT INTO `planes_limites` VALUES (6, 'Consultas con Especialistas', 6, 3, 2, '2025-09-28 12:53:56');
INSERT INTO `planes_limites` VALUES (7, 'Inmovilizaciones', 2, 2, 1, '2025-09-28 12:53:56');
INSERT INTO `planes_limites` VALUES (8, 'Ecografías', 6, 2, 1, '2025-09-28 12:53:56');
INSERT INTO `planes_limites` VALUES (9, 'Rayos X', 10, 4, 2, '2025-09-28 12:53:56');
INSERT INTO `planes_limites` VALUES (10, 'Descuento en cirugías (%)', 40, 30, 20, '2025-09-28 12:54:57');
INSERT INTO `planes_limites` VALUES (11, 'Consulta Odontológica', 3, 2, 1, '2025-09-28 12:54:57');
INSERT INTO `planes_limites` VALUES (12, 'Limpieza Profunda', 3, 2, 1, '2025-09-28 12:54:57');
INSERT INTO `planes_limites` VALUES (13, 'Atención médica domiciliaria', 1, 0, 0, '2025-09-28 12:54:57');
INSERT INTO `planes_limites` VALUES (14, 'Traslado de Ambulancia (%)', 50, 30, 20, '2025-09-28 12:54:57');
INSERT INTO `planes_limites` VALUES (15, 'Hospitalización (%)', 40, 30, 20, '2025-09-28 12:54:57');

-- ----------------------------
-- Table structure for rol_permisos
-- ----------------------------
DROP TABLE IF EXISTS `rol_permisos`;
CREATE TABLE `rol_permisos`  (
  `rol_id` int NOT NULL,
  `permiso_id` int NOT NULL,
  PRIMARY KEY (`rol_id`, `permiso_id`) USING BTREE,
  INDEX `permiso_id`(`permiso_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of rol_permisos
-- ----------------------------
INSERT INTO `rol_permisos` VALUES (1, 1);
INSERT INTO `rol_permisos` VALUES (1, 3);
INSERT INTO `rol_permisos` VALUES (1, 4);
INSERT INTO `rol_permisos` VALUES (1, 5);
INSERT INTO `rol_permisos` VALUES (1, 6);
INSERT INTO `rol_permisos` VALUES (1, 7);
INSERT INTO `rol_permisos` VALUES (1, 8);
INSERT INTO `rol_permisos` VALUES (1, 9);
INSERT INTO `rol_permisos` VALUES (1, 10);
INSERT INTO `rol_permisos` VALUES (2, 4);
INSERT INTO `rol_permisos` VALUES (2, 6);
INSERT INTO `rol_permisos` VALUES (4, 1);
INSERT INTO `rol_permisos` VALUES (4, 4);
INSERT INTO `rol_permisos` VALUES (4, 5);
INSERT INTO `rol_permisos` VALUES (4, 9);

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `nombre`(`nombre`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, 'Administrador', 'Acceso total al sistema');
INSERT INTO `roles` VALUES (2, 'Especialista', 'Acceso a consultas y pacientes propios');
INSERT INTO `roles` VALUES (3, 'Recepcionista', 'Gestión de agenda y pacientes');
INSERT INTO `roles` VALUES (4, 'Facturación', 'Acceso a módulo de facturación');
INSERT INTO `roles` VALUES (5, 'Estandar', 'Acceso limitado solo a consulta propia');

-- ----------------------------
-- Table structure for servicios_consumidos
-- ----------------------------
DROP TABLE IF EXISTS `servicios_consumidos`;
CREATE TABLE `servicios_consumidos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NULL DEFAULT NULL,
  `tipo_servicio` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `notas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `cantidad` int NULL DEFAULT NULL,
  `realizado_por` int NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `codigo_limite` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of servicios_consumidos
-- ----------------------------
INSERT INTO `servicios_consumidos` VALUES (1, 6, 'Atenciones Primarias', NULL, 1, 1, '2025-10-08 23:37:58', 'registrado', 'atenciones_primarias');
INSERT INTO `servicios_consumidos` VALUES (2, 6, 'Inmovilizaciones', NULL, 1, 1, '2025-10-08 23:37:58', 'registrado', 'inmovilizaciones');
INSERT INTO `servicios_consumidos` VALUES (3, 6, 'Rayos X', NULL, 1, 1, '2025-10-08 23:37:58', 'registrado', 'rayos_x');
INSERT INTO `servicios_consumidos` VALUES (4, 6, 'Atenciones Primarias', NULL, 1, 1, '2025-10-08 23:48:46', 'registrado', 'atenciones_primarias');
INSERT INTO `servicios_consumidos` VALUES (5, 6, 'Consultas por Emergencia', NULL, 1, 1, '2025-10-08 23:48:46', 'registrado', 'consultas_por_emergencia');
INSERT INTO `servicios_consumidos` VALUES (6, 6, 'Observaciones por emergencia', NULL, 1, 1, '2025-10-08 23:48:46', 'registrado', 'observaciones_por_emergencia');

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre_completo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `rol` int NOT NULL,
  `fecha_creacion` datetime NULL DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_rol`(`rol`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of usuarios
-- ----------------------------
INSERT INTO `usuarios` VALUES (1, NULL, 'salud', '$2y$10$fLrfiVKJkW.nYebiGDzzCuGaBV6J4GwD/Ya5DMYxBEjz1BRPyvEhi', 'Administrador de Usuarios', NULL, 1, '2025-08-30 18:59:23', NULL);
INSERT INTO `usuarios` VALUES (3, 'V18405531', 'AVALERA', '$2y$10$R87E2CUX/AVGRaTuUINZyuqqHpCLM5BJF3DPSjLXCOzfAdtO2tdgi', 'ANGEL RAFAEL VALERA CAMPOS', '04123488361', 2, NULL, NULL);
INSERT INTO `usuarios` VALUES (4, 'V14926235', 'AOVIEDO', '$2y$10$yC0pkYpefqQqil6ponvt3ugtSn5inW1Cfemn.2qqC0Oa9CjbvZK8a', 'ANAIS JOSEFINA OVIEDO GUTIERREZ', '04127787173', 2, NULL, NULL);
INSERT INTO `usuarios` VALUES (5, 'V21280565', 'FTOVAR', '$2y$10$2PheH7f1zeW0q0IZc71GIummZfAnVUj9EzIovL690D837lny3/laC', 'FELIXMAR DEL VALLE TOVAR', '04144587470', 2, NULL, NULL);
INSERT INTO `usuarios` VALUES (6, 'V19246026', 'RMARTINEZ', '$2y$10$Lm5m8o9935dJMSUCJo/z4u1XRLa2R1dD5awhUsokjdRoyHfWkzwxK', 'RUBEN ELIAS MARTINEZ BARRETO', '04129061888', 2, NULL, NULL);
INSERT INTO `usuarios` VALUES (7, 'V17602635', 'MCELIS', '$2y$10$wfcLVnVlx0YHA39BRaTF5uyRuBijnARpPK5bopYXswJnxA92y6o0i', 'MARIA DEL CARMEN CELIS ALFONZO', '04165493174', 2, NULL, NULL);
INSERT INTO `usuarios` VALUES (8, 'V03835680', 'YURBINA', '$2y$10$Fk52pFFz7XgEk6hf7FunNu2sKBuJmhqSgQjy7mDYuP3ldmdz052Xy', 'YAJAIRA COROMOTO URBINA', '04144786461', 2, NULL, NULL);
INSERT INTO `usuarios` VALUES (9, 'V24662770', 'MPARABABI', '$2y$10$AL5wRJyf0Dnck3U8TJEb5OOhYO96V8wJCN2FYqrSux0P5yEAmpvOC', 'MARIA GABRIELA PARABABI FUNES', '04124115387', 2, NULL, NULL);
INSERT INTO `usuarios` VALUES (13, 'V20906396', 'KSEMINARIO', '$2y$10$EA3wY/kxfmu8VJ/jnPNHSO0/0EyUTmsfpLG107NeBL1zEW2HGKVJq', 'KARINA DEL CARMEN SERMINARIO A', '04122041337', 2, NULL, NULL);
INSERT INTO `usuarios` VALUES (14, 'V18908202', 'RMUJICA', '$2y$10$nW8jA.KdFX80ekavHtJs2uV2eUu/tCLjwsA/oNsZ5.rZrEUsxGRIC', 'RUTH ENIDDARI MUJICA PADILLA', '04243637382', 2, NULL, NULL);
INSERT INTO `usuarios` VALUES (15, '', 'KSEMINARIO', '$2y$10$aabSrnha0OMOWmivUHgvgeAzgwGvXVrafTtgXPMU6tGG4a0If37i6', 'KARINA DEL CARMEN SEMINARIO', '04142041337', 2, NULL, NULL);

-- ----------------------------
-- View structure for seguros_consumos
-- ----------------------------
DROP VIEW IF EXISTS `seguros_consumos`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `seguros_consumos` AS select `sc`.`id` AS `id`,`sc`.`paciente_id` AS `id_paciente`,`sc`.`tipo_servicio` AS `tipo`,`sc`.`notas` AS `detalle`,`sc`.`cantidad` AS `cantidad`,`sc`.`realizado_por` AS `id_especialista`,`sc`.`created_at` AS `fecha` from `servicios_consumidos` `sc` where (`sc`.`estado` = 'registrado');

-- ----------------------------
-- View structure for seguros_limites
-- ----------------------------
DROP VIEW IF EXISTS `seguros_limites`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `seguros_limites` AS select `l`.`id` AS `id`,lower(replace(`l`.`descripcion`,' ','_')) AS `codigo`,`l`.`descripcion` AS `nombre`,`p`.`nombre` AS `plan_nombre`,(case `p`.`clave` when 'premium' then `l`.`plan_premium` when 'plus' then `l`.`plan_plus` when 'salud' then `l`.`plan_salud` else 0 end) AS `maximo` from (`planes_limites` `l` join `planes` `p`) where (((`p`.`clave` = 'premium') and (`l`.`plan_premium` > 0)) or ((`p`.`clave` = 'plus') and (`l`.`plan_plus` > 0)) or ((`p`.`clave` = 'salud') and (`l`.`plan_salud` > 0)));

-- ----------------------------
-- View structure for seguros_planes
-- ----------------------------
DROP VIEW IF EXISTS `seguros_planes`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `seguros_planes` AS select `ps`.`id` AS `id`,`ps`.`paciente_id` AS `id_paciente`,`p`.`nombre` AS `plan_nombre`,`ps`.`estado` AS `estado_plan`,`ps`.`fecha_inscripcion` AS `fecha_inscripcion`,`ps`.`cobertura_inicio` AS `fecha_inicio_cobertura`,`ps`.`monto_mensual` AS `monto_mensual`,`ps`.`activo` AS `activo` from (`plan_suscripciones` `ps` join `planes` `p` on((`p`.`id` = `ps`.`plan_id`))) where (`ps`.`activo` = 1);

-- ----------------------------
-- View structure for vw_consumos_agregados
-- ----------------------------
DROP VIEW IF EXISTS `vw_consumos_agregados`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `vw_consumos_agregados` AS select `sc`.`paciente_id` AS `paciente_id`,lower(replace(`pl`.`descripcion`,' ','_')) AS `codigo`,sum(`sc`.`cantidad`) AS `usado` from (`servicios_consumidos` `sc` left join `planes_limites` `pl` on((lower(replace(`pl`.`descripcion`,' ','_')) = `sc`.`codigo_limite`))) where (`sc`.`estado` = 'registrado') group by `sc`.`paciente_id`,`codigo`;

-- ----------------------------
-- View structure for vw_demo_asistentes
-- ----------------------------
DROP VIEW IF EXISTS `vw_demo_asistentes`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `vw_demo_asistentes` AS select `a`.`id` AS `id`,`a`.`fecha_hora` AS `fecha_hora`,`r`.`id` AS `id_reunion`,`r`.`titulo` AS `titulo`,`p`.`id` AS `id_participante`,`p`.`nombre` AS `nombre` from ((`demo_asistencias` `a` join `demo_reuniones` `r` on((`r`.`id` = `a`.`id_reunion`))) join `demo_participantes` `p` on((`p`.`id` = `a`.`id_participante`)));

-- ----------------------------
-- View structure for vw_paciente_limites
-- ----------------------------
DROP VIEW IF EXISTS `vw_paciente_limites`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `vw_paciente_limites` AS select `ps`.`paciente_id` AS `paciente_id`,`p`.`nombre` AS `plan_nombre`,lower(replace(`pl`.`descripcion`,' ','_')) AS `codigo`,`pl`.`descripcion` AS `nombre_limite`,(case `p`.`clave` when 'premium' then `pl`.`plan_premium` when 'plus' then `pl`.`plan_plus` when 'salud' then `pl`.`plan_salud` else 0 end) AS `maximo`,coalesce(`ca`.`usado`,0) AS `usado`,greatest(0,((case `p`.`clave` when 'premium' then `pl`.`plan_premium` when 'plus' then `pl`.`plan_plus` when 'salud' then `pl`.`plan_salud` else 0 end) - coalesce(`ca`.`usado`,0))) AS `restante` from (((`plan_suscripciones` `ps` join `planes` `p` on((`p`.`id` = `ps`.`plan_id`))) join `planes_limites` `pl`) left join `vw_consumos_agregados` `ca` on(((`ca`.`paciente_id` = `ps`.`paciente_id`) and (`ca`.`codigo` = lower(replace(`pl`.`descripcion`,' ','_')))))) where ((`ps`.`activo` = 1) and (`ps`.`estado` in ('activa','espera','pendiente')));

-- ----------------------------
-- View structure for vw_suscripciones_estado
-- ----------------------------
DROP VIEW IF EXISTS `vw_suscripciones_estado`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `vw_suscripciones_estado` AS select `ps`.`id` AS `id`,`ps`.`paciente_id` AS `paciente_id`,`ps`.`plan_id` AS `plan_id`,(to_days(`ps`.`cobertura_inicio`) - to_days(curdate())) AS `dias_para_cobertura`,(case when ((`ps`.`cobertura_inicio` is not null) and (`ps`.`cobertura_inicio` <= curdate()) and (`ps`.`estado` in ('pendiente','espera'))) then 'activa_pendiente_marcar' else `ps`.`estado` end) AS `estado_calculado` from `plan_suscripciones` `ps` where (`ps`.`activo` = 1);

SET FOREIGN_KEY_CHECKS = 1;
