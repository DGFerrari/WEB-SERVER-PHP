<?php
/**
 * TEMA 4 (MySQLi): Insertar, actualizar y borrar + manejo de errores
 *
 * Ejecutar:  php mysqli/04_insert_update_delete.php
 */
declare(strict_types=1);

/** @var mysqli $db */
$db = require __DIR__ . '/conexion.php';

// --- 4.1 INSERT + insert_id -------------------------------------------------
// La contraseña se guarda SIEMPRE hasheada (ver Tema 6).
$hash = password_hash('secreto123', PASSWORD_DEFAULT);

$stmt = $db->prepare('INSERT INTO usuarios (nombre, email, password_hash, edad) VALUES (?, ?, ?, ?)');
$nombre = 'Pedro Gómez';
$email  = 'pedro' . time() . '@example.com';
$edad   = 40;
$stmt->bind_param('sssi', $nombre, $email, $hash, $edad);
$stmt->execute();
$nuevoId = $stmt->insert_id;          // también disponible en $db->insert_id
echo "4.1 Insertado con id $nuevoId" . PHP_EOL;

// --- 4.2 Insertar NULL ------------------------------------------------------
// Basta con que la variable valga null (aunque el tipo sea "i").
$nombre = 'Sin Edad';
$email  = 'sinedad' . time() . '@example.com';
$edad   = null;
$stmt->execute();                     // las variables siguen enlazadas por referencia
$idSinEdad = $stmt->insert_id;
echo "4.2 Insertado con edad NULL, id $idSinEdad" . PHP_EOL;
$stmt->close();

// --- 4.3 UPDATE + affected_rows ---------------------------------------------
$stmt = $db->prepare('UPDATE usuarios SET edad = ? WHERE id = ?');
$stmt->execute([41, $nuevoId]);       // forma corta PHP 8.1+
echo "4.3 Filas actualizadas: {$stmt->affected_rows}" . PHP_EOL;
// ⚠ Igual que en PDO: 0 si el valor nuevo es igual al anterior.

// --- 4.4 DELETE -------------------------------------------------------------
foreach ([$nuevoId, $idSinEdad] as $id) {
    $db->execute_query('DELETE FROM usuarios WHERE id = ?', [$id]);
    // Con execute_query, el nº de filas afectadas se lee de la conexión:
    echo "4.4 Borrado id $id: " . ($db->affected_rows > 0 ? 'sí' : 'no existía') . PHP_EOL;
}

// --- 4.5 Errores esperables: email duplicado --------------------------------
// getCode() devuelve directamente el código de error de MySQL.
try {
    $db->execute_query(
        'INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)',
        ['Otra Ana', 'ana@example.com', $hash]   // el email ya existe
    );
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo "4.5 Ese email ya está registrado (error controlado)" . PHP_EOL;
    } else {
        throw $e;   // ✔ los errores inesperados no se ocultan
    }
}
