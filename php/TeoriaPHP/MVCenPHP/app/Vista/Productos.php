<h1>Productos</h1>

<ul>
<?php foreach ($productos as $p): ?>

    <li><?= htmlspecialchars(
        $p['nombre']) ?></li>

<?php endforeach; ?>
</ul>
