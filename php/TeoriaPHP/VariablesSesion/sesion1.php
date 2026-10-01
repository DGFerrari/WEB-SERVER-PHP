<?php

// Inicializar la variable sesion
session_start();

// Variable con datos de sesion del navegador
$_SESSION['hola'] = "contenido_sesion";

?>

<a href="sesion2.php">Prueba Sesion</a>