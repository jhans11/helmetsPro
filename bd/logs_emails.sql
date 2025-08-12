-- Tabla para registrar logs de emails enviados
CREATE TABLE IF NOT EXISTS logs_emails (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT,
    tipo VARCHAR(50) NOT NULL COMMENT 'confirmacion, cambio_estado, etc.',
    destinatario VARCHAR(255) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    exitoso TINYINT(1) DEFAULT 1,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    error_mensaje TEXT,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE SET NULL,
    INDEX idx_pedido_fecha (id_pedido, fecha_envio),
    INDEX idx_tipo_fecha (tipo, fecha_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
