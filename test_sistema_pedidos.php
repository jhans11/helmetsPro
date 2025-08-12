<?php
/**
 * Script de pruebas para el Sistema de Gestión de Pedidos
 * Prueba: Dashboard, Detalles, Cambio de Estados y Notificaciones
 */

require_once 'administrador/config/config.php';
require_once 'administrador/config/DB.php';
require_once 'administrador/config/Auth.php';
require_once 'administrador/config/Middleware.php';
require_once 'administrador/config/EmailNotifier.php';

// Obtener instancia de DB usando el patrón Singleton
$db = DB::getInstance();

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>�� Pruebas Sistema de Gestión de Pedidos - HelmetsPro</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .test-success { background-color: #d4edda; border-color: #c3e6cb; }
        .test-error { background-color: #f8d7da; border-color: #f5c6cb; }
        .test-warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .test-info { background-color: #d1ecf1; border-color: #bee5eb; }
        .code-block { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; }
        .status-badge { font-size: 0.8em; padding: 0.3em 0.6em; }
    </style>
</head>
<body>
    <div class='container-fluid'>
        <div class='row'>
            <div class='col-12'>
                <h1 class='text-center mb-4'>
                    🧪 Pruebas del Sistema de Gestión de Pedidos
                </h1>
                <p class='text-center text-muted'>HelmetsPro - Fase 3 Completada</p>
            </div>
        </div>";

// Función para mostrar resultados de pruebas
function mostrarResultado($titulo, $resultado, $tipo = 'info', $detalles = '') {
    $clase = "test-$tipo";
    $icono = $tipo === 'success' ? '✅' : ($tipo === 'error' ? '❌' : ($tipo === 'warning' ? '⚠️' : 'ℹ️'));
    
    echo "<div class='test-section $clase'>
            <h4>$icono $titulo</h4>
            <p><strong>Estado:</strong> $resultado</p>";
    
    if ($detalles) {
        echo "<div class='code-block'>$detalles</div>";
    }
    
    echo "</div>";
}

// Función para verificar tabla
function verificarTabla($db, $nombre_tabla) {
    try {
        $sql = "SELECT COUNT(*) as total FROM information_schema.tables 
                WHERE table_schema = 'sitioweb' AND table_name = ?";
        $resultado = $db->fetchOne($sql, [$nombre_tabla]);
        return $resultado['total'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Función para contar registros
function contarRegistros($db, $tabla) {
    try {
        $sql = "SELECT COUNT(*) as total FROM $tabla";
        $resultado = $db->fetchOne($sql);
        return $resultado['total'];
    } catch (Exception $e) {
        return 0;
    }
}

echo "<div class='row'>
        <div class='col-12'>
            <h2>�� 1. Verificación de Base de Datos</h2>";

// 1. Verificar tablas necesarias
$tablas_requeridas = [
    'pedidos',
    'detalles_pedido', 
    'usuarios_clientes',
    'usuarios_admin',
    'historial_estados_pedido',
    'logs_emails',
    'cascos'
];

foreach ($tablas_requeridas as $tabla) {
    $existe = verificarTabla($db, $tabla);
    $tipo = $existe ? 'success' : 'error';
    $resultado = $existe ? 'Tabla encontrada' : 'Tabla NO encontrada';
    mostrarResultado("Verificar tabla: $tabla", $resultado, $tipo);
}

// 2. Verificar datos en tablas
echo "<h3>📊 Estadísticas de Datos</h3>";

$estadisticas = [
    'pedidos' => 'Pedidos',
    'usuarios_clientes' => 'Usuarios Clientes',
    'cascos' => 'Productos',
    'historial_estados_pedido' => 'Cambios de Estado',
    'logs_emails' => 'Logs de Emails'
];

foreach ($estadisticas as $tabla => $nombre) {
    $total = contarRegistros($db, $tabla);
    $tipo = $total > 0 ? 'success' : 'warning';
    $resultado = $total > 0 ? "$total registros" : 'Sin registros';
    mostrarResultado("Registros en $nombre", $resultado, $tipo);
}

echo "</div></div>";

// 3. Pruebas de funcionalidad
echo "<div class='row'>
        <div class='col-12'>
            <h2>⚙️ 2. Pruebas de Funcionalidad</h2>";

// 3.1 Verificar acceso a páginas admin
$paginas_admin = [
    'administrador/pedidos.php' => 'Dashboard de Pedidos',
    'administrador/detalle-pedido.php' => 'Detalle de Pedido',
    'administrador/actualizar_estado_pedido.php' => 'Actualizar Estado',
    'administrador/exportar_pedidos.php' => 'Exportar Pedidos'
];

foreach ($paginas_admin as $ruta => $nombre) {
    $existe = file_exists($ruta);
    $tipo = $existe ? 'success' : 'error';
    $resultado = $existe ? 'Archivo encontrado' : 'Archivo NO encontrado';
    mostrarResultado("Verificar $nombre", $resultado, $tipo, "Ruta: $ruta");
}

// 3.2 Verificar clases
$clases = [
    'administrador/config/EmailNotifier.php' => 'EmailNotifier',
    'administrador/config/plantillas_email.php' => 'PlantillasEmail'
];

foreach ($clases as $ruta => $nombre) {
    $existe = file_exists($ruta);
    $tipo = $existe ? 'success' : 'error';
    $resultado = $existe ? 'Clase encontrada' : 'Clase NO encontrada';
    mostrarResultado("Verificar clase $nombre", $resultado, $tipo, "Ruta: $ruta");
}

echo "</div></div>";

// 4. Pruebas de datos de ejemplo
echo "<div class='row'>
        <div class='col-12'>
            <h2>📝 3. Crear Datos de Prueba</h2>";

// 4.1 Crear usuario de prueba
try {
    $sql_usuario = "INSERT INTO usuarios_clientes (nombre, apellido, email, password, telefono, fecha_registro) 
                    VALUES (?, ?, ?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE id = id";
    
    $password_hash = password_hash('test123', PASSWORD_DEFAULT);
    $usuario_creado = $db->insert($sql_usuario, [
        'Usuario', 'Prueba', 'test@helmetspro.com', $password_hash, '3001234567'
    ]);
    
    if ($usuario_creado) {
        mostrarResultado("Usuario de prueba creado", "Usuario creado exitosamente", 'success', 
                        "Email: test@helmetspro.com | Password: test123");
    } else {
        mostrarResultado("Usuario de prueba", "Usuario ya existe o error", 'warning');
    }
} catch (Exception $e) {
    mostrarResultado("Usuario de prueba", "Error: " . $e->getMessage(), 'error');
}

// 4.2 Crear pedido de prueba
try {
    // Obtener usuario de prueba
    $sql_get_user = "SELECT id FROM usuarios_clientes WHERE email = ?";
    $usuario = $db->fetchOne($sql_get_user, ['test@helmetspro.com']);
    
    if ($usuario) {
        // Obtener producto de prueba
        $sql_get_product = "SELECT id_casco FROM cascos LIMIT 1";
        $producto = $db->fetchOne($sql_get_product);
        
        if ($producto) {
            // Crear pedido
            $numero_pedido = 'TEST-' . date('Ymd') . '-' . rand(1000, 9999);
            $sql_pedido = "INSERT INTO pedidos (numero_pedido, id_usuario, total, estado, fecha_creacion) 
                          VALUES (?, ?, ?, ?, NOW())";
            
            $pedido_creado = $db->insert($sql_pedido, [
                $numero_pedido, $usuario['id'], 299.99, 'Pendiente'
            ]);
            
            if ($pedido_creado) {
                // Crear detalle del pedido
                $sql_detalle = "INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario) 
                               VALUES (?, ?, ?, ?)";
                
                $detalle_creado = $db->insert($sql_detalle, [
                    $pedido_creado, $producto['id_casco'], 1, 299.99
                ]);
                
                if ($detalle_creado) {
                    mostrarResultado("Pedido de prueba creado", "Pedido creado exitosamente", 'success', 
                                    "Número: $numero_pedido | Total: $299.99 | Estado: Pendiente");
                } else {
                    mostrarResultado("Detalle de pedido", "Error creando detalle", 'error');
                }
            } else {
                mostrarResultado("Pedido de prueba", "Error creando pedido", 'error');
            }
        } else {
            mostrarResultado("Producto de prueba", "No hay productos en la base de datos", 'warning');
        }
    } else {
        mostrarResultado("Usuario para pedido", "Usuario de prueba no encontrado", 'error');
    }
} catch (Exception $e) {
    mostrarResultado("Pedido de prueba", "Error: " . $e->getMessage(), 'error');
}

echo "</div></div>";

// 5. Pruebas de notificaciones
echo "<div class='row'>
        <div class='col-12'>
            <h2>📧 4. Pruebas de Notificaciones</h2>";

// 5.1 Probar envío de email de prueba
try {
    $pedido_prueba = [
        'numero_pedido' => 'TEST-EMAIL',
        'nombre' => 'Usuario',
        'apellido' => 'Prueba',
        'email' => 'test@helmetspro.com',
        'total' => 299.99,
        'fecha_creacion' => date('Y-m-d H:i:s')
    ];
    
    $email_enviado = EmailNotifier::enviarNotificacionEstado(
        $pedido_prueba, 'Pendiente', 'Pagado', 'Prueba de sistema'
    );
    
    if ($email_enviado) {
        mostrarResultado("Envío de email de prueba", "Email enviado correctamente", 'success', 
                        "Revisar logs/emails.log para ver el email simulado");
    } else {
        mostrarResultado("Envío de email de prueba", "Error enviando email", 'error');
    }
} catch (Exception $e) {
    mostrarResultado("Envío de email de prueba", "Error: " . $e->getMessage(), 'error');
}

// 5.2 Verificar archivo de logs
$log_file = 'logs/emails.log';
if (file_exists($log_file)) {
    $log_size = filesize($log_file);
    $tipo = $log_size > 0 ? 'success' : 'warning';
    $resultado = $log_size > 0 ? "Log creado ($log_size bytes)" : "Log vacío";
    mostrarResultado("Archivo de logs de email", $resultado, $tipo, "Ruta: $log_file");
} else {
    mostrarResultado("Archivo de logs de email", "Archivo no encontrado", 'error', "Ruta: $log_file");
}

echo "</div></div>";

// 6. Pruebas de consultas
echo "<div class='row'>
        <div class='col-12'>
            <h2>🔍 5. Pruebas de Consultas</h2>";

// 6.1 Consultar pedidos recientes
try {
    $sql_pedidos = "SELECT p.*, uc.nombre, uc.apellido, uc.email 
                    FROM pedidos p 
                    LEFT JOIN usuarios_clientes uc ON p.id_usuario = uc.id 
                    ORDER BY p.fecha_creacion DESC 
                    LIMIT 5";
    
    $pedidos = $db->fetchAll($sql_pedidos);
    
    if (!empty($pedidos)) {
        $html_pedidos = "<table class='table table-sm'>
                            <thead><tr><th>Número</th><th>Cliente</th><th>Total</th><th>Estado</th></tr></thead>
                            <tbody>";
        
        foreach ($pedidos as $pedido) {
            $estado_class = '';
            switch ($pedido['estado']) {
                case 'Pendiente': $estado_class = 'badge-warning'; break;
                case 'Pagado': $estado_class = 'badge-info'; break;
                case 'Enviado': $estado_class = 'badge-primary'; break;
                case 'Entregado': $estado_class = 'badge-success'; break;
            }
            
            $html_pedidos .= "<tr>
                                <td>#{$pedido['numero_pedido']}</td>
                                <td>{$pedido['nombre']} {$pedido['apellido']}</td>
                                <td>\${$pedido['total']}</td>
                                <td><span class='badge $estado_class'>{$pedido['estado']}</span></td>
                              </tr>";
        }
        
        $html_pedidos .= "</tbody></table>";
        
        mostrarResultado("Pedidos recientes", count($pedidos) . " pedidos encontrados", 'success', $html_pedidos);
    } else {
        mostrarResultado("Pedidos recientes", "No hay pedidos", 'warning');
    }
} catch (Exception $e) {
    mostrarResultado("Pedidos recientes", "Error: " . $e->getMessage(), 'error');
}

// 6.2 Consultar estadísticas
try {
    $sql_stats = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'Pagado' THEN 1 ELSE 0 END) as pagados,
                    SUM(CASE WHEN estado = 'Enviado' THEN 1 ELSE 0 END) as enviados,
                    SUM(CASE WHEN estado = 'Entregado' THEN 1 ELSE 0 END) as entregados
                  FROM pedidos";
    
    $stats = $db->fetchOne($sql_stats);
    
    $html_stats = "<div class='row'>
                        <div class='col-md-2'><strong>Total:</strong> {$stats['total_pedidos']}</div>
                        <div class='col-md-2'><strong>Pendientes:</strong> {$stats['pendientes']}</div>
                        <div class='col-md-2'><strong>Pagados:</strong> {$stats['pagados']}</div>
                        <div class='col-md-2'><strong>Enviados:</strong> {$stats['enviados']}</div>
                        <div class='col-md-2'><strong>Entregados:</strong> {$stats['entregados']}</div>
                    </div>";
    
    mostrarResultado("Estadísticas de pedidos", "Estadísticas calculadas", 'success', $html_stats);
} catch (Exception $e) {
    mostrarResultado("Estadísticas de pedidos", "Error: " . $e->getMessage(), 'error');
}

echo "</div></div>";

// 7. Resumen final
echo "<div class='row'>
        <div class='col-12'>
            <h2>�� 6. Resumen de Pruebas</h2>
            <div class='test-section test-info'>
                <h4>🎯 Funcionalidades Implementadas</h4>
                <ul>
                    <li><strong>Dashboard Admin:</strong> Listado de pedidos con filtros y paginación</li>
                    <li><strong>Detalles de Pedido:</strong> Vista completa con información del cliente y productos</li>
                    <li><strong>Gestión de Estados:</strong> Cambio de estados con historial</li>
                    <li><strong>Notificaciones:</strong> Sistema de emails automáticos</li>
                    <li><strong>Exportación:</strong> Exportar pedidos a CSV</li>
                    <li><strong>Logs:</strong> Registro de cambios y emails enviados</li>
                </ul>
                
                <h4>🔗 Enlaces de Prueba</h4>
                <p><strong>Dashboard Admin:</strong> <a href='administrador/pedidos.php' target='_blank'>Ver Pedidos</a></p>
                <p><strong>Login Admin:</strong> <a href='administrador/index.php' target='_blank'>Acceder al Admin</a></p>
                
                <h4>�� Datos de Prueba</h4>
                <p><strong>Usuario Admin:</strong> admin / admin123</p>
                <p><strong>Usuario Cliente:</strong> test@helmetspro.com / test123</p>
                
                <h4>⚠️ Notas Importantes</h4>
                <ul>
                    <li>Los emails se simulan en desarrollo (ver logs/emails.log)</li>
                    <li>Para producción, configurar SMTP real en EmailNotifier.php</li>
                    <li>Verificar permisos de escritura en carpeta logs/</li>
                </ul>
            </div>
        </div>
    </div>";

echo "</div>
    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>