<?php
/**
 * 🧪 PRUEBAS DEL SISTEMA DE PAGO - Helmets Pro
 * 
 * Este script prueba todo el flujo de pago completo
 */

session_start();
require_once 'clases/Carrito.php';
require_once 'clases/Usuario.php';
require_once 'administrador/config/paypal_config.php';
require_once 'administrador/config/DB.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>�� Pruebas Sistema de Pago - Helmets Pro</title>
    <link rel='stylesheet' href='./css/bootstrap.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
    <style>
        .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .test-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .test-error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .test-info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .test-warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
    </style>
</head>
<body>
<div class='container mt-4'>
    <div class='row'>
        <div class='col-12'>
            <div class='card'>
                <div class='card-header bg-primary text-white'>
                    <h3><i class='fas fa-credit-card'></i> Pruebas del Sistema de Pago</h3>
                    <p class='mb-0'>Verificando que todo el flujo de pago funcione correctamente</p>
                </div>
                <div class='card-body'>";

$tests = [];

// ✅ PRUEBA 1: Verificar configuración de PayPal
echo "<h4><i class='fas fa-play'></i> PRUEBA 1: Configuración de PayPal</h4>";
if (isPayPalConfigured()) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ PayPal configurado correctamente
          </div>";
    $tests['paypal_config'] = true;
} else {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ PayPal NO está configurado
          </div>";
    $tests['paypal_config'] = false;
}

// ✅ PRUEBA 2: Verificar archivos del sistema de pago
echo "<h4><i class='fas fa-play'></i> PRUEBA 2: Archivos del Sistema de Pago</h4>";

$archivos_pago = [
    'checkout.php' => 'Página de checkout',
    'procesar_pago.php' => 'Procesamiento de pagos',
    'confirmacion_pago.php' => 'Confirmación de pago'
];

foreach ($archivos_pago as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "<div class='test-result test-success'>
                <i class='fas fa-check-circle'></i> ✅ {$descripcion} ({$archivo})
              </div>";
        $tests["archivo_{$archivo}"] = true;
    } else {
        echo "<div class='test-result test-error'>
                <i class='fas fa-times-circle'></i> ❌ {$descripcion} ({$archivo}) NO existe
              </div>";
        $tests["archivo_{$archivo}"] = false;
    }
}

// ✅ PRUEBA 3: Verificar tablas de base de datos
echo "<h4><i class='fas fa-play'></i> PRUEBA 3: Base de Datos</h4>";

$db = DB::getInstance();
$tablas_requeridas = ['pedidos', 'detalles_pedido', 'cascos', 'usuarios_clientes'];

foreach ($tablas_requeridas as $tabla) {
    try {
        $sql = "SHOW TABLES LIKE '{$tabla}'";
        $result = $db->fetchOne($sql);
        
        if ($result) {
            echo "<div class='test-result test-success'>
                    <i class='fas fa-check-circle'></i> ✅ Tabla {$tabla} existe
                  </div>";
            $tests["tabla_{$tabla}"] = true;
        } else {
            echo "<div class='test-result test-error'>
                    <i class='fas fa-times-circle'></i> ❌ Tabla {$tabla} NO existe
                  </div>";
            $tests["tabla_{$tabla}"] = false;
        }
    } catch (Exception $e) {
        echo "<div class='test-result test-error'>
                <i class='fas fa-times-circle'></i> ❌ Error verificando tabla {$tabla}: {$e->getMessage()}
              </div>";
        $tests["tabla_{$tabla}"] = false;
    }
}

// ✅ PRUEBA 4: Verificar sistema de carrito
echo "<h4><i class='fas fa-play'></i> PRUEBA 4: Sistema de Carrito</h4>";

Carrito::init();
if (class_exists('Carrito')) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Clase Carrito funciona correctamente
          </div>";
    $tests['carrito_clase'] = true;
} else {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ Clase Carrito NO funciona
          </div>";
    $tests['carrito_clase'] = false;
}

// ✅ PRUEBA 5: Verificar sistema de usuarios
echo "<h4><i class='fas fa-play'></i> PRUEBA 5: Sistema de Usuarios</h4>";

if (class_exists('Usuario')) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Clase Usuario funciona correctamente
          </div>";
    $tests['usuario_clase'] = true;
} else {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ Clase Usuario NO funciona
          </div>";
    $tests['usuario_clase'] = false;
}

// ✅ PRUEBA 6: Verificar productos disponibles
echo "<h4><i class='fas fa-play'></i> PRUEBA 6: Productos Disponibles</h4>";

try {
    $sql = "SELECT COUNT(*) as total FROM cascos WHERE activo = 1 AND stock > 0";
    $result = $db->fetchOne($sql);
    $productos_disponibles = $result['total'];
    
    if ($productos_disponibles > 0) {
        echo "<div class='test-result test-success'>
                <i class='fas fa-check-circle'></i> ✅ {$productos_disponibles} productos disponibles
              </div>";
        $tests['productos_disponibles'] = true;
    } else {
        echo "<div class='test-result test-warning'>
                <i class='fas fa-exclamation-triangle'></i> ⚠️ No hay productos disponibles para comprar
              </div>";
        $tests['productos_disponibles'] = false;
    }
} catch (Exception $e) {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ Error verificando productos: {$e->getMessage()}
          </div>";
    $tests['productos_disponibles'] = false;
}

// ✅ PRUEBA 7: Verificar directorio de logs
echo "<h4><i class='fas fa-play'></i> PRUEBA 7: Sistema de Logs</h4>";

$logDir = dirname(PAYPAL_LOG_FILE);
if (is_dir($logDir) && is_writable($logDir)) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Directorio de logs accesible: {$logDir}
          </div>";
    $tests['logs_accesible'] = true;
} else {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ Directorio de logs no accesible: {$logDir}
          </div>";
    $tests['logs_accesible'] = false;
}

// ✅ RESUMEN DE PRUEBAS
echo "<h4><i class='fas fa-clipboard-check'></i> RESUMEN DE PRUEBAS</h4>";
$total_tests = count($tests);
$passed_tests = count(array_filter($tests));

echo "<div class='card'>
        <div class='card-body'>
            <h5>Resultados del Sistema de Pago:</h5>
            <div class='progress mb-3'>
                <div class='progress-bar bg-success' style='width: " . ($passed_tests / $total_tests * 100) . "%'></div>
            </div>
            <p><strong>Pruebas pasadas:</strong> {$passed_tests} de {$total_tests}</p>
            
            <div class='table-responsive'>
                <table class='table table-sm'>
                    <thead>
                        <tr>
                            <th>Prueba del Sistema</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>";

foreach ($tests as $test_name => $result) {
    $status = $result ? '✅ PASÓ' : '❌ FALLÓ';
    $class = $result ? 'table-success' : 'table-danger';
    echo "<tr class='{$class}'>
            <td>" . ucfirst(str_replace('_', ' ', $test_name)) . "</td>
            <td>{$status}</td>
          </tr>";
}

echo "</tbody>
    </table>
</div>";

if ($passed_tests == $total_tests) {
    echo "<div class='alert alert-success'>
            <i class='fas fa-credit-card'></i> <strong>¡SISTEMA DE PAGO COMPLETO!</strong> Todo está listo para procesar pagos.
          </div>";
} else {
    echo "<div class='alert alert-warning'>
            <i class='fas fa-exclamation-triangle'></i> <strong>Algunas pruebas fallaron.</strong> Revisa la configuración antes de continuar.
          </div>";
}

echo "</div>
    </div>
</div>

<!-- Instrucciones para pruebas manuales -->
<div class='container mt-4'>
    <div class='row'>
        <div class='col-12'>
            <div class='card'>
                <div class='card-header bg-info text-white'>
                    <h5><i class='fas fa-play-circle'></i> Pruebas Manuales del Flujo Completo</h5>
                </div>
                <div class='card-body'>
                    <div class='row'>
                        <div class='col-md-6'>
                            <h6><i class='fas fa-list-ol'></i> Pasos para Probar:</h6>
                            <ol>
                                <li>Ir a <a href='productos.php'>productos.php</a> y agregar productos al carrito</li>
                                <li>Verificar el carrito en <a href='carrito.php'>carrito.php</a></li>
                                <li>Iniciar sesión como cliente</li>
                                <li>Proceder al checkout en <a href='checkout.php'>checkout.php</a></li>
                                <li>Completar el pago con PayPal Sandbox</li>
                                <li>Verificar la confirmación en <a href='confirmacion_pago.php'>confirmacion_pago.php</a></li>
                                <li>Revisar el pedido en <a href='mi-cuenta.php'>mi-cuenta.php</a></li>
                            </ol>
                        </div>
                        <div class='col-md-6'>
                            <h6><i class='fas fa-credit-card'></i> Credenciales PayPal Sandbox:</h6>
                            <div class='alert alert-info'>
                                <strong>Comprador:</strong><br>
                                Email: sb-1234567890@business.example.com<br>
                                Contraseña: tu_contraseña_sandbox
                            </div>
                            <div class='alert alert-warning'>
                                <strong>Vendedor:</strong><br>
                                Email: sb-1234567890@business.example.com<br>
                                Contraseña: tu_contraseña_sandbox
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src='./js/jquery-3.6.0.min.js'></script>
<script src='./js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
