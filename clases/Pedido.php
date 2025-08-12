<?php
require_once __DIR__ . '/../administrador/config/config.php';
require_once __DIR__ . '/../administrador/config/DB.php';

/**
 * Clase Pedido - Manejo de pedidos y transacciones
 */
class Pedido {
    private $db;
    
    public function __construct() {
        $this->db = DB::getInstance();
    }
    
    /**
     * Crear un nuevo pedido
     */
    public function crearPedido($usuario_id, $productos_carrito, $datos_envio) {
        try {
            $this->db->beginTransaction();
            
            // Generar número de pedido único
            $numero_pedido = $this->generarNumeroPedido();
            
            // Calcular totales
            $subtotal = 0;
            foreach ($productos_carrito as $producto) {
                $subtotal += $producto['precio'] * $producto['cantidad'];
            }
            $impuestos = $subtotal * 0.16; // 16% IVA
            $total = $subtotal + $impuestos;
            
            // Crear el pedido principal
            $sql_pedido = "INSERT INTO pedidos 
                          (usuario_id, numero_pedido, subtotal, impuestos, total, estado, 
                           direccion_envio, ciudad_envio, codigo_postal_envio, telefono_envio, 
                           notas, fecha_creacion) 
                          VALUES (?, ?, ?, ?, ?, 'pendiente', ?, ?, ?, ?, ?, NOW())";
            
            $pedido_id = $this->db->insert($sql_pedido, [
                $usuario_id,
                $numero_pedido,
                $subtotal,
                $impuestos,
                $total,
                $datos_envio['direccion'],
                $datos_envio['ciudad'],
                $datos_envio['codigo_postal'],
                $datos_envio['telefono'],
                $datos_envio['notas'] ?? ''
            ]);
            
            if (!$pedido_id) {
                throw new Exception("Error al crear el pedido");
            }
            
            // Crear detalles del pedido
            foreach ($productos_carrito as $producto) {
                $sql_detalle = "INSERT INTO detalles_pedido 
                               (pedido_id, producto_id, nombre_producto, precio_unitario, 
                                cantidad, subtotal, imagen) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $detalle_id = $this->db->insert($sql_detalle, [
                    $pedido_id,
                    $producto['id'],
                    $producto['nombre'],
                    $producto['precio'],
                    $producto['cantidad'],
                    $producto['precio'] * $producto['cantidad'],
                    $producto['imagen']
                ]);
                
                if (!$detalle_id) {
                    throw new Exception("Error al crear detalle del pedido");
                }
                
                // Actualizar stock del producto
                $nuevo_stock = $producto['stock'] - $producto['cantidad'];
                if ($nuevo_stock < 0) {
                    throw new Exception("Stock insuficiente para el producto: " . $producto['nombre']);
                }
                
                $this->db->update(
                    "UPDATE cascos SET stock = ? WHERE id = ?",
                    [$nuevo_stock, $producto['id']]
                );
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'pedido_id' => $pedido_id,
                'numero_pedido' => $numero_pedido,
                'total' => $total
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al crear pedido: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generar número de pedido único
     */
    private function generarNumeroPedido() {
        $prefijo = 'HP';
        $fecha = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefijo . $fecha . $random;
    }
    
    /**
     * Obtener pedido por ID
     */
    public function obtenerPedido($pedido_id, $usuario_id = null) {
        try {
            $sql = "SELECT p.*, u.nombre, u.apellido, u.email 
                    FROM pedidos p 
                    JOIN usuarios_clientes u ON p.usuario_id = u.id 
                    WHERE p.id = ?";
            $params = [$pedido_id];
            
            if ($usuario_id) {
                $sql .= " AND p.usuario_id = ?";
                $params[] = $usuario_id;
            }
            
            return $this->db->fetchOne($sql, $params);
        } catch (Exception $e) {
            error_log("Error al obtener pedido: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener detalles de un pedido
     */
    public function obtenerDetallesPedido($pedido_id) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM detalles_pedido WHERE pedido_id = ? ORDER BY id",
                [$pedido_id]
            );
        } catch (Exception $e) {
            error_log("Error al obtener detalles del pedido: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualizar estado del pedido
     */
    public function actualizarEstado($pedido_id, $nuevo_estado) {
        try {
            $this->db->update(
                "UPDATE pedidos SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?",
                [$nuevo_estado, $pedido_id]
            );
            return true;
        } catch (Exception $e) {
            error_log("Error al actualizar estado del pedido: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener pedidos de un usuario
     */
    public function obtenerPedidosUsuario($usuario_id, $limit = 10) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY fecha_creacion DESC LIMIT ?",
                [$usuario_id, $limit]
            );
        } catch (Exception $e) {
            error_log("Error al obtener pedidos del usuario: " . $e->getMessage());
            return [];
        }
    }
}
?>

