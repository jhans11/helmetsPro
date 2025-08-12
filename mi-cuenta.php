<?php
session_start();
require_once 'clases/Usuario.php';
require_once 'clases/Middleware.php';

// Configurar sesión segura
Middleware::configureSecureSession();
Middleware::setSecurityHeaders();

// Requerir autenticación
Middleware::requireAuth();

// Verificar timeout de sesión
Middleware::checkSessionTimeout();

include("template/cabecera.php");

$usuario = new Usuario();
$usuario_actual = $usuario->obtenerUsuarioActual();
$pedidos = $usuario->obtenerMisPedidos($usuario_actual['id']);

$mensaje = '';
$errores = [];

// Procesar actualización de datos
if ($_POST && isset($_POST['actualizar_datos'])) {
    $datos = [
        'nombre' => filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING),
        'apellido' => filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING),
        'telefono' => filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING),
        'direccion' => filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING),
        'ciudad' => filter_input(INPUT_POST, 'ciudad', FILTER_SANITIZE_STRING),
        'codigo_postal' => filter_input(INPUT_POST, 'codigo_postal', FILTER_SANITIZE_STRING)
    ];
    
    $resultado = $usuario->actualizarDatosPersonales($usuario_actual['id'], $datos);
    
    if ($resultado['success']) {
        $mensaje = 'Datos actualizados correctamente';
        $usuario_actual = $usuario->obtenerUsuarioActual(); // Recargar datos
    } else {
        $errores[] = $resultado['message'];
    }
}

// Procesar cambio de contraseña
if ($_POST && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    
    if ($password_nueva !== $password_confirmar) {
        $errores[] = 'Las contraseñas nuevas no coinciden';
    } elseif (strlen($password_nueva) < 6) {
        $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres';
    } else {
        $resultado = $usuario->cambiarPassword($usuario_actual['id'], $password_actual, $password_nueva);
        
        if ($resultado['success']) {
            $mensaje = 'Contraseña cambiada correctamente';
        } else {
            $errores[] = $resultado['message'];
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <!-- Información del usuario -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle"></i> Mi Perfil
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                        <h5 class="mt-2"><?php echo htmlspecialchars($usuario_actual['nombre'] . ' ' . $usuario_actual['apellido']); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($usuario_actual['email']); ?></p>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <i class="fas fa-calendar"></i> 
                            <strong>Registrado:</strong> <?php echo date('d/m/Y', strtotime($usuario_actual['fecha_registro'])); ?>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-clock"></i> 
                            <strong>Último acceso:</strong> 
                            <?php echo $usuario_actual['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario_actual['ultimo_acceso'])) : 'Nunca'; ?>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> 
                            <strong>Estado:</strong> 
                            <?php echo $usuario_actual['verificado'] ? 'Verificado' : 'Pendiente de verificación'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenido principal -->
        <div class="col-md-8">
            <!-- Mensajes -->
            <?php if ($mensaje) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($mensaje); ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php } ?>
            
            <?php if (!empty($errores)) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <ul class="mb-0">
                        <?php foreach ($errores as $error) { ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php } ?>
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php } ?>
            
            <!-- Tabs de navegación -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="datos-tab" data-toggle="tab" href="#datos" role="tab">
                        <i class="fas fa-user-edit"></i> Datos Personales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pedidos-tab" data-toggle="tab" href="#pedidos" role="tab">
                        <i class="fas fa-shopping-bag"></i> Mis Pedidos
                        <span class="badge badge-primary"><?php echo count($pedidos); ?></span>
                    </a>
                </li>
            </ul>
            
            <div class="tab-content" id="myTabContent">
                <!-- Datos Personales -->
                <div class="tab-pane fade show active" id="datos" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nombre">Nombre</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                                   value="<?php echo htmlspecialchars($usuario_actual['nombre']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="apellido">Apellido</label>
                                            <input type="text" class="form-control" id="apellido" name="apellido" 
                                                   value="<?php echo htmlspecialchars($usuario_actual['apellido']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           value="<?php echo htmlspecialchars($usuario_actual['telefono'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="direccion">Dirección</label>
                                    <textarea class="form-control" id="direccion" name="direccion" rows="2"><?php echo htmlspecialchars($usuario_actual['direccion'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ciudad">Ciudad</label>
                                            <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                                   value="<?php echo htmlspecialchars($usuario_actual['ciudad'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="codigo_postal">Código Postal</label>
                                            <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" 
                                                   value="<?php echo htmlspecialchars($usuario_actual['codigo_postal'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="actualizar_datos" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Actualizar Datos
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Cambiar Contraseña -->
                <div class="tab-pane fade" id="password" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="password_actual">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_nueva">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_nueva" name="password_nueva" 
                                           minlength="6" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" 
                                           minlength="6" required>
                                </div>
                                
                                <button type="submit" name="cambiar_password" class="btn btn-warning">
                                    <i class="fas fa-key"></i> Cambiar Contraseña
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Mis Pedidos -->
                <div class="tab-pane fade" id="pedidos" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($pedidos)) { ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-shopping-bag fa-3x text-muted"></i>
                                    <h5 class="mt-3">No tienes pedidos aún</h5>
                                    <p class="text-muted">¡Haz tu primera compra!</p>
                                    <a href="productos.php" class="btn btn-primary">
                                        <i class="fas fa-shopping-cart"></i> Ver Productos
                                    </a>
                                </div>
                            <?php } else { ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Pedido #</th>
                                                <th>Fecha</th>
                                                <th>Total</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pedidos as $pedido) { ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($pedido['numero_pedido']); ?></strong>
                                                    </td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $pedido['estado'] === 'completado' ? 'success' : 'warning'; ?>">
                                                            <?php echo ucfirst($pedido['estado']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="detalle-pedido.php?id=<?php echo $pedido['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> Ver
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de contraseñas
document.querySelector('form[name="cambiar_password"]')?.addEventListener('submit', function(e) {
    const passwordNueva = document.getElementById('password_nueva').value;
    const passwordConfirmar = document.getElementById('password_confirmar').value;
    
    if (passwordNueva !== passwordConfirmar) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
    }
    
    if (passwordNueva.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres');
        return false;
    }
});
</script>

<?php include("template/pie.php"); ?>