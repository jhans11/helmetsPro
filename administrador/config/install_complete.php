<?php
/**
 * Script de Instalación Completa del Sistema de Autenticación
 * Helmets Pro v2.0 - Instalación desde Cero
 */

// Incluir clases necesarias
require_once 'DB.php';
require_once 'Auth.php';

class CompleteInstaller {
    private $db;
    private $errors = [];
    private $success = [];
    
    public function __construct() {
        try {
            $this->db = DB::getInstance();
            $this->success[] = "✅ Conexión a base de datos establecida";
        } catch (Exception $e) {
            $this->errors[] = "❌ Error de conexión: " . $e->getMessage();
        }
    }
    
    /**
     * Crear tabla de usuarios administradores
     */
    public function createUsersTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS `usuarios_admin` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `usuario` varchar(50) NOT NULL UNIQUE,
            `password_hash` varchar(255) NOT NULL,
            `nombre_completo` varchar(100) NOT NULL,
            `email` varchar(100) DEFAULT NULL,
            `rol` enum('admin','editor','viewer') DEFAULT 'admin',
            `activo` tinyint(1) DEFAULT 1,
            `ultimo_acceso` datetime DEFAULT NULL,
            `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
            `fecha_actualizacion` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_usuario` (`usuario`),
            KEY `idx_activo` (`activo`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        try {
            $this->db->query($sql);
            $this->success[] = "✅ Tabla usuarios_admin creada correctamente";
        } catch (Exception $e) {
            $this->errors[] = "❌ Error creando tabla usuarios_admin: " . $e->getMessage();
        }
    }
    
    /**
     * Crear tabla de logs de acceso
     */
    public function createLogsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS `logs_acceso` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `usuario_id` int(11) DEFAULT NULL,
            `ip_address` varchar(45) NOT NULL,
            `user_agent` text,
            `tipo_acceso` enum('login_exitoso','login_fallido','logout') NOT NULL,
            `fecha_acceso` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_usuario_id` (`usuario_id`),
            KEY `idx_fecha_acceso` (`fecha_acceso`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        try {
            $this->db->query($sql);
            $this->success[] = "✅ Tabla logs_acceso creada correctamente";
        } catch (Exception $e) {
            $this->errors[] = "❌ Error creando tabla logs_acceso: " . $e->getMessage();
        }
    }
    
    /**
     * Eliminar usuario admin existente y crear uno nuevo
     */
    public function recreateAdminUser() {
        try {
            // Eliminar usuario admin existente si existe
            $this->db->query("DELETE FROM usuarios_admin WHERE usuario = 'admin'");
            $this->success[] = "✅ Usuario admin anterior eliminado";
            
            // Crear hash de contraseña
            $password_hash = Auth::hashPassword('admin123');
            
            // Insertar nuevo usuario admin
            $sql = "INSERT INTO usuarios_admin (usuario, password_hash, nombre_completo, email, rol, activo) VALUES (?, ?, ?, ?, ?, ?)";
            $params = ['admin', $password_hash, 'Administrador Principal', 'admin@helmetspro.com', 'admin', 1];
            
            $this->db->insert($sql, $params);
            $this->success[] = "✅ Usuario admin recreado exitosamente";
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Error recreando usuario admin: " . $e->getMessage();
        }
    }
    
    /**
     * Verificar que el usuario admin existe y funciona
     */
    public function testAdminUser() {
        try {
            $sql = "SELECT * FROM usuarios_admin WHERE usuario = 'admin' AND activo = 1";
            $user = $this->db->fetchOne($sql);
            
            if ($user) {
                // Probar la verificación de contraseña
                if (password_verify('admin123', $user['password_hash'])) {
                    $this->success[] = "✅ Usuario admin verificado correctamente";
                    return true;
                } else {
                    $this->errors[] = "❌ Error: La contraseña del usuario admin no coincide";
                    return false;
                }
            } else {
                $this->errors[] = "❌ Error: Usuario admin no encontrado";
                return false;
            }
        } catch (Exception $e) {
            $this->errors[] = "❌ Error verificando usuario admin: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Limpiar logs de intentos fallidos
     */
    public function clearFailedLogs() {
        $log_file = '../logs/failed_logins.log';
        
        if (file_exists($log_file)) {
            // Hacer backup del archivo actual
            $backup_file = $log_file . '.backup.' . date('Y-m-d-H-i-s');
            copy($log_file, $backup_file);
            
            // Limpiar el archivo
            file_put_contents($log_file, '');
            
            $this->success[] = "✅ Archivo de logs limpiado (backup creado: " . basename($backup_file) . ")";
        } else {
            $this->success[] = "ℹ️ No se encontró archivo de logs para limpiar";
        }
    }
    
    /**
     * Crear directorio de logs si no existe
     */
    public function createLogsDirectory() {
        $logs_dir = '../logs';
        if (!is_dir($logs_dir)) {
            if (mkdir($logs_dir, 0755, true)) {
                $this->success[] = "✅ Directorio de logs creado";
            } else {
                $this->errors[] = "❌ No se pudo crear el directorio de logs";
            }
        } else {
            $this->success[] = "ℹ️ Directorio de logs ya existe";
        }
    }
    
    /**
     * Ejecutar instalación completa
     */
    public function install() {
        echo "<h2>🔧 Instalación Completa del Sistema de Autenticación</h2>";
        echo "<p>Helmets Pro v2.0 - Instalación desde Cero</p><hr>";
        
        // Crear tablas
        $this->createUsersTable();
        $this->createLogsTable();
        
        // Crear directorio de logs
        $this->createLogsDirectory();
        
        // Recrear usuario admin
        $this->recreateAdminUser();
        
        // Verificar usuario admin
        $this->testAdminUser();
        
        // Limpiar logs
        $this->clearFailedLogs();
        
        // Mostrar resultados
        $this->displayResults();
    }
    
    /**
     * Mostrar resultados de la instalación
     */
    public function displayResults() {
        echo "<h3>📊 Resultados de la Instalación</h3>";
        
        if (!empty($this->success)) {
            echo "<div class='alert alert-success'>";
            echo "<h4>✅ Operaciones Exitosas:</h4>";
            echo "<ul>";
            foreach ($this->success as $msg) {
                echo "<li>$msg</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
        
        if (!empty($this->errors)) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Errores Encontrados:</h4>";
            echo "<ul>";
            foreach ($this->errors as $msg) {
                echo "<li>$msg</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
        
        if (empty($this->errors)) {
            echo "<div class='alert alert-info'>";
            echo "<h4>🎉 Instalación Completada Exitosamente</h4>";
            echo "<p>El sistema de autenticación está listo para usar.</p>";
            echo "<p><strong>Credenciales de acceso:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Usuario:</strong> admin</li>";
            echo "<li><strong>Contraseña:</strong> admin123</li>";
            echo "</ul>";
            echo "<p><a href='../index.php' class='btn btn-primary'>Ir al Login</a></p>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning'>";
            echo "<h4>⚠️ Instalación con Errores</h4>";
            echo "<p>Algunos errores ocurrieron durante la instalación. Revisa los errores arriba.</p>";
            echo "</div>";
        }
    }
}

// Ejecutar instalación
if (php_sapi_name() !== 'cli') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Instalación Completa - Helmets Pro</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <?php
            $installer = new CompleteInstaller();
            $installer->install();
            ?>
        </div>
    </body>
    </html>
    <?php
} else {
    // Modo CLI
    $installer = new CompleteInstaller();
    $installer->install();
}
?> 