<?php
/**
 * Clase Auth - Gestión segura de autenticación
 * Implementa hash de contraseñas y validación de sesiones
 */
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = DB::getInstance();
    }
    
    /**
     * Verificar credenciales de usuario
     */
    public function login($usuario, $password) {
        try {
            $sql = "SELECT * FROM usuarios_admin WHERE usuario = ? AND activo = 1";
            $user = $this->db->fetchOne($sql, [$usuario]);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Actualizar último acceso
                $this->db->update(
                    "UPDATE usuarios_admin SET ultimo_acceso = NOW() WHERE id = ?",
                    [$user['id']]
                );
                
                return $user;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear sesión segura
     */
    public function crearSesion($usuario) {
        // Regenerar ID de sesión para prevenir session fixation
        session_regenerate_id(true);
        
        $_SESSION['usuario'] = "ok";
        $_SESSION['nombreUsuario'] = $usuario['usuario'];
        $_SESSION['id_usuario'] = $usuario['id'];
        $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
        $_SESSION['fecha_login'] = time();
        
        // Crear token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public function estaAutenticado() {
        return isset($_SESSION['usuario']) && $_SESSION['usuario'] === "ok";
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }
    
    /**
     * Generar hash de contraseña
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verificar token CSRF
     */
    public function verificarCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>