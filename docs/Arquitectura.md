# 🏗️ Arquitectura del Sistema - Helmets Pro v2.0

## **Patrón MVC Implementado**

### **📁 Modelo (Model)**
Ubicación: `clases/`
- **`DB.php`** - Clase de conexión a base de datos con PDO
- **`Carrito.php`** - Gestión del carrito de compras con sesiones
- **`Pedido.php`** - Manejo de pedidos y estados
- **`Usuario.php`** - Gestión de usuarios y autenticación

### **📁 Vista (View)**
Ubicación: `template/`
- **`cabecera.php`** - Header común con navegación
- **`pie.php`** - Footer común
- **Archivos PHP principales** - Páginas con lógica de presentación

### **📁 Controlador (Controller)**
Ubicación: Archivos PHP en raíz
- **`productos.php`** - Controla la lógica de productos
- **`carrito.php`** - Maneja acciones del carrito
- **`checkout.php`** - Procesa el checkout
- **`login.php`** - Autenticación de usuarios


## **Separación de Responsabilidades**

### **✅ Lógica de Negocio**
- Clases en `clases/` manejan la lógica de negocio
- Validaciones y cálculos centralizados
- Reutilización de código

### **✅ Presentación**
- Templates reutilizables en `template/`
- Separación de HTML y PHP
- Diseño responsive con Bootstrap

### **✅ Control de Flujo**
- Archivos PHP principales como controladores
- Manejo de formularios y redirecciones
- Validación de entrada de datos

## **Estructura de Base de Datos**

### **Tablas Principales**
- **`usuarios`** - Información de clientes
- **`cascos`** - Catálogo de productos
- **`pedidos`** - Historial de pedidos
- **`detalles_pedido`** - Productos por pedido

## **Gestión de Sesiones**

### **Sesiones PHP**
- Carrito almacenado en `$_SESSION['carrito']`
- Información de usuario en `$_SESSION['usuario']`
- Tokens CSRF para seguridad

### **Persistencia**
- Base de datos MySQL para datos permanentes
- Sesiones para datos temporales
- Cookies para preferencias de usuario

---

**Versión:** 2.0  
**Última actualización:** Agosto 2025
