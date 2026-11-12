<?php
/**
 * Conexión reutilizable para los temas 02-05 (lo explicado en 01_conexion.php).
 * Uso:  $pdo = require __DIR__ . '/conexion.php';
 */
declare(strict_types=1);

$config = require __DIR__ . '/../config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $config['host'],
    $config['port'],
    $config['dbname'],
    $config['charset']
);

try {
    return new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log('Error de conexión PDO: ' . $e->getMessage());
    exit('No se pudo conectar a la base de datos.' . PHP_EOL);
}
