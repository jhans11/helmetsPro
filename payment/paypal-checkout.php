<?php
session_start();
require_once '../clases/Pedido.php';
require_once 'paypal-config.php';

// Verificar que hay un pedido en sesión
if (!isset($_SESSION['pedido_checkout'])) {
    header('Location: ../checkout.php?error=no_pedido');
    exit();
}

$pedido_info = $_SESSION['pedido_checkout'];

try {
    // Crear orden en PayPal
    $paypal = new PayPalAPI();
    $order = $paypal->createOrder(
        $pedido_info['total'], 
        'MXN', 
        'Pedido #' . $pedido_info['numero_pedido'] . ' - Helmets Pro'
    );
    
    // Guardar PayPal Order ID en la sesión
    $_SESSION['paypal_order_id'] = $order['id'];
    
    // Obtener URL de aprobación
    $approval_url = '';
    foreach ($order['links'] as $link) {
        if ($link['rel'] === 'approve') {
            $approval_url = $link['href'];
            break;
        }
    }
    
    if (empty($approval_url)) {
        throw new Exception("No se pudo obtener URL de aprobación de PayPal");
    }
    
    // Redirigir a PayPal para aprobación
    header('Location: ' . $approval_url);
    exit();
    
} catch (Exception $e) {
    error_log("Error en checkout PayPal: " . $e->getMessage());
    header('Location: ../checkout.php?error=paypal_error&message=' . urlencode($e->getMessage()));
    exit();
}
?>

