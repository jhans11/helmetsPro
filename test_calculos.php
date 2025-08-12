<?php
session_start();
require_once 'clases/Carrito.php';

echo "<h2>🧮 TEST DE CÁLCULOS</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

// Simular productos en el carrito
$_SESSION['carrito'] = [
    1 => [
        'id' => 1,
        'nombre' => 'SHARK',
        'precio' => 399.99,
        'cantidad' => 1
    ],
    2 => [
        'id' => 2,
        'nombre' => 'SAGITARIO',
        'precio' => 279.99,
        'cantidad' => 1
    ]
];

echo "<h3>Productos en carrito:</h3>";
echo "<pre>";
print_r($_SESSION['carrito']);
echo "</pre>";

// Probar cálculos
$subtotal = Carrito::calcularSubtotal();
$impuestos = round($subtotal * 0.16, 2);
$total = round($subtotal + $impuestos, 2);

echo "<h3>Resultados de cálculos:</h3>";
echo "<p><strong>Subtotal:</strong> $" . number_format($subtotal, 2) . " (valor: $subtotal)</p>";
echo "<p><strong>Impuestos (16%):</strong> $" . number_format($impuestos, 2) . " (valor: $impuestos)</p>";
echo "<p><strong>Total:</strong> $" . number_format($total, 2) . " (valor: $total)</p>";

// Verificar formato para PayPal
$paypal_value = number_format($total, 2, '.', '');
echo "<p><strong>Valor para PayPal:</strong> '$paypal_value'</p>";

// Verificar que tenga exactamente 2 decimales
$decimales = strlen(substr(strrchr($paypal_value, "."), 1));
if ($decimales == 2) {
    echo "<p class='success'>✅ Formato correcto: $decimales decimales</p>";
} else {
    echo "<p class='error'>❌ Formato incorrecto: $decimales decimales</p>";
}

// Crear pedido de prueba
$_SESSION['pedido_checkout'] = [
    'usuario_id' => 1,
    'productos' => $_SESSION['carrito'],
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

echo "<h3>Pedido de prueba creado:</h3>";
echo "<pre>";
print_r($_SESSION['pedido_checkout']);
echo "</pre>";

echo "<h3>Enlaces de prueba:</h3>";
echo "<a href='procesar_pago_final.php' style='background:green;color:white;padding:10px;text-decoration:none;border-radius:5px;'>💳 Probar Pago PayPal</a>";
echo "<a href='test_calculos.php' style='background:blue;color:white;padding:10px;margin-left:10px;text-decoration:none;border-radius:5px;'>🔄 Recargar Test</a>";

?>
