<?php 
    //indiquem que necessitarem el conexió
    require_once "conexion.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>BD connection</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <p>LISTA DE ALUMNOS CREADOS EN LA BASE DE DATOS</p>
    <?php
        //obtenim informació de la taula
        $resultado = $conexion->query("select * from alumnos");
 
        while($row = mysqli_fetch_array($resultado)){
            echo $row["nombre"]."<br/>"; //només llegeixo el nombre
        }
    ?>
</body>
</html>