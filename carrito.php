<?php
session_start();
require_once 'clases/Carrito.php';

// Inicializar carrito
Carrito::init();

include("template/cabecera.php");

// Obtener productos del carrito
$productos = Carrito::obtenerProductos();
$subtotal = Carrito::calcularSubtotal();
$iva = $subtotal * 0.16; // 16% IVA
$total = $subtotal + $iva;

// Procesar actualizaciones del carrito
if ($_POST) {
    if (isset($_POST['actualizar'])) {
        foreach ($_POST['cantidad'] as $producto_id => $cantidad) {
            if ($cantidad > 0) {
                Carrito::actualizarCantidad($producto_id, $cantidad);
            } else {
                Carrito::eliminarProducto($producto_id);
            }
        }
        header('Location: carrito.php');
        exit();
    }
    
    if (isset($_POST['eliminar'])) {
        $producto_id = $_POST['producto_id'];
        Carrito::eliminarProducto($producto_id);
        header('Location: carrito.php');
        exit();
    }
    
    if (isset($_POST['limpiar'])) {
        Carrito::limpiarCarrito();
        header('Location: carrito.php');
        exit();
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-shopping-cart"></i> Carrito de Compras</h2>
            <hr>
        </div>
    </div>

    <?php if (Carrito::estaVacio()): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                    <h4>Tu carrito está vacío</h4>
                    <p>Agrega algunos productos para comenzar a comprar.</p>
                    <a href="productos.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Ver Productos
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5><i class="fas fa-list"></i> Productos en el Carrito</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($productos as $producto): ?>
                                <div class="row mb-3 border-bottom pb-3">
                                    <div class="col-md-2">
                                        <?php
                                        $imagen = !empty($producto['imagen']) && file_exists($producto['imagen'])
                                            ? htmlspecialchars($producto['imagen'])
                                            : 'img/no-image.png'; // Ruta a tu imagen por defecto
                                        ?>
                                        <img src="<?php echo $imagen; ?>"
                                             class="img-fluid" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <h6><?php echo htmlspecialchars($producto['nombre']); ?></h6>
                                        <p class="text-muted mb-0">Precio: $<?php echo number_format($producto['precio'], 2); ?></p>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="cantidad_<?php echo $producto['id']; ?>">Cantidad:</label>
                                        <input type="number" name="cantidad[<?php echo $producto['id']; ?>]" 
                                               id="cantidad_<?php echo $producto['id']; ?>"
                                               value="<?php echo $producto['cantidad']; ?>" 
                                               min="1" max="99" class="form-control">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Subtotal:</label>
                                        <p class="text-right">$<?php echo number_format($producto['cantidad'] * $producto['precio'], 2); ?></p>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" name="eliminar" value="<?php echo $producto['id']; ?>" 
                                                class="btn btn-danger btn-sm" 
                                                onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <button type="submit" name="actualizar" class="btn btn-primary">
                                        <i class="fas fa-sync-alt"></i> Actualizar Carrito
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" name="limpiar" class="btn btn-warning" 
                                            onclick="return confirm('¿Estás seguro de que quieres vaciar el carrito?')">
                                        <i class="fas fa-trash-alt"></i> Vaciar Carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5><i class="fas fa-calculator"></i> Resumen de Compra</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6">Subtotal:</div>
                                <div class="col-6 text-right">$<?php echo number_format($subtotal, 2); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">IVA (16%):</div>
                                <div class="col-6 text-right">$<?php echo number_format($iva, 2); ?></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-6"><strong>Total:</strong></div>
                                <div class="col-6 text-right"><strong>$<?php echo number_format($total, 2); ?></strong></div>
                            </div>
                            
                            <!-- ✅ NUEVO: Botón de Checkout -->
                            <div class="d-grid gap-2">
                                <?php if (isset($_SESSION['cliente_id'])): ?>
                                    <a href="checkout_simple.php" class="btn btn-success btn-lg">
                                        <i class="fas fa-credit-card"></i> Proceder al Pago
                                    </a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión para Comprar
                                    </a>
                                    <a href="registro.php" class="btn btn-outline-primary">
                                        <i class="fas fa-user-plus"></i> Crear Cuenta
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    <i class="fas fa-lock"></i> Pago seguro con PayPal
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6><i class="fas fa-info-circle"></i> Información Importante</h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success"></i> Envío gratuito en compras mayores a $50</li>
                                <li><i class="fas fa-check text-success"></i> Devoluciones gratuitas hasta 30 días</li>
                                <li><i class="fas fa-check text-success"></i> Garantía de 1 año en todos los productos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include("template/pie.php"); ?>
