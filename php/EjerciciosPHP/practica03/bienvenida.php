<?php
// Iniciamos Sesion
session_start();

//Pasamos datos
$user_iniciado = $_POST['usuario'];
$pasw_iniciado = $_POST['contra'];

// Comprobamos que no sea nulo nada
if (!($user_iniciado === '' || $pasw_iniciado === '')) {

    // Guardamos en la sesion el usuario
    $_SESSION['usuario'] = $user_iniciado;

    // Printar por pantalla
    echo '<h1>Hola ' . $_SESSION['usuario'] . ', bienvenido al Curso</h1>';

} else {
    header("Location: index.php");
}
?>

<!-- Boton -->
<a href='logout.php'>Cerrar sesion</a>