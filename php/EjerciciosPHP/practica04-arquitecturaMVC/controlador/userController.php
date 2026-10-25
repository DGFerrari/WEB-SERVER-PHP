<?php
require_once __DIR__ . '/../modelo/conexionBD.php';

// Select de toda la tabla (menos las contraseñas) y la guardamos en una variable
$sql = "SELECT usuario, nombre, apellidos, email, rol FROM amvc";
$result = $conexion->prepare($sql);
$result->execute();
$todosLosUsuarios = $result->fetchAll(PDO::FETCH_ASSOC);

$lista = ''; // Lista a devolver

for ($i = 0; $i < count($todosLosUsuarios); $i++) {
    $lista = $lista . '<tr>';
    $lista = $lista . '<td>' . $todosLosUsuarios[$i]['usuario'] . '</td>';
    $lista = $lista . '<td>' . $todosLosUsuarios[$i]['nombre'] . '</td>';
    $lista = $lista . '<td>' . $todosLosUsuarios[$i]['apellidos'] . '</td>';
    $lista = $lista . '<td>' . $todosLosUsuarios[$i]['email'] . '</td>';
    $lista = $lista . '<td>' . $todosLosUsuarios[$i]['rol'] . '</td>';
    $lista = $lista . '</tr>';
}

?>