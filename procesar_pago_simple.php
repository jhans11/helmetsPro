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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        #paypal-button-container { 
            border: 2px dashed #ccc; 
            padding: 20px; 
            margin: 20px 0; 
            background: #f9f9f9;
            min-height: 100px;
        }
        .debug-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fab fa-paypal"></i> Pago con PayPal - Versión Simple</h2>
                
                <!-- Información de Debug -->
                <div class="debug-info">
                    <strong>Debug Info:</strong><br>
                    Client ID: <?php echo defined('PAYPAL_CLIENT_ID') ? substr(PAYPAL_CLIENT_ID, 0, 20) . '...' : 'NO DEFINIDO'; ?><br>
                    Usuario: <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?><br>
                    Total: $<?php echo number_format($pedido['total'], 2); ?><br>
                    Productos: <?php echo count($pedido['productos']); ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h4>Pago Seguro con PayPal</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Información del Pedido:</strong><br>
                            Cliente: <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?><br>
                            Email: <?php echo htmlspecialchars($usuario['email']); ?><br>
                            Total: $<?php echo number_format($pedido['total'], 2); ?>
                        </div>
                        
                        <div class="text-center">
                            <h5>Haz clic en el botón de PayPal para completar tu pago</h5>
                            <p class="text-muted">Serás redirigido a PayPal para procesar el pago de forma segura</p>
                        </div>
                        
                        <!-- PayPal Button Container -->
                        <div id="paypal-button-container">
                            <p style="color: #666; text-align: center;">
                                <i class="fas fa-spinner fa-spin"></i> Cargando botón de PayPal...
                            </p>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="checkout.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PayPal SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=MXN"></script>
    
    <script>
    console.log('Script iniciado');
    console.log('PayPal Client ID:', '<?php echo PAYPAL_CLIENT_ID; ?>');
    
    // Verificar si PayPal SDK se cargó
    if (typeof paypal !== 'undefined') {
        console.log('✅ PayPal SDK cargado correctamente');
        
        paypal.Buttons({
            createOrder: function(data, actions) {
                console.log('Creando orden...');
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo $pedido['total']; ?>'
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
            '<i class="fas fa-exclamation-triangle"></i><br>' +
            '<strong>Error:</strong> No se pudo cargar PayPal SDK<br>' +
            '<small>Verifica la consola del navegador para más detalles</small>' +
            '</div>';
    }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>
