<?php
session_start();
require_once 'clases/Usuario.php';
require_once 'clases/Carrito.php';
require_once 'administrador/config/paypal_config.php';

// Verificar que el usuario esté logueado
$usuario_obj = new Usuario();
if (!$usuario_obj->estaAutenticado()) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

// Verificar que tenemos datos del pedido
if (!isset($_SESSION['pedido_checkout'])) {
    header('Location: checkout.php?error=sin_datos_pedido');
    exit();
}

$pedido = $_SESSION['pedido_checkout'];
$usuario = $usuario_obj->obtenerUsuarioActual();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago con PayPal - Helmets Pro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: #343a40;
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        
        .header h1 {
            text-align: center;
            font-size: 24px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background: #007bff;
            color: white;
            padding: 15px 20px;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 20px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-muted {
            color: #6c757d;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        #paypal-button-container {
            border: 2px dashed #dee2e6;
            padding: 30px;
            margin: 20px 0;
            background: #f8f9fa;
            border-radius: 8px;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .loading {
            color: #6c757d;
            font-size: 16px;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .order-item:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 18px;
        }
        
        .total {
            color: #28a745;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1><i class="fab fa-paypal"></i> Helmets Pro - Pago Seguro</h1>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2><i class="fab fa-paypal"></i> Pago Seguro con PayPal</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Información del Pedido:</strong><br>
                    Cliente: <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?><br>
                    Email: <?php echo htmlspecialchars($usuario['email']); ?><br>
                    Total: $<?php echo number_format($pedido['total'], 2); ?>
                </div>
                
                <div class="text-center">
                    <h3>Haz clic en el botón de PayPal para completar tu pago</h3>
                    <p class="text-muted">Serás redirigido a PayPal para procesar el pago de forma segura</p>
                </div>
                
                <!-- PayPal Button Container -->
                <div id="paypal-button-container">
                    <div class="loading">
                        <div class="spinner"></div>
                        Cargando botón de PayPal...
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="checkout.php" class="btn btn-secondary">
                        ← Volver al Checkout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Resumen del Pedido -->
        <div class="order-summary">
            <h3>Resumen del Pedido</h3>
            <?php foreach ($pedido['productos'] as $producto): ?>
            <div class="order-item">
                <span><?php echo htmlspecialchars($producto['nombre']); ?> (x<?php echo $producto['cantidad']; ?>)</span>
                <span>$<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span>
            </div>
            <?php endforeach; ?>
            
            <div class="order-item">
                <span>Subtotal:</span>
                <span>$<?php echo number_format($pedido['subtotal'], 2); ?></span>
            </div>
            
            <div class="order-item">
                <span>IVA (16%):</span>
                <span>$<?php echo number_format($pedido['impuestos'], 2); ?></span>
            </div>
            
            <div class="order-item total">
                <span>Total:</span>
                <span>$<?php echo number_format($pedido['total'], 2); ?></span>
            </div>
        </div>
    </div>

    <!-- PayPal SDK - SOLO ESTE SCRIPT -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=MXN"></script>
    
    <script>
    // Esperar a que el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado');
        console.log('PayPal Client ID:', '<?php echo PAYPAL_CLIENT_ID; ?>');
        
        // Verificar si PayPal SDK se cargó
        if (typeof paypal !== 'undefined') {
            console.log('✅ PayPal SDK cargado correctamente');
            
            // Limpiar el contenedor
            document.getElementById('paypal-button-container').innerHTML = '';
            
            paypal.Buttons({
                createOrder: function(data, actions) {
                    console.log('Creando orden...');
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: '<?php echo number_format($pedido['total'], 2, '.', ''); ?>'
                            },
                            description: 'Pedido Helmets Pro - <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>'
                        }]
                    });
                },
                onApprove: function(data, actions) {
                    console.log('Orden aprobada:', data);
                    return actions.order.capture().then(function(details) {
                        console.log('Pago completado:', details);
                        alert('¡Pago exitoso! Redirigiendo...');
                        window.location.href = 'confirmacion_pago.php?orderID=' + data.orderID;
                    });
                },
                onError: function(err) {
                    console.error('Error en PayPal:', err);
                    alert('Error al procesar el pago: ' + err.message);
                },
                onCancel: function(data) {
                    console.log('Pago cancelado:', data);
                    alert('Pago cancelado por el usuario');
                }
            }).render('#paypal-button-container');
            
            console.log('✅ Botón PayPal renderizado');
            
        } else {
            console.error('❌ PayPal SDK no disponible');
            document.getElementById('paypal-button-container').innerHTML = 
                '<div style="color: red; text-align: center; padding: 20px;">' +
                '<strong>Error:</strong> No se pudo cargar PayPal SDK<br>' +
                '<small>Verifica la consola del navegador para más detalles</small>' +
                '</div>';
        }
    });
    </script>
</body>
</html>
