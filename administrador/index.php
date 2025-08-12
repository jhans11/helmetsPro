<?php
// Incluir clases de seguridad
require_once 'config/DB.php';
require_once 'config/Auth.php';
require_once 'config/Validator.php';
require_once 'config/Middleware.php';

// Configurar sesión segura
Middleware::configureSecureSession();
session_start();

// Verificar si ya está autenticado
Middleware::requireGuest();

$mensaje = '';
$auth = new Auth();

if ($_POST) {
    // Validar token CSRF
    Middleware::validateCSRF();
    
    // Sanitizar entradas
    $usuario = Validator::sanitizeText('usuario');
    $password = $_POST['contraseña'] ?? '';
    
    // Validar datos requeridos
    $required_fields = ['usuario', 'contraseña'];
    $errors = Validator::validateRequired($_POST, $required_fields);
    
    if (empty($errors)) {
        // Verificar rate limiting
        $ip = $_SERVER['REMOTE_ADDR'];
        Middleware::checkRateLimit($ip);
        
        // Intentar login
        $user = $auth->login($usuario, $password);
        
        if ($user) {
            // Login exitoso
            $auth->crearSesion($user);
            header('Location: inicio.php');
            exit();
        } else {
            // Login fallido
            Middleware::logFailedAttempt($ip, $usuario);
            $mensaje = "Error: Usuario o contraseña incorrectos";
        }
    } else {
        $mensaje = implode('<br>', $errors);
    }
}

// Configurar headers de seguridad
Middleware::setSecurityHeaders();
?>
<!doctype html>
<html lang="en">
  <head>
    <title>Title</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  <body>
   
    <div class="container">
        <div class="row">

        <div class="col-md-4">
            
        </div>

            <div class="col-md-4">
            <br/><br/><br/><br/>              

            <div class="card">
                <div class="card-header">
                    login
                </div>
                <div class="card-body">


                    <?php if (isset($mensaje)) {?>
                <div class="alert alert-danger" role="alert">
                  <?php echo $mensaje; ?>
                </div>
                <?php }?>
                    <form method="POST">
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo Validator::getCSRFToken(); ?>">

                        <div class="form-group">
                            <label>Usuario</label>
                            <input type="text" class="form-control" name="usuario" placeholder="Escribe tu usuario" required>
                        </div>
                        
                        <div class="form-group">  
                            <label>Contraseña:</label>                  
                            <input type="password" class="form-control" name="contraseña" placeholder="Escribe tu contraseña" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Entrar al administrador</button>
                    </form>
                    
                    

                </div>
                
            </div>

            </div>
            
        </div>
    </div>


  </body>
</html>