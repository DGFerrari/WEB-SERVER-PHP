<?php
// Iniciamos Sesion
session_start();

//Pasamos datos
$user_iniciado = $_POST['usuario'];
$pasw_iniciado = $_POST['contra'];

// Comprobamos que no sea nulo nada
if ($user_iniciado === '' || $pasw_iniciado === '') {

    // Guardamos en la sesion el usuario
    $_SESSION['usuario'] = $user_iniciado;
} else {
    header("Location: index.php");
}
?>

<h1>Hola, Bienvenido al Curso</h1>