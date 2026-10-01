<?php
session_start();

    $_SESSION['usuario'] = $usuario;
    $_SESSION['contra'] = $contra;

echo $_SESSION['usuario'];
?>

<h1>Hola, Bienvenido al Curso</h1>