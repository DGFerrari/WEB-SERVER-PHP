<?php
/**
 * Tareas: crear y marcar como completada / pendiente.
 * Recibe por POST un campo "accion": crear | completar | reabrir
 *  - Administrador: todas las tareas.
 *  - Vendedor: solo las de SUS oportunidades.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../model/oportunidadesBD.php';
require_once __DIR__ . '/../model/tareasBD.php';

requerirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('view/tareas.php');
}
validarCSRF();

$accion = $_POST['accion'] ?? '';

// Volver al listado con los mismos filtros (solo filtros validos)
parse_str($_POST['volver'] ?? '', $f);
$filtros = http_build_query(array_filter([
    'estado'         => in_array($f['estado'] ?? '', [...TareasBD::ESTADOS, 'todas'], true) ? $f['estado'] : '',
    'id_oportunidad' => (int) ($f['id_oportunidad'] ?? 0) ?: '',
    'id_cliente'     => (int) ($f['id_cliente'] ?? 0) ?: '',
    'id_usuario'     => (int) ($f['id_usuario'] ?? 0) ?: '',
]));
$listado = 'view/tareas.php?' . ($filtros !== '' ? $filtros . '&' : '');

switch ($accion) {

    //===== CREAR / EDITAR =====\\
    case 'crear':
    case 'editar':
        $id            = (int) ($_POST['id_tarea'] ?? 0);
        $idOportunidad = (int) ($_POST['id_oportunidad'] ?? 0);
        $descripcion   = trim($_POST['descripcion'] ?? '');
        $fechaTexto    = trim($_POST['fecha'] ?? '');
        $estado        = $_POST['estado'] ?? 'pendiente';

        // Al editar: la tarea debe existir y ser del usuario (o ser admin)
        if ($accion === 'editar') {
            $tarea = TareasBD::obtener($id);
            if (!$tarea) {
                redirigir($listado . 'error=noexiste');
            }
            if (!puedeGestionar($tarea)) {
                redirigir($listado . 'error=permiso');
            }
        }

        $_SESSION['form_tarea'] = [
            'id'             => $id,
            'id_oportunidad' => $idOportunidad,
            'descripcion'    => $descripcion,
            'fecha'          => $fechaTexto,
            'estado'         => $estado,
        ];
        $volver = $accion === 'crear'
            ? 'view/tarea_form.php?'
            : 'view/tarea_form.php?id=' . $id . '&';

        if (!in_array($estado, TareasBD::ESTADOS, true)) {
            redirigir($volver . 'error=estado');
        }
        if ($descripcion === '' || $fechaTexto === '') {
            redirigir($volver . 'error=vacio');
        }
        if (mb_strlen($descripcion) > 2000) {
            redirigir($volver . 'error=largo');
        }

        // El input datetime-local envia "2026-11-20T10:30"
        $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $fechaTexto);
        if (!$fecha || $fecha->format('Y-m-d\TH:i') !== $fechaTexto) {
            redirigir($volver . 'error=fecha');
        }

        // La oportunidad debe existir y ser del usuario (o ser admin)
        $oportunidad = OportunidadesBD::obtener($idOportunidad);
        if (!$oportunidad || !puedeGestionar($oportunidad)) {
            redirigir($volver . 'error=oportunidad');
        }

        $fechaBD = $fecha->format('Y-m-d H:i:s');
        $ok = $accion === 'crear'
            ? TareasBD::crear($idOportunidad, $descripcion, $fechaBD)
            : TareasBD::actualizar($id, $idOportunidad, $descripcion, $fechaBD, $estado);

        if (!$ok) {
            redirigir($volver . 'error=bd');
        }

        unset($_SESSION['form_tarea']);
        // Volvemos a todas las tareas de esa oportunidad
        redirigir('view/tareas.php?estado=todas&id_oportunidad=' . $idOportunidad
            . '&ok=' . ($accion === 'crear' ? 'creada' : 'editada'));

    //===== COMPLETAR / REABRIR =====\\
    case 'completar':
    case 'reabrir':
        $id    = (int) ($_POST['id_tarea'] ?? 0);
        $tarea = TareasBD::obtener($id);

        if (!$tarea) {
            redirigir($listado . 'error=noexiste');
        }
        if (!puedeGestionar($tarea)) {
            redirigir($listado . 'error=permiso');
        }

        $nuevo = $accion === 'completar' ? 'completada' : 'pendiente';
        if (!TareasBD::cambiarEstado($id, $nuevo)) {
            redirigir($listado . 'error=bd');
        }
        redirigir($listado . 'ok=' . $nuevo);

    default:
        redirigir('view/tareas.php');
}
