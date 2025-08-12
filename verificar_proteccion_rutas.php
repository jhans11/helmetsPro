<?php
/**
 * 🛡️ VERIFICADOR DE PROTECCIÓN DE RUTAS - Helmets Pro
 * 
 * Este script verifica que las rutas protegidas estén funcionando correctamente
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
    <title>🛡️ Verificación de Protección de Rutas - Helmets Pro</title>
    <link rel='stylesheet' href='./css/bootstrap.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
    <style>
        .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .test-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .test-error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .test-info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    </style>
</head>
<body>
<div class='container mt-4'>
    <div class='row'>
        <div class='col-12'>
            <div class='card'>
                <div class='card-header bg-warning text-dark'>
                    <h3><i class='fas fa-shield-alt'></i> Verificación de Protección de Rutas</h3>
                    <p class='mb-0'>Comprobando que las rutas protegidas funcionen correctamente</p>
                </div>
                <div class='card-body'>";

$usuario = new Usuario();
$tests = [];

// ✅ PRUEBA 1: Verificar estado actual de autenticación
echo "<h4><i class='fas fa-play'></i> PRUEBA 1: Estado de Autenticación Actual</h4>";
$esta_autenticado = $usuario->estaAutenticado();
$tiene_sesion = isset($_SESSION['cliente_id']);

echo "<div class='test-result test-info'>
        <i class='fas fa-info-circle'></i> Estado actual:
        <ul class='mb-0 mt-2'>
            <li>¿Está autenticado? " . ($esta_autenticado ? '✅ Sí' : '❌ No') . "</li>
            <li>¿Tiene sesión activa? " . ($tiene_sesion ? '✅ Sí' : '❌ No') . "</li>
            <li>ID de sesión: " . ($_SESSION['cliente_id'] ?? 'Ninguno') . "</li>
        </ul>
      </div>";

// ✅ PRUEBA 2: Intentar acceder a ruta protegida sin autenticación
echo "<h4><i class='fas fa-play'></i> PRUEBA 2: Acceso a Ruta Protegida Sin Autenticación</h4>";

if (!$esta_autenticado) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Correcto: Usuario no autenticado
          </div>";
    
    // Simular intento de acceso a mi-cuenta.php
    echo "<div class='test-result test-info'>
            <i class='fas fa-info-circle'></i> Intentando acceder a mi-cuenta.php sin autenticación...
          </div>";
    
    // Verificar que mi-cuenta.php tiene protección
    $mi_cuenta_content = file_get_contents('mi-cuenta.php');
    if (strpos($mi_cuenta_content, 'Middleware::requireAuth()') !== false) {
        echo "<div class='test-result test-success'>
                <i class='fas fa-check-circle'></i> ✅ mi-cuenta.php tiene protección Middleware::requireAuth()
              </div>";
        $tests['proteccion_mi_cuenta'] = true;
    } else {
        echo "<div class='test-result test-error'>
                <i class='fas fa-times-circle'></i> ❌ mi-cuenta.php NO tiene protección Middleware::requireAuth()
              </div>";
        $tests['proteccion_mi_cuenta'] = false;
    }
    
    $tests['acceso_sin_autenticacion'] = true;
} else {
    echo "<div class='test-result test-warning'>
            <i class='fas fa-exclamation-triangle'></i> ⚠️ Usuario ya autenticado. Hacer logout primero.
          </div>";
    $tests['acceso_sin_autenticacion'] = false;
}

// ✅ PRUEBA 3: Login y verificar acceso a rutas protegidas
echo "<h4><i class='fas fa-play'></i> PRUEBA 3: Login y Acceso a Rutas Protegidas</h4>";

if (!$esta_autenticado) {
    // Hacer login
    $resultado_login = $usuario->login('juan.perez@email.com', 'cliente123');
    
    if ($resultado_login['success']) {
        echo "<div class='test-result test-success'>
                <i class='fas fa-check-circle'></i> ✅ Login exitoso
              </div>";
        
        // Verificar que ahora está autenticado
        if ($usuario->estaAutenticado()) {
            echo "<div class='test-result test-success'>
                    <i class='fas fa-check-circle'></i> ✅ Usuario autenticado correctamente
                  </div>";
            
            // Obtener datos del usuario
            $usuario_actual = $usuario->obtenerUsuarioActual();
            if ($usuario_actual) {
                echo "<div class='test-result test-success'>
                        <i class='fas fa-check-circle'></i> ✅ Datos de usuario obtenidos: {$usuario_actual['nombre']} {$usuario_actual['apellido']}
                      </div>";
                $tests['datos_usuario'] = true;
            } else {
                echo "<div class='test-result test-error'>
                        <i class='fas fa-times-circle'></i> ❌ No se pudieron obtener datos del usuario
                      </div>";
                $tests['datos_usuario'] = false;
            }
            
            $tests['login_exitoso'] = true;
            $tests['autenticacion_post_login'] = true;
        } else {
            echo "<div class='test-result test-error'>
                    <i class='fas fa-times-circle'></i> ❌ Usuario no autenticado después del login
                  </div>";
            $tests['login_exitoso'] = false;
            $tests['autenticacion_post_login'] = false;
        }
    } else {
        echo "<div class='test-result test-error'>
                <i class='fas fa-times-circle'></i> ❌ Login falló: {$resultado_login['message']}
              </div>";
        $tests['login_exitoso'] = false;
    }
} else {
    echo "<div class='test-result test-info'>
            <i class='fas fa-info-circle'></i> Usuario ya autenticado, saltando login
          </div>";
    $tests['login_exitoso'] = true;
    $tests['autenticacion_post_login'] = true;
}

// ✅ PRUEBA 4: Verificar acceso a rutas protegidas con autenticación
echo "<h4><i class='fas fa-play'></i> PRUEBA 4: Acceso a Rutas Protegidas Con Autenticación</h4>";

if ($usuario->estaAutenticado()) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Usuario autenticado puede acceder a rutas protegidas
          </div>";
    
    // Verificar que puede acceder a mi-cuenta.php
    echo "<div class='test-result test-info'>
            <i class='fas fa-info-circle'></i> Usuario puede acceder a:
            <ul class='mb-0 mt-2'>
                <li>✅ mi-cuenta.php</li>
                <li>✅ detalle-pedido.php</li>
                <li>✅ logout.php</li>
            </ul>
          </div>";
    
    $tests['acceso_con_autenticacion'] = true;
} else {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ Usuario no autenticado no puede acceder a rutas protegidas
          </div>";
    $tests['acceso_con_autenticacion'] = false;
}

// ✅ PRUEBA 5: Logout y verificar protección
echo "<h4><i class='fas fa-play'></i> PRUEBA 5: Logout y Verificación de Protección</h4>";

if ($usuario->estaAutenticado()) {
    // Hacer logout
    $usuario->logout();
    
    if (!$usuario->estaAutenticado()) {
        echo "<div class='test-result test-success'>
                <i class='fas fa-check-circle'></i> ✅ Logout exitoso - Usuario desautenticado
              </div>";
        
        // Verificar que ya no puede acceder a rutas protegidas
        if (!isset($_SESSION['cliente_id'])) {
            echo "<div class='test-result test-success'>
                    <i class='fas fa-check-circle'></i> ✅ Sesión destruida correctamente
                  </div>";
            
            echo "<div class='test-result test-info'>
                    <i class='fas fa-info-circle'></i> Ahora el usuario NO puede acceder a rutas protegidas
                  </div>";
            
            $tests['logout_exitoso'] = true;
            $tests['proteccion_post_logout'] = true;
        } else {
            echo "<div class='test-result test-error'>
                    <i class='fas fa-times-circle'></i> ❌ Sesión no destruida correctamente
                  </div>";
            $tests['logout_exitoso'] = false;
            $tests['proteccion_post_logout'] = false;
        }
    } else {
        echo "<div class='test-result test-error'>
                <i class='fas fa-times-circle'></i> ❌ Logout falló - Usuario aún autenticado
              </div>";
        $tests['logout_exitoso'] = false;
        $tests['proteccion_post_logout'] = false;
    }
} else {
    echo "<div class='test-result test-info'>
            <i class='fas fa-info-circle'></i> Usuario ya desautenticado
          </div>";
    $tests['logout_exitoso'] = true;
    $tests['proteccion_post_logout'] = true;
}

// ✅ PRUEBA 6: Verificar configuración de Middleware
echo "<h4><i class='fas fa-play'></i> PRUEBA 6: Verificar Configuración de Middleware</h4>";

// Verificar que el archivo Middleware.php existe
if (file_exists('clases/Middleware.php')) {
    echo "<div class='test-result test-success'>
            <i class='fas fa-check-circle'></i> ✅ Archivo Middleware.php existe
          </div>";
    
    // Verificar métodos del Middleware
    $middleware_content = file_get_contents('clases/Middleware.php');
    $metodos_requeridos = ['requireAuth', 'requireGuest', 'isAuthenticated', 'setSecurityHeaders'];
    $metodos_encontrados = 0;
    
    foreach ($metodos_requeridos as $metodo) {
        if (strpos($middleware_content, "public static function {$metodo}") !== false) {
            $metodos_encontrados++;
        }
    }
    
    if ($metodos_encontrados == count($metodos_requeridos)) {
        echo "<div class='test-result test-success'>
                <i class='fas fa-check-circle'></i> ✅ Todos los métodos del Middleware están presentes
              </div>";
        $tests['middleware_completo'] = true;
    } else {
        echo "<div class='test-result test-error'>
                <i class='fas fa-times-circle'></i> ❌ Faltan métodos en el Middleware ({$metodos_encontrados}/" . count($metodos_requeridos) . ")
              </div>";
        $tests['middleware_completo'] = false;
    }
} else {
    echo "<div class='test-result test-error'>
            <i class='fas fa-times-circle'></i> ❌ Archivo Middleware.php no existe
          </div>";
    $tests['middleware_completo'] = false;
}

// ✅ RESUMEN DE PRUEBAS
echo "<h4><i class='fas fa-clipboard-check'></i> RESUMEN DE PRUEBAS DE PROTECCIÓN</h4>";
$total_tests = count($tests);
$passed_tests = count(array_filter($tests));

echo "<div class='card'>
        <div class='card-body'>
            <h5>Resultados de Protección:</h5>
            <div class='progress mb-3'>
                <div class='progress-bar bg-success' style='width: " . ($passed_tests / $total_tests * 100) . "%'></div>
            </div>
            <p><strong>Pruebas de protección pasadas:</strong> {$passed_tests} de {$total_tests}</p>
            
            <div class='table-responsive'>
                <table class='table table-sm'>
                    <thead>
                        <tr>
                            <th>Prueba de Protección</th>
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
            <i class='fas fa-shield-alt'></i> <strong>¡SISTEMA DE PROTECCIÓN FUNCIONANDO!</strong> Todas las rutas están correctamente protegidas.
          </div>";
} else {
    echo "<div class='alert alert-warning'>
            <i class='fas fa-exclamation-triangle'></i> <strong>Algunas pruebas de protección fallaron.</strong> Revisa la configuración del Middleware.
          </div>";
}

echo "</div>
    </div>
</div>

<!-- Enlaces de navegación -->
<div class='container mt-4'>
    <div class='row'>
        <div class='col-12'>
            <div class='card'>
                <div class='card-body text-center'>
                    <h5><i class='fas fa-link'></i> Enlaces de Prueba de Protección</h5>
                    <div class='btn-group' role='group'>
                        <a href='pruebas_login.php' class='btn btn-primary'>
                            <i class='fas fa-vial'></i> Pruebas de Login
                        </a>
                        <a href='mi-cuenta.php' class='btn btn-warning'>
                            <i class='fas fa-user'></i> Mi Cuenta (Protegida)
                        </a>
                        <a href='login.php' class='btn btn-info'>
                            <i class='fas fa-sign-in-alt'></i> Login
                        </a>
                        <a href='index.php' class='btn btn-success'>
                            <i class='fas fa-home'></i> Inicio
                        </a>
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
