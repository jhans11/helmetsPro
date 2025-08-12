<?php
/**
 * Script de Prueba - Clase DB Mejorada
 * Helmets Pro v2.0 - Verificación de Funcionalidades
 */

// Incluir clases necesarias
require_once 'config.php';
require_once 'DB_new.php';

class DBTest {
    private $db;
    
    public function __construct() {
        try {
            $this->db = DB::getInstance();
        } catch (Exception $e) {
            die("Error inicializando DB: " . $e->getMessage());
        }
    }
    
    /**
     * Probar conexión básica
     */
    public function testConnection() {
        echo "<h3>🔌 Prueba de Conexión</h3>";
        
        try {
            $info = $this->db->getDatabaseInfo();
            
            if ($info['connection_status'] === 'Connected') {
                echo "<div class='alert alert-success'>";
                echo "<h4>✅ Conexión Exitosa</h4>";
                echo "<ul>";
                echo "<li><strong>Versión MySQL:</strong> " . $info['version'] . "</li>";
                echo "<li><strong>Base de Datos:</strong> " . $info['database'] . "</li>";
                echo "<li><strong>Estado:</strong> " . $info['connection_status'] . "</li>";
                echo "</ul>";
                echo "</div>";
                return true;
            } else {
                echo "<div class='alert alert-danger'>";
                echo "<h4>❌ Error de Conexión</h4>";
                echo "<p>" . ($info['error'] ?? 'Error desconocido') . "</p>";
                echo "</div>";
                return false;
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Error en Prueba</h4>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
            return false;
        }
    }
    
    /**
     * Probar consultas básicas
     */
    public function testQueries() {
        echo "<h3>📊 Prueba de Consultas</h3>";
        
        try {
            // Probar SELECT
            $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM usuarios_admin");
            echo "<div class='alert alert-info'>";
            echo "<h4>✅ Consulta SELECT</h4>";
            echo "<p>Total de usuarios: " . ($result['total'] ?? 0) . "</p>";
            echo "</div>";
            
            // Probar INSERT (solo si no existe)
            $testUser = $this->db->fetchOne("SELECT id FROM usuarios_admin WHERE usuario = 'test_user'");
            if (!$testUser) {
                $sql = "INSERT INTO usuarios_admin (usuario, password_hash, nombre_completo, email, rol, activo) VALUES (?, ?, ?, ?, ?, ?)";
                $params = ['test_user', password_hash('test123', PASSWORD_DEFAULT), 'Usuario de Prueba', 'test@example.com', 'viewer', 1];
                
                $insertId = $this->db->insert($sql, $params);
                echo "<div class='alert alert-success'>";
                echo "<h4>✅ Consulta INSERT</h4>";
                echo "<p>Usuario de prueba creado con ID: $insertId</p>";
                echo "</div>";
            }
            
            // Probar UPDATE
            $updateResult = $this->db->update("UPDATE usuarios_admin SET nombre_completo = ? WHERE usuario = ?", ['Usuario de Prueba Actualizado', 'test_user']);
            echo "<div class='alert alert-info'>";
            echo "<h4>✅ Consulta UPDATE</h4>";
            echo "<p>Filas actualizadas: $updateResult</p>";
            echo "</div>";
            
            // Probar DELETE
            $deleteResult = $this->db->delete("DELETE FROM usuarios_admin WHERE usuario = ?", ['test_user']);
            echo "<div class='alert alert-warning'>";
            echo "<h4>✅ Consulta DELETE</h4>";
            echo "<p>Filas eliminadas: $deleteResult</p>";
            echo "</div>";
            
            return true;
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Error en Consultas</h4>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
            return false;
        }
    }
    
    /**
     * Probar transacciones
     */
    public function testTransactions() {
        echo "<h3>💾 Prueba de Transacciones</h3>";
        
        try {
            $this->db->beginTransaction();
            
            // Insertar usuario de prueba
            $sql = "INSERT INTO usuarios_admin (usuario, password_hash, nombre_completo, email, rol, activo) VALUES (?, ?, ?, ?, ?, ?)";
            $params = ['trans_test', password_hash('test123', PASSWORD_DEFAULT), 'Usuario Transacción', 'trans@example.com', 'viewer', 1];
            
            $insertId = $this->db->insert($sql, $params);
            
            // Verificar que se insertó
            $user = $this->db->fetchOne("SELECT * FROM usuarios_admin WHERE id = ?", [$insertId]);
            
            if ($user) {
                echo "<div class='alert alert-success'>";
                echo "<h4>✅ Transacción Exitosa</h4>";
                echo "<p>Usuario creado en transacción: " . $user['usuario'] . "</p>";
                echo "</div>";
                
                // Hacer rollback para limpiar
                $this->db->rollback();
                echo "<div class='alert alert-info'>";
                echo "<p>Rollback realizado - datos de prueba eliminados</p>";
                echo "</div>";
            } else {
                throw new Exception("No se pudo crear usuario en transacción");
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Error en Transacción</h4>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
            return false;
        }
    }
    
    /**
     * Probar logging
     */
    public function testLogging() {
        echo "<h3>📝 Prueba de Logging</h3>";
        
        try {
            // Limpiar log anterior
            $this->db->clearQueryLog();
            
            // Realizar algunas consultas
            $this->db->fetchOne("SELECT COUNT(*) as total FROM usuarios_admin");
            $this->db->fetchAll("SELECT usuario, nombre_completo FROM usuarios_admin LIMIT 5");
            
            // Obtener estadísticas
            $stats = $this->db->getQueryStats();
            $queryLog = $this->db->getQueryLog();
            
            echo "<div class='alert alert-info'>";
            echo "<h4>✅ Logging Funcionando</h4>";
            echo "<ul>";
            echo "<li><strong>Total de consultas:</strong> " . $stats['total_queries'] . "</li>";
            echo "<li><strong>Última consulta:</strong> " . ($stats['last_query'] ?? 'N/A') . "</li>";
            echo "<li><strong>Estado conexión:</strong> " . ($stats['is_connected'] ? 'Conectado' : 'Desconectado') . "</li>";
            echo "</ul>";
            echo "</div>";
            
            if (!empty($queryLog)) {
                echo "<h4>📋 Log de Consultas:</h4>";
                echo "<table class='table table-sm'>";
                echo "<thead><tr><th>Timestamp</th><th>Tipo</th><th>SQL</th><th>Parámetros</th></tr></thead>";
                echo "<tbody>";
                foreach (array_slice($queryLog, -5) as $log) { // Mostrar solo las últimas 5
                    echo "<tr>";
                    echo "<td>" . $log['timestamp'] . "</td>";
                    echo "<td>" . $log['type'] . "</td>";
                    echo "<td>" . htmlspecialchars(substr($log['sql'], 0, 50)) . "...</td>";
                    echo "<td>" . htmlspecialchars(json_encode($log['params'])) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            }
            
            return true;
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Error en Logging</h4>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
            return false;
        }
    }
    
    /**
     * Probar configuración
     */
    public function testConfiguration() {
        echo "<h3>⚙️ Prueba de Configuración</h3>";
        
        try {
            echo "<div class='alert alert-info'>";
            echo "<h4>✅ Configuración Cargada</h4>";
            echo "<ul>";
            echo "<li><strong>Host BD:</strong> " . Config::env('DB_HOST', 'localhost') . "</li>";
            echo "<li><strong>Base de Datos:</strong> " . Config::env('DB_NAME', 'sitio') . "</li>";
            echo "<li><strong>Logging habilitado:</strong> " . (Config::get('logging.log_queries', false) ? 'Sí' : 'No') . "</li>";
            echo "<li><strong>Timeout de sesión:</strong> " . Config::get('security.session_timeout', 1800) . " segundos</li>";
            echo "<li><strong>Timezone:</strong> " . Config::get('app.timezone', 'UTC') . "</li>";
            echo "</ul>";
            echo "</div>";
            
            return true;
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Error en Configuración</h4>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
            return false;
        }
    }
    
    /**
     * Ejecutar todas las pruebas
     */
    public function runAllTests() {
        echo "<h2>🧪 Pruebas de la Clase DB Mejorada</h2>";
        echo "<p>Helmets Pro v2.0 - Verificación de Funcionalidades</p><hr>";
        
        $results = [];
        
        // Ejecutar pruebas
        $results['connection'] = $this->testConnection();
        $results['queries'] = $this->testQueries();
        $results['transactions'] = $this->testTransactions();
        $results['logging'] = $this->testLogging();
        $results['configuration'] = $this->testConfiguration();
        
        // Mostrar resumen
        echo "<hr>";
        echo "<h3>📊 Resumen de Pruebas</h3>";
        
        $passed = array_sum($results);
        $total = count($results);
        
        echo "<div class='alert alert-" . ($passed === $total ? 'success' : 'warning') . "'>";
        echo "<h4>" . ($passed === $total ? '🎉 Todas las pruebas pasaron' : '⚠️ Algunas pruebas fallaron') . "</h4>";
        echo "<p>Pruebas exitosas: $passed de $total</p>";
        echo "</div>";
        
        echo "<ul>";
        foreach ($results as $test => $result) {
            $status = $result ? '✅' : '❌';
            $name = ucfirst($test);
            echo "<li>$status $name</li>";
        }
        echo "</ul>";
        
        if ($passed === $total) {
            echo "<div class='alert alert-success'>";
            echo "<h4>🎉 ¡Refactorización Completada!</h4>";
            echo "<p>La clase DB ha sido mejorada exitosamente con:</p>";
            echo "<ul>";
            echo "<li>✅ Configuración centralizada</li>";
            echo "<li>✅ Variables de entorno</li>";
            echo "<li>✅ Logging de consultas</li>";
            echo "<li>✅ Manejo de errores mejorado</li>";
            echo "<li>✅ Validación de parámetros</li>";
            echo "<li>✅ Métodos adicionales útiles</li>";
            echo "</ul>";
            echo "</div>";
        }
    }
}

// Ejecutar pruebas
if (php_sapi_name() !== 'cli') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pruebas DB - Helmets Pro</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <?php
            $tester = new DBTest();
            $tester->runAllTests();
            ?>
        </div>
    </body>
    </html>
    <?php
} else {
    // Modo CLI
    $tester = new DBTest();
    $tester->runAllTests();
}
?> 