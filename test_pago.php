<?php
/**
 * Test del Sistema de Pago Completo
 * Verifica todo el flujo de pago desde el carrito hasta la confirmación
 */

session_start();

// Evitar incluir múltiples veces el mismo archivo
if (!defined('PAYPAL_CLIENT_ID')) {
    require_once 'administrador/config/paypal_config.php';
}

require_once 'clases/Usuario.php';
require_once 'clases/Carrito.php';

// Función para mostrar resultados
function mostrarResultado($titulo, $resultado, $detalles = '') {
    $icono = $resultado ? '✅' : '❌';
    $clase = $resultado ? 'success' : 'danger';
    echo "<div class='alert alert-{$clase}'>";
    echo "<strong>{$icono} {$titulo}</strong>";
    if ($detalles) echo "<br><small>{$detalles}</small>";
    echo "</div>";
}

// Función para verificar archivo
function verificarArchivo($archivo, $descripcion) {
    $existe = file_exists($archivo);
    mostrarResultado($descripcion, $existe, $existe ? "Archivo encontrado" : "Archivo no encontrado: {$archivo}");
    return $existe;
}

// Función para verificar tabla (CORREGIDA)
function verificarTabla($tabla, $descripcion) {
    try {
        require_once 'administrador/config/DB.php';
        $db = DB::getInstance();
        
        // Usar una consulta más simple y compatible
        $sql = "SELECT COUNT(*) as total FROM information_schema.tables 
                WHERE table_schema = DATABASE() AND table_name = ?";
        
        $resultado = $db->fetchOne($sql, [$tabla]);
        $existe = $resultado && $resultado['total'] > 0;
        
        mostrarResultado($descripcion, $existe, 
            $existe ? "Tabla encontrada" : "Tabla no encontrada: {$tabla}");
        return $existe;
    } catch (Exception $e) {
        mostrarResultado($descripcion, false, "Error: " . $e->getMessage());
        return false;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Sistema de Pago - Helmets PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .test-container { max-width: 800px; margin: 0 auto; }
        .progress-bar { height: 30px; }
        .resultado-final { font-size: 1.2em; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="test-container">
            <div class="text-center mb-4">
                <h1><i class="fas fa-credit-card text-primary"></i> Test Sistema de Pago</h1>
                <p class="text-muted">Verificación completa del flujo de pago</p>
            </div>

            <?php
            $total_pruebas = 0;
            $pruebas_exitosas = 0;

            echo "<h3><i class='fas fa-cogs'></i> Verificaciones del Sistema</h3>";
            
            // 1. Verificar configuración PayPal
            $total_pruebas++;
            $paypal_config = file_exists('administrador/config/paypal_config.php');
            if ($paypal_config) {
                $config_valida = !empty(PAYPAL_CLIENT_ID) && !empty(PAYPAL_CLIENT_SECRET);
                mostrarResultado("Configuración PayPal", $config_valida, 
                    $config_valida ? "Credenciales configuradas" : "Credenciales faltantes");
                if ($config_valida) $pruebas_exitosas++;
            } else {
                mostrarResultado("Configuración PayPal", false, "Archivo de configuración no encontrado");
            }

            // 2. Verificar archivos del sistema
            echo "<h4 class='mt-4'><i class='fas fa-file-code'></i> Archivos del Sistema</h4>";
            
            $archivos = [
                'checkout.php' => 'Página de Checkout',
                'procesar_pago.php' => 'Procesamiento de Pago',
                'confirmacion_pago.php' => 'Confirmación de Pago',
                'clases/Carrito.php' => 'Clase Carrito',
                'clases/Usuario.php' => 'Clase Usuario',
                'clases/Pedido.php' => 'Clase Pedido'
            ];

            foreach ($archivos as $archivo => $descripcion) {
                $total_pruebas++;
                if (verificarArchivo($archivo, $descripcion)) {
                    $pruebas_exitosas++;
                }
            }

            // 3. Verificar tablas de base de datos
            echo "<h4 class='mt-4'><i class='fas fa-database'></i> Base de Datos</h4>";
            
            $tablas = [
                'pedidos' => 'Tabla Pedidos',
                'detalles_pedido' => 'Tabla Detalles Pedido',
                'usuarios_clientes' => 'Tabla Usuarios Clientes',
                'cascos' => 'Tabla Productos'
            ];

            foreach ($tablas as $tabla => $descripcion) {
                $total_pruebas++;
                if (verificarTabla($tabla, $descripcion)) {
                    $pruebas_exitosas++;
                }
            }

            // 4. Verificar productos disponibles
            $total_pruebas++;
            try {
                $db = DB::getInstance();
                $productos = $db->fetchAll("SELECT COUNT(*) as total FROM cascos WHERE activo = 1");
                $hay_productos = $productos[0]['total'] > 0;
                mostrarResultado("Productos Disponibles", $hay_productos, 
                    $hay_productos ? "{$productos[0]['total']} productos activos" : "No hay productos activos");
                if ($hay_productos) $pruebas_exitosas++;
            } catch (Exception $e) {
                mostrarResultado("Productos Disponibles", false, "Error: " . $e->getMessage());
            }

            // 5. Verificar sistema de logs
            $total_pruebas++;
            $logs_dir = 'logs';
            $logs_existe = is_dir($logs_dir) || mkdir($logs_dir, 0755, true);
            mostrarResultado("Sistema de Logs", $logs_existe, 
                $logs_existe ? "Directorio de logs disponible" : "No se puede crear directorio de logs");
            if ($logs_existe) $pruebas_exitosas++;

            // Resultado final
            $porcentaje = round(($pruebas_exitosas / $total_pruebas) * 100);
            $todos_exitosos = $pruebas_exitosas === $total_pruebas;
            ?>

            <div class="mt-4">
                <h3><i class="fas fa-chart-bar"></i> Resultado Final</h3>
                <div class="progress mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo $todos_exitosos ? 'bg-success' : 'bg-warning'; ?>" 
                         role="progressbar" style="width: <?php echo $porcentaje; ?>%">
                        <?php echo $porcentaje; ?>%
                    </div>
                </div>
                
                <div class="alert <?php echo $todos_exitosos ? 'alert-success' : 'alert-warning'; ?> resultado-final">
                    <?php if ($todos_exitosos): ?>
                        <i class="fas fa-check-circle"></i> ¡SISTEMA DE PAGO COMPLETO! Todo está listo para procesar pagos.
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle"></i> Algunas pruebas fallaron. Revisa los errores arriba.
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-play"></i> Pruebas Manuales del Flujo Completo
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li>Ir a <a href="productos.php" target="_blank">productos.php</a> y agregar productos al carrito</li>
                                    <li>Verificar el carrito en <a href="carrito.php" target="_blank">carrito.php</a></li>
                                    <li>Iniciar sesión como cliente</li>
                                    <li>Proceder al checkout en <a href="checkout.php" target="_blank">checkout.php</a></li>
                                    <li>Completar el pago con PayPal Sandbox</li>
                                    <li>Verificar la confirmación en <a href="confirmacion_pago.php" target="_blank">confirmacion_pago.php</a></li>
                                    <li>Revisar el pedido en <a href="mi-cuenta.php" target="_blank">mi-cuenta.php</a></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <i class="fas fa-key"></i> Credenciales PayPal Sandbox
                            </div>
                            <div class="card-body">
                                <div class="alert alert-primary">
                                    <strong>Comprador:</strong><br>
                                    Email: sb-1234567890@business.example.com<br>
                                    Password: tu_contraseña_sandbox
                                </div>
                                <div class="alert alert-warning">
                                    <strong>Vendedor:</strong><br>
                                    Email: sb-1234567890@business.example.com<br>
                                    Password: tu_contraseña_sandbox
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
