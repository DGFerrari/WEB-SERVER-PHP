<?php
/**
 * Database: crea UNA sola conexión MySQLi por petición y la reutiliza.
 *
 * Los modelos NO llaman a esta clase: reciben el objeto mysqli en el
 * constructor (inyección de dependencias). Solo index.php la usa.
 */
declare(strict_types=1);

final class Database
{
    private static ?mysqli $conexion = null;

    private function __construct()
    {
    }

    public static function getConexion(): mysqli
    {
        if (self::$conexion === null) {
            $c = require __DIR__ . '/../../../config.php';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $db = new mysqli($c['host'], $c['user'], $c['pass'], $c['dbname'], $c['port']);
            $db->set_charset($c['charset']);

            self::$conexion = $db;
        }

        return self::$conexion;
    }
}
