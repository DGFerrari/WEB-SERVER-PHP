<?php
/**
 * CRUD de oportunidades + cambio rapido de estado.
 * Recibe por POST un campo "accion": crear | editar | estado | eliminar
 * Las reglas por rol estan en puedeModificarOportunidad() (auth.php).
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../model/clientesBD.php';
require_once __DIR__ . '/../model/oportunidadesBD.php';

requerirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('view/oportunidades.php');
}
validarCSRF();

$accion = $_POST['accion'] ?? '';
$id     = (int) ($_POST['id_oportunidad'] ?? 0);

// Para volver al listado manteniendo los filtros que tenia.
// Se reconstruye solo con los filtros validos (no se confia en lo que llega).
parse_str($_POST['volver'] ?? '', $f);
$filtros = http_build_query(array_filter([
    'estado'     => in_array($f['estado'] ?? '', OportunidadesBD::ESTADOS, true) ? $f['estado'] : '',
    'id_cliente' => (int) ($f['id_cliente'] ?? 0) ?: '',
]));
$listado = 'view/oportunidades.php?' . ($filtros !== '' ? $filtros . '&' : '');

// editar / estado / eliminar: debe existir y el usuario debe poder modificarla
if (in_array($accion, ['editar', 'estado', 'eliminar'], true)) {
    $oportunidad = OportunidadesBD::obtener($id);
    if (!$oportunidad) {
        redirigir($listado . 'error=noexiste');
    }
    if (!puedeGestionar($oportunidad)) {
        redirigir($listado . 'error=permiso');
    }
    if (!puedeModificarOportunidad($oportunidad)) {
        redirigir($listado . 'error=cerrada');
    }
}

switch ($accion) {

    //===== CREAR / EDITAR =====\\
    case 'crear':
    case 'editar':
        $idCliente   = (int) ($_POST['id_cliente'] ?? 0);
        $titulo      = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $valorTexto  = trim($_POST['valor_estimado'] ?? '');
        $estado      = $_POST['estado'] ?? 'progreso';

        $volver = $accion === 'crear'
            ? 'view/oportunidad_form.php?'
            : 'view/oportunidad_form.php?id=' . $id . '&';

        // Guardar lo escrito para no perderlo si hay error
        $_SESSION['form_oportunidad'] = [
            'id'             => $id,
            'id_cliente'     => $idCliente,
            'titulo'         => $titulo,
            'descripcion'    => $descripcion,
            'valor_estimado' => $valorTexto,
            'estado'         => $estado,
        ];

        // Validaciones
        if ($titulo === '') {
            redirigir($volver . 'error=vacio');
        }
        if (mb_strlen($titulo) > 150) {
            redirigir($volver . 'error=largo');
        }

        // Valor: admite "1500", "1500.50", "1500,50" y "1.500,50"; vacio = 0
        $valorTexto = str_replace([' ', '€'], '', $valorTexto);
        if (strpos($valorTexto, ',') !== false) {
            // Formato español: el punto es de miles y la coma decimal
            $valorTexto = str_replace(['.', ','], ['', '.'], $valorTexto);
        }
        if ($valorTexto === '') {
            $valorTexto = '0';
        }
        if (!is_numeric($valorTexto) || $valorTexto < 0 || $valorTexto > 99999999.99) {
            redirigir($volver . 'error=valor');
        }
        $valor = round((float) $valorTexto, 2);

        if (!in_array($estado, OportunidadesBD::ESTADOS, true)) {
            redirigir($volver . 'error=estado');
        }

        // El cliente debe existir y (si es vendedor) ser suyo
        $cliente = ClientesBD::obtener($idCliente);
        if (!$cliente || !puedeGestionar($cliente)) {
            redirigir($volver . 'error=cliente');
        }

        // El responsable de la oportunidad SIEMPRE es el responsable del cliente
        $responsable = (int) $cliente['usuario_responsable'];

        $descripcion = $descripcion !== '' ? $descripcion : null;

        $ok = $accion === 'crear'
            ? OportunidadesBD::crear($idCliente, $titulo, $descripcion, $valor, $estado, $responsable)
            : OportunidadesBD::actualizar($id, $idCliente, $titulo, $descripcion, $valor, $estado, $responsable);

        if (!$ok) {
            redirigir($volver . 'error=bd');
        }

        unset($_SESSION['form_oportunidad']);
        redirigir('view/oportunidades.php?ok=' . ($accion === 'crear' ? 'creada' : 'editada'));

    //===== CAMBIO RAPIDO DE ESTADO =====\\
    case 'estado':
        $estado = $_POST['estado'] ?? '';
        if (!in_array($estado, OportunidadesBD::ESTADOS, true)) {
            redirigir($listado . 'error=estado');
        }
        if (!OportunidadesBD::cambiarEstado($id, $estado)) {
            redirigir($listado . 'error=bd');
        }
        redirigir($listado . 'ok=estado');

    //===== ELIMINAR =====\\
    case 'eliminar':
        if (OportunidadesBD::eliminar($id)) {
            redirigir($listado . 'ok=eliminada');
        }
        redirigir($listado . 'error=bd');

    default:
        redirigir('view/oportunidades.php');
}
