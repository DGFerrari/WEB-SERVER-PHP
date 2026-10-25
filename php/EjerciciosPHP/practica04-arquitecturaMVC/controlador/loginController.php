<?php
// Iniciamos sesion
session_start();

require_once __DIR__ . '/../modelo/conexionBD.php';

// Comprobamos que pase la info por POST, sino no ejecuta el resto
if (!($_SERVER['REQUEST_METHOD'] === 'POST')) {

    $_SESSION['merror'] = 'esto no es POST';
    header("Location: ../vista/login.php");
    exit;
}

// Recogemos el usuario y la contraseña
$user = trim($_POST['username'], '');
$pasw = $_POST['password'];
// $pasw = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Comprobamos los datos a la BD
$sql = "SELECT usuario, contra, nombre, apellidos, email, rol FROM amvc WHERE usuario = :user"; // Select
$result = $conexion->prepare($sql); // Lo preparamos
$result->execute(['user' => $user]); // Ejecutamos pasando los parametros ":tal"
$comprovar = $result->fetch(PDO::FETCH_ASSOC); // Guardamos el resultado en un array


if (isset($comprovar['contra']) && $pasw === $comprovar['contra']) {
    
    // Guardar datos en session
    $_SESSION['usuario'] = $comprovar['usuario'];
    $_SESSION['nombre'] = $comprovar['nombre'];
    $_SESSION['apellidos'] = $comprovar['apellidos'];
    $_SESSION['email'] = $comprovar['email'];

    // Comprovamos que rol es el usuario:
    if ($comprovar['rol'] === "admin") {   
    header("Location: ../vista/admin.php");
    exit;

    } elseif($comprovar['rol'] === "user") {
    header("Location: ../vista/user.php");
    exit;

    }

// Si verifica que no es igual la contraseña
} else {
    $_SESSION['merror'] = 'Usuaro o Contraseña incorrecta';
    header("Location: ../vista/login.php");
    exit;
}

?>