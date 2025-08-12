<?php
/**
 * Script para generar hash de contraseñas
 * Helmets Pro v2.0 - Herramienta de Administración
 * 
 * USO: Ejecutar desde línea de comandos o navegador
 * php generate_password_hash.php "mi_contraseña"
 */

// Función para generar hash de contraseña
function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Función para verificar contraseña
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Función para generar contraseña aleatoria
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    return $password;
}

// Procesar argumentos de línea de comandos
if (php_sapi_name() === 'cli') {
    if ($argc < 2) {
        echo "Uso: php generate_password_hash.php \"contraseña\"\n";
        echo "O: php generate_password_hash.php --random\n";
        exit(1);
    }
    
    $password = $argv[1];
    
    if ($password === '--random') {
        $password = generateRandomPassword();
        echo "Contraseña generada: $password\n";
    }
    
    $hash = generatePasswordHash($password);
    echo "Hash generado: $hash\n";
    echo "Verificación: " . (verifyPassword($password, $hash) ? 'OK' : 'ERROR') . "\n";
    
} else {
    // Interfaz web
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Generador de Hash de Contraseñas</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Generador de Hash de Contraseñas</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            if ($_POST) {
                                $password = $_POST['password'] ?? '';
                                $action = $_POST['action'] ?? '';
                                
                                if ($action === 'generate' && !empty($password)) {
                                    $hash = generatePasswordHash($password);
                                    $verify = verifyPassword($password, $hash);
                                    
                                    echo '<div class="alert alert-success">';
                                    echo '<strong>Hash generado:</strong><br>';
                                    echo '<code>' . htmlspecialchars($hash) . '</code><br><br>';
                                    echo '<strong>Verificación:</strong> ' . ($verify ? '✅ OK' : '❌ ERROR');
                                    echo '</div>';
                                } elseif ($action === 'random') {
                                    $password = generateRandomPassword();
                                    $hash = generatePasswordHash($password);
                                    
                                    echo '<div class="alert alert-info">';
                                    echo '<strong>Contraseña generada:</strong> ' . htmlspecialchars($password) . '<br><br>';
                                    echo '<strong>Hash:</strong><br>';
                                    echo '<code>' . htmlspecialchars($hash) . '</code>';
                                    echo '</div>';
                                }
                            }
                            ?>
                            
                            <form method="POST">
                                <div class="form-group">
                                    <label for="password">Contraseña:</label>
                                    <input type="text" class="form-control" id="password" name="password" 
                                           placeholder="Ingresa la contraseña">
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="action" value="generate" class="btn btn-primary">
                                        Generar Hash
                                    </button>
                                    <button type="submit" name="action" value="random" class="btn btn-secondary">
                                        Generar Contraseña Aleatoria
                                    </button>
                                </div>
                            </form>
                            
                            <hr>
                            <div class="alert alert-warning">
                                <strong>Nota:</strong> Este script es solo para desarrollo. 
                                No usar en producción sin las medidas de seguridad adecuadas.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?> 