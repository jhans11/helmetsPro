<?php 
include("template/cabecera.php");
include("clases/DB.php");

// Obtener productos destacados (los más caros como ejemplo)
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM cascos WHERE activo = 1 ORDER BY precio DESC LIMIT 4");
    $stmt->execute();
    $productos_destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $productos_destacados = [];
}
?>  

<div class="jumbotron text-center">
    <h1 class="display-3">Bienvenidos a Helmets Pro</h1>
    <p class="lead">Cascos para toda clase de estilos</p>
    <hr class="my-2">
    <img width="600" src="img/grande.jpg" alt="Helmets Pro" class="img-thumbnail rounded mx-auto d-block"/>
    <p>Presiona Click Aqui Para Ver Nuestra Vitrina Virtual</p>
    <p class="lead">
        <a class="btn btn-primary btn-lg" href="productos.php" role="button">Ir a vitrina</a>
    </p>
</div>

<!--  Sección de Productos Destacados -->
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="text-center mb-4">
                <i class="fas fa-star text-warning"></i> 
                Productos Destacados
            </h2>
            <p class="text-center text-muted mb-5">Descubre nuestros cascos más populares</p>
        </div>
    </div>
    
    <div class="row">
        <?php if (!empty($productos_destacados)): ?>
            <?php foreach ($productos_destacados as $producto): ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="img/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="mt-auto">
                                <p class="card-text">
                                    <strong class="text-primary">$<?php echo number_format($producto['precio'], 2); ?> MXN</strong>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-box"></i> Stock: <?php echo $producto['stock']; ?>
                                    </small>
                                    <a href="productos.php?id=<?php echo $producto['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No hay productos destacados disponibles en este momento.
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="productos.php" class="btn btn-success btn-lg">
                <i class="fas fa-shopping-bag"></i> Ver Todos los Productos
            </a>
        </div>
    </div>
</div>

<!-- 📊 Sección de Estadísticas -->
<div class="container-fluid bg-light py-5 mt-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-transparent">
                    <div class="card-body">
                        <i class="fas fa-helmet-safety fa-3x text-primary mb-3"></i>
                        <h4 class="card-title">+100</h4>
                        <p class="card-text text-muted">Cascos Disponibles</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-transparent">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                        <h4 class="card-title">+500</h4>
                        <p class="card-text text-muted">Clientes Satisfechos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-transparent">
                    <div class="card-body">
                        <i class="fas fa-shipping-fast fa-3x text-warning mb-3"></i>
                        <h4 class="card-title">24h</h4>
                        <p class="card-text text-muted">Envío Rápido</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-transparent">
                    <div class="card-body">
                        <i class="fas fa-shield-alt fa-3x text-danger mb-3"></i>
                        <h4 class="card-title">100%</h4>
                        <p class="card-text text-muted">Garantía de Seguridad</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("template/pie.php"); ?>   


    