<?php 
// Incluir clases de seguridad
require_once 'config/DB.php';
require_once 'config/Auth.php';
require_once 'config/Validator.php';
require_once 'config/Middleware.php';

// Configurar sesión segura
Middleware::configureSecureSession();
session_start();

// Verificar autenticación
Middleware::requireAuth();

// Verificar timeout de sesión
Middleware::checkSessionTimeout();

// Configurar headers de seguridad
Middleware::setSecurityHeaders();

include('template/cabecera.php');
?>

<div class="col-md-12">               
    <div class="jumbotron">
        <h1 class="display-3">Bienvenido <?php echo $nombreUsuario; ?> </h1>
        <p class="lead">Administración de tu Tienda Virtual</p>
        <hr class="my-2">
        <p>Panel de Control</p>
        <div class="row">
            <div class="col-md-3">
                <a class="btn btn-primary btn-lg btn-block" href="dashboard.php" role="button">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
            <div class="col-md-3">
                <a class="btn btn-success btn-lg btn-block" href="seccion/productos_mejorado.php" role="button">
                    <i class="fas fa-box"></i> Productos
                </a>
            </div>
            <div class="col-md-3">
                <a class="btn btn-info btn-lg btn-block" href="pedidos.php" role="button">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
            </div>
            <div class="col-md-3">
                <a class="btn btn-warning btn-lg btn-block" href="clientes.php" role="button">
                    <i class="fas fa-users"></i> Clientes
                </a>
            </div>
        </div>
    </div>
</div>

<?php include('template/pie.php');?>

     