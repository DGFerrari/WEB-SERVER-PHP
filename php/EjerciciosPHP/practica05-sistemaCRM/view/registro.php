<?php
require_once __DIR__ . '/../controller/auth.php';

// Si ya hay sesion iniciada, no tiene sentido registrarse
soloInvitados();

$errores = [
    'vacio'     => 'Rellena todos los campos.',
    'nombre'    => 'El nombre no puede tener más de 100 caracteres.',
    'email'     => 'El email no es válido.',
    'corta'     => 'La contraseña debe tener al menos 8 caracteres.',
    'coinciden' => 'Las contraseñas no coinciden.',
    'duplicado' => 'Ya existe una cuenta con ese email.',
    'bd'        => 'No se pudo completar el registro. Inténtalo más tarde.',
];
$error = $errores[$_GET['error'] ?? ''] ?? null;

// Valores que el usuario ya habia escrito (para no perderlos si hay error)
$old = $_SESSION['registro_old'] ?? ['nombre' => '', 'email' => ''];
unset($_SESSION['registro_old']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <h1>Registro</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="../controller/registro_controller.php">
        <label for="nombre">Nombre: </label>
        <input type="text" id="nombre" name="nombre" maxlength="100" required
               value="<?= htmlspecialchars($old['nombre']) ?>"> <br>

        <label for="email">Email: </label>
        <input type="email" id="email" name="email" maxlength="150" required
               value="<?= htmlspecialchars($old['email']) ?>"> <br>

        <label for="contra">Contraseña: </label>
        <input type="password" id="contra" name="contra" minlength="8" required> <br>

        <label for="contra2">Repetir contraseña: </label>
        <input type="password" id="contra2" name="contra2" minlength="8" required> <br>

        <button type="submit">Registrarse</button>
    </form>

    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>

</body>
</html>
