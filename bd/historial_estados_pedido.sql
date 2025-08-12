-- Tabla para el historial de cambios de estado de pedidos
CREATE TABLE IF NOT EXISTS historial_estados_pedido (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    estado_anterior VARCHAR(50) NOT NULL,
    estado_nuevo VARCHAR(50) NOT NULL,
    comentario TEXT,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_admin INT,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
    INDEX idx_pedido_fecha (id_pedido, fecha_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar columna fecha_actualizacion a la tabla pedidos si no existe
ALTER TABLE pedidos 
ADD COLUMN fecha_actualizacion TIMESTAMP NULL DEFAULT NULL 
AFTER fecha_pedido;