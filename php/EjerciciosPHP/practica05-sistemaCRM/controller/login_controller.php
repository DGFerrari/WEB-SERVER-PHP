<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../model/consultasBD.php';

iniciarSesion();

// Solo se aceptan peticiones POST (desde el formulario)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$contra = $_POST['contra'] ?? '';

// Validacion basica de los campos
if ($email === '' || $contra === '') {
    header('Location: ../view/login.php?error=vacio');
    exit;
}

$usuario = ConsultasBD::login($email);

// Error de conexion con la BD
if ($usuario === null) {
    header('Location: ../view/login.php?error=bd');
    exit;
}

// Usuario inexistente o contraseña incorrecta (mismo mensaje para no dar pistas)
if (!$usuario || !password_verify($contra, $usuario['password'])) {
    header('Location: ../view/login.php?error=credenciales');
    exit;
}

// Usuario existente pero no activo
if ($usuario['estado'] !== 'Activo') {
    header('Location: ../view/login.php?error=estado');
    exit;
}

// Login correcto: nuevo id de sesion y guardar datos del usuario
guardarUsuarioEnSesion($usuario);

redirigir('view/dashboard.php');
