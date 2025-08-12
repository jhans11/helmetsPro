<?php
/**
 * Script para verificar usuario admin
 * Helmets Pro v2.0 - Verificación de Usuario
 */

// Incluir clases necesarias
require_once 'DB.php';
require_once 'Auth.php';

class AdminChecker {
    private $db;
    
    public function __construct() {
        try {
            $this->db = DB::getInstance();
        } catch (Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar si existe la tabla usuarios_admin
     */
    public function checkTableExists() {
        try {
            $sql = "SHOW TABLES LIKE 'usuarios_admin'";
            $result = $this->db->fetchOne($sql);
            
            if ($result) {
                return "✅ Tabla usuarios_admin existe";
            } else {
                return "❌ Tabla usuarios_admin NO existe";
            }
        } catch (Exception $e) {
            return "❌ Error verificando tabla: " . $e->getMessage();
        }
    }
    
    /**
     * Verificar si existe el usuario admin
     */
    public function checkAdminUser() {
        try {
            $sql = "SELECT id, usuario, nombre_completo, email, activo FROM usuarios_admin WHERE usuario = 'admin'";
            $user = $this->db->fetchOne($sql);
            
            if ($user) {
                return [
                    'exists' => true,
                    'data' => $user,
                    'message' => "✅ Usuario admin existe"
                ];
            } else {
                return [
                    'exists' => false,
                    'data' => null,
                    'message' => "❌ Usuario admin NO existe"
                ];
            }
        } catch (Exception $e) {
            return [
                'exists' => false,
                'data' => null,
                'message' => "❌ Error verificando usuario: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear usuario admin si no existe
     */
    public function createAdminUser() {
        try {
            // Verificar si ya existe
            $check = $this->checkAdminUser();
            if ($check['exists']) {
                return "ℹ️ Usuario admin ya existe";
            }
            
            // Crear hash de contraseña
            $password_hash = Auth::hashPassword('admin123');
            
            // Insertar usuario
            $sql = "INSERT INTO usuarios_admin (usuario, password_hash, nombre_completo, email, rol, activo) VALUES (?, ?, ?, ?, ?, ?)";
            $params = ['admin', $password_hash, 'Administrador Principal', 'admin@helmetspro.com', 'admin', 1];
            
            $this->db->insert($sql, $params);
            
            return "✅ Usuario admin creado exitosamente";
        } catch (Exception $e) {
            return "❌ Error creando usuario admin: " . $e->getMessage();
        }
    }
    
    /**
     * Mostrar todos los usuarios
     */
    public function showAllUsers() {
        try {
            $sql = "SELECT id, usuario, nombre_completo, email, activo, fecha_creacion FROM usuarios_admin ORDER BY id";
            $users = $this->db->fetchAll($sql);
            
            return $users;
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Ejecutar verificación completa
     */
    public function runCheck() {
        echo "<h2>🔍 Verificación de Usuario Admin</h2>";
        echo "<p>Helmets Pro v2.0 - Diagnóstico del Sistema</p><hr>";
        
        // Verificar tabla
        echo "<h3>📊 Estado de la Base de Datos</h3>";
        $table_status = $this->checkTableExists();
        echo "<p>$table_status</p>";
        
        // Verificar usuario admin
        echo "<h3>👤 Verificación de Usuario Admin</h3>";
        $admin_check = $this->checkAdminUser();
        echo "<p>" . $admin_check['message'] . "</p>";
        
        if ($admin_check['exists']) {
            echo "<div class='alert alert-success'>";
            echo "<h4>✅ Usuario Admin Encontrado:</h4>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $admin_check['data']['id'] . "</li>";
            echo "<li><strong>Usuario:</strong> " . $admin_check['data']['usuario'] . "</li>";
            echo "<li><strong>Nombre:</strong> " . $admin_check['data']['nombre_completo'] . "</li>";
            echo "<li><strong>Email:</strong> " . $admin_check['data']['email'] . "</li>";
            echo "<li><strong>Activo:</strong> " . ($admin_check['data']['activo'] ? 'Sí' : 'No') . "</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning'>";
            echo "<h4>⚠️ Usuario Admin No Existe</h4>";
            echo "<p>Se creará el usuario admin automáticamente.</p>";
            echo "</div>";
            
            // Crear usuario admin
            $create_result = $this->createAdminUser();
            echo "<p>$create_result</p>";
        }
        
        // Mostrar todos los usuarios
        echo "<h3>👥 Todos los Usuarios Registrados</h3>";
        $all_users = $this->showAllUsers();
        
        if (!empty($all_users)) {
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Email</th><th>Activo</th><th>Fecha Creación</th></tr></thead>";
            echo "<tbody>";
            foreach ($all_users as $user) {
                echo "<tr>";
                echo "<td>" . $user['id'] . "</td>";
                echo "<td>" . $user['usuario'] . "</td>";
                echo "<td>" . $user['nombre_completo'] . "</td>";
                echo "<td>" . $user['email'] . "</td>";
                echo "<td>" . ($user['activo'] ? 'Sí' : 'No') . "</td>";
                echo "<td>" . $user['fecha_creacion'] . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p class='text-muted'>No hay usuarios registrados.</p>";
        }
        
        echo "<hr>";
        echo "<h3>🎯 Credenciales de Acceso</h3>";
        echo "<div class='alert alert-info'>";
        echo "<p><strong>Usuario:</strong> admin</p>";
        echo "<p><strong>Contraseña:</strong> admin123</p>";
        echo "</div>";
        echo "<p><a href='../index.php' class='btn btn-primary'>Ir al Login</a></p>";
    }
}

// Ejecutar verificación
if (php_sapi_name() !== 'cli') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verificación de Usuario Admin - Helmets Pro</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <?php
            $checker = new AdminChecker();
            $checker->runCheck();
            ?>
        </div>
    </body>
    </html>
    <?php
} else {
    // Modo CLI
    $checker = new AdminChecker();
    $checker->runCheck();
}
?> 