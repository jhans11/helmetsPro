-- Script para agregar columna de categoría a la tabla cascos
-- Helmets Pro v2.0 - Sistema de Filtros

-- Agregar columna de categoría a la tabla cascos
ALTER TABLE `cascos` 
ADD COLUMN `categoria` VARCHAR(50) NOT NULL DEFAULT 'General' AFTER `nombre`;

-- Actualizar productos existentes con categorías específicas
UPDATE `cascos` SET `categoria` = 'Deportivo' WHERE `id` IN (5, 11, 15);
UPDATE `cascos` SET `categoria` = 'Urbano' WHERE `id` IN (6, 8, 9);
UPDATE `cascos` SET `categoria` = 'Clásico' WHERE `id` IN (10, 12);

-- Crear índice para mejorar rendimiento de filtros
CREATE INDEX `idx_categoria` ON `cascos` (`categoria`);
CREATE INDEX `idx_precio` ON `cascos` (`precio`);
CREATE INDEX `idx_nombre` ON `cascos` (`nombre`);

-- Comentarios sobre la estructura:
-- categoria: Categoría del producto (Deportivo, Urbano, Clásico, etc.)
-- Los índices mejoran el rendimiento de las consultas con filtros