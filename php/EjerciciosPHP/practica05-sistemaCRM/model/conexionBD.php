<?php
class ConexionBD {
    private static $host = "localhost";
    private static $user = "root";
    private static $pass = "";
    private static $name = "crmdgf";

    /**
     * Devuelve una conexion PDO a la BD (o null si falla).
     */
    public static function conectar() {
        try {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$name . ";charset=utf8mb4";
            $conexion = new PDO($dsn, self::$user, self::$pass);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
        } catch (PDOException $e) {
            // Se guarda en el log de PHP en vez de mostrarlo al usuario
            error_log("Error de conexion: " . $e->getMessage());
        }
    }
}
?>
