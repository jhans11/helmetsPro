<?php
session_start();
require_once 'clases/Usuario.php';

// Si el usuario ya está logueado, redirigir
if (Usuario::estaAutenticado()) {
    header('Location: mi-cuenta.php');
    exit();
}

$mensaje = '';
$tipo_mensaje = '';
$token_valido = false;
$usuario = null;

// Verificar token
$token = $_GET['token'] ?? '';
if ($token) {
    $usuario = Usuario::verificarTokenRecuperacion($token);
    if ($usuario) {
        $token_valido = true;
    } else {
        $mensaje = "El enlace de recuperación es inválido o ha expirado.";
        $tipo_mensaje = 'danger';
    }
} else {
    header('Location: login.php');
    exit();
}

// Procesar cambio de contraseña
if ($_POST && $token_valido) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 8) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres.";
        $tipo_mensaje = 'danger';
    } elseif ($password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = 'danger';
    } else {
        Usuario::cambiarPasswordRecuperacion($usuario['id'], $password);
        
        // Invalidar token
        Usuario::invalidarTokenRecuperacion($token);
        
        $mensaje = "Contraseña actualizada exitosamente. Ya puedes iniciar sesión.";
        $tipo_mensaje = 'success';
        
        // Redirigir después de 3 segundos
        header("refresh:3;url=login.php");
    }
}

include('template/cabecera.php');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-key"></i> Restablecer Contraseña
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <?php if ($token_valido): ?>
                    <div class="text-center mb-4">
                        <i class="fas fa-user-check fa-3x text-success"></i>
                        <p class="text-muted mt-2">
                            Hola <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>, 
                            ingresa tu nueva contraseña.
                        </p>
                    </div>

                    <form method="POST" id="formReset">
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i> Nueva Contraseña
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Mínimo 8 caracteres"
                                   required
                                   minlength="8">
                            <small class="form-text text-muted">
                                La contraseña debe tener al menos 8 caracteres.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-lock"></i> Confirmar Contraseña
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Repite tu contraseña"
                                   required>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-save"></i> Cambiar Contraseña
                            </button>
                        </div>
                    </form>

                    <?php else: ?>
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                        <p class="mt-3">
                            El enlace de recuperación no es válido o ha expirado.
                        </p>
                        <a href="recuperar-password.php" class="btn btn-primary">
                            <i class="fas fa-redo"></i> Solicitar Nuevo Enlace
                        </a>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <div class="text-center">
                        <a href="login.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver al Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formReset')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password.length < 8) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 8 caracteres');
        return;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return;
    }
    
    // Mostrar loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
    submitBtn.disabled = true;
});

// Mostrar/ocultar contraseñas
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>

<style>
.card {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-header {
    border-bottom: none;
    padding: 1.5rem;
}

.fa-3x {
    font-size: 3em;
}

.btn-block {
    padding: 0.75rem;
}
</style>

<?php include('template/pie.php'); ?>