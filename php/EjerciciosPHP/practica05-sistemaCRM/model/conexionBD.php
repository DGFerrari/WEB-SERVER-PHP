<?php

    // Datos conexion BD
    define("HOST_DB", "localhost");
    define("USER_DB", "root");
	define("PASS_DB", "");
	define("NAME_DB", "crmdgf");

    // Conexion a la BD con PDO
	try {
		$conexion = new PDO("mysql:host=" . constant("HOST_DB") . ";dbname=" . constant("NAME_DB"), constant("USER_DB"), constant("PASS_DB"));
		
		$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//echo "Connected successfully";
	} catch(PDOException $e) {
		//echo "Connection failed: " . $e->getMessage();
	}

?>