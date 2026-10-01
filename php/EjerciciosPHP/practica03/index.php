<h1>Inicia Sesion</h1>

<form method="POST" action="bienvenida.php">
    <label for="usuario">Usuario</label>
    <input type="text" id="usuario" name="usuario">
    <label for="contra">Contraseña</label>
    <input type="password" id="contra" name="contra">
    <button type="submit">Buscar</button>
</form>

<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $contra = isset($_POST['contra']) ? trim($_POST['contra']) : '';
}
?>