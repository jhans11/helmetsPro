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

include('template/cabecera.php');
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="productos.php">Productos</a></li>
                    <li class="breadcrumb-item"><a href="carrito.php">Carrito</a></li>
                    <li class="breadcrumb-item"><a href="checkout.php">Checkout</a></li>
                    <li class="breadcrumb-item active">Pago con PayPal</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fab fa-paypal"></i> Pago Seguro con PayPal</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Información del Pedido:</strong><br>
                        
                       
                    </div>
                    
                    <div class="text-center">
                        <h5>Haz clic en el botón de PayPal para completar tu pago</h5>
                        <p class="text-muted">Serás redirigido a PayPal para procesar el pago de forma segura</p>
                    </div>
                    
                    <!-- PayPal Button Container -->
                    <div id="paypal-button-container" class="text-center mt-4"></div>
                    
                    <div class="text-center mt-3">
                        <a href="checkout.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-receipt"></i> Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($pedido['productos'] as $producto): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <small class="fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></small>
                            <br>
                            <small class="text-muted">Cantidad: <?php echo $producto['cantidad']; ?></small>
                        </div>
                        <span class="fw-bold">$<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($pedido['subtotal'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>IVA (16%):</span>
                        <span>$<?php echo number_format($pedido['impuestos'], 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold h5">
                        <span>Total:</span>
                        <span class="text-success">$<?php echo number_format($pedido['total'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=MXN"></script>

<script>
paypal.Buttons({
    createOrder: function(data, actions) {
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
        return actions.order.capture().then(function(details) {
            // Redirigir a la página de confirmación
            window.location.href = 'confirmacion_pago.php?orderID=' + data.orderID;
        });
    },
    onError: function(err) {
        console.error('Error en PayPal:', err);
        alert('Error al procesar el pago. Por favor intenta de nuevo.');
    }
}).render('#paypal-button-container');
</script>

<?php include('template/pie.php'); ?>
