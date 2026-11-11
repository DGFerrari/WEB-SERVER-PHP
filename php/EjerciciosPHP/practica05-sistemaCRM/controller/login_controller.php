<?php

session_start();
$_SESSION['ms-login'] = '';

// Traemos la conexion de la BD
require_once __DIR__ . '/../model/conexionBD.php';
$BD = new conexionBD();

// Comprovacion si el metodo del formulario es post
if (!($_SERVER['REQUEST_METHOD'] === 'POST')) {
    $_SESSION['ms-login'] = 'Error interno: el metodo usado no es POST!';
}

// Info Login + Hash
$l_user = trim($_POST['username'], '');
$l_pasw = $_POST['contra'];
$l_hash = password_hash($l_pasw, PASSWORD_DEFAULT);

// Comprovar si el Usuario y Contraseñas son correctos

if ($BD->ComprovarLogin($l_user, $l_hash)) {
    echo "si";
} else {
    echo "no";
}


?>