<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 TEST FORMULARIO CHECKOUT</h2>";

// 1. Verificar si se envió el formulario
echo "<h3>1. Datos POST recibidos:</h3>";
if ($_POST) {
    echo "✅ Formulario enviado<br>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} else {
    echo "❌ No hay datos POST<br>";
}

// 2. Verificar sesión
echo "<h3>2. Estado de la sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 3. Simular el procesamiento del checkout
echo "<h3>3. Simular procesamiento:</h3>";

try {
    require_once 'clases/Usuario.php';
    require_once 'clases/Carrito.php';
    
    $usuario_obj = new Usuario();
    if ($usuario_obj->estaAutenticado()) {
        echo "✅ Usuario autenticado<br>";
        $usuario = $usuario_obj->obtenerUsuarioActual();
        echo "Usuario ID: " . $usuario['id'] . "<br>";
        
        Carrito::init();
        if (!Carrito::estaVacio()) {
            echo "✅ Carrito tiene productos<br>";
            $productos_carrito = Carrito::obtenerProductos();
            $subtotal = Carrito::calcularSubtotal();
            $impuestos = $subtotal * 0.16;
            $total = $subtotal + $impuestos;
            
            echo "Subtotal: $" . number_format($subtotal, 2) . "<br>";
            echo "Impuestos: $" . number_format($impuestos, 2) . "<br>";
            echo "Total: $" . number_format($total, 2) . "<br>";
            
            // Simular datos de envío
            $datos_envio = [
                'direccion' => 'Av. Reforma 123',
                'ciudad' => 'Ciudad de México',
                'codigo_postal' => '06000',
                'telefono' => '555-0101',
                'notas' => 'Test de envío'
            ];
            
            // Crear pedido en sesión
            $_SESSION['pedido_checkout'] = [
                'usuario_id' => $usuario['id'],
                'productos' => $productos_carrito,
                'datos_envio' => $datos_envio,
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $total
            ];
            
            echo "✅ Pedido creado en sesión<br>";
            echo "<a href='procesar_pago.php' class='btn btn-success'>Ir a PayPal</a>";
            
        } else {
            echo "❌ Carrito vacío<br>";
        }
    } else {
        echo "❌ Usuario no autenticado<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 4. Formulario de prueba
echo "<h3>4. Formulario de prueba:</h3>";
?>
<form method="POST" action="test_checkout_form.php">
    <div class="form-group">
        <label>Dirección:</label>
        <input type="text" name="direccion" value="Av. Reforma 123" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Ciudad:</label>
        <input type="text" name="ciudad" value="Ciudad de México" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Código Postal:</label>
        <input type="text" name="codigo_postal" value="06000" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Teléfono:</label>
        <input type="text" name="telefono" value="555-0101" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Notas:</label>
        <textarea name="notas" class="form-control">Test de envío</textarea>
    </div>
    <button type="submit" name="procesar_pedido" class="btn btn-primary">Procesar Pedido</button>
</form>

<style>
.form-group { margin-bottom: 15px; }
.form-control { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
.btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
.btn-primary { background-color: #007bff; color: white; }
.btn-success { background-color: #28a745; color: white; text-decoration: none; display: inline-block; margin-top: 10px; }
</style>