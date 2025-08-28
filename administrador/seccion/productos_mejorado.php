<?php 
// Incluir clases de seguridad
require_once '../config/DB.php';
require_once '../config/Auth.php';
require_once '../config/Validator.php';
require_once '../config/Middleware.php';

// Configurar sesión segura
Middleware::configureSecureSession();
session_start();

// Verificar autenticación de admin
Middleware::requireAdmin();

// Configurar headers de seguridad
Middleware::setSecurityHeaders();

// Procesar formularios
$mensaje = '';
$tipo_mensaje = 'info';

if ($_POST) {
    // Validar token CSRF
    Middleware::validateCSRF();
    
    $accion = $_POST['accion'] ?? '';
    $db = DB::getInstance();
    
    switch($accion) {
        case 'Agregar':
            $nombre = Validator::sanitizeText($_POST['txtNombre']);
            $precio = (float)$_POST['txtPrecio'];
            $descripcion = Validator::sanitizeText($_POST['txtDescripcion']);
            $stock = (int)$_POST['txtStock'];
            $categoria = Validator::sanitizeText($_POST['txtCategoria']);
            
            // Validar datos
            if (empty($nombre) || $precio <= 0) {
                $mensaje = "Error: Nombre y precio son requeridos";
                $tipo_mensaje = 'danger';
            } else {
                // Procesar imagen
                $imagen = 'imagen.jpg';
                if (isset($_FILES['txtImagen']) && $_FILES['txtImagen']['error'] == 0) {
                    $fecha = new DateTime();
                    $imagen = $fecha->getTimestamp() . "_" . $_FILES['txtImagen']['name'];
                    move_uploaded_file($_FILES['txtImagen']['tmp_name'], "../../img/" . $imagen);
                }
                
                // Insertar producto
                $sql = "INSERT INTO cascos (nombre, imagen, precio, descripcion, stock, categoria, activo) 
                        VALUES (?, ?, ?, ?, ?, ?, 1)";
                $db->query($sql, [$nombre, $imagen, $precio, $descripcion, $stock, $categoria]);
                
                $mensaje = "✅ Producto agregado correctamente";
                $tipo_mensaje = 'success';
            }
        break;

        case 'Modificar':
            $id = (int)$_POST['txtID'];
            $nombre = Validator::sanitizeText($_POST['txtNombre']);
            $precio = (float)$_POST['txtPrecio'];
            $descripcion = Validator::sanitizeText($_POST['txtDescripcion']);
            $stock = (int)$_POST['txtStock'];
            $categoria = Validator::sanitizeText($_POST['txtCategoria']);
            
            $sql = "UPDATE cascos SET nombre=?, precio=?, descripcion=?, stock=?, categoria=? WHERE id=?";
            $db->query($sql, [$nombre, $precio, $descripcion, $stock, $categoria, $id]);
            
            $mensaje = "✅ Producto actualizado correctamente";
            $tipo_mensaje = 'success';
            break;
            
        case 'Borrar':
            $id = (int)$_POST['txtID'];
            
            // Obtener imagen para eliminarla
            $producto = $db->fetchOne("SELECT imagen FROM cascos WHERE id = ?", [$id]);
            if ($producto && $producto['imagen'] != 'imagen.jpg') {
                $ruta_imagen = "../../img/" . $producto['imagen'];
                if (file_exists($ruta_imagen)) {
                    unlink($ruta_imagen);
                }
            }
            
            $db->query("DELETE FROM cascos WHERE id = ?", [$id]);
            $mensaje = "✅ Producto eliminado correctamente";
            $tipo_mensaje = 'success';
            break;
    }
}

// Obtener productos
$db = DB::getInstance();
$productos = $db->fetchAll("SELECT * FROM cascos ORDER BY fecha_creacion DESC");

include('../template/cabecera.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - HelmetsPro Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
    .sidebar .nav-link {
        padding: 0.5rem 1rem;
        margin: 0.125rem 0;
    }
    
    .sidebar .nav-link:hover {
        background-color: rgba(255,255,255,0.1);
    }
    
    .sidebar .nav-link.active {
        background-color: #007bff;
    }
    
    .table {
        background: white;
        border-radius: 5px;
    }
</style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <!-- Sidebar Simple -->
<nav class="col-md-2 d-none d-md-block bg-dark sidebar min-vh-100">
    <div class="sidebar-sticky">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Administración</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white" href="../inicio.php">
                    <i class="fas fa-home"></i> Inicio
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white active" href="productos_mejorado.php">
                    <i class="fas fa-box"></i> Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="../pedidos.php">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="../clientes.php">
                    <i class="fas fa-users"></i> Clientes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="../seccion/cerrar.php">
                    <i class="fas fa-sign-out-alt"></i> Cerrar
                </a>
            </li>
        </ul>
    </div>
</nav>

            <!-- Contenido principal -->
            <main role="main" class="col-md-10 ml-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-box"></i> Gestión de Productos
                    </h1>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalProducto">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </button>
        </div>

                <!-- Mensajes -->
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Productos</h5>
                                <h3><?php echo count($productos); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">En Stock</h5>
                                <h3><?php echo array_sum(array_column($productos, 'stock')); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
        <div class="card-body">
                                <h5 class="card-title">Stock Bajo</h5>
                                <h3><?php echo count(array_filter($productos, function($p) { return $p['stock'] < 5; })); ?></h3>
                            </div>
</div>
</div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Valor Total</h5>
                                <h3>$<?php echo number_format(array_sum(array_column($productos, 'precio')), 2); ?></h3>
</div>
    </div>
        </div>
    </div>

                <!-- Tabla de productos -->
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID</th>
                                <th>Imagen</th>
                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo $producto['id']; ?></td>
                                    <td>
                                        <img src="../../img/<?php echo $producto['imagen']; ?>" 
                                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></small>
                                    </td>
                                    <td class="precio">$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $producto['stock'] < 5 ? 'danger' : 
                                                ($producto['stock'] < 10 ? 'warning' : 'success'); 
                                        ?>">
                                            <?php echo $producto['stock']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($producto['categoria']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $producto['activo'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
            </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="editarProducto(<?php echo $producto['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="eliminarProducto(<?php echo $producto['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
            </td>
            </tr>
                            <?php endforeach; ?>
        </tbody>
    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para agregar/editar producto -->
    <div class="modal fade" id="modalProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Producto</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo Validator::getCSRFToken(); ?>">
                        <input type="hidden" name="txtID" id="txtID">
                        <input type="hidden" name="accion" id="accion" value="Agregar">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre del Producto</label>
                                    <input type="text" class="form-control" name="txtNombre" id="txtNombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Precio</label>
                                    <input type="number" class="form-control" name="txtPrecio" id="txtPrecio" 
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Stock</label>
                                    <input type="number" class="form-control" name="txtStock" id="txtStock" 
                                           min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Categoría</label>
                                    <select class="form-control" name="txtCategoria" id="txtCategoria">
                                        <option value="Cascos Deportivos">Cascos Deportivos</option>
                                        <option value="Cascos Urbanos">Cascos Urbanos</option>
                                        <option value="Cascos de Competición">Cascos de Competición</option>
                                        <option value="Accesorios">Accesorios</option>
                                    </select>
                                </div>
                            </div>
</div>
            
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea class="form-control" name="txtDescripcion" id="txtDescripcion" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Imagen</label>
                            <input type="file" class="form-control-file" name="txtImagen" id="txtImagen" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
    <script>
        function editarProducto(id) {
            // Cargar datos del producto para editar
            fetch(`get_producto.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('txtID').value = data.id;
                    document.getElementById('txtNombre').value = data.nombre;
                    document.getElementById('txtPrecio').value = data.precio;
                    document.getElementById('txtStock').value = data.stock;
                    document.getElementById('txtCategoria').value = data.categoria;
                    document.getElementById('txtDescripcion').value = data.descripcion;
                    document.getElementById('accion').value = 'Modificar';
                    document.getElementById('modalTitle').textContent = 'Editar Producto';
                    $('#modalProducto').modal('show');
                });
        }

        function eliminarProducto(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="csrf_token" value="<?php echo Validator::getCSRFToken(); ?>">
                    <input type="hidden" name="txtID" value="${id}">
                    <input type="hidden" name="accion" value="Borrar">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
