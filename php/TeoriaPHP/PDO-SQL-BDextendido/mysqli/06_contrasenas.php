<?php
/**
 * TEMA 6 (MySQLi): Contraseñas con hash
 *
 * password_hash()/password_verify() son funciones de PHP, no de la
 * extensión: son idénticas en PDO y MySQLi. Solo cambia la parte 6.7.
 *
 * Regla de oro: la contraseña NUNCA se guarda tal cual, ni "cifrada".
 * Se guarda un HASH: una huella de un solo sentido. Para comprobar un login
 * se hashea lo que escribe el usuario y se compara con la huella guardada.
 *
 *  ✘ Texto plano            -> si roban la BD, tienen todas las contraseñas.
 *  ✘ md5() / sha1() / sha256 -> son RÁPIDOS: una GPU prueba miles de millones
 *                              por segundo. Están pensados para ficheros, no para claves.
 *  ✘ Cifrado (openssl_encrypt) -> es reversible: quien tenga la clave las descifra.
 *  ✔ password_hash()         -> algoritmo LENTO a propósito (bcrypt/argon2),
 *                              con "sal" aleatoria incluida automáticamente.
 *
 * Ejecutar:  php mysqli/06_contrasenas.php
 */
declare(strict_types=1);

// --- 6.1 password_hash(): crear el hash -------------------------------------
$password = 'secreto123';
$hash1 = password_hash($password, PASSWORD_DEFAULT);
$hash2 = password_hash($password, PASSWORD_DEFAULT);

echo "6.1 Hash 1: $hash1" . PHP_EOL;
echo "    Hash 2: $hash2" . PHP_EOL;
echo "    ¿Iguales? " . ($hash1 === $hash2 ? 'sí' : 'no (cada uno lleva una sal distinta)') . PHP_EOL;
// Anatomía de un hash bcrypt:  $2y$12$ + 22 chars de sal + 31 chars de hash
//                               │   └─ coste (2^12 iteraciones)
//                               └─ algoritmo (2y = bcrypt)
// Todo lo necesario para verificar va DENTRO del hash: no hay que guardar la sal aparte.

// --- 6.2 password_verify(): comprobar una contraseña ------------------------
// ✘ NUNCA compares así:  password_hash($intento) === $hashGuardado  (siempre false)
// ✔ Usa password_verify(), que además compara en tiempo constante.
echo PHP_EOL . "6.2 verify('secreto123'): " . var_export(password_verify('secreto123', $hash1), true) . PHP_EOL;
echo "    verify('Secreto123'): " . var_export(password_verify('Secreto123', $hash1), true) . PHP_EOL;

// --- 6.3 password_get_info(): qué algoritmo y opciones tiene un hash --------
echo PHP_EOL . "6.3 Info: " . json_encode(password_get_info($hash1)) . PHP_EOL;

// --- 6.4 password_needs_rehash(): actualizar hashes antiguos ----------------
// Si algún día subes el coste o PHP cambia el algoritmo por defecto, los
// hashes viejos siguen funcionando, y en el siguiente login se regeneran.
$opciones = ['cost' => 13];   // más coste = más seguro y más lento (~doble por punto)
echo PHP_EOL . "6.4 ¿Necesita rehash con cost 13? "
    . (password_needs_rehash($hash1, PASSWORD_DEFAULT, $opciones) ? 'sí' : 'no') . PHP_EOL;
// ✔ Buen coste: el que tarde ~100-300 ms en tu servidor. El valor por defecto (12) suele ir bien.

// --- 6.5 Argon2id (alternativa moderna, si tu PHP lo incluye) ---------------
if (defined('PASSWORD_ARGON2ID')) {
    echo PHP_EOL . "6.5 Argon2id: " . password_hash($password, PASSWORD_ARGON2ID) . PHP_EOL;
} else {
    echo PHP_EOL . "6.5 Argon2id no disponible en este PHP (bcrypt es perfectamente válido)." . PHP_EOL;
}
// Con PASSWORD_DEFAULT no tienes que elegir: PHP usa el recomendado en cada versión.

// --- 6.6 Límites a validar ANTES de hashear ---------------------------------
// bcrypt solo usa los primeros 72 BYTES: dos contraseñas que coincidan en
// esos 72 bytes darían el mismo resultado. Limita la longitud en la validación.
// (strlen cuenta bytes: "ñ" o un emoji ocupan más de 1.)
function validarPassword(string $password): ?string
{
    return match (true) {
        strlen($password) < 8  => 'La contraseña debe tener al menos 8 caracteres.',
        strlen($password) > 72 => 'La contraseña no puede superar 72 bytes.',
        default                => null,   // null = válida
    };
}
echo PHP_EOL . "6.6 validar('corta'): " . (validarPassword('corta') ?? 'ok') . PHP_EOL;

// --- 6.7 Flujo completo con la base de datos --------------------------------
/** @var mysqli $db */
$db = require __DIR__ . '/conexion.php';

/** Registro: se valida, se hashea y se guarda SOLO el hash. */
function registrar(mysqli $db, string $nombre, string $email, string $password): int
{
    if ($error = validarPassword($password)) {
        throw new InvalidArgumentException($error);
    }

    $db->execute_query(
        'INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)',
        [$nombre, $email, password_hash($password, PASSWORD_DEFAULT)]
    );

    return (int) $db->insert_id;
}

/**
 * Login: devuelve el usuario (SIN el hash) o null si las credenciales fallan.
 */
function login(mysqli $db, string $email, string $password): ?array
{
    $usuario = $db
        ->execute_query('SELECT id, nombre, email, password_hash FROM usuarios WHERE email = ?', [$email])
        ->fetch_assoc();

    if ($usuario === null) {
        // Aunque el email no exista, hacemos un verify "de mentira" para que
        // la respuesta tarde lo mismo. Si no, un atacante podría averiguar
        // qué emails están registrados midiendo el tiempo de respuesta.
        // ⚠ Tiene que ser un hash bcrypt VÁLIDO; si no, verify no hace el trabajo y no sirve.
        password_verify($password, '$2y$12$aDc9wlxEU984wLiwgGR/yuwsJVwPpAOxKnyh6/rAHJENVQIW3eAKi');
        return null;
    }

    if (!password_verify($password, $usuario['password_hash'])) {
        return null;
    }

    // Login correcto: aprovechamos para actualizar el hash si está anticuado.
    if (password_needs_rehash($usuario['password_hash'], PASSWORD_DEFAULT)) {
        $db->execute_query(
            'UPDATE usuarios SET password_hash = ? WHERE id = ?',
            [password_hash($password, PASSWORD_DEFAULT), $usuario['id']]
        );
    }

    unset($usuario['password_hash']);   // ✔ el hash no sale de esta función
    return $usuario;
}

$email = 'login' . time() . '@example.com';
$id = registrar($db, 'Usuario Login', $email, 'MiClaveSegura!');
echo PHP_EOL . "6.7 Registrado id $id" . PHP_EOL;

// ✔ Mensaje de error GENÉRICO: no digas si falló el email o la contraseña.
foreach (['MiClaveSegura!', 'otraClave'] as $intento) {
    $u = login($db, $email, $intento);
    echo "    login('$intento'): " . ($u ? "✔ Bienvenido, {$u['nombre']}" : '✘ Email o contraseña incorrectos') . PHP_EOL;
}
echo "    login(email inexistente): " . (login($db, 'nadie@example.com', 'x') ? '✔' : '✘ Email o contraseña incorrectos') . PHP_EOL;

$db->execute_query('DELETE FROM usuarios WHERE id = ?', [$id]);   // limpieza
