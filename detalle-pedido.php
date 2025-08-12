<?php
session_start();
require_once 'clases/Usuario.php';
require_once 'clases/Carrito.php';

// Verificar que el usuario esté logueado
$usuario_obj = new Usuario();
if (!$usuario_obj->estaAutenticado()) {
    header('Location: login.php?redirect=detalle-pedido.php');
    exit();
}

// Obtener el ID del pedido desde la URL
$pedido_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pedido_id <= 0) {
    header('Location: mi-cuenta.php?error=pedido_invalido');
    exit();
}

// Obtener datos del usuario actual
$usuario_actual = $usuario_obj->obtenerUsuarioActual();
$pedido = Usuario::obtenerPedido($usuario_actual['id'], $pedido_id);

if (!$pedido) {
    header('Location: mi-cuenta.php?error=pedido_no_encontrado');
    exit();
}

// Obtener detalles del pedido
$detalles_pedido = Usuario::obtenerDetallesPedido($pedido_id);

include('template/cabecera.php');
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="mi-cuenta.php">Mi Cuenta</a></li>
                    <li class="breadcrumb-item active">Detalle del Pedido</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-shopping-bag"></i> 
                        Pedido #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Información del Pedido -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle"></i> Información del Pedido</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Número de Pedido:</strong></td>
                                    <td>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha:</strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        <?php
                                        $estados = [
                                            'pendiente' => '<span class="badge badge-warning">Pendiente</span>',
                                            'confirmado' => '<span class="badge badge-info">Confirmado</span>',
                                            'en_proceso' => '<span class="badge badge-primary">En Proceso</span>',
                                            'enviado' => '<span class="badge badge-success">Enviado</span>',
                                            'entregado' => '<span class="badge badge-success">Entregado</span>',
                                            'cancelado' => '<span class="badge badge-danger">Cancelado</span>'
                                        ];
                                        echo $estados[$pedido['estado']] ?? '<span class="badge badge-secondary">Desconocido</span>';
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total:</strong></td>
                                    <td><strong class="text-primary">$<?php echo number_format($pedido['total'], 2); ?></strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-shipping-fast"></i> Dirección de Envío</h5>
                            <div class="border p-3 rounded">
                                <strong><?php echo htmlspecialchars($pedido['nombre_envio']); ?></strong><br>
                                <?php echo htmlspecialchars($pedido['direccion_envio']); ?><br>
                                <?php echo htmlspecialchars($pedido['ciudad_envio'] . ', ' . $pedido['estado_envio']); ?><br>
                                CP: <?php echo htmlspecialchars($pedido['codigo_postal_envio']); ?><br>
                                Tel: <?php echo htmlspecialchars($pedido['telefono_envio']); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Productos del Pedido -->
                    <div class="row">
                        <div class="col-12">
                            <h5><i class="fas fa-box"></i> Productos del Pedido</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Producto</th>
                                            <th>Precio Unitario</th>
                                            <th>Cantidad</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalles_pedido as $detalle): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="img/<?php echo htmlspecialchars($detalle['imagen']); ?>" 
                                                         alt="<?php echo htmlspecialchars($detalle['nombre']); ?>"
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <div class="ml-3">
                                                        <strong><?php echo htmlspecialchars($detalle['nombre']); ?></strong><br>
                                                        <small class="text-muted">SKU: <?php echo htmlspecialchars($detalle['sku']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                                            <td><?php echo $detalle['cantidad']; ?></td>
                                            <td>$<?php echo number_format($detalle['subtotal'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-dark">
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                            <td>$<?php echo number_format($pedido['subtotal'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>IVA (16%):</strong></td>
                                            <td>$<?php echo number_format($pedido['iva'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                            <td><strong>$<?php echo number_format($pedido['total'], 2); ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Estados -->
                    <?php if (!empty($pedido['historial_estados'])): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><i class="fas fa-history"></i> Historial de Estados</h5>
                            <div class="timeline">
                                <?php foreach ($pedido['historial_estados'] as $index => $estado): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker <?php echo $index === 0 ? 'active' : ''; ?>"></div>
                                    <div class="timeline-content">
                                        <h6><?php echo htmlspecialchars($estado['estado']); ?></h6>
                                        <p class="text-muted mb-1">
                                            <?php echo date('d/m/Y H:i', strtotime($estado['fecha'])); ?>
                                        </p>
                                        <?php if (!empty($estado['comentario'])): ?>
                                        <p class="mb-0"><?php echo htmlspecialchars($estado['comentario']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Acciones -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                <a href="mi-cuenta.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver a Mi Cuenta
                                </a>
                                <?php if ($pedido['estado'] === 'pendiente'): ?>
                                <button type="button" class="btn btn-danger" onclick="cancelarPedido(<?php echo $pedido['id']; ?>)">
                                    <i class="fas fa-times"></i> Cancelar Pedido
                                </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-info" onclick="imprimirPedido()">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #dee2e6;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-marker.active {
    background-color: #007bff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}
</style>

<script>
function cancelarPedido(pedidoId) {
    if (confirm('¿Estás seguro de que quieres cancelar este pedido?')) {
        fetch('cancelar-pedido.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                pedido_id: pedidoId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pedido cancelado exitosamente');
                window.location.reload();
            } else {
                alert('Error al cancelar el pedido: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}

function imprimirPedido() {
    window.print();
}
</script>

<?php include('template/pie.php'); ?>