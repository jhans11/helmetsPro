<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 DIAGNÓSTICO PROCESAR PAGO</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .error{color:red;} .success{color:green;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

// 1. Verificar sesión
echo "<h3>1. Estado de la Sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Verificar archivos
echo "<h3>2. Verificar Archivos:</h3>";
$archivos = [
    'clases/Usuario.php',
    'clases/Carrito.php', 
    'administrador/config/paypal_config.php',
    'template/cabecera.php',
    'template/pie.php'
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "<span class='success'>✅ $archivo existe</span><br>";
    } else {
        echo "<span class='error'>❌ $archivo NO existe</span><br>";
    }
}

// 3. Verificar clases
echo "<h3>3. Verificar Clases:</h3>";
try {
    require_once 'clases/Usuario.php';
    echo "<span class='success'>✅ Clase Usuario cargada</span><br>";
    
    $usuario_obj = new Usuario();
    echo "<span class='success'>✅ Instancia de Usuario creada</span><br>";
    
    if ($usuario_obj->estaAutenticado()) {
        echo "<span class='success'>✅ Usuario autenticado</span><br>";
        
        $usuario = $usuario_obj->obtenerUsuarioActual();
        echo "<span class='info'>Tipo de usuario: " . gettype($usuario) . "</span><br>";
        
        if (is_array($usuario)) {
            echo "<span class='success'>✅ Usuario es array</span><br>";
            echo "<pre>";
            print_r($usuario);
            echo "</pre>";
        } else {
            echo "<span class='error'>❌ Usuario NO es array</span><br>";
        }
    } else {
        echo "<span class='error'>❌ Usuario NO autenticado</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Error con Usuario: " . $e->getMessage() . "</span><br>";
}

// 4. Verificar carrito
echo "<h3>4. Verificar Carrito:</h3>";
try {
    require_once 'clases/Carrito.php';
    echo "<span class='success'>✅ Clase Carrito cargada</span><br>";
    
    Carrito::init();
    $productos = Carrito::obtenerProductos();
    echo "<span class='info'>Productos en carrito: " . count($productos) . "</span><br>";
    
    if (!empty($productos)) {
        echo "<pre>";
        print_r($productos);
        echo "</pre>";
    } else {
        echo "<span class='error'>❌ Carrito vacío</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Error con Carrito: " . $e->getMessage() . "</span><br>";
}

// 5. Verificar PayPal config
echo "<h3>5. Verificar PayPal Config:</h3>";
try {
    require_once 'administrador/config/paypal_config.php';
    echo "<span class='success'>✅ PayPal config cargado</span><br>";
    
    if (defined('PAYPAL_CLIENT_ID')) {
        echo "<span class='success'>✅ PAYPAL_CLIENT_ID definido: " . substr(PAYPAL_CLIENT_ID, 0, 20) . "...</span><br>";
    } else {
        echo "<span class='error'>❌ PAYPAL_CLIENT_ID NO definido</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Error con PayPal config: " . $e->getMessage() . "</span><br>";
}

// 6. Verificar pedido en sesión
echo "<h3>6. Verificar Pedido en Sesión:</h3>";
if (isset($_SESSION['pedido_checkout'])) {
    echo "<span class='success'>✅ Pedido en sesión</span><br>";
    echo "<pre>";
    print_r($_SESSION['pedido_checkout']);
    echo "</pre>";
} else {
    echo "<span class='error'>❌ NO hay pedido en sesión</span><br>";
    
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
        
        echo "<span class='success'>✅ Pedido de prueba creado</span><br>";
        echo "<a href='procesar_pago.php' style='background:green;color:white;padding:10px;text-decoration:none;border-radius:5px;'>Ir a Procesar Pago</a>";
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Error creando pedido: " . $e->getMessage() . "</span><br>";
    }
}

// 7. Enlaces de prueba
echo "<h3>7. Enlaces de Prueba:</h3>";
echo "<a href='checkout.php' target='_blank' style='background:blue;color:white;padding:10px;margin:5px;text-decoration:none;border-radius:5px;'>🛒 Ir a Checkout</a>";
echo "<a href='procesar_pago.php' target='_blank' style='background:orange;color:white;padding:10px;margin:5px;text-decoration:none;border-radius:5px;'>💳 Ir a Procesar Pago</a>";
echo "<a href='debug_procesar_pago.php' target='_blank' style='background:purple;color:white;padding:10px;margin:5px;text-decoration:none;border-radius:5px;'>🔄 Recargar Debug</a>";

?>
