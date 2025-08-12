<?php
// Incluir clases necesarias
session_start();
require_once 'clases/Usuario.php';
require_once 'administrador/config/DB.php';

include("template/cabecera.php");

$usuario = new Usuario();
$mensaje = '';
$errores = [];

// ✅ PASO 1: Procesar registro de usuarios (clientes)
if ($_POST && isset($_POST['registrar'])) {
    // Validar datos requeridos
    $campos_requeridos = ['nombre', 'apellido', 'email', 'password', 'confirmar_password'];
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            $errores[] = "El campo " . ucfirst($campo) . " es requerido";
        }
    }
    
    // Validar email
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del email no es válido";
    }
    
    // Validar contraseñas
    if (!empty($_POST['password']) && !empty($_POST['confirmar_password'])) {
        if ($_POST['password'] !== $_POST['confirmar_password']) {
            $errores[] = "Las contraseñas no coinciden";
        }
        
        if (strlen($_POST['password']) < 6) {
            $errores[] = "La contraseña debe tener al menos 6 caracteres";
        }
    }
    
    // Si no hay errores, proceder con el registro
    if (empty($errores)) {
        $datos = [
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'email' => trim($_POST['email']),
            'password' => $_POST['password'],
            'telefono' => $_POST['telefono'] ?? null,
            'direccion' => $_POST['direccion'] ?? null,
            'ciudad' => $_POST['ciudad'] ?? null,
            'codigo_postal' => $_POST['codigo_postal'] ?? null
        ];
        
        $resultado = $usuario->registrar($datos);
        
        if ($resultado['success']) {
            $mensaje = "✅ " . $resultado['message'] . ". Por favor verifica tu email.";
            // Aquí se enviaría el email de verificación
        } else {
            $errores[] = $resultado['message'];
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>👤 Registro de Usuario</h4>
                    <p class="mb-0">Crea tu cuenta para realizar compras</p>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($mensaje)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $mensaje; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php } ?>
                    
                    <?php if (!empty($errores)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($errores as $error) { ?>
                                    <li><?php echo $error; ?></li>
                                <?php } ?>
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php } ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo $_POST['nombre'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apellido">Apellido *</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" 
                                           value="<?php echo $_POST['apellido'] ?? ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo $_POST['email'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Contraseña *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirmar_password">Confirmar Contraseña *</label>
                                    <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   value="<?php echo $_POST['telefono'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2"><?php echo $_POST['direccion'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ciudad">Ciudad</label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                           value="<?php echo $_POST['ciudad'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <input type="text" class="form-control" id="estado" name="estado" 
                                           value="<?php echo $_POST['estado'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo_postal">Código Postal</label>
                                    <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" 
                                           value="<?php echo $_POST['codigo_postal'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terminos" required>
                                <label class="form-check-label" for="terminos">
                                    Acepto los <a href="#" target="_blank">términos y condiciones</a>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" name="registrar" class="btn btn-primary btn-block">
                            📝 Crear Cuenta
                        </button>
                    </form>
                    
                    <hr>
                    <div class="text-center">
                        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("template/pie.php"); ?>