# 🏍️ Helmets Pro – Tienda de Cascos (v2.0)

## 🎯 Descripción del Proyecto
Helmets Pro es una tienda virtual especializada en la venta de cascos para motociclistas, desarrollada con tecnologías web tradicionales (PHP, MySQL, Bootstrap). Este proyecto busca ofrecer una plataforma sólida, escalable y segura, enfocada en la experiencia de usuario y en las buenas prácticas de desarrollo web.

### ✨ Características Principales
- 🛍️ **Catálogo completo** de cascos con filtros avanzados
- 🛒 **Carrito de compras** con gestión de sesiones
- 💳 **Integración PayPal** para pagos seguros
- 👤 **Sistema de usuarios** con registro y login
- 📦 **Gestión de pedidos** con estados y seguimiento
- ⚙️ **Panel administrativo** completo
-  **Seguridad robusta** con múltiples capas de protección

## 🛠️ Tecnologías Utilizadas
- **Backend**: PHP 8.2.4 (PDO para conexión segura)
- **Base de Datos**: MySQL (MariaDB 10.4.28)
- **Frontend**: Bootstrap 4.3.1, HTML5, CSS3, JavaScript
- **Servidor Local**: XAMPP (Apache + MySQL)
- **Control de Versiones**: Git & GitHub
- **Pagos**: PayPal Sandbox API

## 📁 Estructura de Carpetas
```
sitioweb/
├── index.php
├── productos.php
├── nosotros.php
├── carrito.php
├── login.php
├── registro.php
├── pedido_confirmado.php
├── template/
│   ├── cabecera.php
│   └── pie.php
├── administrador/
│   ├── index.php
│   ├── inicio.php
│   ├── estadisticas.php
│   ├── config/
│   │   └── bd.php
│   └── seccion/
│       └── productos.php
├── clases/
│   ├── DB.php
│   ├── Carrito.php
│   ├── Pedido.php
│   └── Usuario.php
├── bd/
│   └── sitio.sql
├── img/
├── css/
├── js/
├── uploads/
└── .env
```

# �� Instrucciones para Clonar y Ejecutar

### **Prerrequisitos**
- XAMPP instalado (Apache + MySQL)
- PHP 8.2.4 o superior
- Git

### **1. Clonar el repositorio**
```bash
git clone https://github.com/jhans11/helmetspro.git
cd helmetspro
```

### **2. Configurar en XAMPP**
- Copia la carpeta a: `C:/xampp/htdocs/sitioweb`
- Inicia Apache y MySQL en XAMPP

### **3. Configurar Base de Datos**
```sql
-- Crear base de datos
CREATE DATABASE sitioweb;

-- Importar estructura
mysql -u root -p sitioweb < bd/sitio.sql
```

### **4. Configurar Conexión**
Edita `clases/DB.php` o crea archivo `.env`:
```php
$host = 'localhost';
$db = 'sitioweb';
$user = 'root';
$password = '';
```

### **5. Acceder al Proyecto**
- 🌐 **Tienda**: `http://localhost/sitioweb/`
- ⚙️ **Admin**: `http://localhost/sitioweb/administrador/`
- 📧 **Credenciales Admin**: admin@helmetspro.com / admin123

## 📚 Documentación Técnica

### **📖 Archivos de Documentación**
- [`docs/Arquitectura.md`](docs/Arquitectura.md) - Patrón MVC y estructura
- [`docs/Seguridad.md`](docs/Seguridad.md) - Medidas de seguridad
- [`docs/Funcionalidades.md`](docs/Funcionalidades.md) - Funcionalidades implementadas
- [`docs/Carrito_Implementacion.md`](docs/Carrito_Implementacion.md) - Sistema de carrito
- [`docs/Seguridad_Implementacion.md`](docs/Seguridad_Implementacion.md) - Implementación de seguridad

## 🔒 Seguridad Implementada

### **🛡️ Medidas de Seguridad**
- ✅ Hash de contraseñas con `password_hash()`
- ✅ Prepared Statements para prevenir SQL Injection
- ✅ Validación de formularios con `filter_input()`
- ✅ Protección CSRF con tokens únicos
- ✅ Control de sesiones seguras
- ✅ Headers de seguridad HTTP

## 🛒 Funcionalidades Principales

### **🛍️ Catálogo de Productos**
- Listado con filtros y paginación
- Detalle completo de productos
- Gestión de stock en tiempo real

### **🛒 Sistema de Carrito**
- Agregar/eliminar productos
- Actualizar cantidades
- Cálculo automático de totales e IVA

### **💳 Sistema de Pagos**
- Integración PayPal Sandbox
- Proceso de checkout seguro
- Confirmación de transacciones

### **👤 Gestión de Usuarios**
- Registro y autenticación
- Perfil personal
- Historial de pedidos

## 🚀 Roadmap v3.0

### **🔄 Próximas Mejoras**
- [ ] API RESTful para integración móvil
- [ ] Sistema de recomendaciones con IA
- [ ] Dashboard avanzado con analíticas
- [ ] Optimización SEO y Lighthouse
- [ ] Sistema de notificaciones (email + WhatsApp)
- [ ] Migración progresiva a Laravel

## Contribuir

### ** Cómo Contribuir**
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 👨‍Autor

**Jhans Jiménez**
- GitHub: [@jhans11](https://github.com/jhans11)
- Email: jhans@helmetspro.com

---

**Versión:** 2.0 IA Copilot Ready  
**Última actualización:** Agosto 2025  
**Estado:** 🟢 Activo y en desarrollo