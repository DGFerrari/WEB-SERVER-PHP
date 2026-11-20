<?php
/**
 * Vista RESUMEN para el administrador:
 *  - Total de clientes registrados
 *  - Total de oportunidades por estado
 *  - Total de actividades (tareas) pendientes
 */
require_once __DIR__ . '/../controller/auth.php';
require_once __DIR__ . '/../model/resumenBD.php';

requerirRol('administrador');

$resumen = ResumenBD::obtener();

function euros($valor) {
    return number_format((float) $valor, 2, ',', '.') . ' €';
}

// Porcentaje de una cantidad sobre el total (sin dividir entre 0)
function porcentaje($parte, $total) {
    return $total > 0 ? round($parte * 100 / $total) . ' %' : '0 %';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <?php require __DIR__ . '/menu.php'; ?>

    <h1>Resumen general</h1>
    <p><small>Datos a <?= e(date('d/m/Y H:i')) ?></small></p>

    <?php if ($resumen === null): ?>
        <p style="color: red;">No se pudo conectar con la base de datos.</p>
    <?php else: ?>

        <!-- ===== Clientes ===== -->
        <h2>Clientes</h2>
        <p>
            Total de clientes registrados: <strong><?= e($resumen['total_clientes']) ?></strong>
            — <a href="clientes.php">Ver clientes</a>
        </p>

        <!-- ===== Oportunidades por estado ===== -->
        <h2>Oportunidades por estado</h2>
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Cantidad</th>
                    <th>%</th>
                    <th>Valor estimado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resumen['oportunidades'] as $estado => $op): ?>
                    <tr>
                        <td><?= e(ucfirst($estado)) ?></td>
                        <td style="text-align: right;"><?= e($op['cantidad']) ?></td>
                        <td style="text-align: right;"><?= porcentaje($op['cantidad'], $resumen['total_oportunidades']) ?></td>
                        <td style="text-align: right;"><?= euros($op['valor']) ?></td>
                        <td><a href="oportunidades.php?estado=<?= e($estado) ?>">Ver</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th style="text-align: right;"><?= e($resumen['total_oportunidades']) ?></th>
                    <th></th>
                    <th style="text-align: right;"><?= euros($resumen['valor_total']) ?></th>
                    <th><a href="oportunidades.php">Ver</a></th>
                </tr>
            </tfoot>
        </table>

        <!-- ===== Tareas pendientes ===== -->
        <h2>Actividades</h2>
        <p>
            Total de tareas pendientes: <strong><?= e($resumen['tareas_pendientes']) ?></strong>
            <?php if ($resumen['tareas_vencidas'] > 0): ?>
                <span style="color: red;">(<?= e($resumen['tareas_vencidas']) ?> vencida(s))</span>
            <?php endif; ?>
            — <a href="tareas.php">Ver pendientes por usuario</a>
        </p>

    <?php endif; ?>

</body>
</html>
