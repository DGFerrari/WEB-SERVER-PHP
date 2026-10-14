<?php
    require "conexion.php";        
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Page Title</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <?php
        $sql = "select * from alumnos";
        $resultados = mysqli_query($conexion, $sql); //pasamos la query a hacer directamente
        $num_filas = mysqli_num_rows($resultados); //nos retorna si existen filas en la tabla
 
        if($num_filas == 0){
            ?>
                <p>No hay alumnos disponibles</p>
            <?php
        }else{
        ?>
        <table>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
            </tr>
            <?php
                while($fila = mysqli_fetch_assoc($resultados)){
                    echo "<tr>";
 
                    echo "<td>".$fila['nombre']."</td>";
                    echo "<td>".$fila['apellido']."</td>";
 
                    echo "</tr>";
                }
            ?>
        </table>
        <?php
        }
        ?>
</body>
</html>