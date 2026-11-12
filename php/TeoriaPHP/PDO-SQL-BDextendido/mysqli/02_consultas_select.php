<?php
/**
 * TEMA 2 (MySQLi): Leer datos (SELECT) y formas de recorrer resultados
 *
 * query() -> solo para SQL FIJO, sin datos del usuario.
 *            Si hay variables, usa sentencias preparadas (Tema 3).
 *
 * Ejecutar:  php mysqli/02_consultas_select.php
 */
declare(strict_types=1);

/** @var mysqli $db */
$db = require __DIR__ . '/conexion.php';

// --- 2.1 fetch_all(): todas las filas de golpe ------------------------------
// ⚠ Pasa SIEMPRE MYSQLI_ASSOC; por defecto devuelve arrays NUMÉRICOS.
$resultado = $db->query('SELECT id, nombre, email FROM usuarios ORDER BY nombre');
$usuarios  = $resultado->fetch_all(MYSQLI_ASSOC);

echo "== fetch_all ==" . PHP_EOL;
foreach ($usuarios as $u) {
    echo "{$u['id']} - {$u['nombre']} <{$u['email']}>" . PHP_EOL;
}
echo "Filas devueltas: {$resultado->num_rows}" . PHP_EOL;
$resultado->free();   // libera la memoria del resultado (opcional)

// --- 2.2 fetch_assoc() en bucle: fila a fila --------------------------------
echo PHP_EOL . "== fetch_assoc en bucle ==" . PHP_EOL;
$resultado = $db->query('SELECT nombre, edad FROM usuarios');
while ($fila = $resultado->fetch_assoc()) {
    echo $fila['nombre'] . ' => ' . ($fila['edad'] ?? 'sin edad') . PHP_EOL;
}

// --- 2.3 Recorrer el resultado con foreach (PHP 5.4+) -----------------------
echo PHP_EOL . "== foreach directo ==" . PHP_EOL;
foreach ($db->query('SELECT nombre FROM usuarios') as $fila) {
    echo $fila['nombre'] . PHP_EOL;   // cada $fila es un array asociativo
}

// --- 2.4 Un único valor: fetch_column() (PHP 8.1+) --------------------------
$total = (int) $db->query('SELECT COUNT(*) FROM usuarios')->fetch_column();
echo PHP_EOL . "Total de usuarios: $total" . PHP_EOL;

// --- 2.5 fetch_object(): objetos (stdClass o tu propia clase) ---------------
final class UsuarioDTO
{
    public int $id;
    public string $nombre;

    public function saludo(): string
    {
        return "Hola, soy {$this->nombre}";
    }
}
$res = $db->query('SELECT id, nombre FROM usuarios LIMIT 1');
$obj = $res->fetch_object(UsuarioDTO::class);
echo "fetch_object: " . $obj->saludo() . PHP_EOL;

// --- 2.6 Tipos de datos -----------------------------------------------------
// ⚠ Con query(), MySQLi devuelve los números como STRING ("1", no 1),
//   salvo que actives la opción MYSQLI_OPT_INT_AND_FLOAT_NATIVE antes de conectar.
// ✔ Con sentencias preparadas + get_result() llegan con su tipo real (int).
$fila = $db->query('SELECT id FROM usuarios LIMIT 1')->fetch_assoc();
echo "Tipo de id con query(): " . gettype($fila['id']) . PHP_EOL;

$fila = $db->execute_query('SELECT id FROM usuarios LIMIT 1')->fetch_assoc();
echo "Tipo de id con preparada: " . gettype($fila['id']) . PHP_EOL;
