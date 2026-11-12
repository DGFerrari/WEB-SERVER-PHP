<?php
/**
 * TEMA 3 (PDO): Sentencias preparadas  ★ EL TEMA MÁS IMPORTANTE ★
 *
 * Regla de oro: cualquier dato que venga de fuera (formularios, URL, cookies,
 * APIs...) va SIEMPRE como parámetro, nunca concatenado en el SQL.
 *
 * Ejecutar:  php pdo/03_consultas_preparadas.php
 */
declare(strict_types=1);

/** @var PDO $pdo */
$pdo = require __DIR__ . '/conexion.php';

// --- 3.0 Lo que NUNCA debes hacer (inyección SQL) ---------------------------
$emailUsuario = "' OR '1'='1";  // imagina que esto llega de $_GET['email']
// ✘ $pdo->query("SELECT * FROM usuarios WHERE email = '$emailUsuario'");
//   El SQL final sería ... WHERE email = '' OR '1'='1'  -> devuelve TODOS.

// --- 3.1 Marcadores con nombre (:nombre) ✔ recomendado ----------------------
// Más legibles y el orden no importa.
$stmt = $pdo->prepare('SELECT id, nombre, email FROM usuarios WHERE email = :email');
$stmt->execute(['email' => 'ana@example.com']);
$usuario = $stmt->fetch();           // false si no hay resultado
echo "3.1 Con nombre: " . ($usuario ? $usuario['nombre'] : 'no encontrado') . PHP_EOL;

// El ataque anterior ahora es inofensivo: se busca literalmente ese texto.
$stmt->execute(['email' => $emailUsuario]);   // ✔ reutilizamos la sentencia
echo "    Intento de inyección: " . ($stmt->fetch() ? 'encontrado' : 'no encontrado') . PHP_EOL;

// --- 3.2 Marcadores posicionales (?) ----------------------------------------
// Válidos, pero con muchos parámetros es fácil equivocarse de orden.
$stmt = $pdo->prepare('SELECT nombre FROM usuarios WHERE edad >= ? AND edad <= ?');
$stmt->execute([20, 30]);
echo "3.2 Entre 20 y 30 años: " . implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN)) . PHP_EOL;

// --- 3.3 bindValue() con tipo explícito -------------------------------------
// execute([...]) envía todo como string. Cuando el tipo importa
// (LIMIT/OFFSET, booleanos, NULL) usa bindValue con PDO::PARAM_*.
$pagina    = 1;
$porPagina = 2;
$stmt = $pdo->prepare('SELECT id, nombre FROM usuarios ORDER BY id LIMIT :limite OFFSET :offset');
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', ($pagina - 1) * $porPagina, PDO::PARAM_INT);
$stmt->execute();
echo "3.3 Página $pagina: " . json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

// --- 3.4 bindParam() vs bindValue() -----------------------------------------
// bindValue: copia el valor EN ESE MOMENTO.
// bindParam: enlaza la VARIABLE por referencia; se lee al hacer execute().
//            Útil para ejecutar la misma sentencia en un bucle.
$stmt = $pdo->prepare('SELECT nombre FROM usuarios WHERE id = :id');
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
foreach ([1, 2, 3] as $id) {
    $stmt->execute();               // usa el valor actual de $id
    echo "3.4 id=$id -> " . ($stmt->fetchColumn() ?: '-') . PHP_EOL;
}

// --- 3.5 Cláusula IN con una lista dinámica ---------------------------------
// No se puede pasar un array a un solo "?". Se genera un "?" por elemento.
$ids = [1, 3, 4];
$marcadores = implode(',', array_fill(0, count($ids), '?'));   // "?,?,?"
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id IN ($marcadores)");
$stmt->execute($ids);
echo "3.5 IN (" . implode(',', $ids) . "): " . implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN)) . PHP_EOL;

// --- 3.6 LIKE ----------------------------------------------------------------
// Los % van en el VALOR, no en el SQL.
$busqueda = 'ar';
$stmt = $pdo->prepare('SELECT nombre FROM usuarios WHERE nombre LIKE :q');
$stmt->execute(['q' => "%$busqueda%"]);
echo "3.6 LIKE '%$busqueda%': " . implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN)) . PHP_EOL;

// ⚠ Lo que NO puede ser parámetro: nombres de tabla/columna, ASC/DESC...
//   Si dependen del usuario, valida contra una "lista blanca":
$ordenPedido = $_GET['orden'] ?? 'nombre';
$columnasPermitidas = ['nombre', 'email', 'edad'];
$orden = in_array($ordenPedido, $columnasPermitidas, true) ? $ordenPedido : 'nombre';
$pdo->query("SELECT nombre FROM usuarios ORDER BY $orden");   // ✔ seguro: valor controlado
