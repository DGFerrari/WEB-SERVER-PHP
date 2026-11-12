<?php
/**
 * TEMA 2 (PDO): Leer datos (SELECT) y modos de fetch
 *
 * query()  -> solo para SQL FIJO, sin ningún dato que venga del usuario.
 *             Si hay variables, usa prepare() (Tema 3).
 *
 * Ejecutar:  php pdo/02_consultas_select.php
 */
declare(strict_types=1);

/** @var PDO $pdo */
$pdo = require __DIR__ . '/conexion.php';

// --- 2.1 fetchAll(): todas las filas de golpe -------------------------------
// ✔ Cómodo para resultados pequeños/medianos (listados, paginación).
// ✔ Selecciona columnas concretas en vez de SELECT * (más claro y eficiente).
$usuarios = $pdo->query('SELECT id, nombre, email FROM usuarios ORDER BY nombre')->fetchAll();

echo "== fetchAll ==" . PHP_EOL;
foreach ($usuarios as $u) {
    echo "{$u['id']} - {$u['nombre']} <{$u['email']}>" . PHP_EOL;
}

// --- 2.2 fetch() en bucle: fila a fila --------------------------------------
// ✔ Mejor para resultados enormes: no carga todo en memoria.
echo PHP_EOL . "== fetch en bucle ==" . PHP_EOL;
$stmt = $pdo->query('SELECT nombre, edad FROM usuarios');
while ($fila = $stmt->fetch()) {
    echo $fila['nombre'] . ' => ' . ($fila['edad'] ?? 'sin edad') . PHP_EOL;
}

// --- 2.3 fetchColumn(): un único valor --------------------------------------
$total = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
echo PHP_EOL . "Total de usuarios: $total" . PHP_EOL;

// --- 2.4 Otros modos de fetch útiles ----------------------------------------
echo PHP_EOL . "== Modos de fetch ==" . PHP_EOL;

// FETCH_OBJ: objetos stdClass  ->  $u->nombre
$primero = $pdo->query('SELECT id, nombre FROM usuarios LIMIT 1')->fetch(PDO::FETCH_OBJ);
echo "FETCH_OBJ: {$primero->nombre}" . PHP_EOL;

// FETCH_COLUMN: array plano con una sola columna
$emails = $pdo->query('SELECT email FROM usuarios')->fetchAll(PDO::FETCH_COLUMN);
echo "FETCH_COLUMN: " . implode(', ', $emails) . PHP_EOL;

// FETCH_KEY_PAIR: [col1 => col2], ideal para <select> en formularios
$opciones = $pdo->query('SELECT id, nombre FROM usuarios')->fetchAll(PDO::FETCH_KEY_PAIR);
echo "FETCH_KEY_PAIR: " . json_encode($opciones, JSON_UNESCAPED_UNICODE) . PHP_EOL;

// FETCH_CLASS: rellena objetos de tu propia clase (útil con entidades/DTOs)
final class UsuarioDTO
{
    public int $id;
    public string $nombre;

    public function saludo(): string
    {
        return "Hola, soy {$this->nombre}";
    }
}
$objetos = $pdo->query('SELECT id, nombre FROM usuarios')->fetchAll(PDO::FETCH_CLASS, UsuarioDTO::class);
echo "FETCH_CLASS: " . $objetos[0]->saludo() . PHP_EOL;
