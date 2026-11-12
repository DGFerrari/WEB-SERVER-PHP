<?php
/**
 * Database: crea UNA sola conexión PDO por petición y la reutiliza.
 *
 * ¿Por qué así?
 *  - Abrir una conexión es caro: no queremos una nueva por cada modelo.
 *  - Los modelos NO llaman a esta clase: reciben el PDO en el constructor
 *    (inyección de dependencias). Así son fáciles de probar y reutilizar.
 *    Solo el "punto de entrada" (index.php) llama a Database::getConexion().
 */
declare(strict_types=1);

final class Database
{
    private static ?PDO $conexion = null;

    // Constructor privado: nadie puede hacer "new Database()".
    private function __construct()
    {
    }

    public static function getConexion(): PDO
    {
        if (self::$conexion === null) {
            $c = require __DIR__ . '/../../../config.php';

            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['dbname']};charset={$c['charset']}";

            self::$conexion = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$conexion;
    }
}
