-- Script para crear tabla de usuarios administradores
-- Helmets Pro v2.0 - Sistema de Autenticación Seguro

-- Crear tabla de usuarios administradores
CREATE TABLE IF NOT EXISTS `usuarios_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rol` enum('admin','editor','viewer') DEFAULT 'admin',
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario administrador por defecto
-- Contraseña: "admin123" (hash generado con password_hash)
INSERT INTO `usuarios_admin` (`usuario`, `password_hash`, `nombre_completo`, `email`, `rol`, `activo`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'admin@helmetspro.com', 'admin', 1),
('jhans', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jhans Jiménez', 'jhans@helmetspro.com', 'admin', 1);

-- Crear tabla de logs de acceso (opcional para auditoría)
CREATE TABLE IF NOT EXISTS `logs_acceso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `tipo_acceso` enum('login_exitoso','login_fallido','logout') NOT NULL,
  `fecha_acceso` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_fecha_acceso` (`fecha_acceso`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_admin`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentarios sobre la estructura:
-- password_hash: Almacena hash bcrypt de la contraseña (60 caracteres)
-- activo: Controla si el usuario puede acceder (1=activo, 0=inactivo)
-- ultimo_acceso: Registra la última vez que el usuario inició sesión
-- rol: Define permisos del usuario (admin, editor, viewer) 