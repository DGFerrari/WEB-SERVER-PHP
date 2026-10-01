<?php

// Inicializar la variable sesion
session_start();

// Mostramos la sesion por pantalla
echo $_SESSION['hola'];
echo "<br>";

// Mostramos el id de la sesion
echo session_id();
?>