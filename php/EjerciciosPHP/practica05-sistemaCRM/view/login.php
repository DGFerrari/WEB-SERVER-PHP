<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    
    <h1>Login</h1>

    <form method="POST" action="../controller/login_controller.php">
        <label id="username">Usuario: </label>
        <input type="text" id="username" name="username" required> <br>
        <label id="contra">Contraseña: </label>
        <input type="password" id="contra" name="contra" required> <br>
        <button type="submit">Iniciar Sesion</button>
    </form>

</body>
</html>