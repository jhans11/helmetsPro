<?php
/**
 * Script para verificar y crear tablas faltantes
 */

require_once 'administrador/config/DB.php';

try {
    $db = DB::getInstance();
    
    echo "<h2>Verificando tablas de la base de datos...</h2>";
    
    // Verificar tabla pedidos
    $resultado = $db->fetchOne("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'pedidos'");
    if ($resultado['total'] == 0) {
        echo "<p>❌ Tabla 'pedidos' no existe. Creándola...</p>";
        
        $sql = "CREATE TABLE pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            numero_pedido VARCHAR(50) UNIQUE NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            impuestos DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            estado ENUM('pendiente', 'pagado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
            direccion_envio TEXT,
            ciudad_envio VARCHAR(100),
            codigo_postal_envio VARCHAR(10),
            telefono_envio VARCHAR(20),
            notas TEXT,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios_clientes(id)
        )";
        
        $db->query($sql);
        echo "<p>✅ Tabla 'pedidos' creada exitosamente.</p>";
    } else {
        echo "<p>✅ Tabla 'pedidos' ya existe.</p>";
    }
    
    // Verificar tabla detalles_pedido
    $resultado = $db->fetchOne("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'detalles_pedido'");
    if ($resultado['total'] == 0) {
        echo "<p>❌ Tabla 'detalles_pedido' no existe. Creándola...</p>";
        
        $sql = "CREATE TABLE detalles_pedido (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pedido_id INT NOT NULL,
            producto_id INT NOT NULL,
            nombre_producto VARCHAR(255) NOT NULL,
            precio_unitario DECIMAL(10,2) NOT NULL,
            cantidad INT NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            imagen VARCHAR(255),
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
            FOREIGN KEY (producto_id) REFERENCES cascos(id)
        )";
        
        $db->query($sql);
        echo "<p>✅ Tabla 'detalles_pedido' creada exitosamente.</p>";
    } else {
        echo "<p>✅ Tabla 'detalles_pedido' ya existe.</p>";
    }
    
    // Verificar tabla usuarios_clientes
    $resultado = $db->fetchOne("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios_clientes'");
    if ($resultado['total'] == 0) {
        echo "<p>❌ Tabla 'usuarios_clientes' no existe. Creándola...</p>";
        
        $sql = "CREATE TABLE usuarios_clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(50) UNIQUE NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            telefono VARCHAR(20),
            direccion TEXT,
            ciudad VARCHAR(100),
            codigo_postal VARCHAR(10),
            verificado BOOLEAN DEFAULT FALSE,
            activo BOOLEAN DEFAULT TRUE,
            token_verificacion VARCHAR(64),
            token_recuperacion VARCHAR(64),
            expiracion_token TIMESTAMP NULL,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ultimo_acceso TIMESTAMP NULL
        )";
        
        $db->query($sql);
        echo "<p>✅ Tabla 'usuarios_clientes' creada exitosamente.</p>";
    } else {
        echo "<p>✅ Tabla 'usuarios_clientes' ya existe.</p>";
    }
    
    // Verificar tabla cascos
    $resultado = $db->fetchOne("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cascos'");
    if ($resultado['total'] == 0) {
        echo "<p>❌ Tabla 'cascos' no existe. Creándola...</p>";
        
        $sql = "CREATE TABLE cascos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            descripcion TEXT,
            precio DECIMAL(10,2) NOT NULL,
            stock INT DEFAULT 0,
            imagen VARCHAR(255),
            activo BOOLEAN DEFAULT TRUE,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $db->query($sql);
        echo "<p>✅ Tabla 'cascos' creada exitosamente.</p>";
    } else {
        echo "<p>✅ Tabla 'cascos' ya existe.</p>";
    }
    
    echo "<h3>✅ Verificación completada. Todas las tablas están listas.</h3>";
    echo "<p><a href='test_pago.php'>Volver al test del sistema de pago</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
