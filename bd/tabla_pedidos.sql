-- Tabla de pedidos para Helmets Pro v2.0
-- Crear tabla principal de pedidos
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `numero_pedido` varchar(20) NOT NULL UNIQUE,
  `estado` enum('pendiente','pagado','procesando','enviado','entregado','cancelado') DEFAULT 'pendiente',
  `subtotal` decimal(10,2) NOT NULL,
  `impuestos` decimal(10,2) DEFAULT 0.00,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` enum('paypal','transferencia','efectivo') DEFAULT 'paypal',
  `transaction_id` varchar(100) DEFAULT NULL,
  `paypal_order_id` varchar(100) DEFAULT NULL,
  `fecha_pedido` timestamp DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `direccion_envio` text NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `codigo_postal` varchar(20) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `notas` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_clientes`(`id`) ON DELETE CASCADE,
  INDEX `idx_usuario` (`usuario_id`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_fecha` (`fecha_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de detalles de pedidos
CREATE TABLE IF NOT EXISTS `pedidos_detalles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `producto_nombre` varchar(255) NOT NULL,
  `producto_imagen` varchar(1000) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `cascos`(`id`) ON DELETE CASCADE,
  INDEX `idx_pedido` (`pedido_id`),
  INDEX `idx_producto` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Agregar campos faltantes a la tabla cascos si no existen
ALTER TABLE `cascos` 
ADD COLUMN IF NOT EXISTS `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS `descripcion` text DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `categoria` varchar(100) DEFAULT 'General',
ADD COLUMN IF NOT EXISTS `stock` int(11) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `activo` tinyint(1) DEFAULT 1,
ADD COLUMN IF NOT EXISTS `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP;

-- Actualizar algunos productos con precios de ejemplo
UPDATE `cascos` SET 
    `precio` = CASE 
        WHEN `nombre` LIKE '%AGV%' THEN 299.99
        WHEN `nombre` LIKE '%ICON%' THEN 249.99
        WHEN `nombre` LIKE '%SHARK%' THEN 199.99
        WHEN `nombre` LIKE '%BLACK%' THEN 179.99
        ELSE 159.99
    END,
    `stock` = 10,
    `categoria` = 'Cascos Deportivos',
    `descripcion` = CONCAT('Casco de alta calidad ', `nombre`, ' - Seguridad y estilo garantizados')
WHERE `precio` = 0.00;

