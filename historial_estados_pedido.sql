-- Tabla para el historial de cambios de estado de pedidos
-- Helmets Pro v2.0 - Sistema de Seguimiento de Pedidos
-- Fecha: 2024

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
ADD COLUMN IF NOT EXISTS fecha_actualizacion TIMESTAMP NULL DEFAULT NULL 
AFTER fecha_pedido;

-- Comentarios sobre la estructura:
-- id_historial: Identificador único del registro de historial
-- id_pedido: Referencia al pedido (FK a pedidos.id)
-- estado_anterior: Estado previo del pedido
-- estado_nuevo: Nuevo estado del pedido
-- comentario: Comentario opcional sobre el cambio
-- fecha_cambio: Fecha y hora del cambio de estado
-- id_admin: ID del administrador que realizó el cambio (opcional)
