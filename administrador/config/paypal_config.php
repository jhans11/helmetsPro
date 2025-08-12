<?php
/**
 * �� CONFIGURACIÓN PAYPAL SANDBOX - Helmets Pro
 * 
 * Este archivo contiene la configuración para PayPal Sandbox
 * IMPORTANTE: En producción, cambiar a credenciales reales
 */

// Configuración de PayPal Sandbox
define('PAYPAL_SANDBOX', true); // Cambiar a false en producción

// Credenciales de PayPal Sandbox
// OBTENER DESDE: https://developer.paypal.com/developer/applications/
define('PAYPAL_CLIENT_ID', 'AX25gxQ8A9YJVwWU0dw9iyHesy--c_5inXctOETohOVh09JUI-YKY2VuYDxdW7IAk5IwV3nnScFGrO2x');
define('PAYPAL_CLIENT_SECRET', 'EGgMyqXsM6Eg5cEc4tNVH4ewwdauZt2u7oMTmqtBpWpNVsfxXPXjlYxbgVDzv4ZjN6OkHiaKyXkO9TIG
');

// URLs de PayPal
if (PAYPAL_SANDBOX) {
    // URLs de Sandbox (pruebas)
    define('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com');
    define('PAYPAL_WEB_URL', 'https://www.sandbox.paypal.com');
    define('PAYPAL_SDK_URL', 'https://www.paypal.com/sdk/js');
} else {
    // URLs de Producción
    define('PAYPAL_BASE_URL', 'https://api-m.paypal.com');
    define('PAYPAL_WEB_URL', 'https://www.paypal.com');
    define('PAYPAL_SDK_URL', 'https://www.paypal.com/sdk/js');
}

// Configuración de la aplicación
define('PAYPAL_CURRENCY', 'USD'); // Moneda por defecto
define('PAYPAL_LOCALE', 'es-ES'); // Idioma por defecto
define('PAYPAL_INTENT', 'capture'); // capture o authorize

// Configuración de botones
define('PAYPAL_BUTTON_COLOR', 'gold'); // gold, blue, silver, black, white
define('PAYPAL_BUTTON_SHAPE', 'rect'); // rect, pill
define('PAYPAL_BUTTON_LABEL', 'pay'); // pay, paypal, buynow, checkout, paypalcredit

// Configuración de webhooks (para producción)
define('PAYPAL_WEBHOOK_ID', ''); // ID del webhook en producción

// Configuración de notificaciones
define('PAYPAL_NOTIFICATION_EMAIL', 'admin@helmetspro.com');

// Configuración de timeouts
define('PAYPAL_TIMEOUT', 30); // segundos

// Configuración de reintentos
define('PAYPAL_MAX_RETRIES', 3);

// Configuración de logging
define('PAYPAL_LOG_ENABLED', true);
define('PAYPAL_LOG_FILE', __DIR__ . '/../../logs/paypal.log');

/**
 * Obtener configuración de PayPal para JavaScript
 */
function getPayPalConfig() {
    return [
        'clientId' => PAYPAL_CLIENT_ID,
        'currency' => PAYPAL_CURRENCY,
        'intent' => PAYPAL_INTENT,
        'locale' => PAYPAL_LOCALE
    ];
}

/**
 * Obtener configuración de botón de PayPal
 */
function getPayPalButtonConfig() {
    return [
        'color' => PAYPAL_BUTTON_COLOR,
        'shape' => PAYPAL_BUTTON_SHAPE,
        'label' => PAYPAL_BUTTON_LABEL
    ];
}

/**
 * Verificar si la configuración está completa
 */
function isPayPalConfigured() {
    return !empty(PAYPAL_CLIENT_ID) && 
           !empty(PAYPAL_CLIENT_SECRET) && 
           PAYPAL_CLIENT_ID !== 'TU_CLIENT_ID_AQUI';
}

/**
 * Obtener URL de PayPal según el entorno
 */
function getPayPalUrl($endpoint = '') {
    return PAYPAL_BASE_URL . $endpoint;
}

/**
 * Log de PayPal
 */
function paypalLog($message, $type = 'INFO') {
    if (!PAYPAL_LOG_ENABLED) return;
    
    $logDir = dirname(PAYPAL_LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$type}] {$message}" . PHP_EOL;
    
    file_put_contents(PAYPAL_LOG_FILE, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Validar respuesta de PayPal
 */
function validatePayPalResponse($response) {
    if (!$response) {
        paypalLog('Respuesta vacía de PayPal', 'ERROR');
        return false;
    }
    
    if (isset($response['error'])) {
        paypalLog('Error de PayPal: ' . json_encode($response['error']), 'ERROR');
        return false;
    }
    
    return true;
}

/**
 * Obtener mensaje de error de PayPal
 */
function getPayPalErrorMessage($error) {
    $errorMessages = [
        'PAYMENT_DENIED' => 'Pago denegado',
        'PAYMENT_FAILED' => 'Pago fallido',
        'PAYMENT_PENDING' => 'Pago pendiente',
        'PAYMENT_REVERSED' => 'Pago revertido',
        'PAYMENT_UNCLAIMED' => 'Pago no reclamado',
        'PAYMENT_EXPIRED' => 'Pago expirado',
        'PAYMENT_CANCELLED' => 'Pago cancelado',
        'INVALID_REQUEST' => 'Solicitud inválida',
        'UNAUTHORIZED' => 'No autorizado',
        'FORBIDDEN' => 'Acceso prohibido',
        'NOT_FOUND' => 'No encontrado',
        'INTERNAL_SERVER_ERROR' => 'Error interno del servidor'
    ];
    
    return $errorMessages[$error] ?? 'Error desconocido de PayPal';
}

// Verificar configuración al cargar el archivo
if (!isPayPalConfigured()) {
    paypalLog('PayPal no está configurado correctamente. Revisa las credenciales.', 'WARNING');
}
?>