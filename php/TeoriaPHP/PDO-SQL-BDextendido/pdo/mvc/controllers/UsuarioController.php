<?php
/**
 * UsuarioController: une la petición HTTP, el modelo y la vista.
 *
 *  1. Recibe y VALIDA los datos de entrada.
 *  2. Llama a los métodos del modelo.
 *  3. Elige qué vista mostrar y le pasa los datos.
 *
 * Fíjate en que este archivo NO contiene SQL ni nada de PDO:
 * el mismo controlador sirve tal cual para la versión MySQLi.
 */
declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';

final class UsuarioController
{
    public function __construct(private Usuario $usuarios)
    {
    }

    /** GET index.php */
    public function index(array $errores = [], array $antiguo = []): void
    {
        $pagina   = max(1, (int) ($_GET['pagina'] ?? 1));
        $usuarios = $this->usuarios->paginar($pagina, 10);
        $total    = $this->usuarios->contar();

        $this->vista('usuarios/index', compact('usuarios', 'total', 'pagina', 'errores', 'antiguo'));
    }

    /** GET index.php?accion=ver&id=1 */
    public function ver(int $id): void
    {
        $usuario = $this->usuarios->buscarPorId($id);

        if ($usuario === null) {
            http_response_code(404);
            echo 'Usuario no encontrado';
            return;
        }

        $this->vista('usuarios/ver', compact('usuario'));
    }

    /** POST index.php?accion=guardar */
    public function guardar(array $datos): void
    {
        // 1) Validar ENTRADA (las sentencias preparadas evitan la inyección,
        //    pero no validan que los datos tengan sentido).
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $email  = trim((string) ($datos['email'] ?? ''));
        $edadRaw = trim((string) ($datos['edad'] ?? ''));
        $edad   = $edadRaw === '' ? null : filter_var($edadRaw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 150],
        ]);
        // ⚠ La contraseña NO se pasa por trim(): los espacios pueden ser intencionados.
        $password     = (string) ($datos['password'] ?? '');
        $confirmacion = (string) ($datos['password_confirmacion'] ?? '');

        $errores = [];
        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $errores[] = 'El nombre es obligatorio (máx. 100 caracteres).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido.';
        }
        if ($edad === false) {
            $errores[] = 'La edad debe ser un número entre 0 y 150.';
        }
        if ($error = Password::validar($password)) {
            $errores[] = $error;
        } elseif (!hash_equals($password, $confirmacion)) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        // 2) Llamar al modelo (él se encarga de hashear la contraseña)
        if ($errores === []) {
            try {
                $this->usuarios->crear($nombre, $email, $password, $edad);

                // 3) Patrón PRG (Post/Redirect/Get): evita reenviar el
                //    formulario al recargar la página.
                header('Location: index.php');
                return;
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            }
        }

        // ✔ La contraseña NO se devuelve al formulario: el usuario la reescribe.
        $this->index($errores, compact('nombre', 'email') + ['edad' => $edadRaw]);
    }

    /** POST index.php?accion=eliminar */
    public function eliminar(int $id): void
    {
        $this->usuarios->eliminar($id);
        header('Location: index.php');
    }

    /** Carga una vista y le pasa variables. */
    private function vista(string $nombre, array $datos = []): void
    {
        extract($datos, EXTR_SKIP);   // ['usuarios' => ...] -> $usuarios
        require __DIR__ . "/../views/{$nombre}.php";
    }
}
