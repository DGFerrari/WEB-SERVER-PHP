<?php
require_once __DIR__ . '/../controller/auth.php';
require_once __DIR__ . '/../model/clientesBD.php';

requerirLogin();

$mensajes = [
    'creado'    => 'Cliente creado correctamente.',
    'editado'   => 'Cliente actualizado correctamente.',
    'eliminado' => 'Cliente eliminado correctamente.',
];
$errores = [
    'noexiste' => 'El cliente no existe.',
    'permiso'  => 'No tienes permiso sobre ese cliente.',
    'bd'       => 'Error al acceder a la base de datos.',
];
$ok    = $mensajes[$_GET['ok'] ?? ''] ?? null;
$error = $errores[$_GET['error'] ?? ''] ?? null;

// Busqueda por nombre o empresa
$busqueda = trim($_GET['q'] ?? '');

// El admin ve todos; el vendedor solo los suyos
$clientes = ClientesBD::listar($busqueda, esAdmin() ? null : $_SESSION['id_usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <?php require __DIR__ . '/menu.php'; ?>

    <h1><?= esAdmin() ? 'Clientes' : 'Mis clientes' ?></h1>

    <?php if ($ok): ?>
        <p style="color: green;"><?= e($ok) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <!-- Busqueda por GET: la URL se puede guardar o compartir -->
    <form method="GET" action="clientes.php">
        <input type="search" name="q" placeholder="Buscar por nombre o empresa"
               value="<?= e($busqueda) ?>">
        <button type="submit">Buscar</button>
        <?php if ($busqueda !== ''): ?>
            <a href="clientes.php">Limpiar</a>
        <?php endif; ?>
    </form>

    <p><a href="cliente_form.php">+ Nuevo cliente</a></p>

    <p><?= count($clientes) ?> cliente(s)<?= $busqueda !== '' ? ' para "' . e($busqueda) . '"' : '' ?></p>

    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Empresa</th>
                <th>Registro</th>
                <?php if (esAdmin()): ?><th>Responsable</th><?php endif; ?>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><?= e($c['id_cliente']) ?></td>
                    <td><?= e($c['nombre_completo']) ?></td>
                    <td><?= e($c['email'] ?? '-') ?></td>
                    <td><?= e($c['tlf'] ?? '-') ?></td>
                    <td><?= e($c['empresa'] ?? '-') ?></td>
                    <td><?= e($c['fecha_registro']) ?></td>
                    <?php if (esAdmin()): ?><td><?= e($c['nombre_responsable']) ?></td><?php endif; ?>
                    <td>
                        <a href="oportunidades.php?id_cliente=<?= e($c['id_cliente']) ?>">Oportunidades</a>
                        <a href="tareas.php?estado=todas&id_cliente=<?= e($c['id_cliente']) ?>">Tareas</a>

                        <?php if (puedeGestionar($c)): ?>
                            <a href="cliente_form.php?id=<?= e($c['id_cliente']) ?>">Editar</a>

                            <form method="POST" action="../controller/clientes_controller.php" style="display: inline;"
                                  onsubmit="return confirm(<?= e(json_encode('¿Eliminar a ' . $c['nombre_completo'] . "?\nSe borrarán también sus oportunidades y tareas.", JSON_UNESCAPED_UNICODE)) ?>);">
                                <?= campoCSRF() ?>
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_cliente" value="<?= e($c['id_cliente']) ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$clientes): ?>
                <tr><td colspan="<?= esAdmin() ? 8 : 7 ?>">No hay clientes.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
