<?php include("template/cabecera.php");?>  
<?php 
include("administrador/config/bd.php");
$sentenciaSQL= $conexion->prepare("SELECT * FROM cascos");
$sentenciaSQL->execute();      
$listaCascos=$sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
?>

<?php foreach ($listaCascos as $cascos ) {?>

<div class="col-md-3">    
<div class="card">
<img class="card-img-top" src="./img/<?php echo $cascos['imagen']; ?>" alt="">
<div class="card-body">
        <h4 class="card-title"><?php echo $cascos['nombre']; ?></h4>
        <a name="" id="" class="btn btn-primary" href="https://www.atoamotos.com/" role="button">Comprar Ahora</a>      
</div>  
</div>
</div>

<?php } ?>





<?php include("template/pie.php");?>   
