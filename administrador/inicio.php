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
                        <p class="lead">Administracion de tu Tienda Virtual</p>
                        <hr class="my-2">
                        <p>Administrador</p>
                        <p class="lead">
                        <a class="btn btn-primary btn-lg" href="seccion/productos_mejorado.php" role="button">Administrar Tienda</a>
                        </p>
                    </div>

                </div>
<?php include('template/pie.php');?>

     