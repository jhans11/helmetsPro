-- Script para actualizar tabla cascos con campos de precio
-- Helmets Pro v2.0 - Sistema de Carrito de Compras

-- Agregar columna de precio a la tabla cascos
ALTER TABLE `cascos` 
ADD COLUMN `precio` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `imagen`,
ADD COLUMN `descripcion` TEXT NULL AFTER `precio`,
ADD COLUMN `stock` INT NOT NULL DEFAULT 0 AFTER `descripcion`,
ADD COLUMN `activo` TINYINT(1) NOT NULL DEFAULT 1 AFTER `stock`;

-- Actualizar productos existentes con precios de ejemplo
UPDATE `cascos` SET 
    `precio` = 299.99,
    `descripcion` = 'Casco AGV de alta calidad para motociclistas',
    `stock` = 50,
    `activo` = 1
WHERE `id` = 5;

UPDATE `cascos` SET 
    `precio` = 199.99,
    `descripcion` = 'Casco ICON BLACK con diseño moderno',
    `stock` = 30,
    `activo` = 1
WHERE `id` = 6;

UPDATE `cascos` SET 
    `precio` = 249.99,
    `descripcion` = 'Casco ICONE RED con ventilación avanzada',
    `stock` = 25,
    `activo` = 1
WHERE `id` = 8;

UPDATE `cascos` SET 
    `precio` = 179.99,
    `descripcion` = 'Casco ICON BLUE con tecnología de absorción de impacto',
    `stock` = 40,
    `activo` = 1
WHERE `id` = 9;

UPDATE `cascos` SET 
    `precio` = 159.99,
    `descripcion` = 'Casco BLACK DARK con acabado mate',
    `stock` = 35,
    `activo` = 1
WHERE `id` = 10;

UPDATE `cascos` SET 
    `precio` = 399.99,
    `descripcion` = 'Casco SHARK de competición',
    `stock` = 15,
    `activo` = 1
WHERE `id` = 11;

UPDATE `cascos` SET 
    `precio` = 129.99,
    `descripcion` = 'Casco BLACK FRIDAY con descuento especial',
    `stock` = 20,
    `activo` = 1
WHERE `id` = 12;

UPDATE `cascos` SET 
    `precio` = 279.99,
    `descripcion` = 'Casco SAGITARIO con diseño deportivo',
    `stock` = 22,
    `activo` = 1
WHERE `id` = 15;

-- Crear tabla de pedidos (para futuras funcionalidades)
CREATE TABLE IF NOT EXISTS `pedidos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `cliente_id` int(11) DEFAULT NULL,
    `fecha_pedido` timestamp DEFAULT CURRENT_TIMESTAMP,
    `estado` enum('pendiente','confirmado','enviado','entregado','cancelado') DEFAULT 'pendiente',
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `iva` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `direccion_envio` TEXT NOT NULL,
    `telefono` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `notas` TEXT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_cliente` (`cliente_id`),
    KEY `idx_fecha` (`fecha_pedido`),
    KEY `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de detalles de pedidos
CREATE TABLE IF NOT EXISTS `detalles_pedido` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `pedido_id` int(11) NOT NULL,
    `producto_id` int(11) NOT NULL,
    `cantidad` int(11) NOT NULL,
    `precio_unitario` DECIMAL(10,2) NOT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_pedido` (`pedido_id`),
    KEY `idx_producto` (`producto_id`),
    FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`producto_id`) REFERENCES `cascos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentarios sobre la estructura:
-- precio: Precio del producto en pesos mexicanos
-- descripcion: Descripción detallada del producto
-- stock: Cantidad disponible en inventario
-- activo: Controla si el producto está disponible para venta
