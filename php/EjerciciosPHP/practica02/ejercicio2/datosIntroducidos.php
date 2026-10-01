<?php

// Detecta cuando se envia el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recogemos lo que el usuario ha escrito
    // [isset] Por si en nulo
    // [trim] Para quitar los espacios
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';

    // Construimos la redirección con los parámetros en la URL
    header("Location: datosIntroducidos.php?nombre=" . urlencode($nombre) . "&apellido=" . urlencode($apellido));
    exit();
}
?>

<form action="datosAlumno.php" method="POST">
    <label for="nombre">Nombre:</label><br>
    <input type="text" id="nombre" name="nombre">
    <label for="apellido">Apellido:</label><br>
    <input type="text" id="apellido" name="apellido">
<button type="submit">Enviar</button>
