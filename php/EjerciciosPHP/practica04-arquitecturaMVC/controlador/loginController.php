<?php
    // Llamamos la conexion a la BD
    require_once __DIR__ . '/../modelo/conexionBD.php';

    // Pillamos usuario y contraseña del login
    $user = $_POST['usuario'];
    $pswd = $_POST['contrasena'];

    // Consulta si existe usuario y contraseña
    $sql = "SELECT usuario, contra FROM amvc WHERE usuario = '$user' AND contra = '$pswd'";

    // Pasamos la query a hacer directamente
    $resultados = mysqli_query($conexion, $sql); 

    // Nos retorna si existen filas en la tabla
    $num_filas = mysqli_num_rows($resultados); 

    // Comprobamos si existe el usuario y contraseña
    if($num_filas == 0){
        header("Location: ../vista/login.php");

    } else {
        // Iniciamos Sesion
        session_start();

        // Guardamos en la sesion el usuario
        $_SESSION['usuario'] = $user;

        // Redireccionamos a la pagina de inicio
        header("Location: ../vista/inicio.php");
    
    }

?>