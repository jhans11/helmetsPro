<?php
// Incluir clases necesarias
session_start();
require_once 'clases/Usuario.php';
require_once 'administrador/config/DB.php';

// Si ya está logueado, redirigir
if (isset($_SESSION['cliente_id'])) {
    header('Location: mi-cuenta.php');
    exit();
}

include("template/cabecera.php");

$usuario = new Usuario();
$mensaje = '';
$errores = [];

// ✅ PASO 2: Login de usuarios con control de sesiones mejorado
if ($_POST && isset($_POST['login'])) {
    // Sanitizar inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Validar datos requeridos
    if (empty($email) || empty($password)) {
        $errores[] = "Email y contraseña son requeridos";
    }
    
    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Formato de email inválido";
    }
    
    // Validar longitud de contraseña
    if (strlen($password) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    // Si no hay errores, proceder con el login
    if (empty($errores)) {
        $resultado = $usuario->login($email, $password);
        
        if ($resultado['success']) {
            // Redirigir al panel de usuario
            header('Location: mi-cuenta.php');
            exit();
        } else {
            $errores[] = $resultado['message'];
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </h4>
                    <p class="mb-0">Accede a tu cuenta de Helmets Pro</p>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($errores)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Error:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errores as $error) { ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php } ?>
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php } ?>
                    
                    <form method="POST" id="loginForm">
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   placeholder="tu@email.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i> Contraseña
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Tu contraseña" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="recordar">
                                <label class="form-check-label" for="recordar">
                                    <i class="fas fa-remember"></i> Recordarme
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </button>
                    </form>
                    
                    <hr>
                    <div class="text-center">
                        <p class="mb-2">
                            <i class="fas fa-user-plus"></i> 
                            ¿No tienes cuenta? <a href="registro.php" class="text-primary">Regístrate aquí</a>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-key"></i> 
                            <a href="recuperar-password.php" class="text-primary">¿Olvidaste tu contraseña?</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Información adicional -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle"></i> Información de Usuarios de Prueba:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Email:</strong> juan.perez@email.com</li>
                        <li><strong>Contraseña:</strong> cliente123</li>
                        <li><strong>Estado:</strong> Verificado</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('Por favor completa todos los campos');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres');
        return false;
    }
});
</script>

<?php include("template/pie.php"); ?>