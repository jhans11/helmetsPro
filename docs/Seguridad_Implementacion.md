# 🔐 Sistema de Seguridad - Helmets Pro v2.0

## **Fase 1: Seguridad y Base Sólida (COMPLETADA)**

### ✅ **1. Hash de Contraseñas (password_hash en PHP)**

#### **Archivos Implementados:**
- `administrador/config/Auth.php` - Clase principal de autenticación
- `administrador/config/DB.php` - Conexión segura con PDO
- `bd/usuarios_admin.sql` - Script de creación de tabla

#### **Funcionalidades Implementadas:**

```php
// Generar hash de contraseña
public static function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verificar contraseña
if (password_verify($password, $user['password_hash'])) {
    // Login exitoso
}
```

#### **Estructura de Base de Datos:**
```sql
CREATE TABLE `usuarios_admin` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `usuario` varchar(50) NOT NULL UNIQUE,
    `password_hash` varchar(255) NOT NULL,  -- Hash bcrypt (60 chars)
    `nombre_completo` varchar(100) NOT NULL,
    `email` varchar(100) DEFAULT NULL,
    `rol` enum('admin','editor','viewer') DEFAULT 'admin',
    `activo` tinyint(1) DEFAULT 1,
    `ultimo_acceso` datetime DEFAULT NULL,
    `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);
```

### ✅ **2. Tokens CSRF Seguros**

#### **Archivos Implementados:**
- `administrador/config/Validator.php` - Clase de validación
- `administrador/config/Middleware.php` - Middleware de seguridad

#### **Funcionalidades Implementadas:**

```php
// Generar token CSRF
public static function getCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validar token CSRF
public static function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
```

#### **Uso en Formularios:**
```html
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo Validator::getCSRFToken(); ?>">
    <!-- Campos del formulario -->
</form>
```

### ✅ **3. PDO Prepared Statements**

#### **Archivos Implementados:**
- `administrador/config/DB.php` - Clase de base de datos segura

#### **Funcionalidades Implementadas:**

```php
// Consulta segura con prepared statements
public function fetchOne($sql, $params = []) {
    $stmt = $this->connection->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

// Ejemplo de uso
$user = $this->db->fetchOne(
    "SELECT * FROM usuarios_admin WHERE usuario = ? AND activo = 1", 
    [$usuario]
);
```

### ✅ **4. Middleware de Autenticación**

#### **Archivos Implementados:**
- `administrador/config/Middleware.php` - Protección de rutas

#### **Funcionalidades Implementadas:**

```php
// Verificar autenticación
public static function requireAuth() {
    session_start();
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== "ok") {
        header('Location: index.php');
        exit();
    }
}

// Verificar si NO está autenticado
public static function requireGuest() {
    session_start();
    if (isset($_SESSION['usuario']) && $_SESSION['usuario'] === "ok") {
        header('Location: inicio.php');
        exit();
    }
}
```

### ✅ **5. Validación con filter_input()**

#### **Archivos Implementados:**
- `administrador/config/Validator.php` - Sanitización completa

#### **Funcionalidades Implementadas:**

```php
// Sanitizar entrada de texto
public static function sanitizeText($input) {
    return filter_input(INPUT_POST, $input, FILTER_SANITIZE_STRING);
}

// Sanitizar email
public static function sanitizeEmail($input) {
    return filter_input(INPUT_POST, $input, FILTER_SANITIZE_EMAIL);
}

// Validar email
public static function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
```

### ✅ **6. Validación de Tipo MIME para Imágenes**

#### **Funcionalidades Implementadas:**

```php
// Validar tipo MIME de imagen
public static function validateImageMime($file) {
    $allowed_types = [
        'image/jpeg', 'image/jpg', 'image/png', 
        'image/gif', 'image/webp'
    ];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    return in_array($mime_type, $allowed_types);
}

// Validar tamaño de archivo
public static function validateFileSize($file, $max_size = 5242880) {
    return $file['size'] <= $max_size; // 5MB por defecto
}
```

## **🛠️ Scripts de Instalación**

### **1. Script de Instalación Automática**
**Archivo:** `administrador/config/install_auth_system.php`

**Uso:**
```bash
# Desde navegador
http://localhost/sitioweb/administrador/config/install_auth_system.php

# Desde línea de comandos
php administrador/config/install_auth_system.php
```

**Funcionalidades:**
- ✅ Crear tabla `usuarios_admin`
- ✅ Crear tabla `logs_acceso`
- ✅ Insertar usuario administrador por defecto
- ✅ Verificar configuración de seguridad
- ✅ Crear directorio de logs

### **2. Generador de Hash de Contraseñas**
**Archivo:** `administrador/config/generate_password_hash.php`

**Uso:**
```bash
# Generar hash de contraseña específica
php generate_password_hash.php "mi_contraseña"

# Generar contraseña aleatoria
php generate_password_hash.php --random
```

## **🔧 Rutas y Archivos del Sistema**

### **Estructura de Archivos:**
```
administrador/
├── config/
│   ├── Auth.php              # Clase de autenticación
│   ├── DB.php                # Conexión segura a BD
│   ├── Validator.php         # Validación de formularios
│   ├── Middleware.php        # Protección de rutas
│   ├── install_auth_system.php    # Script de instalación
│   └── generate_password_hash.php  # Generador de hash
├── logs/                     # Directorio de logs
├── index.php                 # Login actualizado
└── inicio.php                # Dashboard protegido
```

### **Rutas de Acceso:**
- **Login:** `http://localhost/sitioweb/administrador/`
- **Dashboard:** `http://localhost/sitioweb/administrador/inicio.php`
- **Instalación:** `http://localhost/sitioweb/administrador/config/install_auth_system.php`

## **🔐 Credenciales por Defecto**

Después de ejecutar el script de instalación:

- **Usuario:** `admin`
- **Contraseña:** `admin123`

## **📊 Características de Seguridad Implementadas**

### **1. Protección contra Ataques:**
- ✅ **SQL Injection:** PDO Prepared Statements
- ✅ **CSRF:** Tokens únicos en sesiones
- ✅ **Session Fixation:** Regeneración de ID de sesión
- ✅ **XSS:** Sanitización con `filter_input()`
- ✅ **File Upload:** Validación de tipo MIME
- ✅ **Brute Force:** Rate limiting básico

### **2. Headers de Seguridad:**
```php
// Configurados automáticamente
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'...');
```

### **3. Configuración de Sesiones:**
```php
// Cookies seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

// Timeout de sesión (30 minutos)
ini_set('session.gc_maxlifetime', 1800);
```

## **🚀 Próximos Pasos (Fase 2)**

### **Pendiente de Implementar:**
- [ ] **Sistema de Roles y Permisos**
- [ ] **Logs de Auditoría Avanzados**
- [ ] **Recuperación de Contraseñas**
- [ ] **Autenticación de Dos Factores**
- [ ] **API RESTful Segura**

---

**Versión:** 2.0  
**Autor:** Jhans Jiménez  
**Fecha:** Implementación Completa  
**Estado:** ✅ PRODUCCIÓN LISTA 