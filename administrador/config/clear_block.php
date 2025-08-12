<?php
/**
 * Script para limpiar bloqueo de acceso
 * Helmets Pro v2.0 - Reset de Rate Limiting
 */

// Incluir clases necesarias
require_once 'DB.php';
require_once 'Auth.php';

class BlockClearer {
    private $db;
    
    public function __construct() {
        try {
            $this->db = DB::getInstance();
        } catch (Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Limpiar archivo de logs de intentos fallidos
     */
    public function clearFailedLogs() {
        $log_file = '../logs/failed_logins.log';
        
        if (file_exists($log_file)) {
            // Hacer backup del archivo actual
            $backup_file = $log_file . '.backup.' . date('Y-m-d-H-i-s');
            copy($log_file, $backup_file);
            
            // Limpiar el archivo
            file_put_contents($log_file, '');
            
            return "✅ Archivo de logs limpiado (backup creado: " . basename($backup_file) . ")";
        } else {
            return "ℹ️ No se encontró archivo de logs para limpiar";
        }
    }
    
    /**
     * Limpiar registros de bloqueo en la base de datos
     */
    public function clearDatabaseBlocks() {
        try {
            // Limpiar logs de acceso recientes (últimas 24 horas)
            $sql = "DELETE FROM logs_acceso WHERE tipo_acceso = 'login_fallido' AND fecha_acceso > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $this->db->query($sql);
            
            return "✅ Registros de bloqueo en BD limpiados";
        } catch (Exception $e) {
            return "❌ Error limpiando BD: " . $e->getMessage();
        }
    }
    
    /**
     * Verificar estado del sistema
     */
    public function checkSystemStatus() {
        $status = [];
        
        // Verificar archivo de logs
        $log_file = '../logs/failed_logins.log';
        if (file_exists($log_file)) {
            $size = filesize($log_file);
            $status[] = "📄 Archivo de logs: " . ($size > 0 ? $size . " bytes" : "vacío");
        } else {
            $status[] = "📄 Archivo de logs: no existe";
        }
        
        // Verificar conexión a BD
        try {
            $this->db->query("SELECT 1");
            $status[] = "🗄️ Base de datos: conectada";
        } catch (Exception $e) {
            $status[] = "❌ Base de datos: error de conexión";
        }
        
        return $status;
    }
    
    /**
     * Ejecutar limpieza completa
     */
    public function clearAll() {
        echo "<h2>🔓 Limpieza de Bloqueo de Acceso</h2>";
        echo "<p>Helmets Pro v2.0 - Reset de Rate Limiting</p><hr>";
        
        // Verificar estado inicial
        echo "<h3>📊 Estado Inicial del Sistema</h3>";
        $status = $this->checkSystemStatus();
        echo "<ul>";
        foreach ($status as $item) {
            echo "<li>$item</li>";
        }
        echo "</ul>";
        
        echo "<h3>🧹 Proceso de Limpieza</h3>";
        
        // Limpiar logs
        $log_result = $this->clearFailedLogs();
        echo "<p>$log_result</p>";
        
        // Limpiar BD
        $db_result = $this->clearDatabaseBlocks();
        echo "<p>$db_result</p>";
        
        echo "<hr>";
        echo "<h3>✅ Limpieza Completada</h3>";
        echo "<p>El sistema de bloqueo ha sido reseteado. Ahora puedes intentar hacer login nuevamente.</p>";
        echo "<p><strong>Credenciales por defecto:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Usuario:</strong> admin</li>";
        echo "<li><strong>Contraseña:</strong> admin123</li>";
        echo "</ul>";
        echo "<p><a href='../index.php' class='btn btn-primary'>Ir al Login</a></p>";
    }
}

// Ejecutar limpieza
if (php_sapi_name() !== 'cli') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Limpieza de Bloqueo - Helmets Pro</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <?php
            $clearer = new BlockClearer();
            $clearer->clearAll();
            ?>
        </div>
    </body>
    </html>
    <?php
} else {
    // Modo CLI
    $clearer = new BlockClearer();
    $clearer->clearAll();
}
?> 