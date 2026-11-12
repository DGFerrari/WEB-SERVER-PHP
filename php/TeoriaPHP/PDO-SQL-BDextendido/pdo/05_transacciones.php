<?php
/**
 * TEMA 5 (PDO): Transacciones
 *
 * Una transacción agrupa varias operaciones en un "todo o nada".
 * Ejemplo clásico: transferir dinero. Si falla el ingreso, el cargo
 * también debe deshacerse.
 *
 * Ejecutar:  php pdo/05_transacciones.php
 */
declare(strict_types=1);

/** @var PDO $pdo */
$pdo = require __DIR__ . '/conexion.php';

function transferir(PDO $pdo, int $origen, int $destino, float $importe): void
{
    if ($importe <= 0) {
        throw new InvalidArgumentException('El importe debe ser positivo.');
    }

    $pdo->beginTransaction();
    try {
        // FOR UPDATE bloquea la fila hasta el commit: evita que dos
        // transferencias simultáneas lean el mismo saldo.
        $stmt = $pdo->prepare('SELECT saldo FROM cuentas WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $origen]);
        $saldo = $stmt->fetchColumn();

        if ($saldo === false) {
            throw new RuntimeException("La cuenta $origen no existe.");
        }
        if ((float) $saldo < $importe) {
            throw new RuntimeException('Saldo insuficiente.');
        }

        $pdo->prepare('UPDATE cuentas SET saldo = saldo - :imp WHERE id = :id')
            ->execute(['imp' => $importe, 'id' => $origen]);

        $stmt = $pdo->prepare('UPDATE cuentas SET saldo = saldo + :imp WHERE id = :id');
        $stmt->execute(['imp' => $importe, 'id' => $destino]);
        if ($stmt->rowCount() === 0) {
            throw new RuntimeException("La cuenta $destino no existe.");
        }

        $pdo->commit();       // ✔ todo bien: se guardan los cambios
    } catch (Throwable $e) {
        $pdo->rollBack();     // ✘ algo falló: se deshace TODO
        throw $e;             // el que llama decide qué mostrar
    }
}

function mostrarSaldos(PDO $pdo): void
{
    foreach ($pdo->query('SELECT id, titular, saldo FROM cuentas') as $c) {
        echo "   [{$c['id']}] {$c['titular']}: {$c['saldo']} €" . PHP_EOL;
    }
}

echo "Saldos iniciales:" . PHP_EOL;
mostrarSaldos($pdo);

foreach ([[1, 2, 50.0], [1, 2, 99999.0], [1, 999, 10.0]] as [$de, $a, $imp]) {
    try {
        transferir($pdo, $de, $a, $imp);
        echo "✔ Transferidos $imp € de $de a $a" . PHP_EOL;
    } catch (RuntimeException $e) {
        echo "✘ Transferencia de $imp € de $de a $a cancelada: {$e->getMessage()}" . PHP_EOL;
    }
}

echo "Saldos finales:" . PHP_EOL;
mostrarSaldos($pdo);
