<?php
/**
 * Clase Carrito - Sistema de carrito de compras con sesiones PHP
 * Helmets Pro v2.0 - Gestión de productos en carrito
 * 
 * Funcionalidades:
 * - Agregar productos al carrito
 * - Actualizar cantidades
 * - Eliminar productos
 * - Calcular totales
 * - Limpiar carrito
 */
class Carrito {
    
    /**
     * Inicializar carrito en sesión
     */
    public static function init() {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
    }
    
    /**
     * Agregar producto al carrito
     * @param int $id_producto - ID del producto
     * @param string $nombre - Nombre del producto
     * @param float $precio - Precio del producto
     * @param string $imagen - URL de la imagen
     * @param int $cantidad - Cantidad (por defecto 1)
     * @return bool - True si se agregó correctamente
     */
    public static function agregarProducto($id_producto, $nombre, $precio, $imagen, $cantidad = 1) {
        self::init();
        
        // Verificar si el producto ya existe en el carrito
        if (isset($_SESSION['carrito'][$id_producto])) {
            // Actualizar cantidad si ya existe
            $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
        } else {
            // Agregar nuevo producto
            $_SESSION['carrito'][$id_producto] = [
                'id' => $id_producto,
                'nombre' => $nombre,
                'precio' => $precio,
                'imagen' => $imagen,
                'cantidad' => $cantidad
            ];
        }
        
        return true;
    }
    
    /**
     * Actualizar cantidad de un producto
     * @param int $id_producto - ID del producto
     * @param int $cantidad - Nueva cantidad
     * @return bool - True si se actualizó correctamente
     */
    public static function actualizarCantidad($id_producto, $cantidad) {
        self::init();
        
        if (isset($_SESSION['carrito'][$id_producto])) {
            if ($cantidad > 0) {
                $_SESSION['carrito'][$id_producto]['cantidad'] = $cantidad;
            } else {
                // Si la cantidad es 0 o menor, eliminar el producto
                self::eliminarProducto($id_producto);
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Eliminar producto del carrito
     * @param int $id_producto - ID del producto a eliminar
     * @return bool - True si se eliminó correctamente
     */
    public static function eliminarProducto($id_producto) {
        self::init();
        
        if (isset($_SESSION['carrito'][$id_producto])) {
            unset($_SESSION['carrito'][$id_producto]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtener todos los productos del carrito
     * @return array - Array con los productos del carrito
     */
    public static function obtenerProductos() {
        self::init();
        return $_SESSION['carrito'];
    }
    
    /**
     * Obtener cantidad total de productos en el carrito
     * @return int - Cantidad total de productos
     */
    public static function obtenerCantidadTotal() {
        self::init();
        $total = 0;
        
        foreach ($_SESSION['carrito'] as $producto) {
            $total += $producto['cantidad'];
        }
        
        return $total;
    }
    
    /**
     * Calcular subtotal del carrito
     * @return float - Subtotal sin impuestos
     */
    public static function calcularSubtotal() {
        self::init();
        $subtotal = 0;
        
        foreach ($_SESSION['carrito'] as $producto) {
            $subtotal += $producto['precio'] * $producto['cantidad'];
        }
        
        return round($subtotal, 2);
    }
    
    /**
     * Calcular total con impuestos (IVA 16%)
     * @return float - Total con impuestos
     */
    public static function calcularTotal() {
        $subtotal = self::calcularSubtotal();
        $iva = round($subtotal * 0.16, 2); // 16% IVA
        return round($subtotal + $iva, 2);
    }
    
    /**
     * Limpiar todo el carrito
     * @return bool - True si se limpió correctamente
     */
    public static function limpiarCarrito() {
        $_SESSION['carrito'] = [];
        return true;
    }
    
    /**
     * Verificar si el carrito está vacío
     * @return bool - True si está vacío
     */
    public static function estaVacio() {
        self::init();
        return empty($_SESSION['carrito']);
    }
    
    /**
     * Obtener información de un producto específico
     * @param int $id_producto - ID del producto
     * @return array|null - Información del producto o null si no existe
     */
    public static function obtenerProducto($id_producto) {
        self::init();
        
        if (isset($_SESSION['carrito'][$id_producto])) {
            return $_SESSION['carrito'][$id_producto];
        }
        
        return null;
    }
    
    /**
     * Calcular subtotal de un producto específico
     * @param int $id_producto - ID del producto
     * @return float - Subtotal del producto
     */
    public static function calcularSubtotalProducto($id_producto) {
        $producto = self::obtenerProducto($id_producto);
        
        if ($producto) {
            return $producto['precio'] * $producto['cantidad'];
        }
        
        return 0;
    }
    
    /**
     * Formatear precio para mostrar
     * @param float $precio - Precio a formatear
     * @return string - Precio formateado
     */
    public static function formatearPrecio($precio) {
        return '$' . number_format($precio, 2, '.', ',');
    }
}
?>
