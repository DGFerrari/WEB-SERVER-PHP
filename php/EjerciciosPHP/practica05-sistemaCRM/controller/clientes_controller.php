<?php
/**
 * Alta, edicion y eliminacion de clientes.
 * Recibe por POST un campo "accion": crear | editar | eliminar
 *  - Administrador: puede con todos y elige el responsable.
 *  - Vendedor: solo con sus clientes; el responsable siempre es el mismo.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../model/clientesBD.php';

requerirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('view/clientes.php');
}
validarCSRF();

$accion = $_POST['accion'] ?? '';
$id     = (int) ($_POST['id_cliente'] ?? 0);

// Para editar/eliminar: el cliente debe existir y ser "gestionable" por este usuario
if ($accion === 'editar' || $accion === 'eliminar') {
    $cliente = ClientesBD::obtener($id);
    if (!$cliente) {
        redirigir('view/clientes.php?error=noexiste');
    }
    if (!puedeGestionar($cliente)) {
        redirigir('view/clientes.php?error=permiso');
    }
}

switch ($accion) {

    //===== CREAR / EDITAR =====\\
    case 'crear':
    case 'editar':
        $nombre  = trim($_POST['nombre_completo'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $tlf     = trim($_POST['tlf'] ?? '');
        $empresa = trim($_POST['empresa'] ?? '');

        // Responsable: el admin lo elige; el vendedor no puede cambiarlo
        if (esAdmin()) {
            $responsable = (int) ($_POST['usuario_responsable'] ?? 0);
        } else {
            $responsable = $accion === 'crear'
                ? $_SESSION['id_usuario']
                : (int) $cliente['usuario_responsable'];
        }

        $volver = $accion === 'crear'
            ? 'view/cliente_form.php?'
            : 'view/cliente_form.php?id=' . $id . '&';

        // Guardar lo escrito para no perderlo si hay error
        $_SESSION['form_cliente'] = [
            'id'                  => $id,
            'nombre_completo'     => $nombre,
            'email'               => $email,
            'tlf'                 => $tlf,
            'empresa'             => $empresa,
            'usuario_responsable' => $responsable,
        ];

        // Validaciones (email, tlf y empresa son opcionales)
        if ($nombre === '') {
            redirigir($volver . 'error=vacio');
        }
        if (mb_strlen($nombre) > 150 || mb_strlen($empresa) > 150) {
            redirigir($volver . 'error=largo');
        }
        if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150)) {
            redirigir($volver . 'error=email');
        }
        if ($tlf !== '' && !preg_match('/^[0-9+\s()-]{6,20}$/', $tlf)) {
            redirigir($volver . 'error=tlf');
        }
        if (!UsuariosBD::obtener($responsable)) {
            redirigir($volver . 'error=responsable');
        }

        // Campos opcionales vacios se guardan como NULL
        $email   = $email   !== '' ? $email   : null;
        $tlf     = $tlf     !== '' ? $tlf     : null;
        $empresa = $empresa !== '' ? $empresa : null;

        $ok = $accion === 'crear'
            ? ClientesBD::crear($nombre, $email, $tlf, $empresa, $responsable)
            : ClientesBD::actualizar($id, $nombre, $email, $tlf, $empresa, $responsable);

        if (!$ok) {
            redirigir($volver . 'error=bd');
        }

        unset($_SESSION['form_cliente']);
        redirigir('view/clientes.php?ok=' . ($accion === 'crear' ? 'creado' : 'editado'));

    //===== ELIMINAR =====\\
    case 'eliminar':
        if (ClientesBD::eliminar($id)) {
            redirigir('view/clientes.php?ok=eliminado');
        }
        redirigir('view/clientes.php?error=bd');

    default:
        redirigir('view/clientes.php');
}
