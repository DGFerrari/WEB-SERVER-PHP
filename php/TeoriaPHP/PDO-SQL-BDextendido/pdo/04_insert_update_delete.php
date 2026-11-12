<?php
/**
 * TEMA 4 (PDO): Insertar, actualizar y borrar + manejo de errores
 *
 * Ejecutar:  php pdo/04_insert_update_delete.php
 */
declare(strict_types=1);

/** @var PDO $pdo */
$pdo = require __DIR__ . '/conexion.php';

// --- 4.1 INSERT + lastInsertId() --------------------------------------------
// La contraseña se guarda SIEMPRE hasheada (ver Tema 6).
$hash = password_hash('secreto123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO usuarios (nombre, email, password_hash, edad) VALUES (:nombre, :email, :hash, :edad)'
);
$stmt->execute([
    'nombre' => 'Pedro Gómez',
    'email'  => 'pedro' . time() . '@example.com',  // único en cada ejecución
    'hash'   => $hash,
    'edad'   => 40,
]);
$nuevoId = (int) $pdo->lastInsertId();
echo "4.1 Insertado con id $nuevoId" . PHP_EOL;

// --- 4.2 Insertar NULL ------------------------------------------------------
// Basta con pasar null; con bindValue usa PDO::PARAM_NULL.
$stmt->bindValue(':nombre', 'Sin Edad');
$stmt->bindValue(':email', 'sinedad' . time() . '@example.com');
$stmt->bindValue(':hash', $hash);
$stmt->bindValue(':edad', null, PDO::PARAM_NULL);
$stmt->execute();
$idSinEdad = (int) $pdo->lastInsertId();
echo "4.2 Insertado con edad NULL, id $idSinEdad" . PHP_EOL;

// --- 4.3 UPDATE + rowCount() ------------------------------------------------
$stmt = $pdo->prepare('UPDATE usuarios SET edad = :edad WHERE id = :id');
$stmt->execute(['edad' => 41, 'id' => $nuevoId]);
echo "4.3 Filas actualizadas: " . $stmt->rowCount() . PHP_EOL;
// ⚠ En MySQL, rowCount() devuelve 0 si el nuevo valor es IGUAL al anterior
//   (la fila existe pero no "cambió"). No lo uses para saber si existe.

// --- 4.4 DELETE -------------------------------------------------------------
$stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
foreach ([$nuevoId, $idSinEdad] as $id) {
    $stmt->execute(['id' => $id]);
    echo "4.4 Borrado id $id: " . ($stmt->rowCount() > 0 ? 'sí' : 'no existía') . PHP_EOL;
}

// --- 4.5 Errores esperables: email duplicado --------------------------------
// Con ERRMODE_EXCEPTION, cualquier error SQL lanza PDOException.
// errorInfo[1] contiene el código de error de MySQL (1062 = clave duplicada).
try {
    $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)');
    $stmt->execute(['Otra Ana', 'ana@example.com', $hash]);   // el email ya existe
} catch (PDOException $e) {
    if (($e->errorInfo[1] ?? null) === 1062) {
        echo "4.5 Ese email ya está registrado (error controlado)" . PHP_EOL;
    } else {
        throw $e;   // ✔ errores inesperados: no los ocultes, relánzalos
    }
}
