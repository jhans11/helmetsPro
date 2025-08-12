<?php
require_once __DIR__ . '/../administrador/config/config.php';
require_once __DIR__ . '/../administrador/config/DB.php';

/**
 * Clase Usuario - Sistema de usuarios clientes
 */
class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = DB::getInstance();
    }
    
    // Registro
    public function registrar($datos) {
        try {
            $email_existe = $this->db->fetchOne(
                "SELECT id FROM usuarios_clientes WHERE email = ?",
                [$datos['email']]
            );
            if ($email_existe) {
                return ['success' => false, 'message' => 'El email ya está registrado'];
            }

            $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
            $token_verificacion = bin2hex(random_bytes(32));
            
            // Generar nombre de usuario único basado en email
            $usuario_nombre = strtolower(str_replace(['@', '.'], ['', ''], $datos['email']));
            $usuario_nombre = preg_replace('/[^a-z0-9]/', '', $usuario_nombre);
            
            // Verificar que el nombre de usuario no exista
            $usuario_existe = $this->db->fetchOne(
                "SELECT id FROM usuarios_clientes WHERE usuario = ?",
                [$usuario_nombre]
            );
            if ($usuario_existe) {
                $usuario_nombre = $usuario_nombre . '_' . time();
            }

            $sql = "INSERT INTO usuarios_clientes
                    (usuario, nombre, apellido, email, password_hash, telefono, direccion, ciudad, codigo_postal, token_verificacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $usuario_nombre,
                $datos['nombre'],
                $datos['apellido'],
                $datos['email'],
                $password_hash,
                $datos['telefono'] ?? null,
                $datos['direccion'] ?? null,
                $datos['ciudad'] ?? null,
                $datos['codigo_postal'] ?? null,
                $token_verificacion
            ];
            $usuario_id = $this->db->insert($sql, $params);

            if ($usuario_id) {
                return [
                    'success' => true,
                    'message' => 'Usuario registrado correctamente',
                    'usuario_id' => $usuario_id,
                    'token_verificacion' => $token_verificacion
                ];
            }
            return ['success' => false, 'message' => 'Error al registrar usuario'];
        } catch (Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Login
    public function login($email, $password) {
        try {
            $usuario = $this->db->fetchOne(
                "SELECT * FROM usuarios_clientes WHERE email = ? AND activo = 1",
                [$email]
            );
            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                if (!$usuario['verificado']) {
                    return ['success' => false, 'message' => 'Por favor verifica tu email antes de iniciar sesión'];
                }
                $this->db->update("UPDATE usuarios_clientes SET ultimo_acceso = NOW() WHERE id = ?", [$usuario['id']]);
                $this->crearSesionUsuario($usuario);
                return ['success' => true, 'message' => 'Login exitoso', 'usuario' => $usuario];
            }
            return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Sesión
    public function crearSesionUsuario($usuario) {
        session_regenerate_id(true);
        $_SESSION['cliente_id'] = $usuario['id'];
        $_SESSION['cliente_email'] = $usuario['email'];
        $_SESSION['cliente_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
        $_SESSION['cliente_login'] = time();
        $_SESSION['tipo_usuario'] = 'cliente';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public function estaAutenticado() {
        return isset($_SESSION['cliente_id']) && ($_SESSION['tipo_usuario'] ?? '') === 'cliente';
    }

    public function obtenerUsuarioActual() {
        if (!$this->estaAutenticado()) return null;
        return $this->db->fetchOne("SELECT * FROM usuarios_clientes WHERE id = ?", [$_SESSION['cliente_id']]);
    }

    // Pedidos (panel usuario)
    public function obtenerMisPedidos($usuario_id) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM historial_pedidos WHERE usuario_id = ? ORDER BY fecha_pedido DESC",
                [$usuario_id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo pedidos: " . $e->getMessage());
            return [];
        }
    }

    // Renombrado para evitar conflicto con el método estático
    public function obtenerDetallesPedidoUsuario($pedido_id, $usuario_id) {
        try {
            $pedido = $this->db->fetchOne(
                "SELECT * FROM historial_pedidos WHERE id = ? AND usuario_id = ?",
                [$pedido_id, $usuario_id]
            );
            if ($pedido) {
                $pedido['productos'] = $this->db->fetchAll(
                    "SELECT * FROM detalles_pedido_usuario WHERE pedido_id = ?",
                    [$pedido_id]
                );
                return $pedido;
            }
            return null;
        } catch (Exception $e) {
            error_log("Error obteniendo detalles del pedido: " . $e->getMessage());
            return null;
        }
    }

    // Datos personales
    public function actualizarDatosPersonales($usuario_id, $datos) {
        try {
            $sql = "UPDATE usuarios_clientes SET 
                    nombre = ?, apellido = ?, telefono = ?, 
                    direccion = ?, ciudad = ?, codigo_postal = ?
                    WHERE id = ?";
            $params = [
                $datos['nombre'],
                $datos['apellido'],
                $datos['telefono'] ?? null,
                $datos['direccion'] ?? null,
                $datos['ciudad'] ?? null,
                $datos['codigo_postal'] ?? null,
                $usuario_id
            ];
            $res = $this->db->update($sql, $params);
            return $res > 0
                ? ['success' => true, 'message' => 'Datos actualizados correctamente']
                : ['success' => false, 'message' => 'No se pudieron actualizar los datos'];
        } catch (Exception $e) {
            error_log("Error actualizando datos: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Cambiar password desde Mi Cuenta (con password actual)
    public function cambiarPassword($usuario_id, $password_actual, $password_nueva) {
        try {
            $usuario = $this->db->fetchOne(
                "SELECT password_hash FROM usuarios_clientes WHERE id = ?",
                [$usuario_id]
            );
            if (!$usuario || !password_verify($password_actual, $usuario['password_hash'])) {
                return ['success' => false, 'message' => 'La contraseña actual es incorrecta'];
            }
            $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            $res = $this->db->update(
                "UPDATE usuarios_clientes SET password_hash = ? WHERE id = ?",
                [$password_hash, $usuario_id]
            );
            return $res > 0
                ? ['success' => true, 'message' => 'Contraseña cambiada correctamente']
                : ['success' => false, 'message' => 'No se pudo cambiar la contraseña'];
        } catch (Exception $e) {
            error_log("Error cambiando password: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Logout
    public function logout() {
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    // Verificación de email
    public function verificarEmail($token) {
        try {
            $usuario = $this->db->fetchOne(
                "SELECT id FROM usuarios_clientes WHERE token_verificacion = ? AND verificado = 0",
                [$token]
            );
            if ($usuario) {
                $res = $this->db->update(
                    "UPDATE usuarios_clientes SET verificado = 1, fecha_verificacion = NOW(), token_verificacion = NULL WHERE id = ?",
                    [$usuario['id']]
                );
                if ($res > 0) return ['success' => true, 'message' => 'Email verificado correctamente'];
            }
            return ['success' => false, 'message' => 'Token de verificación inválido'];
        } catch (Exception $e) {
            error_log("Error verificando email: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    // Utilidad
    public static function generarNumeroPedido() {
        $fecha = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return "PED-{$fecha}-{$random}";
    }

    // ===== Recuperación de contraseña (métodos estáticos) =====

    public static function buscarPorEmail($email) {
        $db = DB::getInstance();
        $sql = "SELECT * FROM usuarios_clientes WHERE email = ? AND activo = 1";
        return $db->fetchOne($sql, [$email]);
    }

    public static function guardarTokenRecuperacion($usuario_id, $token, $expiracion) {
        $db = DB::getInstance();
        $sql = "UPDATE usuarios_clientes SET token_recuperacion = ?, expiracion_token = ? WHERE id = ?";
        return $db->update($sql, [$token, $expiracion, $usuario_id]);
    }

    public static function verificarTokenRecuperacion($token) {
        $db = DB::getInstance();
        $sql = "SELECT * FROM usuarios_clientes WHERE token_recuperacion = ? AND expiracion_token > NOW() AND activo = 1";
        return $db->fetchOne($sql, [$token]);
    }

    // Renombrado para no chocar con el método de instancia
    public static function cambiarPasswordRecuperacion($usuario_id, $nueva_password) {
        $db = DB::getInstance();
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios_clientes
                SET password_hash = ?, token_recuperacion = NULL, expiracion_token = NULL
                WHERE id = ?";
        return $db->update($sql, [$password_hash, $usuario_id]);
    }

    public static function invalidarTokenRecuperacion($token) {
        $db = DB::getInstance();
        $sql = "UPDATE usuarios_clientes SET token_recuperacion = NULL, expiracion_token = NULL WHERE token_recuperacion = ?";
        return $db->update($sql, [$token]);
    }

    // ===== Consultas de pedido para páginas públicas (estáticas) =====

    public static function obtenerPedido($usuario_id, $pedido_id) {
        $db = DB::getInstance();
        $sql = "SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?";
        return $db->fetchOne($sql, [$pedido_id, $usuario_id]);
    }

    public static function obtenerDetallesPedido($pedido_id) {
        $db = DB::getInstance();
        $sql = "SELECT dp.*, c.nombre, c.imagen, c.sku
                FROM detalles_pedido dp
                JOIN cascos c ON dp.casco_id = c.id
                WHERE dp.pedido_id = ?";
        return $db->fetchAll($sql, [$pedido_id]);
    }
}
?>