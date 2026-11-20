<?php
require_once __DIR__ . '/auth.php';

iniciarSesion();

// Solo por POST y con token CSRF (asi otra web no puede cerrarte la sesion)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('view/dashboard.php');
}
validarCSRF();

// Vaciar sesion, borrar cookie y destruir sesion
cerrarSesion();

redirigir('view/login.php?logout=ok');
