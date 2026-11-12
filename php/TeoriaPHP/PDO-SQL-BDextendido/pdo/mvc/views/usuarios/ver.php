<?php
/** @var array<string, mixed> $usuario */
$e = static fn (mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $e($usuario['nombre']) ?></title>
</head>
<body>
<main>
    <h1><?= $e($usuario['nombre']) ?></h1>
    <dl>
        <dt>Email</dt><dd><?= $e($usuario['email']) ?></dd>
        <dt>Edad</dt><dd><?= $e($usuario['edad'] ?? 'No indicada') ?></dd>
        <dt>Alta</dt><dd><?= $e($usuario['creado_en']) ?></dd>
    </dl>
    <p><a href="index.php">Volver al listado</a></p>
</main>
</body>
</html>
