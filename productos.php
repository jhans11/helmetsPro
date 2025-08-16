<?php 
// Incluir clases necesarias
session_start();
require_once 'clases/Carrito.php';
require_once 'clases/FiltrosProductos.php';

include("template/cabecera.php");

// Inicializar carrito
Carrito::init();

// Inicializar sistema de filtros
$filtros = new FiltrosProductos();

// ✅ PASO 3: Procesar filtros del formulario
$filtrosAplicados = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filtrosAplicados = FiltrosProductos::sanitizarFiltros($_GET);
}

// Obtener datos para los filtros
$categorias = $filtros->obtenerCategorias();
$rangoPrecios = $filtros->obtenerRangoPrecios();

// Obtener productos con filtros aplicados
$listaCascos = $filtros->filtrarProductos($filtrosAplicados);
$totalProductos = $filtros->contarProductos($filtrosAplicados);

// Procesar agregar al carrito
if (isset($_POST['agregar_carrito'])) {
    $id_producto = (int)$_POST['id_producto'];
    $cantidad = (int)$_POST['cantidad'];
    
    // Buscar información del producto
    $producto = null;
    foreach ($listaCascos as $casco) {
        if ($casco['id'] == $id_producto) {
            $producto = $casco;
            break;
        }
    }
    
    if ($producto) {
        // ✅ PASO 1: Agregar producto al carrito usando sesiones PHP
        Carrito::agregarProducto(
            $producto['id'],
            $producto['nombre'],
            $producto['precio'],
            $producto['imagen'],
            $cantidad
        );
        
        $mensaje = "✅ Producto agregado al carrito";
    }
}
?>

<!-- ✅ PASO 4: Formulario de filtros -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🔍 Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" id="filtrosForm">
                        <!-- Búsqueda por texto -->
                        <div class="form-group">
                            <label for="busqueda">Buscar producto:</label>
                            <input type="text" class="form-control" name="busqueda" id="busqueda" 
                                   value="<?php echo htmlspecialchars($filtrosAplicados['busqueda'] ?? ''); ?>" 
                                   placeholder="Nombre o descripción...">
                        </div>
                        
                        <!-- Filtro por categoría -->
                        <div class="form-group">
                            <label for="categoria">Categoría:</label>
                            <select class="form-control" name="categoria" id="categoria">
                                <option value="todas">Todas las categorías</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['categoria']); ?>" 
                                            <?php echo (isset($filtrosAplicados['categoria']) && $filtrosAplicados['categoria'] === $cat['categoria']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['categoria']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Filtro por rango de precios -->
                        <div class="form-group">
                            <label>Rango de precios:</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="precio_min" 
                                           value="<?php echo htmlspecialchars($filtrosAplicados['precio_min'] ?? ''); ?>" 
                                           placeholder="Mín" step="0.01" min="0">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="precio_max" 
                                           value="<?php echo htmlspecialchars($filtrosAplicados['precio_max'] ?? ''); ?>" 
                                           placeholder="Máx" step="0.01" min="0">
                                </div>
                            </div>
                            <small class="text-muted">
                                Rango: $<?php echo number_format($rangoPrecios['precio_min'], 2); ?> - 
                                $<?php echo number_format($rangoPrecios['precio_max'], 2); ?>
                            </small>
                        </div>
                        
                        <!-- Ordenamiento -->
                        <div class="form-group">
                            <label for="orden">Ordenar por:</label>
                            <select class="form-control" name="orden" id="orden">
                                <option value="nombre" <?php echo (isset($filtrosAplicados['orden']) && $filtrosAplicados['orden'] === 'nombre') ? 'selected' : ''; ?>>Nombre</option>
                                <option value="precio" <?php echo (isset($filtrosAplicados['orden']) && $filtrosAplicados['orden'] === 'precio') ? 'selected' : ''; ?>>Precio</option>
                                <option value="categoria" <?php echo (isset($filtrosAplicados['orden']) && $filtrosAplicados['orden'] === 'categoria') ? 'selected' : ''; ?>>Categoría</option>
                                <option value="stock" <?php echo (isset($filtrosAplicados['orden']) && $filtrosAplicados['orden'] === 'stock') ? 'selected' : ''; ?>>Stock</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion">Dirección:</label>
                            <select class="form-control" name="direccion" id="direccion">
                                <option value="ASC" <?php echo (isset($filtrosAplicados['direccion']) && $filtrosAplicados['direccion'] === 'ASC') ? 'selected' : ''; ?>>Ascendente</option>
                                <option value="DESC" <?php echo (isset($filtrosAplicados['direccion']) && $filtrosAplicados['direccion'] === 'DESC') ? 'selected' : ''; ?>>Descendente</option>
                            </select>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                🔍 Aplicar Filtros
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <a href="productos.php" class="btn btn-outline-secondary btn-block">
                                🗑️ Limpiar Filtros
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- ✅ PASO 5: Lista de productos con filtros -->
        <div class="col-md-9">
            <!-- Información de resultados -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <h4>🛡️ Cascos Disponibles</h4>
                    <p class="text-muted">
                        Mostrando <?php echo $totalProductos; ?> producto<?php echo $totalProductos != 1 ? 's' : ''; ?>
                        <?php if (!empty($filtrosAplicados)): ?>
                            con filtros aplicados
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-6 text-right">
                    <?php if (!empty($filtrosAplicados)): ?>
                        <a href="productos.php" class="btn btn-sm btn-outline-primary">
                            🔄 Ver todos los productos
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Mensaje de éxito -->
            <?php if (isset($mensaje)) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $mensaje; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php } ?>
            
            <!-- Grid de productos -->
            <div class="row">
                <?php if (empty($listaCascos)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <h5>🔍 No se encontraron productos</h5>
                            <p>Intenta ajustar los filtros o <a href="productos.php">ver todos los productos</a></p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($listaCascos as $casco) { ?>
                        <div class="col-md-4 mb-4">    
                            <div class="card h-100">
                                <img class="card-img-top" src="./img/<?php echo $casco['imagen']; ?>" 
                                     alt="<?php echo htmlspecialchars($casco['nombre']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($casco['nombre']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($casco['descripcion'] ?? 'Casco de alta calidad'); ?></p>
                                    
                                    <!-- Badge de categoría -->
                                    <span class="badge badge-info mb-2"><?php echo htmlspecialchars($casco['categoria']); ?></span>
                                    
                                    <div class="mt-auto">
                                        <h6 class="text-primary"><?php echo FiltrosProductos::formatearPrecio($casco['precio']); ?></h6>
                                        <p class="text-success"><small>Stock: <?php echo $casco['stock']; ?> unidades</small></p>
                                        
                                        <!-- ✅ PASO 2: Formulario para agregar al carrito -->
                                        <form method="POST" class="mt-2">
                                            <input type="hidden" name="id_producto" value="<?php echo $casco['id']; ?>">
                                            <div class="form-group">
                                                <label for="cantidad_<?php echo $casco['id']; ?>">Cantidad:</label>
                                                <input type="number" class="form-control" name="cantidad" 
                                                       id="cantidad_<?php echo $casco['id']; ?>" 
                                                       value="1" min="1" max="<?php echo $casco['stock']; ?>" required>
                                            </div>
                                            <button type="submit" name="agregar_carrito" class="btn btn-primary btn-block">
                                                🛒 Agregar al Carrito
                                            </button>
                                        </form>
                                    </div>
                                </div>  
                            </div>
                        </div>
                    <?php } ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ✅ PASO 6: JavaScript para mejorar UX de filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit en cambios de select
    const selects = document.querySelectorAll('#categoria, #orden, #direccion');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filtrosForm').submit();
        });
    });
    
    // Debounce para búsqueda
    let timeoutBusqueda;
    const inputBusqueda = document.getElementById('busqueda');
    inputBusqueda.addEventListener('input', function() {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => {
            document.getElementById('filtrosForm').submit();
        }, 500);
    });
    
    // Auto-submit en cambios de precio
    const inputsPrecio = document.querySelectorAll('input[name="precio_min"], input[name="precio_max"]');
    inputsPrecio.forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('filtrosForm').submit();
        });
    });
});
</script>

<?php include("template/pie.php");?>   
