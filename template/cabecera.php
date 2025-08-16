<?php
// Inicializar carrito para mostrar contador
if (file_exists('clases/Carrito.php')) {
    require_once 'clases/Carrito.php';
    Carrito::init();
    $cantidad_carrito = Carrito::obtenerCantidadTotal();
} else {
    $cantidad_carrito = 0;
}

// Verificar si hay usuario logueado
$usuario_logueado = null;
if (isset($_SESSION['cliente_id'])) {
    require_once 'clases/Usuario.php';
    $usuario = new Usuario();
    $usuario_logueado = $usuario->obtenerUsuarioActual();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helmets PRO - Tienda de Cascos</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-helmet-safety"></i> Helmets PRO
            </a>
            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
              <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Inicio
                        </a>
              </li>
              <li class="nav-item">
                        <a class="nav-link" href="productos.php">
                            <i class="fas fa-helmet-battle"></i> Cascos
                        </a>
              </li>
              
                </ul>
                
                <!-- ✅ PASO 11: Contador del carrito en la navegación -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="carrito.php">
                            <i class="fas fa-shopping-cart"></i> Carrito
                            <?php if ($cantidad_carrito > 0) { ?>
                                <span class="badge badge-light"><?php echo $cantidad_carrito; ?></span>
                            <?php } ?>
                        </a>
                    </li>
                    
                    <?php if ($usuario_logueado) { ?>
                        <!-- Usuario logueado -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> 
                                <?php echo htmlspecialchars($usuario_logueado['nombre']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="mi-cuenta.php">
                                    <i class="fas fa-user"></i> Mi Cuenta
                                </a>
                                <a class="dropdown-item" href="mi-cuenta.php#pedidos">
                                    <i class="fas fa-shopping-bag"></i> Mis Pedidos
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php" 
                                   onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </a>
                            </div>
                        </li>
                    <?php } else { ?>
                        <!-- Usuario no logueado -->
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
              </li>
              <li class="nav-item">
                            <a class="nav-link" href="registro.php">
                                <i class="fas fa-user-plus"></i> Registrarse
                            </a>
              </li>
                    <?php } ?>
                    
                    <!-- Enlace al administrador -->
              <li class="nav-item">
                        <a class="nav-link" href="administrador/">
                            <i class="fas fa-cog"></i> Admin
                        </a>
              </li>

              <li class="nav-item">
                        <a class="nav-link" href="nosotros.php">
                            <i class="fas fa-info-circle"></i> Nosotros
                        </a>
                    </li>
          </ul>
            </div>
        </div>
      </nav>

    <!-- Mensajes de sistema -->
    <?php if (isset($_SESSION['mensaje'])) { ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $_SESSION['tipo_mensaje'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($_SESSION['mensaje']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php 
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        ?>
    <?php } ?>

        <div class="container">
            <br/>
            <div class="row">