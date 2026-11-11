<?php

	class conexionBD {
    	// Datos conexion BD
    	private PDO $conexion;

		//=== CONSTRUCTOR ===//
	
		public function __constructor(PDO $conexion) {
			define("HOST_DB", "localhost");
			define("USER_DB", "root");
			define("PASS_DB", "");
			define("NAME_DB", "crmdgf");

			try {
				$conexion = new PDO("mysql:host=" . constant("HOST_DB") . ";dbname=" . constant("NAME_DB"), constant("USER_DB"), constant("PASS_DB"));
		
				$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				//echo "Connected successfully";
			} catch(PDOException $e) {
				//echo "Connection failed: " . $e->getMessage();
			}
		}

		//=== Comprovar Usuario y Contraseña ===///

		public function ComprovarLogin($u, $p) {

			// Consulta SQL
			$sql = "SELECT id_usuario, password FROM usuarios WHERE id_usuario = :u AND password = :p";
			$result = $conexion->prepare($sql); 
			$result->execute(['u' => $u], ['p' => $p]); 
			$comprovar = $result->fetch(PDO::FETCH_ASSOC); 

			// Comprobar si la contraseña es la correcta
			// Si no encuentra usuario, tampoco contraseña, asi que nos sirve.
			if (isset($comprovar['password']) && password_verify($p, $comprovar['password'])) {
				return true;
			} else {
				return false;
			}
		}


		
	}

?>