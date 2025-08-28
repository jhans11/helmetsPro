# 🔒 Seguridad - Helmets Pro v2.0

## **Medidas de Seguridad Implementadas**

### **🔐 Autenticación y Autorización**

#### **Hash de Contraseñas**
```php
// Uso de password_hash() para encriptación segura
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Verificación con password_verify()
if (password_verify($password, $stored_hash)) {
    // Usuario autenticado
}
```

#### **Control de Sesiones**
- Regeneración de ID de sesión en login
- Timeout de sesión configurado
- Destrucción segura en logout

### **Protección contra Inyección SQL**

#### **Prepared Statements con PDO**
```php
// Conexión segura con PDO
$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Uso de prepared statements
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
```

### **🔍 Validación de Entrada**

#### **Filtrado de Datos**
```php
// Uso de filter_input() para validación
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
```

#### **Validación de Formularios**
- Validación del lado del servidor
- Sanitización de datos de entrada
- Verificación de tipos de datos

### **🛡️ Protección CSRF**

#### **Tokens Únicos**
```php
// Generación de token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Verificación en formularios
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Error de seguridad CSRF');
}
```

### **🔒 Control de Acceso**

#### **Protección de Rutas**
- Verificación de autenticación en páginas protegidas
- Redirección a login si no autenticado
- Control de roles de usuario

#### **Headers de Seguridad**
```php
// Headers de seguridad básicos
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
```

### **�� Seguridad en Pagos**

#### **Integración PayPal**
- Uso de PayPal Sandbox para pruebas
- Validación de transacciones
- Manejo seguro de datos de pago

#### **Validación de Pedidos**
- Verificación de stock antes del pago
- Validación de precios
- Confirmación de transacción

### **📊 Logs de Seguridad**

#### **Registro de Actividades**
- Logs de login/logout
- Registro de transacciones
- Monitoreo de intentos fallidos

### **🔧 Configuración del Servidor**

#### **XAMPP Seguro**
- Configuración de Apache
- Configuración de MySQL
- Permisos de archivos

---

**Versión:** 2.0  
**Última actualización:** Agosto 2025