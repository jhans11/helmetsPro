-- Script para crear tabla de usuarios clientes
-- Helmets Pro v2.0 - Sistema de Usuarios Clientes
-- Fecha: 2024

-- Crear tabla de usuarios clientes
CREATE TABLE IF NOT EXISTS `usuarios_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
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
  `fecha_registro` timestamp DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario`),
  KEY `idx_email` (`email`),
  KEY `idx_activo` (`activo`),
  KEY `idx_verificado` (`verificado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de direcciones de envío
CREATE TABLE IF NOT EXISTS `direcciones_envio` (
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
  `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_es_principal` (`es_principal`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_clientes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de historial de sesiones
CREATE TABLE IF NOT EXISTS `sesiones_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `fecha_inicio` timestamp DEFAULT CURRENT_TIMESTAMP,
  `fecha_fin` datetime DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_activa` (`activa`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_clientes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de logs de acceso de clientes
CREATE TABLE IF NOT EXISTS `logs_acceso_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `tipo_acceso` enum('login_exitoso','login_fallido','logout','registro','verificacion_email') NOT NULL,
  `detalles` text DEFAULT NULL,
  `fecha_acceso` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_fecha_acceso` (`fecha_acceso`),
  KEY `idx_tipo_acceso` (`tipo_acceso`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_clientes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuarios de ejemplo
-- Contraseña: "cliente123" (hash generado con password_hash)
INSERT INTO `usuarios_clientes` (`usuario`, `password_hash`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `ciudad`, `codigo_postal`, `verificado`, `activo`) VALUES
('juan.perez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan', 'Pérez', 'juan.perez@email.com', '555-0101', 'Av. Reforma 123', 'Ciudad de México', '06000', 1, 1),
('maria.garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'García', 'maria.garcia@email.com', '555-0202', 'Calle Juárez 456', 'Guadalajara', '44100', 1, 1),
('carlos.lopez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'López', 'carlos.lopez@email.com', '555-0303', 'Blvd. Constitución 789', 'Monterrey', '64000', 0, 1);

-- Insertar direcciones de envío de ejemplo
INSERT INTO `direcciones_envio` (`usuario_id`, `nombre_direccion`, `nombre_completo`, `telefono`, `direccion`, `ciudad`, `estado`, `codigo_postal`, `es_principal`) VALUES
(1, 'Casa', 'Juan Pérez', '555-0101', 'Av. Reforma 123, Col. Centro', 'Ciudad de México', 'CDMX', '06000', 1),
(1, 'Trabajo', 'Juan Pérez', '555-0101', 'Paseo de la Reforma 500, Col. Juárez', 'Ciudad de México', 'CDMX', '06600', 0),
(2, 'Casa', 'María García', '555-0202', 'Calle Juárez 456, Col. Americana', 'Guadalajara', 'Jalisco', '44100', 1);

-- Comentarios sobre la estructura:
-- password_hash: Almacena hash bcrypt de la contraseña (60 caracteres)
-- activo: Controla si el usuario puede acceder (1=activo, 0=inactivo)
-- verificado: Indica si el email ha sido verificado (1=verificado, 0=no verificado)
-- token_verificacion: Token único para verificar email
-- ultimo_acceso: Registra la última vez que el usuario inició sesión
-- es_principal: Indica si es la dirección principal de envío
-- tipo_acceso: Tipos de eventos de acceso registrados
