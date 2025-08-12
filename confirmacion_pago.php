<?php
session_start();

$orderID = $_GET['orderID'] ?? '';
$pedido = $_SESSION['pedido_checkout'] ?? null;

if (!$pedido) {
    header('Location: index.php');
    exit();
}

// Limpiar el carrito después del pago exitoso
if (isset($_SESSION['carrito'])) {
    unset($_SESSION['carrito']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pago - Helmets PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center bg-success text-white">
                        <h3><i class="fas fa-check-circle"></i> ¡Pago Exitoso!</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="alert alert-success">
                            <h4>¡Gracias por tu compra!</h4>
                            <p>Tu pedido ha sido procesado correctamente.</p>
                            <p><strong>ID de Orden PayPal:</strong> <?php echo htmlspecialchars($orderID); ?></p>
                            <p><strong>Total Pagado:</strong> $<?php echo number_format($pedido['total'], 2); ?></p>
                        </div>
                        
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-home"></i> Volver al Inicio
                            </a>
                            <a href="mi-cuenta.php" class="btn btn-info">
                                <i class="fas fa-user"></i> Mi Cuenta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
