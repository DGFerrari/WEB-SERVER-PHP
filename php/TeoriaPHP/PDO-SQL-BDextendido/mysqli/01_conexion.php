<?php
/**
 * TEMA 1 (MySQLi): Conexión a la base de datos
 *
 * MySQLi = MySQL improved. Solo funciona con MySQL/MariaDB, pero da acceso
 * a funciones específicas de MySQL. Tiene dos estilos:
 *   - Orientado a objetos:  $db->query(...)      ✔ recomendado
 *   - Procedural:           mysqli_query($db,...) (mismo resultado, más verboso)
 *
 * Ejecutar:  php mysqli/01_conexion.php
 */
declare(strict_types=1);

$config = require __DIR__ . '/../config.php';

// ★ 1) Activar excepciones ANTES de conectar.
//    Desde PHP 8.1 es el comportamiento por defecto, pero ponerlo explícito
//    garantiza que funcione igual en cualquier servidor.
//    Sin esto, los errores devuelven false en silencio y hay que comprobar
//    $db->error después de CADA llamada.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 2) Conectar (estilo orientado a objetos)
    $db = new mysqli(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['dbname'],
        $config['port']
    );

    // ★ 3) Charset: SIEMPRE con set_charset(), no con "SET NAMES".
    //    set_charset() también informa a la librería cliente, lo que hace que
    //    real_escape_string() funcione correctamente.
    $db->set_charset($config['charset']);

    echo "✔ Conectado con MySQLi a '{$config['dbname']}'" . PHP_EOL;
    echo "  Versión del servidor: {$db->server_info}" . PHP_EOL;
} catch (mysqli_sql_exception $e) {
    // ✘ No muestres el mensaje real al usuario en producción.
    error_log('Error de conexión MySQLi: ' . $e->getMessage());
    exit('No se pudo conectar a la base de datos.' . PHP_EOL);
}

// --- Equivalente en estilo PROCEDURAL (solo para que lo reconozcas) ---------
// $db = mysqli_connect($host, $user, $pass, $dbname, $port);
// mysqli_set_charset($db, 'utf8mb4');
// $res = mysqli_query($db, 'SELECT 1');
// mysqli_close($db);

// 4) Cerrar: PHP la cierra al acabar el script. Para cerrarla antes:
$db->close();
