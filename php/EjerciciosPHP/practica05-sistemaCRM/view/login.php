<?php
require_once __DIR__ . '/../controller/auth.php';

// Si ya hay sesion iniciada, ir directamente al panel
soloInvitados();

$errores = [
    'vacio'        => 'Rellena todos los campos.',
    'credenciales' => 'Email o contraseña incorrectos.',
    'estado'       => 'Tu cuenta no está activa.',
    'expirada'     => 'Tu sesión ha caducado por inactividad. Vuelve a iniciar sesión.',
    'bd'           => 'No se pudo conectar con la base de datos.',
];
$error = $errores[$_GET['error'] ?? ''] ?? null; // "??" evita el warning si no hay error en la URL

// Mensaje cuando se viene de un registro correcto
$registroOk = ($_GET['registro'] ?? '') === 'ok';
$logoutOk   = ($_GET['logout'] ?? '') === 'ok';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <h1>Login</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($registroOk): ?>
        <p style="color: green;">Cuenta creada correctamente. Ya puedes iniciar sesión.</p>
    <?php endif; ?>

    <?php if ($logoutOk): ?>
        <p style="color: green;">Has cerrado sesión correctamente.</p>
    <?php endif; ?>

    <form method="POST" action="../controller/login_controller.php">
        <label for="email">Email: </label>
        <input type="email" id="email" name="email" required> <br>
        <label for="contra">Contraseña: </label>
        <input type="password" id="contra" name="contra" required> <br>
        <button type="submit">Iniciar Sesion</button>
    </form>

    <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>

</body>
</html>
