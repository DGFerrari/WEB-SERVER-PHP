<?php
// Iniciamos sesion
session_start();

// requiere de user controller
//require_once __DIR__ . '/../controlador/userController.php';
?>

<h1> PANEL USER </h1>

<?php

echo "<p>Usuario: " . $_SESSION['usuario'] . "</p><br>";
echo "<p>Nombre: " . $_SESSION['nombre'] . "</p><br>";
echo "<p>Apellidos: " . $_SESSION['apellidos'] . "</p><br>";
echo "<p>Email: " . $_SESSION['email'] . "</p><br>";

if (isset($_SESSION['mensaje'])) {
    echo $_SESSION['mensaje'];
    $_SESSION['mensaje'] ='';
}

?>



<form method='POST' action='../controlador/userController.php'>
    <label for='email'>Cambiar email:</label>
    <input type='email' name='email' required>
    <button type='submit'>Cambiar</button>
</form>