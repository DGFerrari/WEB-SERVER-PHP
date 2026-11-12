<?php
/**
 * Password: TODO lo relacionado con hashear y verificar contraseñas.
 *
 * ¿Por qué una clase aparte y no meterlo en el modelo?
 *  - Si mañana cambias de algoritmo o de coste, lo cambias SOLO aquí.
 *  - Cualquier modelo (Usuario, Admin, Cliente...) puede reutilizarla.
 *  - No depende de la base de datos: es idéntica en PDO y MySQLi.
 */
declare(strict_types=1);

final class Password
{
    public const LONGITUD_MINIMA = 8;
    public const BYTES_MAXIMOS   = 72;   // bcrypt ignora lo que pase de 72 bytes

    /**
     * Hash bcrypt válido de una cadena aleatoria. Se usa para "gastar" el mismo
     * tiempo cuando el email no existe y así no revelar qué emails hay registrados.
     */
    private const HASH_FALSO = '$2y$12$aDc9wlxEU984wLiwgGR/yuwsJVwPpAOxKnyh6/rAHJENVQIW3eAKi';

    /**
     * @param string|int|null $algoritmo PASSWORD_DEFAULT (recomendado), PASSWORD_BCRYPT o PASSWORD_ARGON2ID
     * @param array<string, int> $opciones p. ej. ['cost' => 12] para bcrypt
     */
    public function __construct(
        private string|int|null $algoritmo = PASSWORD_DEFAULT,
        private array $opciones = [],
    ) {
    }

    /**
     * Reglas de la contraseña. Estática porque es una función pura:
     * el controlador la usa para dar mensajes al usuario.
     *
     * @return string|null mensaje de error, o null si es válida
     */
    public static function validar(string $plano): ?string
    {
        return match (true) {
            mb_strlen($plano) < self::LONGITUD_MINIMA =>
                'La contraseña debe tener al menos ' . self::LONGITUD_MINIMA . ' caracteres.',
            strlen($plano) > self::BYTES_MAXIMOS =>
                'La contraseña es demasiado larga (máx. ' . self::BYTES_MAXIMOS . ' bytes).',
            default => null,
        };
    }

    /** @throws InvalidArgumentException si la contraseña no cumple las reglas */
    public function hash(string $plano): string
    {
        if ($error = self::validar($plano)) {
            throw new InvalidArgumentException($error);
        }

        return password_hash($plano, $this->algoritmo, $this->opciones);
    }

    public function verificar(string $plano, string $hash): bool
    {
        return password_verify($plano, $hash);
    }

    /** true si el hash se creó con otro algoritmo/coste y conviene regenerarlo */
    public function necesitaRehash(string $hash): bool
    {
        return password_needs_rehash($hash, $this->algoritmo, $this->opciones);
    }

    /** Hace el mismo trabajo que verificar() pero sin usuario real. */
    public function simularVerificacion(string $plano): void
    {
        password_verify($plano, self::HASH_FALSO);
    }
}
