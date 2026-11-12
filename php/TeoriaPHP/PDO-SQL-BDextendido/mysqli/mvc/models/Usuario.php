<?php
/**
 * Usuario: modelo de la tabla "usuarios" (versión MySQLi).
 *
 * Responsabilidad del MODELO: hablar con la base de datos y nada más.
 *  ✔ Ejecuta SQL y devuelve datos (arrays, int, bool...).
 *  ✘ No hace echo, no lee $_POST/$_GET, no genera HTML.
 *
 * Contraseñas:
 *  - El modelo recibe la contraseña en texto plano y la hashea ÉL MISMO.
 *  - password_hash NO está en $columnas: ningún método de lectura lo devuelve.
 */
declare(strict_types=1);

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../services/Password.php';

final class Usuario extends Model
{
    protected string $tabla = 'usuarios';
    protected string $columnas = 'id, nombre, email, edad, creado_en';   // ✔ sin password_hash

    private const ERROR_DUPLICADO = 1062;

    public function __construct(mysqli $db, private Password $passwords = new Password())
    {
        parent::__construct($db);
    }

    /** @return array<string, mixed>|null */
    public function buscarPorEmail(string $email): ?array
    {
        return $this->db
            ->execute_query("SELECT {$this->columnas} FROM usuarios WHERE email = ?", [$email])
            ->fetch_assoc();
    }

    /**
     * @param string $password contraseña en texto plano (se guarda hasheada)
     * @throws InvalidArgumentException si la contraseña no es válida
     * @throws DomainException si el email ya existe
     */
    public function crear(string $nombre, string $email, string $password, ?int $edad = null): int
    {
        $hash = $this->passwords->hash($password);

        // bind_param para tener tipos explícitos (s = string, i = int).
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash, edad) VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('sssi', $nombre, $email, $hash, $edad);

        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            // Traducimos el error de MySQL a un error "de negocio".
            if ($e->getCode() === self::ERROR_DUPLICADO) {
                throw new DomainException('El email ya está registrado.', 0, $e);
            }
            throw $e;
        }

        return (int) $stmt->insert_id;
    }

    /**
     * Actualiza el perfil. La contraseña se cambia aparte con cambiarPassword().
     *
     * @throws DomainException si el email ya lo usa otro usuario
     */
    public function actualizar(int $id, string $nombre, string $email, ?int $edad): bool
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET nombre = ?, email = ?, edad = ? WHERE id = ?');
        $stmt->bind_param('ssii', $nombre, $email, $edad, $id);

        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === self::ERROR_DUPLICADO) {
                throw new DomainException('El email ya está registrado.', 0, $e);
            }
            throw $e;
        }

        return $stmt->affected_rows > 0;
    }

    /**
     * Comprueba email + contraseña (login).
     *
     * @return array<string, mixed>|null el usuario SIN el hash, o null si fallan las credenciales
     */
    public function autenticar(string $email, string $password): ?array
    {
        $usuario = $this->db
            ->execute_query("SELECT {$this->columnas}, password_hash FROM usuarios WHERE email = ?", [$email])
            ->fetch_assoc();

        if ($usuario === null) {
            // Mismo tiempo de respuesta exista o no el email (evita enumerar usuarios)
            $this->passwords->simularVerificacion($password);
            return null;
        }

        if (!$this->passwords->verificar($password, $usuario['password_hash'])) {
            return null;
        }

        // Regenerar hashes antiguos aprovechando que tenemos la contraseña en claro
        if ($this->passwords->necesitaRehash($usuario['password_hash'])) {
            $this->guardarHash((int) $usuario['id'], $this->passwords->hash($password));
        }

        unset($usuario['password_hash']);
        return $usuario;
    }

    /**
     * @return bool false si la contraseña actual no es correcta o el usuario no existe
     * @throws InvalidArgumentException si la nueva contraseña no es válida
     */
    public function cambiarPassword(int $id, string $actual, string $nueva): bool
    {
        $hash = $this->db
            ->execute_query('SELECT password_hash FROM usuarios WHERE id = ?', [$id])
            ->fetch_column();

        if ($hash === false || !$this->passwords->verificar($actual, $hash)) {
            return false;
        }

        $this->guardarHash($id, $this->passwords->hash($nueva));
        return true;
    }

    /** @return array<int, array<string, mixed>> */
    public function paginar(int $pagina, int $porPagina = 10): array
    {
        $offset = (max(1, $pagina) - 1) * $porPagina;

        $stmt = $this->db->prepare("SELECT {$this->columnas} FROM usuarios ORDER BY id LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $porPagina, $offset);   // LIMIT/OFFSET como enteros
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Método privado: solo lo usa la propia clase. */
    private function guardarHash(int $id, string $hash): void
    {
        $this->db->execute_query('UPDATE usuarios SET password_hash = ? WHERE id = ?', [$hash, $id]);
    }
}
