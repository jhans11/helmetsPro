<?php
session_start();
require_once 'clases/Usuario.php';
require_once 'clases/Carrito.php';

// Verificar que el usuario esté logueado
$usuario_obj = new Usuario();
if (!$usuario_obj->estaAutenticado()) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

// Verificar que el carrito no esté vacío
Carrito::init();
if (Carrito::estaVacio()) {
    header('Location: carrito.php?error=carrito_vacio');
    exit();
}

// Obtener datos del usuario y carrito
$usuario = $usuario_obj->obtenerUsuarioActual();
$productos_carrito = Carrito::obtenerProductos();
$subtotal = Carrito::calcularSubtotal();
$impuestos = round($subtotal * 0.16, 2); // 16% IVA
$total = round($subtotal + $impuestos, 2);

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario de checkout
if ($_POST && isset($_POST['procesar_pedido'])) {
    // Validar datos de envío
    $datos_envio = [
        'direccion' => trim($_POST['direccion'] ?? ''),
        'ciudad' => trim($_POST['ciudad'] ?? ''),
        'codigo_postal' => trim($_POST['codigo_postal'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'notas' => trim($_POST['notas'] ?? '')
    ];
    
    $errores = [];
    if (empty($datos_envio['direccion'])) $errores[] = "La dirección es requerida";
    if (empty($datos_envio['ciudad'])) $errores[] = "La ciudad es requerida";
    if (empty($datos_envio['codigo_postal'])) $errores[] = "El código postal es requerido";
    if (empty($datos_envio['telefono'])) $errores[] = "El teléfono es requerido";
    
    if (empty($errores)) {
        // Guardar información del pedido en sesión para PayPal
        $_SESSION['pedido_checkout'] = [
            'usuario_id' => $usuario['id'],
            'productos' => $productos_carrito,
            'datos_envio' => $datos_envio,
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $total
        ];
        
        // Redirigir a PayPal
        header('Location: procesar_pago_final.php');
        exit();
    } else {
        $mensaje = implode('<br>', $errores);
        $tipo_mensaje = 'danger';
    }
}

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
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <!-- Formulario de datos de envío -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-shipping-fast"></i> Datos de Envío</h4>
                </div>
                <div class="card-body">
                    <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="checkoutForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre Completo</label>
                                    <input type="text" class="form-control" id="nombre" 
                                           value="<?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>" 
                                           readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?php echo htmlspecialchars($usuario['email']); ?>" 
                                           readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion">Dirección de Envío *</label>
                            <textarea class="form-control" id="direccion" name="direccion" 
                                      rows="2" placeholder="Calle, número, colonia..." required><?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ciudad">Ciudad *</label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                           value="<?php echo htmlspecialchars($usuario['ciudad'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="codigo_postal">Código Postal *</label>
                                    <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" 
                                           value="<?php echo htmlspecialchars($usuario['codigo_postal'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="telefono">Teléfono *</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="notas">Notas del Pedido (Opcional)</label>
                            <textarea class="form-control" id="notas" name="notas" 
                                      rows="3" placeholder="Instrucciones especiales de entrega..."></textarea>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <button type="submit" name="procesar_pedido" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card"></i> Procesar Pedido con PayPal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Resumen del pedido -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-receipt"></i> Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <!-- Productos del carrito -->
                    <?php if (!empty($productos_carrito)): ?>
                        <?php foreach ($productos_carrito as $producto): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($producto['imagen'])): ?>
                                <img src="./img/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                     class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <?php endif; ?>
                                <div>
                                    <small class="fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></small>
                                    <br>
                                    <small class="text-muted">Cantidad: <?php echo $producto['cantidad']; ?></small>
                                </div>
                            </div>
                            <span class="fw-bold">$<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No hay productos en el carrito</p>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <!-- Totales -->
                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>IVA (16%):</span>
                        <span>$<?php echo number_format($impuestos, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold h5">
                        <span>Total:</span>
                        <span class="text-success">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt"></i> Pago seguro con PayPal<br>
                            <i class="fas fa-truck"></i> Envío gratis en pedidos mayores a $500
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Información de seguridad -->
            <div class="card mt-3">
                <div class="card-body text-center">
                    <h6><i class="fas fa-lock text-success"></i> Compra Segura</h6>
                    <small class="text-muted">
                        Tus datos están protegidos con encriptación SSL
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    submitBtn.disabled = true;
    
    // Si hay algún error, restaurar el botón
    setTimeout(() => {
        if (!this.checkValidity()) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }, 100);
});
</script>

<style>
.img-thumbnail {
    border-radius: 8px;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
</style>

<?php include('template/pie.php'); ?>

