<h1>Información del Alumno</h1>

<?php
    // Leemos los datos que llegan en la URL
    $nombre = isset($_REQUEST['nombre']) ? trim($_REQUEST['nombre']) : '';
    $apellido = isset($_REQUEST['apellido']) ? trim($_REQUEST['apellido']) : '';

    // Comprovamos si hay alguno vacio
    if ($nombre === '' || $apellido === '') {
        echo "<p>Alumno no introducido</p>";
    
    // Mostrar datos alumno
    } else {
        echo "<p><strong>Nombre:</strong> " . htmlspecialchars($nombre) . "</p>";
        echo "<p><strong>Apellido:</strong> " . htmlspecialchars($apellido) . "</p>";
    }
?>
