<?php
/**
 * �� VERIFICADOR DE CONFIGURACIÓN PAYPAL - Helmets Pro
 * 
 * Este script verifica que la configuración de PayPal esté correcta
 */

// Incluir configuración de PayPal
require_once 'administrador/config/paypal_config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🔍 Verificar Configuración PayPal - Helmets Pro</title>
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
                <div class='card-header bg-info text-white'>
                    <h3><i class='fas fa-credit-card'></i> Verificar Configuración PayPal</h3>
                    <p class='mb-0'>Comprobando que la configuración de PayPal esté correcta</p>
                </div>
                <div class='card-body'>";

$tests = [];

// ✅ PRUEBA 1: Verificar que el archivo de configuración existe
echo "<h4><i class='fas fa-play'></i> PRUEBA 1: Archivo de Configuración</h4>";
if (file_exists('administrador/config/paypal_config.php')) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Archivo paypal_config.php existe
          </div>";
    $tests['archivo_config'] = true;
} else {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ Archivo paypal_config.php NO existe
          </div>";
    $tests['archivo_config'] = false;
}

// ✅ PRUEBA 2: Verificar configuración básica
echo "<h4><i class='fas fa-play'></i> PRUEBA 2: Configuración Básica</h4>";

echo "<div class='test-result test-info'>
        <i class='fas fa-info-circle'></i> Configuración actual:
        <ul class='mb-0 mt-2'>
            <li><strong>Sandbox:</strong> " . (PAYPAL_SANDBOX ? '✅ Activado' : '❌ Desactivado') . "</li>
            <li><strong>Moneda:</strong> " . PAYPAL_CURRENCY . "</li>
            <li><strong>Idioma:</strong> " . PAYPAL_LOCALE . "</li>
            <li><strong>Intent:</strong> " . PAYPAL_INTENT . "</li>
            <li><strong>Base URL:</strong> " . PAYPAL_BASE_URL . "</li>
        </ul>
      </div>";

$tests['config_basica'] = true;

// ✅ PRUEBA 3: Verificar credenciales
echo "<h4><i class='fas fa-play'></i> PRUEBA 3: Credenciales de PayPal</h4>";

if (isPayPalConfigured()) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Credenciales configuradas correctamente
          </div>";
    
    echo "<div class='test-result test-info'>
            <i class='fas fa-info-circle'></i> Credenciales:
            <ul class='mb-0 mt-2'>
                <li><strong>Client ID:</strong> " . substr(PAYPAL_CLIENT_ID, 0, 10) . "...</li>
                <li><strong>Client Secret:</strong> " . (strlen(PAYPAL_CLIENT_SECRET) > 0 ? '✅ Configurado' : '❌ No configurado') . "</li>
            </ul>
          </div>";
    
    $tests['credenciales'] = true;
} else {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ Credenciales NO configuradas
          </div>";
    
    echo "<div class='test-result test-warning'>
            <i class='fas fa-exclamation-triangle'></i> ⚠️ Para configurar PayPal:
            <ol class='mb-0 mt-2'>
                <li>Ve a <a href='https://developer.paypal.com/' target='_blank'>PayPal Developer</a></li>
                <li>Crea una cuenta o inicia sesión</li>
                <li>Ve a 'Apps & Credentials' → 'Sandbox'</li>
                <li>Crea una nueva aplicación</li>
                <li>Copia el Client ID y Client Secret</li>
                <li>Actualiza el archivo paypal_config.php</li>
            </ol>
          </div>";
    
    $tests['credenciales'] = false;
}

// ✅ PRUEBA 4: Verificar funciones de configuración
echo "<h4><i class='fas fa-play'></i> PRUEBA 4: Funciones de Configuración</h4>";

$config_js = getPayPalConfig();
$config_button = getPayPalButtonConfig();

if ($config_js && $config_button) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Funciones de configuración funcionando
          </div>";
    
    echo "<div class='test-result test-info'>
            <i class='fas fa-info-circle'></i> Configuración JavaScript:
            <pre class='mb-0 mt-2'>" . json_encode($config_js, JSON_PRETTY_PRINT) . "</pre>
          </div>";
    
    $tests['funciones_config'] = true;
} else {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ Funciones de configuración fallaron
          </div>";
    $tests['funciones_config'] = false;
}

// ✅ PRUEBA 5: Verificar directorio de logs
echo "<h4><i class='fas fa-play'></i> PRUEBA 5: Directorio de Logs</h4>";

$logDir = dirname(PAYPAL_LOG_FILE);
if (is_dir($logDir)) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Directorio de logs existe: {$logDir}
          </div>";
    $tests['directorio_logs'] = true;
} else {
    echo "<div class='test-result test-warning'>
            <i class='fas fa-exclamation-triangle'></i> ⚠️ Directorio de logs no existe: {$logDir}
          </div>";
    
    // Intentar crear el directorio
    if (mkdir($logDir, 0755, true)) {
        echo "<div class='test-result test-success'>
                <i class='fas fa-check-circle'></i> ✅ Directorio de logs creado exitosamente
              </div>";
        $tests['directorio_logs'] = true;
    } else {
        echo "<div class='test-result test-error'>
                <i class='fas fa-times-circle'></i> ❌ No se pudo crear el directorio de logs
              </div>";
        $tests['directorio_logs'] = false;
    }
}

// ✅ PRUEBA 6: Probar logging
echo "<h4><i class='fas fa-play'></i> PRUEBA 6: Sistema de Logging</h4>";

paypalLog('Prueba de logging desde verificación de configuración', 'TEST');

if (file_exists(PAYPAL_LOG_FILE)) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Sistema de logging funcionando
          </div>";
    $tests['sistema_logging'] = true;
} else {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ Sistema de logging no funciona
          </div>";
    $tests['sistema_logging'] = false;
}

// ✅ PRUEBA 7: Verificar URLs de PayPal
echo "<h4><i class='fas fa-play'></i> PRUEBA 7: URLs de PayPal</h4>";

$urls = [
    'Base URL' => getPayPalUrl(),
    'Web URL' => PAYPAL_WEB_URL,
    'SDK URL' => PAYPAL_SDK_URL
];

echo "<div class='test-result test-info'>
        <i class='fas fa-info-circle'></i> URLs configuradas:
        <ul class='mb-0 mt-2'>";

foreach ($urls as $name => $url) {
    echo "<li><strong>{$name}:</strong> <a href='{$url}' target='_blank'>{$url}</a></li>";
}

echo "</ul>
      </div>";

$tests['urls_paypal'] = true;

// ✅ RESUMEN DE PRUEBAS
echo "<h4><i class='fas fa-clipboard-check'></i> RESUMEN DE PRUEBAS</h4>";
$total_tests = count($tests);
$passed_tests = count(array_filter($tests));

echo "<div class='card'>
        <div class='card-body'>
            <h5>Resultados de Configuración:</h5>
            <div class='progress mb-3'>
                <div class='progress-bar bg-success' style='width: " . ($passed_tests / $total_tests * 100) . "%'></div>
            </div>
            <p><strong>Pruebas pasadas:</strong> {$passed_tests} de {$total_tests}</p>
            
            <div class='table-responsive'>
                <table class='table table-sm'>
                    <thead>
                        <tr>
                            <th>Prueba de Configuración</th>
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
            <i class='fas fa-credit-card'></i> <strong>¡CONFIGURACIÓN PAYPAL COMPLETA!</strong> Todo está listo para implementar pagos.
          </div>";
} else {
    echo "<div class='alert alert-warning'>
            <i class='fas fa-exclamation-triangle'></i> <strong>Algunas pruebas fallaron.</strong> Revisa la configuración antes de continuar.
          </div>";
}

echo "</div>
    </div>
</div>

<!-- Instrucciones adicionales -->
<div class='container mt-4'>
    <div class='row'>
        <div class='col-12'>
            <div class='card'>
                <div class='card-header bg-warning text-dark'>
                    <h5><i class='fas fa-lightbulb'></i> Próximos Pasos</h5>
                </div>
                <div class='card-body'>
                    <div class='row'>
                        <div class='col-md-6'>
                            <h6><i class='fas fa-check-circle text-success'></i> Si todo está bien:</h6>
                            <ul>
                                <li>Proceder al Paso 2: Implementación del Sistema de Pago</li>
                                <li>Crear página de checkout</li>
                                <li>Integrar botón de PayPal</li>
                            </ul>
                        </div>
                        <div class='col-md-6'>
                            <h6><i class='fas fa-times-circle text-danger'></i> Si hay problemas:</h6>
                            <ul>
                                <li>Configurar credenciales de PayPal</li>
                                <li>Verificar permisos de directorios</li>
                                <li>Revisar configuración de PHP</li>
                            </ul>
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
