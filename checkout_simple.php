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
$impuestos = round($subtotal * 0.16, 2);
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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Helmets Pro</title>
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
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .col-md-6 {
            flex: 0 0 50%;
            padding: 0 10px;
        }
        
        .col-md-3 {
            flex: 0 0 25%;
            padding: 0 10px;
        }
        
        .col-md-8 {
            flex: 0 0 66.666667%;
            padding: 0 10px;
        }
        
        .col-md-4 {
            flex: 0 0 33.333333%;
            padding: 0 10px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
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
        
        .breadcrumb {
            background: #e9ecef;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🛒 Helmets Pro - Checkout</h1>
        </div>
    </div>

    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Inicio</a> / 
            <a href="productos.php">Productos</a> / 
            <a href="carrito.php">Carrito</a> / 
            <span>Checkout</span>
        </div>
        
        <div class="row">
            <!-- Formulario de datos de envío -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2>🚚 Datos de Envío</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($mensaje): ?>
                        <div class="alert alert-danger">
                            <?php echo $mensaje; ?>
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
                                <button type="submit" name="procesar_pedido" class="btn btn-success">
                                    💳 Procesar Pedido con PayPal
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
                        <h2>📋 Resumen del Pedido</h2>
                    </div>
                    <div class="card-body">
                        <!-- Productos del carrito -->
                        <?php if (!empty($productos_carrito)): ?>
                            <?php foreach ($productos_carrito as $producto): ?>
                            <div class="order-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong><br>
                                    <small>Cantidad: <?php echo $producto['cantidad']; ?></small>
                                </div>
                                <span>$<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay productos en el carrito</p>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <!-- Totales -->
                        <div class="order-item">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="order-item">
                            <span>IVA (16%):</span>
                            <span>$<?php echo number_format($impuestos, 2); ?></span>
                        </div>
                        <div class="order-item total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <div style="margin-top: 20px; text-align: center;">
                            <small style="color: #6c757d;">
                                🔒 Pago seguro con PayPal<br>
                                🚚 Envío gratis en pedidos mayores a $500
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
