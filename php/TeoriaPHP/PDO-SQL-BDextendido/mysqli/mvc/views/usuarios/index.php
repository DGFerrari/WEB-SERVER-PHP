<?php
/**
 * Vista: listado + formulario.
 * La vista solo MUESTRA datos. Todo lo que se imprime pasa por
 * htmlspecialchars() para evitar XSS.
 *
 * @var array<int, array<string, mixed>> $usuarios
 * @var int $total
 * @var int $pagina
 * @var string[] $errores
 * @var array<string, string> $antiguo
 */
$e = static fn (mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
</head>
<body>
<main>
    <h1>Usuarios (<?= $total ?>)</h1>

    <table>
        <caption>Listado de usuarios, página <?= $pagina ?></caption>
        <thead>
        <tr><th scope="col">ID</th><th scope="col">Nombre</th><th scope="col">Email</th><th scope="col">Edad</th><th scope="col">Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= $e($u['id']) ?></td>
                <td><a href="index.php?accion=ver&amp;id=<?= $e($u['id']) ?>"><?= $e($u['nombre']) ?></a></td>
                <td><?= $e($u['email']) ?></td>
                <td><?= $e($u['edad'] ?? '-') ?></td>
                <td>
                    <form method="post" action="index.php?accion=eliminar">
                        <input type="hidden" name="id" value="<?= $e($u['id']) ?>">
                        <button type="submit" aria-label="Eliminar a <?= $e($u['nombre']) ?>">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Nuevo usuario</h2>

    <?php if ($errores): ?>
        <div role="alert">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= $e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?accion=guardar">
        <p>
            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" required maxlength="100" value="<?= $e($antiguo['nombre'] ?? '') ?>">
        </p>
        <p>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required value="<?= $e($antiguo['email'] ?? '') ?>">
        </p>
        <p>
            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" required minlength="8"
                   autocomplete="new-password" aria-describedby="password-ayuda">
            <small id="password-ayuda">Mínimo 8 caracteres.</small>
        </p>
        <p>
            <label for="password_confirmacion">Repite la contraseña</label>
            <input id="password_confirmacion" name="password_confirmacion" type="password" required
                   minlength="8" autocomplete="new-password">
        </p>
        <p>
            <label for="edad">Edad (opcional)</label>
            <input id="edad" name="edad" type="number" min="0" max="150" value="<?= $e($antiguo['edad'] ?? '') ?>">
        </p>
        <button type="submit">Guardar</button>
    </form>
</main>
</body>
</html>
