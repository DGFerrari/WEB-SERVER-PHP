<?php
/**
 * Front controller: ÚNICO punto de entrada de la aplicación web.
 *
 * Aquí (y solo aquí) se "monta" todo:
 *   Database -> mysqli -> Usuario(modelo) -> UsuarioController
 *
 * Es idéntico a pdo/mvc/index.php: solo cambia lo que hay dentro de
 * config/Database.php y models/.
 *
 * Ejecutar desde la carpeta mysqli/mvc:
 *   php -S localhost:8000
 * y abrir http://localhost:8000
 */
declare(strict_types=1);

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/controllers/UsuarioController.php';
// En proyectos reales: Composer + autoload PSR-4 en vez de require_once.

try {
    $db         = Database::getConexion();
    $controller = new UsuarioController(new Usuario($db));

    $accion = $_GET['accion'] ?? 'index';
    $esPost = $_SERVER['REQUEST_METHOD'] === 'POST';

    // Las acciones que MODIFICAN datos solo se aceptan por POST.
    match (true) {
        $accion === 'index'                => $controller->index(),
        $accion === 'ver'                  => $controller->ver((int) ($_GET['id'] ?? 0)),
        $accion === 'guardar'  && $esPost  => $controller->guardar($_POST),
        $accion === 'eliminar' && $esPost  => $controller->eliminar((int) ($_POST['id'] ?? 0)),
        default => (function (): void {
            http_response_code(404);
            echo 'Página no encontrada';
        })(),
    };
} catch (Throwable $e) {
    // Cualquier error no controlado: se registra y se muestra algo genérico.
    error_log((string) $e);
    http_response_code(500);
    echo 'Ha ocurrido un error. Inténtalo más tarde.';
}
