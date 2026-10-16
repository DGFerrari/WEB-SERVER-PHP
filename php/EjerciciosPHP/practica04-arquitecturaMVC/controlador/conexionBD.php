<?php
// Archivo con los datos para realizar la conexcion con la BD

    // Variables con info para la conexion
    define("HOST_DB", "localhost");
    define("USER_DB", "root");
	define("PASS_DB", "");
	define("NAME_DB", "test");

    // Conexion a la BD
    $conexion = new mysqli(
		constant("HOST_DB"),
		constant("USER_DB"),
		constant("PASS_DB"),
		constant("NAME_DB")
	);
?>