<?php include("../template/cabecera.php"); ?>
<?php 


$txtID=(isset($_POST['txtID']))?$_POST['txtID']:"";
$txtNombre=(isset($_POST['txtNombre']))?$_POST['txtNombre']:"";
$txtImagen=(isset($_FILES['txtImagen']['name']))?$_FILES['txtImagen']['name']:"";
$accion=(isset($_POST['accion']))?$_POST['accion']:"";
 
include("../config/bd.php");


switch($accion){

    case"Agregar":
        $sentenciaSQL= $conexion->prepare("INSERT INTO cascos (nombre,imagen) VALUES (:nombre,:imagen);");
        $sentenciaSQL->bindParam(':nombre',$txtNombre);

        $fecha= new DateTime();
        $nombreArchivo=($txtImagen!="")?$fecha->getTimestamp()."_".$_FILES["txtImagen"]["name"]:"imagen.jpg";

        $tmpImagen=$_FILES["txtImagen"]["tmp_name"];

        if($tmpImagen!=""){

                move_uploaded_file($tmpImagen,"../../img/".$nombreArchivo);
        }

        $sentenciaSQL->bindParam(':imagen',$nombreArchivo);
        $sentenciaSQL->execute();  
        
        header("location:productos.php");
        break;

    case"Modificar":

        $sentenciaSQL= $conexion->prepare("UPDATE cascos SET nombre=:nombre WHERE id=:id");
        $sentenciaSQL->bindParam(':nombre',$txtNombre);
        $sentenciaSQL->bindParam(':id',$txtID);
        $sentenciaSQL->execute();

        if($txtImagen!=""){

            $fecha= new DateTime();
            $nombreArchivo=($txtImagen!="")?$fecha->getTimestamp()."_".$_FILES["txtImagen"]["name"]:"imagen.jpg";           
            $tmpImagen=$_FILES["txtImagen"]["tmp_name"];

            move_uploaded_file($tmpImagen,"../../img/".$nombreArchivo);

        $sentenciaSQL= $conexion->prepare("SELECT imagen FROM cascos WHERE id=:id");
        $sentenciaSQL->bindParam(':id',$txtID);
        $sentenciaSQL->execute();      
        $cascos=$sentenciaSQL->fetch(PDO::FETCH_LAZY);

        if( isset($cascos["imagen"]) &&($cascos["imagen"]!="imagen.jpg") ){

            if(file_exists("../../img/".$cascos["imagen"])){

                unlink("../../img/".$cascos["imagen"]);
            }

        }



            $sentenciaSQL= $conexion->prepare("UPDATE cascos SET imagen=:imagen WHERE id=:id");
            $sentenciaSQL->bindParam(':imagen',$nombreArchivo);
            $sentenciaSQL->bindParam(':id',$txtID);
            $sentenciaSQL->execute();
        }

        header("location:productos.php");
        break;

    case"Cancelar":
        header("location:productos.php");
        break;

    case"Seleccionar":

        $sentenciaSQL= $conexion->prepare("SELECT * FROM cascos WHERE id=:id");
        $sentenciaSQL->bindParam(':id',$txtID);
        $sentenciaSQL->execute();      
        $cascos=$sentenciaSQL->fetch(PDO::FETCH_LAZY);

        $txtNombre=$cascos['nombre'];
        $txtImagen=$cascos['imagen'];

            //echo "Presionado botón Seleccionar";
        break;

    case"Borrar":

        $sentenciaSQL= $conexion->prepare("SELECT imagen FROM cascos WHERE id=:id");
        $sentenciaSQL->bindParam(':id',$txtID);
        $sentenciaSQL->execute();      
        $cascos=$sentenciaSQL->fetch(PDO::FETCH_LAZY);

        if( isset($cascos["imagen"]) &&($cascos["imagen"]!="imagen.jpg") ){

            if(file_exists("../../img/".$cascos["imagen"])){

                unlink("../../img/".$cascos["imagen"]);
            }

        }


            $sentenciaSQL= $conexion->prepare("DELETE FROM cascos WHERE id=:id");
            $sentenciaSQL->bindParam(':id',$txtID);
            $sentenciaSQL->execute(); 
            header("location:productos.php");
            break;
}

$sentenciaSQL= $conexion->prepare("SELECT * FROM cascos");
$sentenciaSQL->execute();      
$listaCascos=$sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);

?>


<div class="col-md-5">

    <div class="card">
        <div class="card-header">
            Datos de Casco
        </div>

        <div class="card-body">
            
        <form method="POST" enctype="multipart/form-data" >

<div class = "form-group">
<label for="txtID">ID:</label>
<input type="text" required readonly class="form-control" value="<?php echo $txtID;?>" name="txtID" id="txtID"  placeholder="ID">
</div>

<div class = "form-group">
<label for="txtNombre">Nombre:</label>
<input type="text" required class="form-control" value="<?php echo $txtNombre;?>" name="txtNombre" id="txtNombre"  placeholder="Nombre del casco">
</div>

<div class = "form-group">
<label for="txtNombre">Imagen:</label>

<br/>

    <?php if($txtImagen!=""){ ?>

        <img class="img-thumbnail rounded"  src="../../img/<?php echo $txtImagen;?> " width="50" alt="" srcset="">
                

    <?php } ?>


<input type="file" class="form-control" name="txtImagen" id="txtImagen"  placeholder="Nombre del casco">
</div>


    <div class="btn-group" role="group" aria-label="">
        <button type="submit" name="accion" <?php echo ($accion=="Seleccionar")?"disabled":""; ?> value="Agregar" class="btn btn-success">Agregar</button>
        <button type="submit" name="accion" <?php echo ($accion!="Seleccionar")?"disabled":""; ?> value="Modificar" class="btn btn-warning">Modificar</button>
        <button type="submit" name="accion" <?php echo ($accion!="Seleccionar")?"disabled":""; ?> value="Cancelar" class="btn btn-info">Cancelar</button>
    </div>


</form>

        </div>

        

    </div>


    
    
    

</div>
<div class="col-md-7">

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($listaCascos as $cascos)  { ?>
            <tr>
                <td><?php echo $cascos['id'];?></td>
                <td><?php echo $cascos['nombre'];?></td>
                <td>

                <img class="img-thumbnail rounded" src="../../img/<?php echo $cascos['imagen']; ?>" width="50" alt="" srcset="">
                    
                
            
            </td>

                <td>             
                <form method="post">

                    <input type="hidden" name="txtID" id="txtID" value="<?php echo $cascos['id'];?>" />

                    <input type="submit" name="accion" value="Seleccionar" class="btn btn-primary" />

                    <input type="submit" name="accion" value="Borrar" class="btn btn-danger" />


                </form>

            </td>

            </tr>
            <?php }?>
        </tbody>
    </table>

</div>
            
<?php include("../template/pie.php"); ?>
