<?php
/**
 * Clase Middleware - Protección de rutas y seguridad
 * Helmets Pro v2.0
 */

class Middleware {
    
    /**
     * Requerir autenticación de cliente
     */
    public static function requireAuth() {
        if (!isset($_SESSION['cliente_id'])) {
            $_SESSION['mensaje'] = 'Debes iniciar sesión para acceder a esta página';
            $_SESSION['tipo_mensaje'] = 'warning';
            header('Location: login.php');
            exit();
        }
    }
    
    /**
     * Requerir que NO esté autenticado (para login/registro)
     */
    public static function requireGuest() {
        if (isset($_SESSION['cliente_id'])) {
            header('Location: mi-cuenta.php');
            exit();
        }
    }
    
    /**
     * Verificar si está autenticado
     */
    public static function isAuthenticated() {
        return isset($_SESSION['cliente_id']);
    }
    
    /**
     * Obtener ID del usuario actual
     */
    public static function getCurrentUserId() {
        return $_SESSION['cliente_id'] ?? null;
    }
    
    /**
     * Configurar headers de seguridad
     */
    public static function setSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
    
    /**
     * Configurar sesión segura
     */
    public static function configureSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
       
        // Regenerar ID de sesión periódicamente
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutos
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Verificar timeout de sesión
     */
    public static function checkSessionTimeout() {
        $timeout = 1800; // 30 minutos
        
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > $timeout)) {
            
            session_unset();
            session_destroy();
            $_SESSION['mensaje'] = 'Tu sesión ha expirado. Por favor inicia sesión nuevamente.';
            $_SESSION['tipo_mensaje'] = 'warning';
            header('Location: login.php');
            exit();
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Log de intentos fallidos
     */
    public static function logFailedAttempt($email, $ip) {
        $db = DB::getInstance();
        
        $sql = "INSERT INTO logs_acceso_clientes 
                (email, ip_address, tipo_acceso, detalles) 
                VALUES (?, ?, 'login_fallido', ?)";
        
        $detalles = json_encode([
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        $db->insert($sql, [$email, $ip, $detalles]);
    }
    
    /**
     * Verificar rate limiting
     */
    public static function checkRateLimit($email, $max_attempts = 5, $time_window = 300) {
        $db = DB::getInstance();
        
        $sql = "SELECT COUNT(*) as attempts FROM logs_acceso_clientes 
                WHERE email = ? AND tipo_acceso = 'login_fallido' 
                AND fecha_acceso > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $result = $db->fetchOne($sql, [$email, $time_window]);
        
        return $result['attempts'] < $max_attempts;
    }
}
?>