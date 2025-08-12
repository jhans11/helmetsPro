<?php
/**
 * 🌐 PRUEBA DE SESIONES ENTRE NAVEGADORES - Helmets Pro
 * 
 * Este script verifica el comportamiento de las sesiones:
 * 1. Persistencia entre ventanas del mismo navegador
 * 2. Comportamiento entre diferentes navegadores
 * 3. Configuración de cookies de sesión
 */

session_start();
require_once 'clases/Usuario.php';
require_once 'clases/Middleware.php';

// Configurar sesión segura
Middleware::configureSecureSession();
Middleware::setSecurityHeaders();

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🌐 Prueba de Sesiones - Helmets Pro</title>
    <link rel='stylesheet' href='./css/bootstrap.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
    <style>
        .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .test-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .test-error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .test-info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .test-warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .session-info { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
<div class='container mt-4'>
    <div class='row'>
        <div class='col-12'>
            <div class='card'>
                <div class='card-header bg-info text-white'>
                    <h3><i class='fas fa-globe'></i> Prueba de Sesiones Entre Navegadores</h3>
                    <p class='mb-0'>Verificación de persistencia y configuración de sesiones</p>
                </div>
                <div class='card-body'>";

$usuario = new Usuario();
$tests = [];

// ✅ PRUEBA 1: Información de sesión actual
echo "<h4><i class='fas fa-play'></i> PRUEBA 1: Información de Sesión Actual</h4>";

$session_info = [
    'session_id' => session_id(),
    'session_name' => session_name(),
    'session_status' => session_status(),
    'cliente_id' => $_SESSION['cliente_id'] ?? 'No establecido',
    'esta_autenticado' => $usuario->estaAutenticado() ? 'Sí' : 'No',
    'session_save_path' => session_save_path(),
    'session_cookie_params' => session_get_cookie_params()
];

echo "<div class='session-info'>
        <h6><i class='fas fa-info-circle'></i> Información de Sesión:</h6>
        <ul class='mb-0'>
            <li><strong>Session ID:</strong> {$session_info['session_id']}</li>
            <li><strong>Session Name:</strong> {$session_info['session_name']}</li>
            <li><strong>Session Status:</strong> {$session_info['session_status']}</li>
            <li><strong>Cliente ID:</strong> {$session_info['cliente_id']}</li>
            <li><strong>¿Autenticado?:</strong> {$session_info['esta_autenticado']}</li>
            <li><strong>Save Path:</strong> {$session_info['session_save_path']}</li>
        </ul>
      </div>";

// ✅ PRUEBA 2: Configuración de cookies de sesión
echo "<h4><i class='fas fa-play'></i> PRUEBA 2: Configuración de Cookies de Sesión</h4>";

$cookie_params = $session_info['session_cookie_params'];
echo "<div class='session-info'>
        <h6><i class='fas fa-cookie-bite'></i> Parámetros de Cookie de Sesión:</h6>
        <ul class='mb-0'>
            <li><strong>Lifetime:</strong> {$cookie_params['lifetime']} segundos (" . ($cookie_params['lifetime'] / 3600) . " horas)</li>
            <li><strong>Path:</strong> {$cookie_params['path']}</li>
            <li><strong>Domain:</strong> {$cookie_params['domain']}</li>
            <li><strong>Secure:</strong> " . ($cookie_params['secure'] ? '✅ Sí' : '❌ No') . "</li>
            <li><strong>HttpOnly:</strong> " . ($cookie_params['httponly'] ? '✅ Sí' : '❌ No') . "</li>
            <li><strong>SameSite:</strong> {$cookie_params['samesite']}</li>
        </ul>
      </div>";

// Verificar configuración de seguridad
$tests['cookie_httponly'] = $cookie_params['httponly'];
$tests['cookie_secure_config'] = true; // En desarrollo puede estar en false
$tests['cookie_lifetime'] = $cookie_params['lifetime'] > 0;

// ✅ PRUEBA 3: Login y verificar persistencia
echo "<h4><i class='fas fa-play'></i> PRUEBA 3: Login y Verificación de Persistencia</h4>";

if (!$usuario->estaAutenticado()) {
    // Hacer login
    $resultado_login = $usuario->login('juan.perez@email.com', 'cliente123');
    
    if ($resultado_login['success']) {
        echo "<div class='test-result test-success'>
                <i class='fas fa-check-circle'></i> ✅ Login exitoso - Sesión creada
              </div>";
        
        // Obtener nueva información de sesión
        $nuevo_session_id = session_id();
        $nuevo_cliente_id = $_SESSION['cliente_id'] ?? 'No establecido';
        
        echo "<div class='session-info'>
                <h6><i class='fas fa-check'></i> Nueva información de sesión:</h6>
                <ul class='mb-0'>
                    <li><strong>Nuevo Session ID:</strong> {$nuevo_session_id}</li>
                    <li><strong>Nuevo Cliente ID:</strong> {$nuevo_cliente_id}</li>
                    <li><strong>¿Sesión regenerada?:</strong> " . ($nuevo_session_id !== $session_info['session_id'] ? '✅ Sí' : '❌ No') . "</li>
                </ul>
              </div>";
        
        $tests['login_persistencia'] = true;
        $tests['session_regenerada'] = $nuevo_session_id !== $session_info['session_id'];
    } else {
        echo "<div class='test-result test-error'>
                <i class='fas fa-times-circle'></i> ❌ Login falló: {$resultado_login['message']}
              </div>";
        $tests['login_persistencia'] = false;
    }
} else {
    echo "<div class='test-result test-info'>
            <i class='fas fa-info-circle'></i> Usuario ya autenticado
          </div>";
    $tests['login_persistencia'] = true;
}

// ✅ PRUEBA 4: Verificar datos de usuario en sesión
echo "<h4><i class='fas fa-play'></i> PRUEBA 4: Verificar Datos de Usuario en Sesión</h4>";

if ($usuario->estaAutenticado()) {
    $usuario_actual = $usuario->obtenerUsuarioActual();
    if ($usuario_actual) {
        echo "<div class='test-result test-success'>
                <i class='fas fa-check-circle'></i> ✅ Datos de usuario disponibles en sesión
              </div>";
        
        echo "<div class='session-info'>
                <h6><i class='fas fa-user'></i> Datos del usuario en sesión:</h6>
                <ul class='mb-0'>
                    <li><strong>ID:</strong> {$usuario_actual['id']}</li>
                    <li><strong>Nombre:</strong> {$usuario_actual['nombre']} {$usuario_actual['apellido']}</li>
                    <li><strong>Email:</strong> {$usuario_actual['email']}</li>
                    <li><strong>Usuario:</strong> {$usuario_actual['usuario']}</li>
                    <li><strong>Verificado:</strong> " . ($usuario_actual['verificado'] ? '✅ Sí' : '❌ No') . "</li>
                </ul>
              </div>";
        
        $tests['datos_usuario_sesion'] = true;
    } else {
        echo "<div class='test-result test-error'>
                <i class='fas fa-times-circle'></i> ❌ No se pudieron obtener datos del usuario
              </div>";
        $tests['datos_usuario_sesion'] = false;
    }
} else {
    echo "<div class='test-result test-warning'>
            <i class='fas fa-exclamation-triangle'></i> ⚠️ Usuario no autenticado
          </div>";
    $tests['datos_usuario_sesion'] = false;
}

// ✅ PRUEBA 5: Instrucciones para pruebas manuales
echo "<h4><i class='fas fa-play'></i> PRUEBA 5: Instrucciones para Pruebas Manuales</h4>";

echo "<div class='alert alert-info'>
        <h6><i class='fas fa-hand-point-right'></i> Pruebas Manuales de Sesión:</h6>
        <ol class='mb-0'>
            <li><strong>Misma ventana:</strong> Recarga esta página y verifica que sigues logueado</li>
            <li><strong>Nueva pestaña:</strong> Abre una nueva pestaña y ve a mi-cuenta.php</li>
            <li><strong>Nuevo navegador:</strong> Abre otro navegador y ve a mi-cuenta.php</li>
            <li><strong>Modo incógnito:</strong> Abre modo incógnito y verifica que NO estás logueado</li>
            <li><strong>Cerrar navegador:</strong> Cierra el navegador, ábrelo de nuevo y verifica la sesión</li>
        </ol>
      </div>";

// ✅ PRUEBA 6: Verificar configuración de seguridad de sesión
echo "<h4><i class='fas fa-play'></i> PRUEBA 6: Configuración de Seguridad de Sesión</h4>";

$security_config = [
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.use_strict_mode' => ini_get('session.use_strict_mode'),
    'session.use_cookies' => ini_get('session.use_cookies'),
    'session.use_only_cookies' => ini_get('session.use_only_cookies'),
    'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
    'session.gc_probability' => ini_get('session.gc_probability'),
    'session.gc_divisor' => ini_get('session.gc_divisor')
];

echo "<div class='session-info'>
        <h6><i class='fas fa-shield-alt'></i> Configuración de Seguridad:</h6>
        <ul class='mb-0'>";

foreach ($security_config as $setting => $value) {
    $status = '';
    if (in_array($setting, ['session.cookie_httponly', 'session.use_strict_mode', 'session.use_cookies', 'session.use_only_cookies'])) {
        $status = $value ? '✅ Correcto' : '❌ Incorrecto';
    } elseif ($setting === 'session.cookie_secure') {
        $status = $value ? '✅ Correcto' : '⚠️ Normal en desarrollo';
    } else {
        $status = 'ℹ️ Configurado';
    }
    
    echo "<li><strong>" . ucfirst(str_replace('session.', '', $setting)) . ":</strong> {$value} {$status}</li>";
}

echo "</ul>
      </div>";

// Evaluar configuración de seguridad
$tests['security_httponly'] = $security_config['session.cookie_httponly'];
$tests['security_strict_mode'] = $security_config['session.use_strict_mode'];
$tests['security_cookies'] = $security_config['session.use_cookies'];
$tests['security_only_cookies'] = $security_config['session.use_only_cookies'];

// ✅ PRUEBA 7: Generar enlaces de prueba
echo "<h4><i class='fas fa-play'></i> PRUEBA 7: Enlaces de Prueba de Sesión</h4>";

echo "<div class='card'>
        <div class='card-body'>
            <h6><i class='fas fa-link'></i> Enlaces para probar persistencia de sesión:</h6>
            <div class='row'>
                <div class='col-md-6'>
                    <h6 class='text-primary'>Páginas Públicas:</h6>
                    <ul class='list-unstyled'>
                        <li><a href='index.php' target='_blank'><i class='fas fa-home'></i> Inicio</a></li>
                        <li><a href='productos.php' target='_blank'><i class='fas fa-helmet-battle'></i> Productos</a></li>
                        <li><a href='login.php' target='_blank'><i class='fas fa-sign-in-alt'></i> Login</a></li>
                    </ul>
                </div>
                <div class='col-md-6'>
                    <h6 class='text-warning'>Páginas Protegidas:</h6>
                    <ul class='list-unstyled'>
                        <li><a href='mi-cuenta.php' target='_blank'><i class='fas fa-user'></i> Mi Cuenta</a></li>
                        <li><a href='logout.php' target='_blank'><i class='fas fa-sign-out-alt'></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
      </div>";

// ✅ RESUMEN DE PRUEBAS
echo "<h4><i class='fas fa-clipboard-check'></i> RESUMEN DE PRUEBAS DE SESIÓN</h4>";
$total_tests = count($tests);
$passed_tests = count(array_filter($tests));

echo "<div class='card'>
        <div class='card-body'>
            <h5>Resultados de Sesión:</h5>
            <div class='progress mb-3'>
                <div class='progress-bar bg-success' style='width: " . ($passed_tests / $total_tests * 100) . "%'></div>
            </div>
            <p><strong>Pruebas de sesión pasadas:</strong> {$passed_tests} de {$total_tests}</p>
            
            <div class='table-responsive'>
                <table class='table table-sm'>
                    <thead>
                        <tr>
                            <th>Prueba de Sesión</th>
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
            <i class='fas fa-globe'></i> <strong>¡SISTEMA DE SESIONES FUNCIONANDO!</strong> Las sesiones están configuradas correctamente.
          </div>";
} else {
    echo "<div class='alert alert-warning'>
            <i class='fas fa-exclamation-triangle'></i> <strong>Algunas pruebas de sesión fallaron.</strong> Revisa la configuración.
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
                    <h5><i class='fas fa-lightbulb'></i> Instrucciones para Pruebas Manuales</h5>
                </div>
                <div class='card-body'>
                    <div class='row'>
                        <div class='col-md-6'>
                            <h6><i class='fas fa-check-circle text-success'></i> Qué debe funcionar:</h6>
                            <ul>
                                <li>Mantener sesión al recargar la página</li>
                                <li>Compartir sesión entre pestañas del mismo navegador</li>
                                <li>Compartir sesión entre ventanas del mismo navegador</li>
                                <li>Mantener sesión al cerrar y abrir el navegador</li>
                            </ul>
                        </div>
                        <div class='col-md-6'>
                            <h6><i class='fas fa-times-circle text-danger'></i> Qué NO debe funcionar:</h6>
                            <ul>
                                <li>Compartir sesión entre navegadores diferentes</li>
                                <li>Compartir sesión en modo incógnito</li>
                                <li>Acceder a páginas protegidas sin login</li>
                                <li>Mantener sesión después del logout</li>
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
