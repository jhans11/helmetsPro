<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 TEST PROCESAR PAGO</h2>";

// 1. Verificar sesión
echo "<h3>1. Estado de la Sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Verificar clases
echo "<h3>2. Verificar Clases:</h3>";
try {
    require_once 'clases/Usuario.php';
    require_once 'clases/Carrito.php';
    require_once 'administrador/config/paypal_config.php';
    
    echo "✅ Todas las clases cargadas<br>";
    
    // Verificar PayPal config
    if (defined('PAYPAL_CLIENT_ID')) {
        echo "✅ PAYPAL_CLIENT_ID definido: " . substr(PAYPAL_CLIENT_ID, 0, 20) . "...<br>";
    } else {
        echo "❌ PAYPAL_CLIENT_ID NO definido<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error cargando clases: " . $e->getMessage() . "<br>";
}

// 3. Verificar usuario
echo "<h3>3. Verificar Usuario:</h3>";
try {
    $usuario_obj = new Usuario();
    if ($usuario_obj->estaAutenticado()) {
        echo "✅ Usuario autenticado<br>";
        $usuario = $usuario_obj->obtenerUsuarioActual();
        echo "Tipo de usuario: " . gettype($usuario) . "<br>";
        if (is_array($usuario)) {
            echo "✅ Usuario es array<br>";
            echo "Datos del usuario:<br>";
            echo "<pre>";
            print_r($usuario);
            echo "</pre>";
        } else {
            echo "❌ Usuario NO es array, es: " . gettype($usuario) . "<br>";
        }
    } else {
        echo "❌ Usuario NO autenticado<br>";
    }
} catch (Exception $e) {
    echo "❌ Error con usuario: " . $e->getMessage() . "<br>";
}

// 4. Verificar pedido
echo "<h3>4. Verificar Pedido:</h3>";
if (isset($_SESSION['pedido_checkout'])) {
    echo "✅ Pedido en sesión<br>";
    echo "<pre>";
    print_r($_SESSION['pedido_checkout']);
    echo "</pre>";
} else {
    echo "❌ NO hay pedido en sesión<br>";
    
    // Crear pedido de prueba
    echo "<h4>Crear pedido de prueba:</h4>";
    try {
        $usuario_obj = new Usuario();
        $usuario = $usuario_obj->obtenerUsuarioActual();
        
        Carrito::init();
        $productos_carrito = Carrito::obtenerProductos();
        $subtotal = Carrito::calcularSubtotal();
        $impuestos = $subtotal * 0.16;
        $total = $subtotal + $impuestos;
        
        $_SESSION['pedido_checkout'] = [
            'usuario_id' => $usuario['id'],
            'productos' => $productos_carrito,
            'datos_envio' => [
                'direccion' => 'Test Dirección',
                'ciudad' => 'Test Ciudad',
                'codigo_postal' => '12345',
                'telefono' => '555-0000',
                'notas' => 'Test'
            ],
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $total
        ];
        
        echo "✅ Pedido de prueba creado<br>";
        echo "<a href='procesar_pago.php' class='btn btn-success'>Ir a Procesar Pago</a>";
        
    } catch (Exception $e) {
        echo "❌ Error creando pedido: " . $e->getMessage() . "<br>";
    }
}

// 5. Enlaces de prueba
echo "<h3>5. Enlaces de Prueba:</h3>";
echo "<a href='checkout.php' target='_blank'>🛒 Ir a Checkout</a><br>";
echo "<a href='procesar_pago.php' target='_blank'>💳 Ir a Procesar Pago</a><br>";
echo "<a href='test_procesar_pago.php' target='_blank'>🔄 Recargar Test</a><br>";

?>

<style>
.btn {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
}
.btn-success {
    background-color: #28a745;
}
</style>
