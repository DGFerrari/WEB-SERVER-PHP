<?php
    // Llamamos la conexion a la BD
    require __DIR__ . '/../modelo/conexionBD.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
</head>
<body>
    <form action="../controlador/loginController.php" method="POST">
        <label for="usuario">Usuario</label>
        <input type="text" name="usuario" id="usuario" required> <br>

        <label for="contrasena">Contraseña</label>
        <input type="password" name="contrasena" id="contrasena" required> <br>

        <input type="submit" value="Iniciar sesión">
    </form>
</body>
</html>