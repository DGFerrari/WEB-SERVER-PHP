<?php
/**
 * Conexión reutilizable para los temas 02-05 (lo explicado en 01_conexion.php).
 * Uso:  $db = require __DIR__ . '/conexion.php';
 */
declare(strict_types=1);

$config = require __DIR__ . '/../config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = new mysqli($config['host'], $config['user'], $config['pass'], $config['dbname'], $config['port']);
    $db->set_charset($config['charset']);
    return $db;
} catch (mysqli_sql_exception $e) {
    error_log('Error de conexión MySQLi: ' . $e->getMessage());
    exit('No se pudo conectar a la base de datos.' . PHP_EOL);
}
