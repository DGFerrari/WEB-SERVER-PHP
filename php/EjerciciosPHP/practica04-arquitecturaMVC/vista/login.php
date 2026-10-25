<?php

session_start();
if (isset($_SESSION['merror'])) {
    echo '<h2>Error: ' . $_SESSION['merror'] . '</h2>';
}
session_destroy();

?>

<form method="POST" action="../controlador/loginController.php">
    <label for="username">Usuario:</label>
    <input type="text" id="username" name="username" required>
    <br>
    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit">Iniciar Sesión</button>
</form>