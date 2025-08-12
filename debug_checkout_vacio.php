<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 DIAGNÓSTICO CHECKOUT VACÍO</h2>";
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
    'template/cabecera.php',
    'template/pie.php',
    'checkout.php'
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
        
        $subtotal = Carrito::calcularSubtotal();
        $impuestos = round($subtotal * 0.16, 2);
        $total = round($subtotal + $impuestos, 2);
        
        echo "<p><strong>Subtotal:</strong> $subtotal</p>";
        echo "<p><strong>Impuestos:</strong> $impuestos</p>";
        echo "<p><strong>Total:</strong> $total</p>";
        
    } else {
        echo "<span class='error'>❌ Carrito vacío</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Error con Carrito: " . $e->getMessage() . "</span><br>";
}

// 5. Verificar template cabecera
echo "<h3>5. Verificar Template Cabecera:</h3>";
if (file_exists('template/cabecera.php')) {
    $contenido = file_get_contents('template/cabecera.php');
    if (strpos($contenido, '<?php') !== false) {
        echo "<span class='success'>✅ Template cabecera tiene código PHP</span><br>";
    } else {
        echo "<span class='error'>❌ Template cabecera NO tiene código PHP</span><br>";
    }
    
    // Verificar si tiene Bootstrap
    if (strpos($contenido, 'bootstrap') !== false) {
        echo "<span class='success'>✅ Template incluye Bootstrap</span><br>";
    } else {
        echo "<span class='error'>❌ Template NO incluye Bootstrap</span><br>";
    }
} else {
    echo "<span class='error'>❌ Template cabecera no existe</span><br>";
}

// 6. Simular checkout sin template
echo "<h3>6. Simular Checkout Sin Template:</h3>";
echo "<div style='border:2px solid #ccc;padding:20px;margin:20px;background:#f9f9f9;'>";

try {
    // Simular el código de checkout
    $usuario_obj = new Usuario();
    $usuario = $usuario_obj->obtenerUsuarioActual();
    
    Carrito::init();
    $productos_carrito = Carrito::obtenerProductos();
    $subtotal = Carrito::calcularSubtotal();
    $impuestos = round($subtotal * 0.16, 2);
    $total = round($subtotal + $impuestos, 2);
    
    echo "<h4>Datos del Usuario:</h4>";
    echo "<p><strong>Nombre:</strong> " . htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($usuario['email']) . "</p>";
    echo "<p><strong>Dirección:</strong> " . htmlspecialchars($usuario['direccion'] ?? 'No definida') . "</p>";
    
    echo "<h4>Productos en Carrito:</h4>";
    echo "<p><strong>Cantidad:</strong> " . count($productos_carrito) . "</p>";
    
    echo "<h4>Cálculos:</h4>";
    echo "<p><strong>Subtotal:</strong> $" . number_format($subtotal, 2) . "</p>";
    echo "<p><strong>Impuestos:</strong> $" . number_format($impuestos, 2) . "</p>";
    echo "<p><strong>Total:</strong> $" . number_format($total, 2) . "</p>";
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Error en simulación: " . $e->getMessage() . "</span><br>";
}

echo "</div>";

// 7. Enlaces de prueba
echo "<h3>7. Enlaces de Prueba:</h3>";
echo "<a href='checkout.php' target='_blank' style='background:blue;color:white;padding:10px;margin:5px;text-decoration:none;border-radius:5px;'>🛒 Ir a Checkout</a>";
echo "<a href='carrito.php' target='_blank' style='background:green;color:white;padding:10px;margin:5px;text-decoration:none;border-radius:5px;'>🛍️ Ir a Carrito</a>";
echo "<a href='debug_checkout_vacio.php' target='_blank' style='background:purple;color:white;padding:10px;margin:5px;text-decoration:none;border-radius:5px;'>🔄 Recargar Debug</a>";

?>
