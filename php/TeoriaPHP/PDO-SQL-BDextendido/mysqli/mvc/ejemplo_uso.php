<?php
/**
 * Cómo usar el modelo Usuario DESDE CUALQUIER SITIO (otro script, un cron,
 * otro controlador, una API...). Solo necesitas:
 *   1. Cargar las clases.
 *   2. Crear el modelo pasándole la conexión.
 *   3. Llamar a sus métodos.
 *
 * Ejecutar:  php mysqli/mvc/ejemplo_uso.php
 * (Mismo código que la versión PDO: el modelo oculta qué extensión se usa.)
 */
declare(strict_types=1);

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Usuario.php';

$usuarios = new Usuario(Database::getConexion());

// --- Leer --------------------------------------------------------------------
echo "Total: " . $usuarios->contar() . PHP_EOL;

foreach ($usuarios->todos() as $u) {
    echo " - {$u['nombre']} ({$u['email']})" . PHP_EOL;
}

$ana = $usuarios->buscarPorEmail('ana@example.com');
echo "Buscar por email: " . ($ana['nombre'] ?? 'no existe') . PHP_EOL;

// --- Crear -------------------------------------------------------------------
try {
    // La contraseña se pasa en claro: el modelo la hashea antes de guardarla.
    $email = 'prueba' . time() . '@example.com';
    $id = $usuarios->crear('Prueba Modelo', $email, 'ClaveInicial1', 30);
    echo "Creado con id $id" . PHP_EOL;

    // --- Login (autenticar) --------------------------------------------------
    $logueado = $usuarios->autenticar($email, 'ClaveInicial1');
    echo "Login correcto: " . ($logueado ? "sí, hola {$logueado['nombre']}" : 'no') . PHP_EOL;
    echo "Login con clave mala: " . ($usuarios->autenticar($email, 'otra') ? 'sí' : 'no') . PHP_EOL;
    echo "¿El resultado incluye el hash? " . (isset($logueado['password_hash']) ? 'sí ✘' : 'no ✔') . PHP_EOL;

    // --- Cambiar contraseña (pide la actual) ---------------------------------
    echo "Cambiar con actual incorrecta: " . ($usuarios->cambiarPassword($id, 'mala', 'ClaveNueva22') ? 'ok' : 'rechazado') . PHP_EOL;
    echo "Cambiar con actual correcta: " . ($usuarios->cambiarPassword($id, 'ClaveInicial1', 'ClaveNueva22') ? 'ok' : 'rechazado') . PHP_EOL;
    echo "Login con la nueva: " . ($usuarios->autenticar($email, 'ClaveNueva22') ? 'sí' : 'no') . PHP_EOL;

    // --- Actualizar ----------------------------------------------------------
    $cambiado = $usuarios->actualizar($id, 'Prueba Editada', 'editado' . time() . '@example.com', 31);
    echo "Actualizado: " . ($cambiado ? 'sí' : 'no') . PHP_EOL;
    print_r($usuarios->buscarPorId($id));

    // --- Eliminar ------------------------------------------------------------
    echo "Eliminado: " . ($usuarios->eliminar($id) ? 'sí' : 'no') . PHP_EOL;
} catch (DomainException | InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

// --- Email duplicado: el modelo lanza DomainException ------------------------
try {
    $usuarios->crear('Duplicada', 'ana@example.com', 'secreto123');
} catch (DomainException $e) {
    echo "Esperado -> " . $e->getMessage() . PHP_EOL;
}

// --- Contraseña demasiado corta: InvalidArgumentException --------------------
try {
    $usuarios->crear('Corta', 'corta' . time() . '@example.com', '123');
} catch (InvalidArgumentException $e) {
    echo "Esperado -> " . $e->getMessage() . PHP_EOL;
}

// --- Usuarios de prueba del schema.sql: contraseña "secreto123" --------------
echo "Login de ana@example.com: " . ($usuarios->autenticar('ana@example.com', 'secreto123') ? 'sí' : 'no') . PHP_EOL;

// --- Usar el modelo dentro de OTRA clase (inyección de dependencias) ---------
final class InformeUsuarios
{
    public function __construct(private Usuario $usuarios)
    {
    }

    public function resumen(): string
    {
        $mayores = array_filter($this->usuarios->todos(), fn ($u) => ($u['edad'] ?? 0) >= 30);
        return sprintf('%d usuarios, %d con 30 años o más', $this->usuarios->contar(), count($mayores));
    }
}

echo (new InformeUsuarios($usuarios))->resumen() . PHP_EOL;
