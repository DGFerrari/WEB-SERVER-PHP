<?php

session_start(); // Empieza
session_unset(); // Libera
session_destroy(); // Destruye

header("Location: index.php"); // Redirige

?>