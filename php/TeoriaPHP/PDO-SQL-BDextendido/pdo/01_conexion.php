<?php
/**
 * TEMA 1 (PDO): Conexión a la base de datos
 *
 * PDO = PHP Data Objects. Es una capa de acceso a datos que funciona con
 * muchos motores (MySQL, PostgreSQL, SQLite, SQL Server...). Si mañana
 * cambias de motor, gran parte del código sigue sirviendo.
 *
 * Ejecutar:  php pdo/01_conexion.php
 */
declare(strict_types=1);

$config = require __DIR__ . '/../config.php';

// 1) DSN (Data Source Name): cadena que indica motor, servidor, BD y charset.
//    El charset va en el DSN, NO con "SET NAMES" después.
$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $config['host'],
    $config['port'],
    $config['dbname'],
    $config['charset']
);

// 2) Opciones recomendadas (★ = imprescindible)
$opciones = [
    // ★ Lanza excepciones ante cualquier error SQL (desde PHP 8 es el valor
    //   por defecto, pero ponerlo explícito deja clara la intención).
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

    // ★ Devuelve las filas como arrays asociativos ['columna' => valor]
    //   en lugar de duplicar cada valor con índice numérico y por nombre.
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // ★ Usa sentencias preparadas REALES del servidor MySQL (no emuladas).
    //   Además los INT llegan como int y no como string.
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// 3) Crear la conexión dentro de try/catch
try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $opciones);
    echo "✔ Conectado con PDO a '{$config['dbname']}'" . PHP_EOL;
    echo "  Versión del servidor: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
} catch (PDOException $e) {
    // ✘ NUNCA muestres $e->getMessage() al usuario final en producción:
    //   puede revelar usuario, host o estructura de tu BD.
    error_log('Error de conexión PDO: ' . $e->getMessage()); // al log
    exit('No se pudo conectar a la base de datos.' . PHP_EOL);  // mensaje genérico
}

// 4) Cerrar la conexión: PHP la cierra solo al terminar el script.
//    Si quieres cerrarla antes, basta con eliminar todas las referencias:
$pdo = null;
