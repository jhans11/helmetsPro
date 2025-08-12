<?php
session_start();
require_once '../clases/Pedido.php';
require_once '../clases/Carrito.php';
require_once 'paypal-config.php';

// Verificar que hay información de pedido y PayPal
if (!isset($_SESSION['pedido_checkout']) || !isset($_SESSION['paypal_order_id'])) {
    header('Location: ../checkout.php?error=sesion_invalida');
    exit();
}

$pedido_info = $_SESSION['pedido_checkout'];
$paypal_order_id = $_SESSION['paypal_order_id'];

// Obtener token y PayerID de PayPal
$token = $_GET['token'] ?? '';
$payer_id = $_GET['PayerID'] ?? '';

if (empty($token) || empty($payer_id)) {
    header('Location: ../checkout.php?error=pago_incompleto');
    exit();
}

try {
    // Capturar el pago en PayPal
    $paypal = new PayPalAPI();
    $capture_result = $paypal->captureOrder($paypal_order_id);
    
    // Verificar que el pago fue exitoso
    if ($capture_result['status'] === 'COMPLETED') {
        // Obtener ID de transacción
        $transaction_id = $capture_result['purchase_units'][0]['payments']['captures'][0]['id'];
        
        // Actualizar el pedido como pagado
        $pedido = new Pedido();
        $actualizado = $pedido->actualizarEstado(
            $pedido_info['pedido_id'], 
            'pagado', 
            $transaction_id, 
            $paypal_order_id
        );
        
        if ($actualizado) {
            // Limpiar carrito
            Carrito::limpiarCarrito();
            
            // Limpiar sesión de checkout
            unset($_SESSION['pedido_checkout']);
            unset($_SESSION['paypal_order_id']);
            
            // Redirigir a página de confirmación
            header('Location: ../pedido-confirmado.php?pedido=' . $pedido_info['numero_pedido']);
            exit();
        } else {
            throw new Exception("Error al actualizar el estado del pedido");
        }
    } else {
        throw new Exception("El pago no fue completado exitosamente");
    }
    
} catch (Exception $e) {
    error_log("Error en payment-success: " . $e->getMessage());
    
    // Marcar pedido como fallido
    try {
        $pedido = new Pedido();
        $pedido->actualizarEstado($pedido_info['pedido_id'], 'cancelado');
    } catch (Exception $update_error) {
        error_log("Error adicional al cancelar pedido: " . $update_error->getMessage());
    }
    
    header('Location: ../checkout.php?error=pago_fallido&message=' . urlencode($e->getMessage()));
    exit();
}
?>

