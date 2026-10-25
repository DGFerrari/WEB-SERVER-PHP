<?php
// Iniciamos sesion
session_start();

// requiere de user controller
require_once __DIR__ . '/../controlador/adminController.php';
?>

<h1> PANEL ADMIN </h1>

<table border="5px">
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Email</th>
            <th>Rol</th>
        </tr>
    </thead>
    <tbody>

<?php

echo $lista;

?>

    </tbody>
</table>