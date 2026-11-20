<?php
require_once __DIR__ . '/../controller/auth.php';

requerirRol('administrador');

$mensajes = [
    'creado'    => 'Usuario creado correctamente.',
    'editado'   => 'Usuario actualizado correctamente.',
    'eliminado' => 'Usuario eliminado correctamente.',
];
$errores = [
    'propio'     => 'No puedes eliminarte, quitarte el rol de administrador ni desactivar tu propia cuenta.',
    'relaciones' => 'No se puede eliminar: el usuario tiene clientes u oportunidades asignados. Deshabilítalo en su lugar.',
    'noexiste'   => 'El usuario no existe.',
    'bd'         => 'Error al acceder a la base de datos.',
];
$ok    = $mensajes[$_GET['ok'] ?? ''] ?? null;
$error = $errores[$_GET['error'] ?? ''] ?? null;

$usuarios = UsuariosBD::listar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de usuarios</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <?php require __DIR__ . '/menu.php'; ?>

    <h1>Gestión de usuarios</h1>

    <?php if ($ok): ?>
        <p style="color: green;"><?= e($ok) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <p><a href="usuario_form.php">+ Nuevo usuario</a></p>

    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= e($u['id_usuario']) ?></td>
                    <td><?= e($u['nombre']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['rol']) ?></td>
                    <td><?= e($u['estado']) ?></td>
                    <td><?= e($u['fecha_registro']) ?></td>
                    <td>
                        <a href="usuario_form.php?id=<?= e($u['id_usuario']) ?>">Editar</a>

                        <?php if ((int) $u['id_usuario'] !== $_SESSION['id_usuario']): ?>
                            <!-- Eliminar por POST (nunca por enlace GET) y con token CSRF -->
                            <form method="POST" action="../controller/usuarios_controller.php" style="display: inline;"
                                  onsubmit="return confirm(<?= e(json_encode('¿Eliminar a ' . $u['nombre'] . '?', JSON_UNESCAPED_UNICODE)) ?>);">
                                <?= campoCSRF() ?>
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_usuario" value="<?= e($u['id_usuario']) ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        <?php else: ?>
                            (tú)
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$usuarios): ?>
                <tr><td colspan="7">No hay usuarios.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
