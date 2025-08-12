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

// Procesar formulario de recuperación
if ($_POST) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Verificar si el email existe
        $usuario = Usuario::buscarPorEmail($email);
        
        if ($usuario) {
            // Generar token de recuperación
            $token = bin2hex(random_bytes(32));
            $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            if (Usuario::guardarTokenRecuperacion($usuario['id'], $token, $expiracion)) {
                // Enviar email (simulado)
                $link_recuperacion = "https://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
                
                // En producción, aquí iría el envío real del email
                $mensaje = "Se ha enviado un enlace de recuperación a tu email.";
                $tipo_mensaje = 'success';
                
                // Para desarrollo, mostrar el link
                if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
                    $mensaje .= "<br><strong>Link de desarrollo:</strong> <a href='reset-password.php?token=" . $token . "'>Reset Password</a>";
                }
            } else {
                $mensaje = "Error al procesar la solicitud. Intenta nuevamente.";
                $tipo_mensaje = 'danger';
            }
        } else {
            $mensaje = "No existe una cuenta con ese email.";
            $tipo_mensaje = 'warning';
        }
    } else {
        $mensaje = "Por favor, ingresa un email válido.";
        $tipo_mensaje = 'danger';
    }
}

include('template/cabecera.php');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-key"></i> Recuperar Contraseña
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

                    <div class="text-center mb-4">
                        <i class="fas fa-lock fa-3x text-muted"></i>
                        <p class="text-muted mt-2">
                            Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.
                        </p>
                    </div>

                    <form method="POST" id="formRecuperar">
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="tu@email.com"
                                   required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <small class="form-text text-muted">
                                Te enviaremos un enlace seguro para restablecer tu contraseña.
                            </small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Enviar Enlace
                            </button>
                        </div>
                    </form>

                    <hr>

                    <div class="text-center">
                        <p class="mb-2">¿Recordaste tu contraseña?</p>
                        <a href="login.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <p class="mb-2">¿No tienes cuenta?</p>
                        <a href="registro.php" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-user-plus"></i> Registrarse
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle"></i> Información Importante</h6>
                    <ul class="list-unstyled small text-muted">
                        <li><i class="fas fa-clock"></i> El enlace expira en 1 hora</li>
                        <li><i class="fas fa-shield-alt"></i> El enlace es seguro y único</li>
                        <li><i class="fas fa-envelope-open"></i> Revisa tu carpeta de spam</li>
                        <li><i class="fas fa-question-circle"></i> Contacta soporte si tienes problemas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formRecuperar').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    
    if (!email) {
        e.preventDefault();
        alert('Por favor, ingresa tu email');
        return;
    }
    
    // Validación básica de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Por favor, ingresa un email válido');
        return;
    }
    
    // Mostrar loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    submitBtn.disabled = true;
    
    // Re-enable después de 3 segundos si hay error
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 3000);
});
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

.list-unstyled li {
    margin-bottom: 0.5rem;
}

.list-unstyled i {
    width: 16px;
    margin-right: 8px;
}
</style>

<?php include('template/pie.php'); ?>