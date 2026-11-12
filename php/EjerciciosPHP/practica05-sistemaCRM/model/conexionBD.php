<?php
class ConexionBD {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $name = "crmdgf";

    public static function conectar() {

    try {
		$conexion = new PDO("mysql:host=" . constant("HOST_DB") . ";dbname=" . constant("NAME_DB"), constant("USER_DB"), constant("PASS_DB"));
		$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
        $mensaje = addslashes($e->getMessage()); // Para caracteres que puedan romper el codigo
        // Imprimir en la consola
        echo "<script>console.error('Error de conexión: " . $mensaje . "');</script>";
	    }
    }
}
?>