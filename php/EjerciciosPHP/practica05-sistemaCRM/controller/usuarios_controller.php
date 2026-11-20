<?php
/**
 * CRUD de usuarios. SOLO administrador.
 * Recibe por POST un campo "accion": crear | editar | eliminar
 */
require_once __DIR__ . '/auth.php';

requerirRol('administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('view/usuarios.php');
}
validarCSRF();

$accion = $_POST['accion'] ?? '';
$miId   = $_SESSION['id_usuario'];

switch ($accion) {

    //===== CREAR / EDITAR =====\\
    case 'crear':
    case 'editar':
        $id     = (int) ($_POST['id_usuario'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $rol    = $_POST['rol'] ?? '';
        $estado = $_POST['estado'] ?? '';
        $contra = $_POST['contra'] ?? '';

        // Pagina a la que volver si hay error
        $volver = $accion === 'crear'
            ? 'view/usuario_form.php'
            : 'view/usuario_form.php?id=' . $id;
        $sep = $accion === 'crear' ? '?' : '&';

        // Guardar lo escrito para no perderlo si hay error
        // (con el id, para no rellenar por error el formulario de OTRO usuario)
        $_SESSION['form_usuario'] = compact('id', 'nombre', 'email', 'rol', 'estado');

        if ($accion === 'editar' && !UsuariosBD::obtener($id)) {
            redirigir('view/usuarios.php?error=noexiste');
        }

        // Validaciones
        if ($nombre === '' || $email === '') {
            redirigir($volver . $sep . 'error=vacio');
        }
        if (mb_strlen($nombre) > 100) {
            redirigir($volver . $sep . 'error=nombre');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            redirigir($volver . $sep . 'error=email');
        }
        if (!in_array($rol, UsuariosBD::ROLES, true) || !in_array($estado, UsuariosBD::ESTADOS, true)) {
            redirigir($volver . $sep . 'error=valores');
        }
        // Al crear la contraseña es obligatoria; al editar, vacia = no cambiarla
        if ($accion === 'crear' && $contra === '') {
            redirigir($volver . $sep . 'error=vacio');
        }
        if ($contra !== '' && strlen($contra) < 8) {
            redirigir($volver . $sep . 'error=corta');
        }
        if (UsuariosBD::emailEnUso($email, $id)) {
            redirigir($volver . $sep . 'error=duplicado');
        }
        // El admin no puede quitarse a si mismo el rol ni desactivarse
        // (asi siempre queda al menos un administrador activo)
        if ($accion === 'editar' && $id === $miId
            && ($rol !== 'administrador' || $estado !== 'Activo')) {
            redirigir($volver . $sep . 'error=propio');
        }

        $hash = $contra !== '' ? password_hash($contra, PASSWORD_DEFAULT) : null;

        $ok = $accion === 'crear'
            ? UsuariosBD::crear($nombre, $email, $hash, $rol, $estado)
            : UsuariosBD::actualizar($id, $nombre, $email, $rol, $estado, $hash);

        if (!$ok) {
            redirigir($volver . $sep . 'error=bd');
        }

        unset($_SESSION['form_usuario']);
        redirigir('view/usuarios.php?ok=' . ($accion === 'crear' ? 'creado' : 'editado'));

    //===== ELIMINAR =====\\
    case 'eliminar':
        $id = (int) ($_POST['id_usuario'] ?? 0);

        if ($id === $miId) {
            redirigir('view/usuarios.php?error=propio');
        }

        $resultado = UsuariosBD::eliminar($id);
        if ($resultado === 'ok') {
            redirigir('view/usuarios.php?ok=eliminado');
        }
        redirigir('view/usuarios.php?error=' . ($resultado === 'relaciones' ? 'relaciones' : 'bd'));

    default:
        redirigir('view/usuarios.php');
}
