<?php
/**
 * Funciones comunes de SESION y CONTROL DE ACCESO.
 * Se incluye al principio de todas las vistas y controladores.
 */

require_once __DIR__ . '/../model/usuariosBD.php';

// Zona horaria de la aplicacion (fechas de tareas, vencimientos...)
date_default_timezone_set('Europe/Madrid');

// Minutos de inactividad antes de cerrar la sesion automaticamente
const MINUTOS_INACTIVIDAD = 30;

/**
 * URL absoluta dentro del proyecto. Funciona tanto desde /view como
 * desde /controller porque ambas carpetas estan al mismo nivel.
 * Ej: url('view/login.php')
 */
function url($ruta) {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'], 2)), '/');
    return $base . '/' . $ruta;
}

function redirigir($ruta) {
    header('Location: ' . url($ruta));
    exit;
}

/**
 * Inicia la sesion con una cookie mas segura y controla la inactividad.
 */
function iniciarSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,        // la cookie muere al cerrar el navegador
            'path'     => '/',
            'httponly' => true,     // JavaScript no puede leer la cookie
            'samesite' => 'Strict', // no se envia desde otras webs
        ]);
        session_start();
    }

    // Cierre por inactividad
    if (isset($_SESSION['id_usuario'], $_SESSION['ultima_actividad'])
        && time() - $_SESSION['ultima_actividad'] > MINUTOS_INACTIVIDAD * 60) {
        cerrarSesion();
        redirigir('view/login.php?error=expirada');
    }
    $_SESSION['ultima_actividad'] = time();
}

/**
 * Guarda en la sesion los datos del usuario que acaba de iniciar sesion.
 */
function guardarUsuarioEnSesion($usuario) {
    session_regenerate_id(true); // evita la fijacion de sesion
    $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
    $_SESSION['nombre']     = $usuario['nombre'];
    $_SESSION['email']      = $usuario['email'];
    $_SESSION['rol']        = $usuario['rol'];
    $_SESSION['ultima_actividad'] = time();
}

/**
 * Cierre seguro: vacia la sesion, borra la cookie y destruye la sesion.
 */
function cerrarSesion() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Pagina solo para usuarios logueados.
 * Ademas vuelve a leer el usuario de la BD: si un admin lo ha
 * deshabilitado, borrado o le ha cambiado el rol, se aplica al momento.
 */
function requerirLogin() {
    iniciarSesion();

    if (!isset($_SESSION['id_usuario'])) {
        redirigir('view/login.php');
    }

    $usuario = UsuariosBD::obtener($_SESSION['id_usuario']);
    if (!$usuario || $usuario['estado'] !== 'Activo') {
        cerrarSesion();
        redirigir('view/login.php?error=estado');
    }

    // Refrescar datos por si han cambiado
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['email']  = $usuario['email'];
    $_SESSION['rol']    = $usuario['rol'];
}

/**
 * Pagina solo para un rol concreto (ej: 'administrador').
 */
function requerirRol($rol) {
    requerirLogin();
    if ($_SESSION['rol'] !== $rol) {
        redirigir('view/dashboard.php?error=permiso');
    }
}

function esAdmin() {
    return ($_SESSION['rol'] ?? '') === 'administrador';
}

/**
 * Permiso sobre un registro con "usuario_responsable" (cliente, oportunidad...):
 * el administrador puede con todos; el vendedor solo con los suyos.
 */
function puedeGestionar($registro) {
    return esAdmin()
        || (int) $registro['usuario_responsable'] === $_SESSION['id_usuario'];
}

/**
 * CONTROL POR ROL DE LAS OPORTUNIDADES:
 *  - Administrador: puede modificar cualquiera y ponerla en cualquier estado
 *    (incluido reabrir una 'ganada' o 'perdida').
 *  - Vendedor: solo las suyas y solo mientras esten en 'progreso'.
 *    Una vez cerrada (ganada/perdida) queda bloqueada para el.
 */
function puedeModificarOportunidad($oportunidad) {
    if (esAdmin()) {
        return true;
    }
    return puedeGestionar($oportunidad) && $oportunidad['estado'] === 'progreso';
}

/**
 * Para paginas publicas (login/registro): si ya esta logueado, al panel.
 */
function soloInvitados() {
    iniciarSesion();
    if (isset($_SESSION['id_usuario'])) {
        redirigir('view/dashboard.php');
    }
}

//===== PROTECCION CSRF =====\\
// Cada formulario que modifica datos lleva un token oculto que solo
// conoce nuestra sesion, asi otra web no puede enviar el formulario por ti.

function tokenCSRF() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function campoCSRF() {
    return '<input type="hidden" name="csrf" value="' . tokenCSRF() . '">';
}

function validarCSRF() {
    $token = $_POST['csrf'] ?? '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(400);
        exit('Petición no válida (token CSRF).');
    }
}

/**
 * Atajo para escapar texto al imprimirlo en HTML.
 */
function e($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}
