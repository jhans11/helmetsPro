<?php
/**
 * Archivo de Configuración Centralizada
 * Helmets Pro v2.0 - Configuración de Entorno
 * 
 * Este archivo centraliza todas las configuraciones del sistema:
 * - Configuración de base de datos
 * - Variables de entorno
 * - Configuraciones de seguridad
 * - Configuraciones de logging
 */

class Config {
    // Configuración de Base de Datos
    private static $dbConfig = [
        'host' => 'localhost',
        'dbname' => 'sitioweb',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    ];
    
    // Configuración de Seguridad
    private static $securityConfig = [
        'session_timeout' => 1800, // 30 minutos
        'max_login_attempts' => 5,
        'lockout_duration' => 300, // 5 minutos
        'password_min_length' => 8,
        'csrf_token_expiry' => 3600 // 1 hora
    ];
    
    // Configuración de Logging
    private static $loggingConfig = [
        'enabled' => true,
        'log_queries' => true,
        'log_errors' => true,
        'log_file' => '../logs/system.log',
        'max_log_size' => 10485760 // 10MB
    ];
    
    // Configuración de Aplicación
    private static $appConfig = [
        'app_name' => 'Helmets Pro',
        'app_version' => '2.0',
        'debug_mode' => false,
        'timezone' => 'America/Mexico_City'
    ];
    
    /**
     * Obtener configuración de base de datos
     */
    public static function getDBConfig() {
        return self::$dbConfig;
    }
    
    /**
     * Obtener configuración de seguridad
     */
    public static function getSecurityConfig() {
        return self::$securityConfig;
    }
    
    /**
     * Obtener configuración de logging
     */
    public static function getLoggingConfig() {
        return self::$loggingConfig;
    }
    
    /**
     * Obtener configuración de aplicación
     */
    public static function getAppConfig() {
        return self::$appConfig;
    }
    
    /**
     * Obtener valor específico de configuración
     */
    public static function get($key, $default = null) {
        $keys = explode('.', $key);
        $config = null;
        
        switch ($keys[0]) {
            case 'db':
                $config = self::$dbConfig;
                break;
            case 'security':
                $config = self::$securityConfig;
                break;
            case 'logging':
                $config = self::$loggingConfig;
                break;
            case 'app':
                $config = self::$appConfig;
                break;
            default:
                return $default;
        }
        
        for ($i = 1; $i < count($keys); $i++) {
            if (isset($config[$keys[$i]])) {
                $config = $config[$keys[$i]];
            } else {
                return $default;
            }
        }
        
        return $config;
    }
    
    /**
     * Cargar variables de entorno desde archivo .env
     */
    public static function loadEnv($envFile = '.env') {
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remover comillas si existen
                    if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                        $value = $matches[2];
                    }
                    
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
    
    /**
     * Obtener variable de entorno
     */
    public static function env($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
    
    /**
     * Validar configuración requerida
     */
    public static function validate() {
        $errors = [];
        
        // Validar configuración de BD
        $dbConfig = self::getDBConfig();
        if (empty($dbConfig['host']) || empty($dbConfig['dbname'])) {
            $errors[] = "Configuración de base de datos incompleta";
        }
        
        // Validar directorio de logs
        $logConfig = self::getLoggingConfig();
        if ($logConfig['enabled']) {
            $logDir = dirname($logConfig['log_file']);
            if (!is_dir($logDir) && !mkdir($logDir, 0755, true)) {
                $errors[] = "No se pudo crear el directorio de logs: $logDir";
            }
        }
        
        return $errors;
    }
    
    /**
     * Inicializar configuración
     */
    public static function init() {
        // Cargar variables de entorno
        self::loadEnv();
        
        // Configurar timezone
        date_default_timezone_set(self::get('app.timezone', 'UTC'));
        
        // Validar configuración
        $errors = self::validate();
        if (!empty($errors)) {
            throw new Exception("Errores de configuración: " . implode(', ', $errors));
        }
        
        // Configurar logging si está habilitado
        if (self::get('logging.enabled', false)) {
            error_reporting(E_ALL);
            ini_set('log_errors', 1);
            ini_set('error_log', self::get('logging.log_file'));
        }
    }
}

// Inicializar configuración al cargar el archivo
Config::init();
?> 