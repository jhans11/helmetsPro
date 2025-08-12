<?php
session_start();

// Limpiar datos de PayPal de la sesión
unset($_SESSION['paypal_order_id']);

include('../template/cabecera.php');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-times-circle fa-5x text-warning"></i>
                    </div>
                    
                    <h2 class="text-warning mb-3">Pago Cancelado</h2>
                    
                    <p class="text-muted mb-4">
                        Has cancelado el proceso de pago. Tu pedido no ha sido procesado.
                    </p>
                    
                    <div class="d-grid gap-2">
                        <a href="../checkout.php" class="btn btn-primary">
                            <i class="fas fa-credit-card"></i> Intentar Pago Nuevamente
                        </a>
                        
                        <a href="../carrito.php" class="btn btn-outline-secondary">
                            <i class="fas fa-shopping-cart"></i> Volver al Carrito
                        </a>
                        
                        <a href="../productos.php" class="btn btn-outline-primary">
                            <i class="fas fa-shopping-bag"></i> Continuar Comprando
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-info-circle"></i> 
                            Si tienes problemas con el pago, contáctanos en 
                            <a href="mailto:soporte@helmetspro.com">soporte@helmetspro.com</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.fa-5x {
    font-size: 5rem;
}

.card {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border: none;
}
</style>

<?php include('../template/pie.php'); ?>

