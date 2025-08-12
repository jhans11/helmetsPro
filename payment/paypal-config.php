<?php
/**
 * Configuración de PayPal para Helmets Pro
 * Sandbox (pruebas) - Cambiar a producción cuando esté listo
 */

// Configuración de PayPal Sandbox
define('PAYPAL_CLIENT_ID', 'TU_CLIENT_ID_AQUI'); // Reemplazar con tu Client ID
define('PAYPAL_CLIENT_SECRET', 'TU_CLIENT_SECRET_AQUI'); // Reemplazar con tu Client Secret
define('PAYPAL_MODE', 'sandbox'); // 'sandbox' o 'live'

// URLs de PayPal
if (PAYPAL_MODE === 'sandbox') {
    define('PAYPAL_API_URL', 'https://api.sandbox.paypal.com');
    define('PAYPAL_WEB_URL', 'https://www.sandbox.paypal.com');
} else {
    define('PAYPAL_API_URL', 'https://api.paypal.com');
    define('PAYPAL_WEB_URL', 'https://www.paypal.com');
}

// URLs de retorno
$base_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
define('PAYPAL_RETURN_URL', $base_url . '/payment-success.php');
define('PAYPAL_CANCEL_URL', $base_url . '/payment-cancel.php');

/**
 * Clase PayPalAPI para manejar la integración
 */
class PayPalAPI {
    private $client_id;
    private $client_secret;
    private $mode;
    private $api_url;
    
    public function __construct() {
        $this->client_id = PAYPAL_CLIENT_ID;
        $this->client_secret = PAYPAL_CLIENT_SECRET;
        $this->mode = PAYPAL_MODE;
        $this->api_url = PAYPAL_API_URL;
    }
    
    /**
     * Obtener token de acceso de PayPal
     */
    public function getAccessToken() {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->api_url . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->client_id . ":" . $this->client_secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code != 200) {
            throw new Exception("Error al obtener token de PayPal: " . $response);
        }
        
        $result = json_decode($response, true);
        return $result['access_token'];
    }
    
    /**
     * Crear orden de pago en PayPal
     */
    public function createOrder($amount, $currency = 'MXN', $description = 'Pedido Helmets Pro') {
        try {
            $access_token = $this->getAccessToken();
            
            $order_data = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => number_format($amount, 2, '.', '')
                        ],
                        'description' => $description
                    ]
                ],
                'application_context' => [
                    'return_url' => PAYPAL_RETURN_URL,
                    'cancel_url' => PAYPAL_CANCEL_URL,
                    'brand_name' => 'Helmets Pro',
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW'
                ]
            ];
            
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $this->api_url . '/v2/checkout/orders');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code != 201) {
                throw new Exception("Error al crear orden en PayPal: " . $response);
            }
            
            return json_decode($response, true);
            
        } catch (Exception $e) {
            error_log("Error en PayPal createOrder: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Capturar pago de PayPal
     */
    public function captureOrder($order_id) {
        try {
            $access_token = $this->getAccessToken();
            
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $this->api_url . '/v2/checkout/orders/' . $order_id . '/capture');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code != 201) {
                throw new Exception("Error al capturar pago en PayPal: " . $response);
            }
            
            return json_decode($response, true);
            
        } catch (Exception $e) {
            error_log("Error en PayPal captureOrder: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener detalles de una orden
     */
    public function getOrderDetails($order_id) {
        try {
            $access_token = $this->getAccessToken();
            
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $this->api_url . '/v2/checkout/orders/' . $order_id);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $access_token
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code != 200) {
                throw new Exception("Error al obtener detalles de orden PayPal: " . $response);
            }
            
            return json_decode($response, true);
            
        } catch (Exception $e) {
            error_log("Error en PayPal getOrderDetails: " . $e->getMessage());
            throw $e;
        }
    }
}
?>

