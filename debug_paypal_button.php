<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 DIAGNÓSTICO BOTÓN PAYPAL</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .error{color:red;} .success{color:green;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

// 1. Verificar PayPal config
echo "<h3>1. Verificar PayPal Config:</h3>";
try {
    require_once 'administrador/config/paypal_config.php';
    echo "<span class='success'>✅ PayPal config cargado</span><br>";
    
    if (defined('PAYPAL_CLIENT_ID')) {
        echo "<span class='success'>✅ PAYPAL_CLIENT_ID: " . substr(PAYPAL_CLIENT_ID, 0, 20) . "...</span><br>";
        echo "<span class='info'>Longitud del Client ID: " . strlen(PAYPAL_CLIENT_ID) . "</span><br>";
    } else {
        echo "<span class='error'>❌ PAYPAL_CLIENT_ID NO definido</span><br>";
    }
    
    if (defined('PAYPAL_CLIENT_SECRET')) {
        echo "<span class='success'>✅ PAYPAL_CLIENT_SECRET definido</span><br>";
    } else {
        echo "<span class='error'>❌ PAYPAL_CLIENT_SECRET NO definido</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Error con PayPal config: " . $e->getMessage() . "</span><br>";
}

// 2. Verificar pedido en sesión
echo "<h3>2. Verificar Pedido:</h3>";
if (isset($_SESSION['pedido_checkout'])) {
    echo "<span class='success'>✅ Pedido en sesión</span><br>";
    echo "<span class='info'>Total: $" . $_SESSION['pedido_checkout']['total'] . "</span><br>";
} else {
    echo "<span class='error'>❌ NO hay pedido en sesión</span><br>";
}

// 3. Generar HTML de prueba
echo "<h3>3. HTML de Prueba:</h3>";
echo "<div style='border:2px solid #ccc;padding:20px;margin:20px;'>";
echo "<h4>Página de Prueba PayPal</h4>";
echo "<div id='paypal-button-container' style='border:1px solid red;padding:20px;background:#f0f0f0;'>";
echo "<p>Este es el contenedor del botón PayPal</p>";
echo "</div>";
echo "</div>";

// 4. Script de PayPal de prueba
echo "<h3>4. Script PayPal de Prueba:</h3>";
echo "<script src='https://www.paypal.com/sdk/js?client-id=" . (defined('PAYPAL_CLIENT_ID') ? PAYPAL_CLIENT_ID : 'TEST') . "&currency=MXN'></script>";
echo "<script>";
echo "console.log('Script PayPal cargado');";
echo "if (typeof paypal !== 'undefined') {";
echo "    console.log('PayPal SDK disponible');";
echo "    paypal.Buttons({";
echo "        createOrder: function(data, actions) {";
echo "            console.log('Creando orden...');";
echo "            return actions.order.create({";
echo "                purchase_units: [{";
echo "                    amount: {";
echo "                        value: '10.00'";
echo "                    }";
echo "                }]";
echo "            });";
echo "        },";
echo "        onApprove: function(data, actions) {";
echo "            console.log('Orden aprobada:', data);";
echo "            alert('Pago exitoso!');";
echo "        },";
echo "        onError: function(err) {";
echo "            console.error('Error PayPal:', err);";
echo "            alert('Error: ' + err.message);";
echo "        }";
echo "    }).render('#paypal-button-container');";
echo "    console.log('Botón renderizado');";
echo "} else {";
echo "    console.error('PayPal SDK no disponible');";
echo "    document.getElementById('paypal-button-container').innerHTML = '<p style=\"color:red;\">Error: PayPal SDK no cargado</p>';";
echo "}";
echo "</script>";

// 5. Verificar archivo paypal_config.php
echo "<h3>5. Contenido de paypal_config.php:</h3>";
if (file_exists('administrador/config/paypal_config.php')) {
    $contenido = file_get_contents('administrador/config/paypal_config.php');
    echo "<pre style='max-height:200px;overflow:auto;'>";
    echo htmlspecialchars($contenido);
    echo "</pre>";
} else {
    echo "<span class='error'>❌ Archivo paypal_config.php no existe</span><br>";
}

// 6. Enlaces de prueba
echo "<h3>6. Enlaces de Prueba:</h3>";
echo "<a href='debug_paypal_button.php' style='background:blue;color:white;padding:10px;margin:5px;text-decoration:none;border-radius:5px;'>🔄 Recargar Debug</a>";
echo "<a href='procesar_pago.php' style='background:green;color:white;padding:10px;margin:5px;text-decoration:none;border-radius:5px;'>💳 Ir a Procesar Pago</a>";

?>
