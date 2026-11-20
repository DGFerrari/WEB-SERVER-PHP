<?php
require_once __DIR__ . '/../controller/auth.php';
require_once __DIR__ . '/../model/tareasBD.php';

// Pagina protegida: sin sesion se vuelve al login
requerirLogin();

$error = ($_GET['error'] ?? '') === 'permiso'
    ? 'No tienes permiso para acceder a esa página.'
    : null;

// Mis tareas pendientes (ordenadas por fecha, las vencidas primero)
$misPendientes = TareasBD::listar([
    'estado'     => 'pendiente',
    'id_usuario' => $_SESSION['id_usuario'],
]);
$ahora = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel CRM</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <?php require __DIR__ . '/menu.php'; ?>

    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <h1>Bienvenido, <?= e($_SESSION['nombre']) ?></h1>
    <p>Rol: <?= e($_SESSION['rol']) ?></p>

    <p><a href="clientes.php"><?= esAdmin() ? 'Gestionar clientes' : 'Mis clientes' ?></a></p>
    <p><a href="oportunidades.php"><?= esAdmin() ? 'Gestionar oportunidades' : 'Mis oportunidades' ?></a></p>
    <p><a href="tareas.php"><?= esAdmin() ? 'Tareas (pendientes por usuario)' : 'Mis tareas' ?></a></p>

    <?php if (esAdmin()): ?>
        <p><a href="resumen.php"><strong>Ver resumen general</strong></a></p>
        <p><a href="usuarios.php">Gestionar usuarios</a></p>
    <?php endif; ?>

    <h2>Mis tareas pendientes (<?= count($misPendientes) ?>)</h2>
    <?php if (!$misPendientes): ?>
        <p>No tienes tareas pendientes. 🎉</p>
    <?php else: ?>
        <ul>
            <?php foreach (array_slice($misPendientes, 0, 5) as $t): ?>
                <?php $vencida = $t['fecha'] < $ahora; ?>
                <li style="<?= $vencida ? 'color: red;' : '' ?>">
                    <strong><?= e(date('d/m/Y H:i', strtotime($t['fecha']))) ?></strong><?= $vencida ? ' (vencida)' : '' ?>
                    — <?= e($t['descripcion']) ?>
                    <small>[<?= e($t['titulo_oportunidad']) ?> · <?= e($t['nombre_cliente']) ?>]</small>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if (count($misPendientes) > 5): ?>
            <p><a href="tareas.php">Ver todas</a></p>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>
