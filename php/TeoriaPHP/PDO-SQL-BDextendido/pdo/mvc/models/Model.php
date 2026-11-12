<?php
/**
 * Model: clase BASE con los métodos comunes a todas las tablas.
 *
 * Cada modelo concreto (Usuario, Producto, Pedido...) hereda de aquí y
 * solo define su tabla, sus columnas y sus métodos específicos.
 * Así no repites buscarPorId(), todos(), eliminar()... en cada modelo.
 */
declare(strict_types=1);

abstract class Model
{
    /** Nombre de la tabla. Lo define cada modelo hijo. */
    protected string $tabla;

    /** Columnas a seleccionar (mejor explícitas que SELECT *). */
    protected string $columnas = '*';

    protected string $clavePrimaria = 'id';

    // PHP 8: "constructor property promotion" -> declara y asigna $this->db.
    public function __construct(protected PDO $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function todos(): array
    {
        // $this->tabla y $this->columnas los define el PROGRAMADOR, no el
        // usuario, por eso es seguro interpolarlos. Los VALORES van con ":".
        return $this->db
            ->query("SELECT {$this->columnas} FROM {$this->tabla} ORDER BY {$this->clavePrimaria}")
            ->fetchAll();
    }

    /** @return array<string, mixed>|null  null si no existe */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT {$this->columnas} FROM {$this->tabla} WHERE {$this->clavePrimaria} = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;   // fetch() devuelve false si no hay fila
    }

    public function contar(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->tabla}")->fetchColumn();
    }

    /** @return bool true si se borró alguna fila */
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->tabla} WHERE {$this->clavePrimaria} = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
