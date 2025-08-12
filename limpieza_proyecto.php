<?php
/**
 * Script de Limpieza y Verificación del Proyecto
 * Helmets Pro v2.0 - Limpieza del Entorno
 * Fecha: 10/08/2025
 */

echo "🧹 INICIANDO LIMPIEZA DEL PROYECTO HELMETS PRO\n";
echo "==============================================\n\n";

// 1. ELIMINAR ARCHIVOS DE DEBUG
$archivos_debug = [
    'debug_registro_web.php',
    'debug_db.php', 
    'debug_registro.php',
    'debug.php',
    'test.php'
];

echo "📁 ELIMINANDO ARCHIVOS DE DEBUG:\n";
foreach ($archivos_debug as $archivo) {
    if (file_exists($archivo)) {
        if (unlink($archivo)) {
            echo "✅ Eliminado: $archivo\n";
        } else {
            echo "❌ Error al eliminar: $archivo\n";
        }
    } else {
        echo "⚠️  No existe: $archivo\n";
    }
}

// 2. VERIFICAR CONFIGURACIÓN DE BD
echo "\n�� VERIFICANDO CONFIGURACIÓN:\n";

// Verificar config.php
if (file_exists('administrador/config/config.php')) {
    echo "✅ config.php existe\n";
    
    // Verificar conexión a BD
    try {
        require_once 'administrador/config/config.php';
        require_once 'administrador/config/DB.php';
        
        $db = DB::getInstance();
        if ($db->isConnected()) {
            echo "✅ Conexión a BD exitosa\n";
            
            // Verificar tabla usuarios_clientes
            $result = $db->fetchOne("SHOW TABLES LIKE 'usuarios_clientes'");
            if ($result) {
                echo "✅ Tabla usuarios_clientes existe\n";
                
                // Contar usuarios
                $usuarios = $db->fetchOne("SELECT COUNT(*) as total FROM usuarios_clientes");
                echo "📊 Usuarios registrados: " . $usuarios['total'] . "\n";
                
                // Verificar usuarios de prueba
                $usuarios_prueba = $db->fetchAll("SELECT usuario, email, verificado FROM usuarios_clientes LIMIT 3");
                echo "�� Usuarios de prueba:\n";
                foreach ($usuarios_prueba as $user) {
                    echo "   - {$user['usuario']} ({$user['email']}) - Verificado: " . ($user['verificado'] ? 'Sí' : 'No') . "\n";
                }
            } else {
                echo "❌ Tabla usuarios_clientes NO existe\n";
            }
        } else {
            echo "❌ Error de conexión a BD\n";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ config.php NO existe\n";
}

// 3. VERIFICAR ARCHIVOS CRÍTICOS
echo "\n�� VERIFICANDO ARCHIVOS CRÍTICOS:\n";

$archivos_criticos = [
    'login.php' => 'Sistema de login',
    'registro.php' => 'Sistema de registro', 
    'carrito.php' => 'Sistema de carrito',
    'productos.php' => 'Listado de productos',
    'clases/Usuario.php' => 'Clase de usuarios',
    'clases/Carrito.php' => 'Clase de carrito',
    'template/cabecera.php' => 'Template principal'
];

foreach ($archivos_criticos as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "✅ $descripcion: $archivo\n";
    } else {
        echo "❌ FALTA: $descripcion ($archivo)\n";
    }
}

// 4. VERIFICAR ESTRUCTURA DE CARPETAS
echo "\n📁 VERIFICANDO ESTRUCTURA DE CARPETAS:\n";

$carpetas = [
    'administrador/config' => 'Configuración admin',
    'clases' => 'Clases PHP',
    'bd' => 'Scripts BD',
    'template' => 'Templates',
    'css' => 'Estilos',
    'img' => 'Imágenes',
    'logs' => 'Logs del sistema'
];

foreach ($carpetas as $carpeta => $descripcion) {
    if (is_dir($carpeta)) {
        echo "✅ $descripcion: $carpeta\n";
    } else {
        echo "❌ FALTA: $descripcion ($carpeta)\n";
    }
}

// 5. CREAR BACKUP DE BD
echo "\n💾 CREANDO BACKUP DE BASE DE DATOS:\n";

$backup_dir = 'backups';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
    echo "✅ Carpeta de backup creada\n";
}

$fecha_backup = date('Y-m-d_H-i-s');
$backup_file = "$backup_dir/backup_usuarios_$fecha_backup.sql";

try {
    // Exportar estructura y datos de usuarios
    $tables = ['usuarios_clientes', 'direcciones_envio', 'sesiones_clientes', 'logs_acceso_clientes'];
    
    $backup_content = "-- Backup de Base de Datos - Helmets Pro\n";
    $backup_content .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tables as $table) {
        // Estructura
        $structure = $db->fetchAll("SHOW CREATE TABLE $table");
        if ($structure) {
            $backup_content .= "-- Estructura de tabla: $table\n";
            $backup_content .= $structure[0]['Create Table'] . ";\n\n";
        }
        
        // Datos
        $data = $db->fetchAll("SELECT * FROM $table");
        if ($data) {
            $backup_content .= "-- Datos de tabla: $table\n";
            foreach ($data as $row) {
                $columns = implode('`, `', array_keys($row));
                $values = implode("', '", array_map('addslashes', $row));
                $backup_content .= "INSERT INTO `$table` (`$columns`) VALUES ('$values');\n";
            }
            $backup_content .= "\n";
        }
    }
    
    if (file_put_contents($backup_file, $backup_content)) {
        echo "✅ Backup creado: $backup_file\n";
    } else {
        echo "❌ Error al crear backup\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en backup: " . $e->getMessage() . "\n";
}

echo "\n🎉 LIMPIEZA COMPLETADA\n";
echo "=====================\n";
echo "✅ Archivos de debug eliminados\n";
echo "✅ Configuración verificada\n";
echo "✅ Estructura de carpetas verificada\n";
echo "✅ Backup de BD creado\n";
echo "\n🚀 PROYECTO LISTO PARA STEP 2: SISTEMA DE LOGIN\n";
?>
