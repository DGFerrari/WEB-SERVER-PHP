<?php
/**
 * TEMA 3 (MySQLi): Sentencias preparadas  ★ EL TEMA MÁS IMPORTANTE ★
 *
 * MySQLi solo admite marcadores posicionales "?" (no ":nombre").
 *
 * Tres formas, de la más clásica a la más moderna:
 *   A) prepare + bind_param + execute      (todas las versiones, tipos explícitos)
 *   B) prepare + execute([...])            (PHP 8.1+)
 *   C) execute_query($sql, [...])          (PHP 8.2+, en una sola línea) ✔
 *
 * Ejecutar:  php mysqli/03_consultas_preparadas.php
 */
declare(strict_types=1);

/** @var mysqli $db */
$db = require __DIR__ . '/conexion.php';

// --- 3.0 Lo que NUNCA debes hacer (inyección SQL) ---------------------------
$emailUsuario = "' OR '1'='1";   // imagina que llega de $_GET['email']
// ✘ $db->query("SELECT * FROM usuarios WHERE email = '$emailUsuario'");
// ✘ real_escape_string() "funciona", pero es fácil olvidarlo. Usa preparadas.

// --- 3.1 (A) bind_param: la forma clásica -----------------------------------
// Tipos: i = int, d = float(double), s = string, b = blob
$stmt = $db->prepare('SELECT id, nombre, email FROM usuarios WHERE email = ?');
$email = 'ana@example.com';
$stmt->bind_param('s', $email);      // ⚠ se pasa por REFERENCIA: debe ser variable
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();   // null si no hay fila
echo "3.1 bind_param: " . ($usuario['nombre'] ?? 'no encontrado') . PHP_EOL;

// Reutilizar la sentencia: cambiar la variable y volver a ejecutar
$email = $emailUsuario;
$stmt->execute();
echo "    Intento de inyección: " . ($stmt->get_result()->fetch_assoc() ? 'encontrado' : 'no encontrado') . PHP_EOL;
$stmt->close();

// Varios parámetros: una letra por cada "?" en el mismo orden
$stmt = $db->prepare('SELECT nombre FROM usuarios WHERE edad >= ? AND edad <= ?');
$min = 20;
$max = 30;
$stmt->bind_param('ii', $min, $max);
$stmt->execute();
$nombres = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'nombre');
echo "3.1 Entre 20 y 30 años: " . implode(', ', $nombres) . PHP_EOL;

// --- 3.2 (B) execute([...]) — PHP 8.1+ --------------------------------------
// Sin bind_param. Todos los valores se envían como string (MySQL convierte).
$stmt = $db->prepare('SELECT nombre FROM usuarios WHERE id = ?');
$stmt->execute([2]);
echo "3.2 execute([]): " . $stmt->get_result()->fetch_column() . PHP_EOL;

// --- 3.3 (C) execute_query() — PHP 8.2+ ✔ lo más cómodo ---------------------
// Prepara, enlaza y ejecuta en una sola llamada. Devuelve mysqli_result
// (en SELECT) o true (en INSERT/UPDATE/DELETE).
$fila = $db->execute_query('SELECT nombre FROM usuarios WHERE email = ?', ['maria@example.com'])->fetch_assoc();
echo "3.3 execute_query: " . ($fila['nombre'] ?? '-') . PHP_EOL;

// --- 3.4 Cuándo usar bind_param sí o sí: LIMIT/OFFSET y tipos estrictos -----
$porPagina = 2;
$offset    = 0;
$stmt = $db->prepare('SELECT id, nombre FROM usuarios ORDER BY id LIMIT ? OFFSET ?');
$stmt->bind_param('ii', $porPagina, $offset);
$stmt->execute();
echo "3.4 Página 1: " . json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC), JSON_UNESCAPED_UNICODE) . PHP_EOL;

// --- 3.5 Cláusula IN con lista dinámica -------------------------------------
$ids = [1, 3, 4];
$marcadores = implode(',', array_fill(0, count($ids), '?'));            // "?,?,?"
$resultado = $db->execute_query("SELECT nombre FROM usuarios WHERE id IN ($marcadores)", $ids);
echo "3.5 IN: " . implode(', ', array_column($resultado->fetch_all(MYSQLI_ASSOC), 'nombre')) . PHP_EOL;

// Con bind_param se usa el operador "..." (spread):
// $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);

// --- 3.6 LIKE: los % van en el valor ----------------------------------------
$busqueda  = 'ar';
$resultado = $db->execute_query('SELECT nombre FROM usuarios WHERE nombre LIKE ?', ["%$busqueda%"]);
echo "3.6 LIKE: " . implode(', ', array_column($resultado->fetch_all(MYSQLI_ASSOC), 'nombre')) . PHP_EOL;

// ⚠ Nombres de tabla/columna u ORDER BY no pueden ser "?": usa lista blanca.
$ordenPedido = $_GET['orden'] ?? 'nombre';
$orden = in_array($ordenPedido, ['nombre', 'email', 'edad'], true) ? $ordenPedido : 'nombre';
$db->query("SELECT nombre FROM usuarios ORDER BY $orden");   // ✔ valor controlado
