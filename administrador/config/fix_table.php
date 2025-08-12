<?php
/**
 * Script para verificar y corregir la estructura de la tabla usuarios_admin
 * Helmets Pro v2.0 - Corrección de Estructura
 */

// Incluir clases necesarias
require_once 'DB.php';
require_once 'Auth.php';

class TableFixer {
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
     * Verificar estructura actual de la tabla
     */
    public function checkTableStructure() {
        try {
            $sql = "DESCRIBE usuarios_admin";
            $columns = $this->db->fetchAll($sql);
            
            $this->success[] = "✅ Estructura actual de la tabla usuarios_admin:";
            foreach ($columns as $column) {
                $this->success[] = "  - " . $column['Field'] . " (" . $column['Type'] . ")";
            }
            
            return $columns;
        } catch (Exception $e) {
            $this->errors[] = "❌ Error verificando estructura: " . $e->getMessage();
            return [];
        }
    }
    
    /**
     * Verificar si existe la columna rol
     */
    public function checkRolColumn($columns) {
        foreach ($columns as $column) {
            if ($column['Field'] === 'rol') {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Agregar columna rol si no existe
     */
    public function addRolColumn() {
        try {
            $sql = "ALTER TABLE usuarios_admin ADD COLUMN rol enum('admin','editor','viewer') DEFAULT 'admin' AFTER email";
            $this->db->query($sql);
            $this->success[] = "✅ Columna 'rol' agregada correctamente";
        } catch (Exception $e) {
            $this->errors[] = "❌ Error agregando columna rol: " . $e->getMessage();
        }
    }
    
    /**
     * Crear tabla desde cero si no existe
     */
    public function createTableFromScratch() {
        try {
            // Eliminar tabla si existe
            $this->db->query("DROP TABLE IF EXISTS usuarios_admin");
            $this->success[] = "✅ Tabla anterior eliminada";
            
            // Crear tabla nueva
            $sql = "
            CREATE TABLE `usuarios_admin` (
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
            
            $this->db->query($sql);
            $this->success[] = "✅ Tabla usuarios_admin creada desde cero";
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Error creando tabla: " . $e->getMessage();
        }
    }
    
    /**
     * Crear usuario admin
     */
    public function createAdminUser() {
        try {
            // Crear hash de contraseña
            $password_hash = Auth::hashPassword('admin123');
            
            // Insertar usuario admin
            $sql = "INSERT INTO usuarios_admin (usuario, password_hash, nombre_completo, email, rol, activo) VALUES (?, ?, ?, ?, ?, ?)";
            $params = ['admin', $password_hash, 'Administrador Principal', 'admin@helmetspro.com', 'admin', 1];
            
            $this->db->insert($sql, $params);
            $this->success[] = "✅ Usuario admin creado exitosamente";
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Error creando usuario admin: " . $e->getMessage();
        }
    }
    
    /**
     * Verificar que el usuario admin funciona
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
     * Ejecutar corrección completa
     */
    public function fix() {
        echo "<h2>🔧 Corrección de Estructura de Tabla</h2>";
        echo "<p>Helmets Pro v2.0 - Solución de Problemas</p><hr>";
        
        // Verificar estructura actual
        echo "<h3>📊 Verificando Estructura Actual</h3>";
        $columns = $this->checkTableStructure();
        
        if (!empty($columns)) {
            // Verificar si existe la columna rol
            $hasRol = $this->checkRolColumn($columns);
            
            if ($hasRol) {
                echo "<div class='alert alert-success'>";
                echo "<h4>✅ Columna 'rol' ya existe</h4>";
                echo "<p>La tabla tiene la estructura correcta.</p>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-warning'>";
                echo "<h4>⚠️ Columna 'rol' no existe</h4>";
                echo "<p>Se agregará la columna faltante.</p>";
                echo "</div>";
                
                // Agregar columna rol
                $this->addRolColumn();
            }
        } else {
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Tabla no existe o hay error</h4>";
            echo "<p>Se creará la tabla desde cero.</p>";
            echo "</div>";
            
            // Crear tabla desde cero
            $this->createTableFromScratch();
        }
        
        // Crear usuario admin
        echo "<h3>👤 Creando Usuario Admin</h3>";
        $this->createAdminUser();
        
        // Verificar usuario
        echo "<h3>✅ Verificando Usuario</h3>";
        $this->testAdminUser();
        
        // Mostrar resultados
        $this->displayResults();
    }
    
    /**
     * Mostrar resultados
     */
    public function displayResults() {
        echo "<h3>📊 Resultados de la Corrección</h3>";
        
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
            echo "<h4>🎉 Corrección Completada</h4>";
            echo "<p>La estructura de la tabla ha sido corregida.</p>";
            echo "<p><strong>Credenciales de acceso:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Usuario:</strong> admin</li>";
            echo "<li><strong>Contraseña:</strong> admin123</li>";
            echo "</ul>";
            echo "<p><a href='../index.php' class='btn btn-primary'>Ir al Login</a></p>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning'>";
            echo "<h4>⚠️ Corrección con Errores</h4>";
            echo "<p>Algunos errores ocurrieron durante la corrección.</p>";
            echo "</div>";
        }
    }
}

// Ejecutar corrección
if (php_sapi_name() !== 'cli') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Corrección de Tabla - Helmets Pro</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <?php
            $fixer = new TableFixer();
            $fixer->fix();
            ?>
        </div>
    </body>
    </html>
    <?php
} else {
    // Modo CLI
    $fixer = new TableFixer();
    $fixer->fix();
}
?> 