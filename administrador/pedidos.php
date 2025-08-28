<?php
require_once 'config/config.php';
require_once 'config/DB.php';
require_once 'config/Auth.php';
require_once 'config/Middleware.php';

// Verificar autenticación de admin
Middleware::requireAdmin();

$db = DB::getInstance(); // AGREGAR ESTA LÍNEA

// Configurar paginación
$por_pagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $por_pagina;

// Filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if (!empty($filtro_estado)) {
    $where_conditions[] = "p.estado = ?";
    $params[] = $filtro_estado;
}

if (!empty($filtro_fecha)) {
    $where_conditions[] = "DATE(p.fecha_pedido) = ?";
    $params[] = $filtro_fecha;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Obtener total de pedidos para paginación (CORREGIDO)
$sql_count = "SELECT COUNT(*) as total FROM pedidos p 
              LEFT JOIN usuarios_clientes uc ON p.cliente_id = uc.id 
              $where_clause";
$total_pedidos = $db->fetchOne($sql_count, $params)['total'];
$total_paginas = ceil($total_pedidos / $por_pagina);

// Obtener pedidos (CORREGIDO)
$sql = "SELECT p.*, uc.nombre, uc.apellido, uc.email 
        FROM pedidos p 
        LEFT JOIN usuarios_clientes uc ON p.cliente_id = uc.id 
        $where_clause 
        ORDER BY p.fecha_pedido DESC 
        LIMIT ? OFFSET ?";

$params[] = $por_pagina;
$params[] = $offset;
$pedidos = $db->fetchAll($sql, $params);

// Obtener estadísticas (CORREGIDO)
$sql_stats = "SELECT 
                COUNT(*) as total_pedidos,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'confirmado' THEN 1 ELSE 0 END) as confirmados,
                SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) as enviados,
                SUM(CASE WHEN estado = 'entregado' THEN 1 ELSE 0 END) as entregados
              FROM pedidos";
$stats = $db->fetchOne($sql_stats);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - HelmetsPro Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .estado-badge {
            font-size: 0.8em;
            padding: 0.3em 0.6em;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .table-responsive {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .filtros-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-shopping-cart text-primary"></i> 
                        Gestión de Pedidos
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarPedidos()">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?= $stats['total_pedidos'] ?></h5>
                                <p class="card-text">Total Pedidos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?= $stats['pendientes'] ?></h5>
                                <p class="card-text">Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?= $stats['pagados'] ?></h5>
                                <p class="card-text">Pagados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?= $stats['enviados'] ?></h5>
                                <p class="card-text">Enviados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?= $stats['entregados'] ?></h5>
                                <p class="card-text">Entregados</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filtros-container">
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <label for="estado">Estado:</label>
                            <select name="estado" id="estado" class="form-control">
                                <option value="">Todos los estados</option>
                                <option value="Pendiente" <?= $filtro_estado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="Pagado" <?= $filtro_estado === 'Pagado' ? 'selected' : '' ?>>Pagado</option>
                                <option value="Enviado" <?= $filtro_estado === 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                                <option value="Entregado" <?= $filtro_estado === 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha">Fecha:</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" value="<?= $filtro_fecha ?>">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="pedidos.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla de Pedidos -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID Pedido</th>
                                <th>Cliente</th>
                                <th>Email</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pedidos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        No se encontraron pedidos
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= $pedido['numero_pedido'] ?></strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']) ?>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($pedido['email']) ?></small>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) ?>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($pedido['total'], 2) ?></strong>
                                        </td>
                                        <td>
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
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="detalle-pedido.php?id=<?= $pedido['id_pedido'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        onclick="cambiarEstado(<?= $pedido['id_pedido'] ?>, '<?= $pedido['estado'] ?>')"
                                                        title="Cambiar estado">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Navegación de páginas">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagina > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?= $pagina - 1 ?>&estado=<?= $filtro_estado ?>&fecha=<?= $filtro_fecha ?>">
                                        <i class="fas fa-chevron-left"></i> Anterior
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
                                <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="?pagina=<?= $i ?>&estado=<?= $filtro_estado ?>&fecha=<?= $filtro_fecha ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagina < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?= $pagina + 1 ?>&estado=<?= $filtro_estado ?>&fecha=<?= $filtro_fecha ?>">
                                        Siguiente <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Modal para cambiar estado -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Estado del Pedido</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formCambiarEstado">
                        <input type="hidden" id="pedido_id" name="pedido_id">
                        <div class="form-group">
                            <label for="nuevo_estado">Nuevo Estado:</label>
                            <select id="nuevo_estado" name="nuevo_estado" class="form-control" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Pagado">Pagado</option>
                                <option value="Enviado">Enviado</option>
                                <option value="Entregado">Entregado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="comentario">Comentario (opcional):</label>
                            <textarea id="comentario" name="comentario" class="form-control" rows="3"></textarea>
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

        function exportarPedidos() {
            const filtros = new URLSearchParams(window.location.search);
            window.open('exportar_pedidos.php?' + filtros.toString(), '_blank');
        }
    </script>
</body>
</html>
