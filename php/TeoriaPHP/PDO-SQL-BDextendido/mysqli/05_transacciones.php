<?php
/**
 * TEMA 5 (MySQLi): Transacciones ("todo o nada")
 *
 * Ejecutar:  php mysqli/05_transacciones.php
 */
declare(strict_types=1);

/** @var mysqli $db */
$db = require __DIR__ . '/conexion.php';

function transferir(mysqli $db, int $origen, int $destino, float $importe): void
{
    if ($importe <= 0) {
        throw new InvalidArgumentException('El importe debe ser positivo.');
    }

    $db->begin_transaction();
    try {
        // FOR UPDATE bloquea la fila hasta el commit
        $saldo = $db->execute_query('SELECT saldo FROM cuentas WHERE id = ? FOR UPDATE', [$origen])
            ->fetch_column();

        if ($saldo === false) {
            throw new RuntimeException("La cuenta $origen no existe.");
        }
        if ((float) $saldo < $importe) {
            throw new RuntimeException('Saldo insuficiente.');
        }

        $db->execute_query('UPDATE cuentas SET saldo = saldo - ? WHERE id = ?', [$importe, $origen]);

        $db->execute_query('UPDATE cuentas SET saldo = saldo + ? WHERE id = ?', [$importe, $destino]);
        if ($db->affected_rows === 0) {
            throw new RuntimeException("La cuenta $destino no existe.");
        }

        $db->commit();        // ✔ se guardan los cambios
    } catch (Throwable $e) {
        $db->rollback();      // ✘ se deshace TODO
        throw $e;
    }
}

function mostrarSaldos(mysqli $db): void
{
    foreach ($db->query('SELECT id, titular, saldo FROM cuentas') as $c) {
        echo "   [{$c['id']}] {$c['titular']}: {$c['saldo']} €" . PHP_EOL;
    }
}

echo "Saldos iniciales:" . PHP_EOL;
mostrarSaldos($db);

foreach ([[1, 2, 50.0], [1, 2, 99999.0], [1, 999, 10.0]] as [$de, $a, $imp]) {
    try {
        transferir($db, $de, $a, $imp);
        echo "✔ Transferidos $imp € de $de a $a" . PHP_EOL;
    } catch (RuntimeException $e) {
        echo "✘ Transferencia de $imp € de $de a $a cancelada: {$e->getMessage()}" . PHP_EOL;
    }
}

echo "Saldos finales:" . PHP_EOL;
mostrarSaldos($db);
