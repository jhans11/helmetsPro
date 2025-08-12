<?php
/**
 * Clase Middleware - Protección de rutas y verificación de autenticación
 * Implementa redirecciones y verificaciones de seguridad
 */
class Middleware {
    
    /**
     * Verificar si el usuario está autenticado
     * Redirige al login si no está autenticado
     */
    public static function requireAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== "ok") {
            header('Location: index.php');
            exit();
        }
    }
    
    /**
     * Verificar si el usuario NO está autenticado
     * Redirige al dashboard si ya está autenticado
     */
    public static function requireGuest() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['usuario']) && $_SESSION['usuario'] === "ok") {
            header('Location: inicio.php');
            exit();
        }
    }
    
    /**
     * Verificar permisos de administrador
     */
    public static function requireAdmin() {
        self::requireAuth();
        
        // Aquí puedes agregar lógica adicional para verificar roles
        // Por ejemplo, verificar si el usuario tiene rol de administrador
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: index.php');
            exit();
        }
    }
    
    /**
     * Verificar token CSRF en formularios POST
     */
    public static function validateCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            
            if (!Validator::validateCSRF($token)) {
                die('Error: Token CSRF inválido');
            }
        }
    }
    
    /**
     * Configurar headers de seguridad
     */
    public static function setSecurityHeaders() {
        // Prevenir clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevenir MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Configurar política de referrer
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Configurar Content Security Policy básica
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://stackpath.bootstrapcdn.com; style-src 'self' 'unsafe-inline' https://stackpath.bootstrapcdn.com; img-src 'self' data: https:;");
    }
    
    /**
     * Configurar sesión segura
     */
    public static function configureSecureSession() {
        // Configurar cookies seguras
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        
        // Configurar timeout de sesión (30 minutos)
        ini_set('session.gc_maxlifetime', 1800);
        session_set_cookie_params(1800);
    }
    
    /**
     * Verificar timeout de sesión
     */
    public static function checkSessionTimeout() {
        if (isset($_SESSION['fecha_login'])) {
            $timeout = 1800; // 30 minutos
            
            if (time() - $_SESSION['fecha_login'] > $timeout) {
                session_destroy();
                header('Location: index.php?timeout=1');
                exit();
            }
        }
    }
    
    /**
     * Registrar intento de acceso fallido
     */
    public static function logFailedAttempt($ip, $username) {
        $log_entry = date('Y-m-d H:i:s') . " - Failed login attempt from IP: $ip, Username: $username\n";
        error_log($log_entry, 3, 'logs/failed_logins.log');
    }
    
    /**
     * Verificar rate limiting básico
     */
    public static function checkRateLimit($ip, $max_attempts = 5, $time_window = 300) {
        $log_file = 'logs/failed_logins.log';
        
        if (file_exists($log_file)) {
            $recent_attempts = 0;
            $current_time = time();
            
            $lines = file($log_file);
            foreach ($lines as $line) {
                if (strpos($line, $ip) !== false) {
                    $timestamp = strtotime(substr($line, 0, 19));
                    if ($current_time - $timestamp < $time_window) {
                        $recent_attempts++;
                    }
                }
            }
            
            if ($recent_attempts >= $max_attempts) {
                http_response_code(429);
                die('Demasiados intentos de acceso. Intente más tarde.');
            }
        }
    }
}
?> 