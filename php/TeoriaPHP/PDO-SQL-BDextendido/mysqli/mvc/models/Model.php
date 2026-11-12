<?php
/**
 * Model: clase BASE con los métodos comunes a todas las tablas (versión MySQLi).
 *
 * Tiene EXACTAMENTE los mismos métodos públicos que la versión PDO.
 * Por eso el controlador y las vistas son idénticos en ambas carpetas:
 * esa es la ventaja de encapsular el acceso a datos en el modelo.
 */
declare(strict_types=1);

abstract class Model
{
    protected string $tabla;
    protected string $columnas = '*';
    protected string $clavePrimaria = 'id';

    public function __construct(protected mysqli $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function todos(): array
    {
        // Tabla y columnas las fija el programador: interpolarlas es seguro.
        return $this->db
            ->query("SELECT {$this->columnas} FROM {$this->tabla} ORDER BY {$this->clavePrimaria}")
            ->fetch_all(MYSQLI_ASSOC);
    }

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->db
            ->execute_query(
                "SELECT {$this->columnas} FROM {$this->tabla} WHERE {$this->clavePrimaria} = ?",
                [$id]
            )
            ->fetch_assoc();   // ya devuelve null si no hay fila
    }

    public function contar(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->tabla}")->fetch_column();
    }

    public function eliminar(int $id): bool
    {
        $this->db->execute_query("DELETE FROM {$this->tabla} WHERE {$this->clavePrimaria} = ?", [$id]);

        return $this->db->affected_rows > 0;
    }
}
