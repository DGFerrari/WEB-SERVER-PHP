<?php
/**
 * Menu comun para las paginas privadas.
 * Se incluye con: require __DIR__ . '/menu.php';
 * (la pagina ya debe haber llamado a requerirLogin() o requerirRol())
 */
?>
<nav>
    <a href="dashboard.php">Inicio</a>
    | <a href="clientes.php">Clientes</a>
    | <a href="oportunidades.php">Oportunidades</a>
    | <a href="tareas.php">Tareas</a>
    <?php if (esAdmin()): ?>
        | <a href="resumen.php">Resumen</a>
        | <a href="usuarios.php">Usuarios</a>
    <?php endif; ?>

    | <?= e($_SESSION['nombre']) ?> (<?= e($_SESSION['rol']) ?>)

    <!-- Cerrar sesion por POST con token CSRF: otra web no puede desloguearte -->
    <form method="POST" action="../controller/logout_controller.php" style="display: inline;">
        <?= campoCSRF() ?>
        <button type="submit">Cerrar sesión</button>
    </form>
</nav>
<hr>
