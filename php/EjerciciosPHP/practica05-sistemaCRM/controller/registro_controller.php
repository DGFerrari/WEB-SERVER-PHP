<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../model/consultasBD.php';

soloInvitados();

// Solo se aceptan peticiones POST (desde el formulario)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/registro.php');
    exit;
}

$nombre  = trim($_POST['nombre'] ?? '');
$email   = trim($_POST['email'] ?? '');
$contra  = $_POST['contra'] ?? '';
$contra2 = $_POST['contra2'] ?? '';

// Guardamos nombre y email para rellenar el formulario si hay error
$_SESSION['registro_old'] = ['nombre' => $nombre, 'email' => $email];

// Funcion auxiliar para volver al formulario con un error
function volverConError($codigo) {
    header('Location: ../view/registro.php?error=' . $codigo);
    exit;
}

// ===== Validaciones =====
if ($nombre === '' || $email === '' || $contra === '' || $contra2 === '') {
    volverConError('vacio');
}
if (mb_strlen($nombre) > 100) {
    volverConError('nombre');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    volverConError('email');
}
if (strlen($contra) < 8) {
    volverConError('corta');
}
if ($contra !== $contra2) {
    volverConError('coinciden');
}

// ¿El email ya esta registrado?
$existe = ConsultasBD::existeEmail($email);
if ($existe === null) {
    volverConError('bd');
}
if ($existe) {
    volverConError('duplicado');
}

// ===== Registrar (la contraseña se guarda encriptada) =====
$hash = password_hash($contra, PASSWORD_DEFAULT);

if (!ConsultasBD::registrar($nombre, $email, $hash)) {
    volverConError('bd');
}

// Todo OK: limpiar datos temporales y mandar al login
unset($_SESSION['registro_old']);
header('Location: ../view/login.php?registro=ok');
exit;
