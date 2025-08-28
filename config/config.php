<?php
/**
 * Configuración Global - Helmets Pro v2.0
 * Archivo centralizado de configuración
 */

// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sitioweb');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de la Aplicación
define('APP_NAME', 'Helmets Pro');
define('APP_VERSION', '2.0');
define('APP_URL', 'http://localhost/sitioweb');
define('ADMIN_EMAIL', 'admin@helmetspro.com');

// Configuración de PayPal
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id_here');
define('PAYPAL_CLIENT_SECRET', 'your_paypal_secret_here');
define('PAYPAL_MODE', 'sandbox'); // sandbox o live

// Configuración de Seguridad
define('SESSION_TIMEOUT', 3600); // 1 hora
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// Configuración de IVA
define('IVA_PORCENTAJE', 16);

// Configuración de Archivos
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Configuración de Paginación
define('ITEMS_PER_PAGE', 12);

// Configuración de Email (futuro)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_email_password');

// Configuración de Logs
define('LOG_DIR', 'logs/');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Configuración de Cache
define('CACHE_ENABLED', false);
define('CACHE_DIR', 'cache/');
define('CACHE_TIME', 3600);

// Configuración de Debug
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);

// Configuración de Zona Horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de Idioma
define('DEFAULT_LANGUAGE', 'es');

// Configuración de Moneda
define('CURRENCY', 'MXN');
define('CURRENCY_SYMBOL', '$');

// Configuración de SEO
define('META_TITLE', 'Helmets Pro - Cascos para Motociclistas');
define('META_DESCRIPTION', 'Tienda especializada en cascos para motociclistas. Calidad, seguridad y estilo.');
define('META_KEYWORDS', 'cascos, motocicletas, seguridad, motociclistas, helmets');

// Configuración de Redes Sociales
define('FACEBOOK_URL', 'https://facebook.com/helmetspro');
define('INSTAGRAM_URL', 'https://instagram.com/helmetspro');
define('TWITTER_URL', 'https://twitter.com/helmetspro');

// Configuración de Analytics (futuro)
define('GOOGLE_ANALYTICS_ID', 'GA-XXXXXXXXX-X');

// Configuración de WhatsApp Business (futuro)
define('WHATSAPP_NUMBER', '+5215512345678');
define('WHATSAPP_MESSAGE', 'Hola, me interesa un casco de Helmets Pro');

/**
 * Función para obtener configuración
 */
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

/**
 * Función para validar configuración
 */
function validateConfig() {
    $required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'APP_NAME'];
    $errors = [];
    
    foreach ($required as $config) {
        if (!defined($config) || empty(constant($config))) {
            $errors[] = "Configuración requerida faltante: $config";
        }
    }
    
    return $errors;
}

/**
 * Función para formatear precio
 */
function formatPrice($price) {
    return getConfig('CURRENCY_SYMBOL') . number_format($price, 2);
}

/**
 * Función para calcular IVA
 */
function calculateIVA($price) {
    return $price * (getConfig('IVA_PORCENTAJE') / 100);
}

/**
 * Función para log
 */
function logMessage($message, $level = 'INFO') {
    if (!is_dir(getConfig('LOG_DIR'))) {
        mkdir(getConfig('LOG_DIR'), 0755, true);
    }
    
    $logFile = getConfig('LOG_DIR') . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Validar configuración al cargar
if (getConfig('DEBUG_MODE')) {
    $configErrors = validateConfig();
    if (!empty($configErrors)) {
        foreach ($configErrors as $error) {
            logMessage($error, 'ERROR');
        }
    }
}
?>
