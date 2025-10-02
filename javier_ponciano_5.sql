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

 Date: 01/10/2025 22:39:57
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
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of atencion_procesos
-- ----------------------------

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
) ENGINE = MyISAM AUTO_INCREMENT = 18 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of consultas
-- ----------------------------

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
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Fixed;

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
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_reunion`(`id_reunion`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of demo_participantes
-- ----------------------------

-- ----------------------------
-- Table structure for demo_reuniones
-- ----------------------------
DROP TABLE IF EXISTS `demo_reuniones`;
CREATE TABLE `demo_reuniones`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

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
) ENGINE = MyISAM AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pacientes
-- ----------------------------
INSERT INTO `pacientes` VALUES (1, '16912337', 'JAVIER ALEJANDRO', 'PONCIANO', '1984-10-07', 'Masculino', '04144679693', 'MISION ABAJO', 'JPONCIANG@GMAIL.COM', NULL, '2025-07-01 04:14:11', NULL, NULL);
INSERT INTO `pacientes` VALUES (6, '6922095', 'Vicente Emilio', 'Alfonzo Marcano', '1966-10-12', 'Masculino', '04243081205', 'Urbanización Francisco Lazo Marti\r\nAvenida 07', 'telefonodevicente@gmail.com', '68b74e96675e7-17568435794756952897789118965955.jpg', '2025-09-02 20:07:50', NULL, NULL);
INSERT INTO `pacientes` VALUES (7, '16075887', 'Hector', 'Corro', '1984-07-12', 'Masculino', '04128710617', 'Ciudadela', NULL, '68b897a7bfb9c-17569278299038582488441905856490.jpg', '2025-09-03 19:31:51', NULL, NULL);
INSERT INTO `pacientes` VALUES (8, '26752296', 'Willian Isaac', 'Guzmán Madera', '1995-11-27', 'Masculino', '04121316123', 'misión arriba calle 8 con carrera 7', NULL, '68b9ab6086801-17569984838993059449144085255784.jpg', '2025-09-04 15:08:16', NULL, NULL);
INSERT INTO `pacientes` VALUES (9, '12345678', 'Noris', 'Barrios', '2025-09-12', 'Femenino', '', 'Calabozo', NULL, NULL, '2025-09-12 15:36:33', NULL, NULL);
INSERT INTO `pacientes` VALUES (10, '23123123', 'JAVIER ALEJANDRO', 'Ponciano Olivo', '2025-09-30', 'Masculino', '04144679693', 'CASCO CENTRAL, CARRERA 12', NULL, '68dc822b7a652-captura.png', '2025-09-30 21:21:47', NULL, NULL);

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
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plan_pagos
-- ----------------------------
INSERT INTO `plan_pagos` VALUES (1, 1, 2, 'inscripcion', 20.00, '2025-10-02', '', NULL, NULL, '2025-10-01 22:00:32');
INSERT INTO `plan_pagos` VALUES (2, 1, 2, 'inscripcion', 100.00, '2025-10-02', '', NULL, NULL, '2025-10-01 22:28:03');
INSERT INTO `plan_pagos` VALUES (3, 1, 2, 'mensualidad', 40.00, '2025-10-02', '0202523', NULL, NULL, '2025-10-01 22:29:56');

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
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plan_suscripciones
-- ----------------------------
INSERT INTO `plan_suscripciones` VALUES (2, 1, 2, NULL, '2025-10-01', '2025-11-15', 45, 'pendiente', 1, 40.00, 120.00, '', '2025-10-01 21:46:23', '2025-10-01 21:46:23');

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
INSERT INTO `plan_suscripciones_historial` VALUES (2, 1, 2, NULL, '2025-10-01', '2025-11-15', 45, 'pendiente', 1, 40.00, 120.00, '', '2025-10-01 21:46:23', '2025-10-01 21:46:23');

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
INSERT INTO `planes` VALUES (1, 1, 'premium', 'Premium', 180.00, 60.00);
INSERT INTO `planes` VALUES (2, 1, 'plus', 'Plus', 120.00, 40.00);
INSERT INTO `planes` VALUES (3, 1, 'salud', 'Salud', 60.00, 20.00);

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
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of servicios_consumidos
-- ----------------------------

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
  `rol` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `fecha_creacion` datetime NULL DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of usuarios
-- ----------------------------
INSERT INTO `usuarios` VALUES (1, NULL, 'salud', '$2y$10$fLrfiVKJkW.nYebiGDzzCuGaBV6J4GwD/Ya5DMYxBEjz1BRPyvEhi', 'Administrador de Usuarios', NULL, 'admin_usuarios', '2025-08-30 18:59:23', NULL);
INSERT INTO `usuarios` VALUES (3, 'V18405531', 'AVALERA', '$2y$10$Q2LY2ytPSmyR.5I8t.vFDeYgyDo3Ei5KR7skOyNuA2JU26HwA6MLC', 'ANGEL RAFAEL VALERA CAMPOS', '04123488361', 'especialista', NULL, NULL);
INSERT INTO `usuarios` VALUES (4, 'V14926235', 'AOVIEDO', '$2y$10$yC0pkYpefqQqil6ponvt3ugtSn5inW1Cfemn.2qqC0Oa9CjbvZK8a', 'ANAIS JOSEFINA OVIEDO GUTIERREZ', '04127787173', 'especialista', NULL, NULL);
INSERT INTO `usuarios` VALUES (5, 'V21280565', 'FTOVAR', '$2y$10$2PheH7f1zeW0q0IZc71GIummZfAnVUj9EzIovL690D837lny3/laC', 'FELIXMAR DEL VALLE TOVAR', '04144587470', 'especialista', NULL, NULL);
INSERT INTO `usuarios` VALUES (6, 'V19246026', 'RMARTINEZ', '$2y$10$Lm5m8o9935dJMSUCJo/z4u1XRLa2R1dD5awhUsokjdRoyHfWkzwxK', 'RUBEN ELIAS MARTINEZ BARRETO', '04129061888', 'especialista', NULL, NULL);
INSERT INTO `usuarios` VALUES (7, 'V17602635', 'MCELIS', '$2y$10$wfcLVnVlx0YHA39BRaTF5uyRuBijnARpPK5bopYXswJnxA92y6o0i', 'MARIA DEL CARMEN CELIS ALFONZO', '04165493174', 'especialista', NULL, NULL);
INSERT INTO `usuarios` VALUES (8, 'V03835680', 'YURBINA', '$2y$10$Fk52pFFz7XgEk6hf7FunNu2sKBuJmhqSgQjy7mDYuP3ldmdz052Xy', 'YAJAIRA COROMOTO URBINA', '04144786461', 'especialista', NULL, NULL);
INSERT INTO `usuarios` VALUES (9, 'V24662770', 'MPARABABI', '$2y$10$AL5wRJyf0Dnck3U8TJEb5OOhYO96V8wJCN2FYqrSux0P5yEAmpvOC', 'MARIA GABRIELA PARABABI FUNES', '04124115387', 'especialista', NULL, NULL);
INSERT INTO `usuarios` VALUES (13, 'V20906396', 'KSEMINARIO', '$2y$10$EA3wY/kxfmu8VJ/jnPNHSO0/0EyUTmsfpLG107NeBL1zEW2HGKVJq', 'KARINA DEL CARMEN SERMINARIO A', '04122041337', 'especialista', NULL, NULL);
INSERT INTO `usuarios` VALUES (14, 'V18908202', 'RMUJICA', '$2y$10$nW8jA.KdFX80ekavHtJs2uV2eUu/tCLjwsA/oNsZ5.rZrEUsxGRIC', 'RUTH ENIDDARI MUJICA PADILLA', '04243637382', 'especialista', NULL, NULL);
INSERT INTO `usuarios` VALUES (15, '', 'KSEMINARIO', '$2y$10$aabSrnha0OMOWmivUHgvgeAzgwGvXVrafTtgXPMU6tGG4a0If37i6', 'KARINA DEL CARMEN SEMINARIO', '04142041337', 'especialista', NULL, NULL);

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
