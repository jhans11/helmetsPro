<?php 
// Incluir clases necesarias
session_start();
require_once 'clases/Carrito.php';

include("template/cabecera.php");

// Inicializar carrito
Carrito::init();

// Obtener productos activos con precios
include("administrador/config/bd.php");
$sentenciaSQL = $conexion->prepare("SELECT * FROM cascos WHERE activo = 1 ORDER BY nombre");
$sentenciaSQL->execute();      
$listaCascos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);

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

<?php if (isset($mensaje)) { ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php } ?>

<?php foreach ($listaCascos as $casco) { ?>
    <div class="col-md-3 mb-4">    
        <div class="card h-100">
            <img class="card-img-top" src="./img/<?php echo $casco['imagen']; ?>" alt="<?php echo $casco['nombre']; ?>">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo $casco['nombre']; ?></h5>
                <p class="card-text text-muted"><?php echo $casco['descripcion'] ?? 'Casco de alta calidad'; ?></p>
                <div class="mt-auto">
                    <h6 class="text-primary"><?php echo Carrito::formatearPrecio($casco['precio']); ?></h6>
                    <p class="text-success"><small>Stock: <?php echo $casco['stock']; ?> unidades</small></p>
                    
                    <!-- ✅ PASO 2: Formulario para agregar al carrito -->
                    <form method="POST" class="mt-2">
                        <input type="hidden" name="id_producto" value="<?php echo $casco['id']; ?>">
                        <div class="form-group">
                            <label for="cantidad_<?php echo $casco['id']; ?>">Cantidad:</label>
                            <input type="number" class="form-control" name="cantidad" id="cantidad_<?php echo $casco['id']; ?>" 
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





<?php include("template/pie.php");?>   
