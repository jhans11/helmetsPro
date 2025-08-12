-- Backup de Base de Datos - Helmets Pro
-- Fecha: 2025-08-10 18:00:16

-- Estructura de tabla: usuarios_clientes
CREATE TABLE `usuarios_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `pais` varchar(50) DEFAULT 'México',
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F','O') DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `verificado` tinyint(1) DEFAULT 0,
  `token_verificacion` varchar(255) DEFAULT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_usuario` (`usuario`),
  KEY `idx_email` (`email`),
  KEY `idx_activo` (`activo`),
  KEY `idx_verificado` (`verificado`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de tabla: usuarios_clientes
INSERT INTO `usuarios_clientes` (`id`, `usuario`, `password_hash`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `ciudad`, `codigo_postal`, `pais`, `fecha_nacimiento`, `genero`, `activo`, `verificado`, `token_verificacion`, `ultimo_acceso`, `fecha_registro`, `fecha_actualizacion`) VALUES ('1', 'juan.perez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan', 'Pérez', 'juan.perez@email.com', '555-0101', 'Av. Reforma 123', 'Ciudad de México', '06000', 'México', '', '', '1', '1', '', '', '2025-08-08 14:41:20', '2025-08-08 14:41:20');
INSERT INTO `usuarios_clientes` (`id`, `usuario`, `password_hash`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `ciudad`, `codigo_postal`, `pais`, `fecha_nacimiento`, `genero`, `activo`, `verificado`, `token_verificacion`, `ultimo_acceso`, `fecha_registro`, `fecha_actualizacion`) VALUES ('2', 'maria.garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'García', 'maria.garcia@email.com', '555-0202', 'Calle Juárez 456', 'Guadalajara', '44100', 'México', '', '', '1', '1', '', '', '2025-08-08 14:41:20', '2025-08-08 14:41:20');
INSERT INTO `usuarios_clientes` (`id`, `usuario`, `password_hash`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `ciudad`, `codigo_postal`, `pais`, `fecha_nacimiento`, `genero`, `activo`, `verificado`, `token_verificacion`, `ultimo_acceso`, `fecha_registro`, `fecha_actualizacion`) VALUES ('3', 'carlos.lopez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'López', 'carlos.lopez@email.com', '555-0303', 'Blvd. Constitución 789', 'Monterrey', '64000', 'México', '', '', '1', '0', '', '', '2025-08-08 14:41:20', '2025-08-08 14:41:20');
INSERT INTO `usuarios_clientes` (`id`, `usuario`, `password_hash`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `ciudad`, `codigo_postal`, `pais`, `fecha_nacimiento`, `genero`, `activo`, `verificado`, `token_verificacion`, `ultimo_acceso`, `fecha_registro`, `fecha_actualizacion`) VALUES ('4', '', '$2y$10$HyAzTVZKBpewE0t3uSyOLO8IafeBp955n.l4Nm8Y6DurL2IqB9dx2', 'Test', 'User', 'test@debug.com', '123456789', 'Test Address', 'Test City', '12345', 'México', '', '', '1', '0', '3d7263436603bbcae54bf77aebead52ed333d22a8c0c5251e7e2d0dd3afac175', '', '2025-08-09 23:36:32', '2025-08-09 23:36:32');
INSERT INTO `usuarios_clientes` (`id`, `usuario`, `password_hash`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `ciudad`, `codigo_postal`, `pais`, `fecha_nacimiento`, `genero`, `activo`, `verificado`, `token_verificacion`, `ultimo_acceso`, `fecha_registro`, `fecha_actualizacion`) VALUES ('11', 'testweb1754801274examplecom', '$2y$10$6t1IedV1vKExiL3VZ0ek/epaLi.aV11Ayo.iVDRqeArDH/ghktE0S', 'Test Web', 'User', 'testweb1754801274@example.com', '123456789', 'Test Address', 'Test City', '12345', 'México', '', '', '1', '0', '056c0f5e0078f2e8a4a7195454746b798ae3630d5751532b8a8c425671cf5dec', '', '2025-08-09 23:47:54', '2025-08-09 23:47:54');
INSERT INTO `usuarios_clientes` (`id`, `usuario`, `password_hash`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `ciudad`, `codigo_postal`, `pais`, `fecha_nacimiento`, `genero`, `activo`, `verificado`, `token_verificacion`, `ultimo_acceso`, `fecha_registro`, `fecha_actualizacion`) VALUES ('12', 'deibyokgmailcom', '$2y$10$qc9x6LJiUF89owmNyaIFduW5h0y.RcH9o.VaSs9omxTetH60k.lHO', 'JHANS DEIBY', 'ECHAVARRIA JIMENEZ', 'deibyok@gmail.com', '3242597162', 'CLL 77 # 25-60', 'Medellín (Antioquia)', '050005', 'México', '', '', '1', '0', 'cfa3f4b15a387a5812d4c994c769e7c1b021bb241ee94020d698541fe0c9ec09', '', '2025-08-09 23:49:45', '2025-08-09 23:49:45');

-- Estructura de tabla: direcciones_envio
CREATE TABLE `direcciones_envio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nombre_direccion` varchar(100) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` text NOT NULL,
  `ciudad` varchar(50) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `pais` varchar(50) DEFAULT 'México',
  `es_principal` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_es_principal` (`es_principal`),
  CONSTRAINT `direcciones_envio_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de tabla: direcciones_envio
INSERT INTO `direcciones_envio` (`id`, `usuario_id`, `nombre_direccion`, `nombre_completo`, `telefono`, `direccion`, `ciudad`, `estado`, `codigo_postal`, `pais`, `es_principal`, `activo`, `fecha_creacion`) VALUES ('1', '1', 'Casa', 'Juan Pérez', '555-0101', 'Av. Reforma 123, Col. Centro', 'Ciudad de México', 'CDMX', '06000', 'México', '1', '1', '2025-08-08 14:41:22');
INSERT INTO `direcciones_envio` (`id`, `usuario_id`, `nombre_direccion`, `nombre_completo`, `telefono`, `direccion`, `ciudad`, `estado`, `codigo_postal`, `pais`, `es_principal`, `activo`, `fecha_creacion`) VALUES ('2', '1', 'Trabajo', 'Juan Pérez', '555-0101', 'Paseo de la Reforma 500, Col. Juárez', 'Ciudad de México', 'CDMX', '06600', 'México', '0', '1', '2025-08-08 14:41:22');
INSERT INTO `direcciones_envio` (`id`, `usuario_id`, `nombre_direccion`, `nombre_completo`, `telefono`, `direccion`, `ciudad`, `estado`, `codigo_postal`, `pais`, `es_principal`, `activo`, `fecha_creacion`) VALUES ('3', '2', 'Casa', 'María García', '555-0202', 'Calle Juárez 456, Col. Americana', 'Guadalajara', 'Jalisco', '44100', 'México', '1', '1', '2025-08-08 14:41:22');

-- Estructura de tabla: sesiones_clientes
CREATE TABLE `sesiones_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_activa` (`activa`),
  CONSTRAINT `sesiones_clientes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de tabla: logs_acceso_clientes
CREATE TABLE `logs_acceso_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `tipo_acceso` enum('login_exitoso','login_fallido','logout','registro','verificacion_email') NOT NULL,
  `detalles` text DEFAULT NULL,
  `fecha_acceso` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_fecha_acceso` (`fecha_acceso`),
  KEY `idx_tipo_acceso` (`tipo_acceso`),
  CONSTRAINT `logs_acceso_clientes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

