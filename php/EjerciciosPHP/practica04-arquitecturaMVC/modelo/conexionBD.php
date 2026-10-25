<?php
// Archivo con los datos para realizar la conexcion con la BD

    // Variables con info para la conexion
    define("HOST_DB", "localhost");
    define("USER_DB", "root");
	define("PASS_DB", "");
	define("NAME_DB", "test");

	// Conexion a la BD con PDO
	try {
		$conexion = new PDO("mysql:host=" . constant("HOST_DB") . ";dbname=" . constant("NAME_DB"), constant("USER_DB"), constant("PASS_DB"));
		
		$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//echo "Connected successfully";
	} catch(PDOException $e) {
		//echo "Connection failed: " . $e->getMessage();
	}
	
    // Conexion a la BD con Mysqli
	/*
    $conexion = new mysqli(
		constant("HOST_DB"),
		constant("USER_DB"),
		constant("PASS_DB"),
		constant("NAME_DB")
	);
	*/
?>