<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 DIAGNÓSTICO CHECKOUT</h2>";

// 1. Verificar sesión
echo "<h3>1. Estado de la Sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Verificar si el usuario está logueado
echo "<h3>2. Verificar Usuario:</h3>";
if (isset($_SESSION['cliente_id'])) {
    echo "✅ Usuario logueado: ID " . $_SESSION['cliente_id'] . "<br>";
    echo "Email: " . ($_SESSION['cliente_email'] ?? 'No disponible') . "<br>";
} else {
    echo "❌ Usuario NO logueado<br>";
}

// 3. Verificar carrito
echo "<h3>3. Estado del Carrito:</h3>";
if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
    echo "✅ Carrito tiene productos:<br>";
    echo "<pre>";
    print_r($_SESSION['carrito']);
    echo "</pre>";
} else {
    echo "❌ Carrito vacío o no existe<br>";
}

// 4. Verificar clases
echo "<h3>4. Verificar Clases:</h3>";
try {
    require_once 'clases/Usuario.php';
    echo "✅ Clase Usuario cargada<br>";
    
    $usuario_obj = new Usuario();
    echo "✅ Objeto Usuario creado<br>";
    
    if ($usuario_obj->estaAutenticado()) {
        echo "✅ Usuario autenticado según la clase<br>";
        $usuario = $usuario_obj->obtenerUsuarioActual();
        echo "Datos del usuario:<br>";
        echo "<pre>";
        print_r($usuario);
        echo "</pre>";
    } else {
        echo "❌ Usuario NO autenticado según la clase<br>";
    }
} catch (Exception $e) {
    echo "❌ Error con clase Usuario: " . $e->getMessage() . "<br>";
}

try {
    require_once 'clases/Carrito.php';
    echo "✅ Clase Carrito cargada<br>";
    
    Carrito::init();
    if (Carrito::estaVacio()) {
        echo "❌ Carrito vacío según la clase<br>";
    } else {
        echo "✅ Carrito tiene productos según la clase<br>";
        $productos = Carrito::obtenerProductos();
        echo "Productos en carrito:<br>";
        echo "<pre>";
        print_r($productos);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "❌ Error con clase Carrito: " . $e->getMessage() . "<br>";
}

// 5. Verificar base de datos
echo "<h3>5. Verificar Base de Datos:</h3>";
try {
    require_once 'administrador/config/DB.php';
    $db = DB::getInstance();
    echo "✅ Conexión a BD exitosa<br>";
    
    // Verificar tabla usuarios_clientes
    $usuarios = $db->fetchAll("SELECT COUNT(*) as total FROM usuarios_clientes");
    echo "Total usuarios: " . $usuarios[0]['total'] . "<br>";
    
    // Verificar tabla cascos
    $cascos = $db->fetchAll("SELECT COUNT(*) as total FROM cascos WHERE activo = 1");
    echo "Total cascos activos: " . $cascos[0]['total'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error con BD: " . $e->getMessage() . "<br>";
}

echo "<h3>6. Enlaces de Prueba:</h3>";
echo "<a href='login.php' target='_blank'>🔐 Ir a Login</a><br>";
echo "<a href='productos.php' target='_blank'>🛍️ Ir a Productos</a><br>";
echo "<a href='carrito.php' target='_blank'>🛒 Ir a Carrito</a><br>";
echo "<a href='checkout.php' target='_blank'>🛍️ Ir a Checkout</a><br>";
?>
