<?php
require_once 'config/config.php';
require_once 'config/DB.php';
require_once 'config/Auth.php';
require_once 'config/Middleware.php';

// Verificar autenticación de admin
Middleware::requireAdmin();

// Obtener ID del pedido
$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$pedido_id) {
    header('Location: pedidos.php');
    exit;
}

// Obtener información del pedido
$sql_pedido = "SELECT p.*, uc.nombre, uc.apellido, uc.email, uc.telefono,
                       de.direccion, de.ciudad, de.codigo_postal, de.pais
                FROM pedidos p 
                LEFT JOIN usuarios_clientes uc ON p.id_usuario = uc.id 
                LEFT JOIN direcciones_envio de ON p.id_direccion_envio = de.id_direccion
                WHERE p.id_pedido = ?";

$pedido = DB::fetchOne($sql_pedido, [$pedido_id]);

if (!$pedido) {
    header('Location: pedidos.php');
    exit;
}

// Obtener productos del pedido
$sql_productos = "SELECT dp.*, c.nombre as nombre_producto, c.imagen, c.precio as precio_unitario
                  FROM detalles_pedido dp
                  LEFT JOIN cascos c ON dp.id_producto = c.id_casco
                  WHERE dp.id_pedido = ?";

$productos = DB::fetchAll($sql_productos, [$pedido_id]);

// Obtener historial de estados
$sql_historial = "SELECT h.*, a.nombre as admin_nombre
                  FROM historial_estados_pedido h
                  LEFT JOIN usuarios_admin a ON h.id_admin = a.id
                  WHERE h.id_pedido = ?
                  ORDER BY h.fecha_cambio DESC";

$historial = DB::fetchAll($sql_historial, [$pedido_id]);

// Calcular totales
$subtotal = 0;
$total_iva = 0;
foreach ($productos as $producto) {
    $subtotal += $producto['precio_unitario'] * $producto['cantidad'];
}
$total_iva = $subtotal * 0.19; // 19% IVA
$total_final = $subtotal + $total_iva;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Pedido #<?= $pedido['numero_pedido'] ?> - HelmetsPro Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .pedido-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .producto-item {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .estado-timeline {
            position: relative;
            padding-left: 30px;
        }
        .estado-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .estado-item {
            position: relative;
            margin-bottom: 20px;
        }
        .estado-item::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #007bff;
        }
        .estado-badge {
            font-size: 0.8em;
            padding: 0.3em 0.6em;
        }
        .btn-volver {
            background: #6c757d;
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
        }
        .btn-volver:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block bg-dark sidebar min-vh-100">
                <div class="sidebar-sticky">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Administración</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="inicio.php">
                                <i class="fas fa-home"></i> Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="pedidos.php">
                                <i class="fas fa-shopping-cart"></i> Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="productos.php">
                                <i class="fas fa-box"></i> Productos
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main role="main" class="col-md-10 ml-sm-auto px-4">
                <!-- Header del pedido -->
                <div class="pedido-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h2 mb-0">
                                <i class="fas fa-file-invoice"></i> 
                                Pedido #<?= $pedido['numero_pedido'] ?>
                            </h1>
                            <p class="mb-0 mt-2">
                                <i class="fas fa-calendar"></i> 
                                Creado: <?= date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <a href="pedidos.php" class="btn btn-volver text-white">
                                <i class="fas fa-arrow-left"></i> Volver a Pedidos
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Información del Cliente -->
                    <div class="col-md-6">
                        <div class="card info-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user"></i> Información del Cliente
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nombre:</strong><br>
                                        <?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']) ?></p>
                                        
                                        <p><strong>Email:</strong><br>
                                        <a href="mailto:<?= htmlspecialchars($pedido['email']) ?>">
                                            <?= htmlspecialchars($pedido['email']) ?>
                                        </a></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Teléfono:</strong><br>
                                        <?= htmlspecialchars($pedido['telefono'] ?? 'No especificado') ?></p>
                                        
                                        <p><strong>Estado:</strong><br>
                                        <?php
                                        $estado_class = '';
                                        switch ($pedido['estado']) {
                                            case 'Pendiente':
                                                $estado_class = 'badge-warning';
                                                break;
                                            case 'Pagado':
                                                $estado_class = 'badge-info';
                                                break;
                                            case 'Enviado':
                                                $estado_class = 'badge-primary';
                                                break;
                                            case 'Entregado':
                                                $estado_class = 'badge-success';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $estado_class ?> estado-badge">
                                            <?= $pedido['estado'] ?>
                                        </span>
                                    </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dirección de Envío -->
                        <div class="card info-card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-map-marker-alt"></i> Dirección de Envío
                                </h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Dirección:</strong><br>
                                <?= htmlspecialchars($pedido['direccion'] ?? 'No especificada') ?></p>
                                
                                <p><strong>Ciudad:</strong><br>
                                <?= htmlspecialchars($pedido['ciudad'] ?? 'No especificada') ?></p>
                                
                                <p><strong>Código Postal:</strong><br>
                                <?= htmlspecialchars($pedido['codigo_postal'] ?? 'No especificado') ?></p>
                                
                                <p><strong>País:</strong><br>
                                <?= htmlspecialchars($pedido['pais'] ?? 'No especificado') ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Pedido -->
                    <div class="col-md-6">
                        <div class="card info-card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-shopping-cart"></i> Resumen del Pedido
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Número de Pedido:</strong><br>
                                        #<?= $pedido['numero_pedido'] ?></p>
                                        
                                        <p><strong>Fecha de Creación:</strong><br>
                                        <?= date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) ?></p>
                                        
                                        <?php if ($pedido['fecha_actualizacion']): ?>
                                        <p><strong>Última Actualización:</strong><br>
                                        <?= date('d/m/Y H:i', strtotime($pedido['fecha_actualizacion'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Subtotal:</strong><br>
                                        $<?= number_format($subtotal, 2) ?></p>
                                        
                                        <p><strong>IVA (19%):</strong><br>
                                        $<?= number_format($total_iva, 2) ?></p>
                                        
                                        <p><strong>Total:</strong><br>
                                        <strong>$<?= number_format($total_final, 2) ?></strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Historial de Estados -->
                        <div class="card info-card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="fas fa-history"></i> Historial de Estados
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="estado-timeline">
                                    <?php if (empty($historial)): ?>
                                        <p class="text-muted">No hay historial de cambios</p>
                                    <?php else: ?>
                                        <?php foreach ($historial as $cambio): ?>
                                            <div class="estado-item">
                                                <div class="d-flex justify-content-between">
                                                    <strong><?= $cambio['estado_nuevo'] ?></strong>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y H:i', strtotime($cambio['fecha_cambio'])) ?>
                                                    </small>
                                                </div>
                                                <small class="text-muted">
                                                    Cambió de "<?= $cambio['estado_anterior'] ?>" a "<?= $cambio['estado_nuevo'] ?>"
                                                </small>
                                                <?php if ($cambio['comentario']): ?>
                                                    <p class="mb-0 mt-1"><small><?= htmlspecialchars($cambio['comentario']) ?></small></p>
                                                <?php endif; ?>
                                                <?php if ($cambio['admin_nombre']): ?>
                                                    <small class="text-info">Por: <?= htmlspecialchars($cambio['admin_nombre']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos del Pedido -->
                <div class="card info-card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-box"></i> Productos del Pedido
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($productos)): ?>
                            <p class="text-muted text-center">No se encontraron productos en este pedido</p>
                        <?php else: ?>
                            <?php foreach ($productos as $producto): ?>
                                <div class="producto-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <?php if ($producto['imagen']): ?>
                                                <img src="../<?= htmlspecialchars($producto['imagen']) ?>" 
                                                     alt="<?= htmlspecialchars($producto['nombre_producto']) ?>"
                                                     class="img-fluid rounded" style="max-width: 80px;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 80px; height: 80px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="mb-1"><?= htmlspecialchars($producto['nombre_producto']) ?></h6>
                                            <small class="text-muted">ID: <?= $producto['id_producto'] ?></small>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <strong>Cantidad:</strong><br>
                                            <?= $producto['cantidad'] ?>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <strong>Precio Unitario:</strong><br>
                                            $<?= number_format($producto['precio_unitario'], 2) ?>
                                        </div>
                                        <div class="col-md-2 text-right">
                                            <strong>Subtotal:</strong><br>
                                            $<?= number_format($producto['precio_unitario'] * $producto['cantidad'], 2) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="text-center mt-4 mb-4">
                    <button type="button" class="btn btn-primary btn-lg" 
                            onclick="cambiarEstado(<?= $pedido_id ?>, '<?= $pedido['estado'] ?>')">
                        <i class="fas fa-edit"></i> Cambiar Estado
                    </button>
                    <a href="pedidos.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Volver a Pedidos
                    </a>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para cambiar estado -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Estado del Pedido #<?= $pedido['numero_pedido'] ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formCambiarEstado">
                        <input type="hidden" id="pedido_id" name="pedido_id" value="<?= $pedido_id ?>">
                        <div class="form-group">
                            <label for="nuevo_estado">Nuevo Estado:</label>
                            <select id="nuevo_estado" name="nuevo_estado" class="form-control" required>
                                <option value="Pendiente" <?= $pedido['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="Pagado" <?= $pedido['estado'] === 'Pagado' ? 'selected' : '' ?>>Pagado</option>
                                <option value="Enviado" <?= $pedido['estado'] === 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                                <option value="Entregado" <?= $pedido['estado'] === 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="comentario">Comentario (opcional):</label>
                            <textarea id="comentario" name="comentario" class="form-control" rows="3" 
                                      placeholder="Agregar comentario sobre el cambio de estado..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarCambioEstado()">
                        <i class="fas fa-save"></i> Guardar Cambio
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cambiarEstado(pedidoId, estadoActual) {
            $('#pedido_id').val(pedidoId);
            $('#nuevo_estado').val(estadoActual);
            $('#modalCambiarEstado').modal('show');
        }

        function guardarCambioEstado() {
            const pedidoId = $('#pedido_id').val();
            const nuevoEstado = $('#nuevo_estado').val();
            const comentario = $('#comentario').val();

            $.ajax({
                url: 'actualizar_estado_pedido.php',
                method: 'POST',
                data: {
                    pedido_id: pedidoId,
                    nuevo_estado: nuevoEstado,
                    comentario: comentario
                },
                success: function(response) {
                    if (response.success) {
                        $('#modalCambiarEstado').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al actualizar el estado del pedido');
                }
            });
        }
    </script>
</body>
</html>
