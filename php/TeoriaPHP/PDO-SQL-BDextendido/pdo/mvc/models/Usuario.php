<?php
/**
 * Usuario: modelo de la tabla "usuarios".
 *
 * Responsabilidad del MODELO: hablar con la base de datos y nada más.
 *  ✔ Ejecuta SQL y devuelve datos (arrays, int, bool...).
 *  ✘ No hace echo, no lee $_POST/$_GET, no genera HTML.
 *
 * Hereda de Model: todos(), buscarPorId(), contar(), eliminar().
 *
 * Contraseñas:
 *  - El modelo recibe la contraseña en texto plano y la hashea ÉL MISMO,
 *    así es imposible guardar una contraseña sin hashear por descuido.
 *  - La columna password_hash NO está en $columnas: ningún método de
 *    lectura (todos, buscarPorId, paginar...) devuelve nunca el hash.
 */
declare(strict_types=1);

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../services/Password.php';

final class Usuario extends Model
{
    protected string $tabla = 'usuarios';
    protected string $columnas = 'id, nombre, email, edad, creado_en';   // ✔ sin password_hash

    private const ERROR_DUPLICADO = 1062;

    // PHP 8.1+: "new" como valor por defecto. Puedes inyectar otro Password
    // (p. ej. con más coste) sin tocar esta clase.
    public function __construct(PDO $db, private Password $passwords = new Password())
    {
        parent::__construct($db);
    }

    /** @return array<string, mixed>|null */
    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT {$this->columnas} FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Inserta un usuario y devuelve su nuevo id.
     *
     * @param string $password contraseña en texto plano (se guarda hasheada)
     * @throws InvalidArgumentException si la contraseña no es válida
     * @throws DomainException si el email ya existe
     */
    public function crear(string $nombre, string $email, string $password, ?int $edad = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash, edad)
             VALUES (:nombre, :email, :hash, :edad)'
        );

        try {
            $stmt->execute([
                'nombre' => $nombre,
                'email'  => $email,
                'hash'   => $this->passwords->hash($password),
                'edad'   => $edad,
            ]);
        } catch (PDOException $e) {
            // Traducimos el error técnico de MySQL a un error "de negocio".
            // Así el controlador no necesita saber nada de PDO ni de MySQL.
            if (($e->errorInfo[1] ?? null) === self::ERROR_DUPLICADO) {
                throw new DomainException('El email ya está registrado.', 0, $e);
            }
            throw $e;
        }

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza los datos del perfil. La contraseña se cambia aparte con cambiarPassword().
     *
     * @return bool true si la fila cambió (false si no existe o los datos eran iguales)
     * @throws DomainException si el email ya lo usa otro usuario
     */
    public function actualizar(int $id, string $nombre, string $email, ?int $edad): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET nombre = :nombre, email = :email, edad = :edad WHERE id = :id'
        );

        try {
            $stmt->execute(['id' => $id, 'nombre' => $nombre, 'email' => $email, 'edad' => $edad]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === self::ERROR_DUPLICADO) {
                throw new DomainException('El email ya está registrado.', 0, $e);
            }
            throw $e;
        }

        return $stmt->rowCount() > 0;
    }

    /**
     * Comprueba email + contraseña (login).
     *
     * @return array<string, mixed>|null el usuario SIN el hash, o null si fallan las credenciales
     */
    public function autenticar(string $email, string $password): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT {$this->columnas}, password_hash FROM usuarios WHERE email = :email"
        );
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario === false) {
            // Mismo tiempo de respuesta exista o no el email (evita enumerar usuarios)
            $this->passwords->simularVerificacion($password);
            return null;
        }

        if (!$this->passwords->verificar($password, $usuario['password_hash'])) {
            return null;
        }

        // Si el hash es de un algoritmo/coste antiguo, se regenera ahora que
        // tenemos la contraseña en claro (solo es posible en el login).
        if ($this->passwords->necesitaRehash($usuario['password_hash'])) {
            $this->guardarHash((int) $usuario['id'], $this->passwords->hash($password));
        }

        unset($usuario['password_hash']);
        return $usuario;
    }

    /**
     * Cambia la contraseña pidiendo la actual (lo correcto en un "cambiar contraseña").
     *
     * @return bool false si la contraseña actual no es correcta o el usuario no existe
     * @throws InvalidArgumentException si la nueva contraseña no es válida
     */
    public function cambiarPassword(int $id, string $actual, string $nueva): bool
    {
        $stmt = $this->db->prepare('SELECT password_hash FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $hash = $stmt->fetchColumn();

        if ($hash === false || !$this->passwords->verificar($actual, $hash)) {
            return false;
        }

        $this->guardarHash($id, $this->passwords->hash($nueva));
        return true;
    }

    /** @return array<int, array<string, mixed>> */
    public function paginar(int $pagina, int $porPagina = 10): array
    {
        $pagina = max(1, $pagina);

        $stmt = $this->db->prepare(
            "SELECT {$this->columnas} FROM usuarios ORDER BY id LIMIT :limite OFFSET :offset"
        );
        // LIMIT/OFFSET necesitan tipo entero explícito
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($pagina - 1) * $porPagina, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** Método privado: solo lo usa la propia clase. */
    private function guardarHash(int $id, string $hash): void
    {
        $this->db
            ->prepare('UPDATE usuarios SET password_hash = :hash WHERE id = :id')
            ->execute(['hash' => $hash, 'id' => $id]);
    }
}
