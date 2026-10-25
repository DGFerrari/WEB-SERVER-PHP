<?php
session_start();
require_once __DIR__ . '/../modelo/conexionBD.php';

// Comprobamos que pase la info por POST, sino no ejecuta el resto
if (!($_SERVER['REQUEST_METHOD'] === 'POST')) {

    header("Location: ../vista/user.php");
    exit;
}

// Pillamos el Correo a cambiar
$email = trim($_POST['email']);

// Guardamos el correo
$sql = "UPDATE amvc SET email = :correo WHERE usuario = :user ";
$result = $conexion->prepare($sql);
$result->execute(['correo' => $email, 'user' => $_SESSION['usuario']]);

// Mandamos mensaje de que se a realizado
$_SESSION['mensaje'] = '¡Correo Cambiado!';
$_SESSION['email'] = $email;
header("Location: ../vista/user.php");
exit;

?>