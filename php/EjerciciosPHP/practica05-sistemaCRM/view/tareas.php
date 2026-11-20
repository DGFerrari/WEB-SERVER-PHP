<?php
require_once __DIR__ . '/../controller/auth.php';
require_once __DIR__ . '/../model/clientesBD.php';
require_once __DIR__ . '/../model/oportunidadesBD.php';
require_once __DIR__ . '/../model/tareasBD.php';

requerirLogin();

$mensajes = [
    'creada'     => 'Tarea creada correctamente.',
    'editada'    => 'Tarea actualizada correctamente.',
    'completada' => 'Tarea marcada como completada.',
    'pendiente'  => 'Tarea marcada otra vez como pendiente.',
];
$errores = [
    'noexiste' => 'La tarea no existe.',
    'permiso'  => 'No tienes permiso sobre esa tarea.',
    'bd'       => 'Error al acceder a la base de datos.',
];
$ok    = $mensajes[$_GET['ok'] ?? ''] ?? null;
$error = $errores[$_GET['error'] ?? ''] ?? null;

// ===== Filtros =====
// Por defecto se muestran las PENDIENTES; "todas" quita el filtro
$filtroEstado = $_GET['estado'] ?? 'pendiente';
if (!in_array($filtroEstado, TareasBD::ESTADOS, true)) {
    $filtroEstado = '';
}
$filtroOportunidad = (int) ($_GET['id_oportunidad'] ?? 0);
$filtroCliente     = (int) ($_GET['id_cliente'] ?? 0);
// El vendedor SIEMPRE ve solo las suyas; el admin puede filtrar por usuario
$filtroUsuario = esAdmin() ? (int) ($_GET['id_usuario'] ?? 0) : $_SESSION['id_usuario'];

$tareas = TareasBD::listar([
    'estado'         => $filtroEstado,
    'id_oportunidad' => $filtroOportunidad,
    'id_cliente'     => $filtroCliente,
    'id_usuario'     => $filtroUsuario,
]);

// Datos para los desplegables de filtros
$idResp        = esAdmin() ? null : $_SESSION['id_usuario'];
$clientes      = ClientesBD::listar('', $idResp);
$oportunidades = OportunidadesBD::listar('', 0, $idResp);
$usuarios      = esAdmin() ? UsuariosBD::listar() : [];

// Resumen de pendientes por usuario (solo admin)
$resumen = esAdmin() ? TareasBD::pendientesPorUsuario() : [];

// Filtros actuales para que las acciones vuelvan aqui
$queryFiltros = http_build_query(array_filter([
    'estado'         => $filtroEstado === '' ? 'todas' : $filtroEstado,
    'id_oportunidad' => $filtroOportunidad ?: '',
    'id_cliente'     => $filtroCliente ?: '',
    'id_usuario'     => esAdmin() && $filtroUsuario ? $filtroUsuario : '',
]));

$ahora = date('Y-m-d H:i:s');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <?php require __DIR__ . '/menu.php'; ?>

    <h1><?= esAdmin() ? 'Tareas' : 'Mis tareas' ?></h1>

    <?php if ($ok): ?>
        <p style="color: green;"><?= e($ok) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if (esAdmin()): ?>
        <!-- ===== Pendientes por usuario ===== -->
        <h2>Pendientes por usuario</h2>
        <table border="1" cellpadding="5">
            <thead>
                <tr><th>Usuario</th><th>Rol</th><th>Pendientes</th><th>Vencidas</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($resumen as $r): ?>
                    <tr>
                        <td><?= e($r['nombre']) ?><?= $r['estado'] !== 'Activo' ? ' <small>(' . e($r['estado']) . ')</small>' : '' ?></td>
                        <td><?= e($r['rol']) ?></td>
                        <td style="text-align: right;"><?= e($r['pendientes']) ?></td>
                        <td style="text-align: right; <?= $r['vencidas'] > 0 ? 'color: red; font-weight: bold;' : '' ?>"><?= e($r['vencidas']) ?></td>
                        <td>
                            <?php if ($r['pendientes'] > 0): ?>
                                <a href="tareas.php?estado=pendiente&id_usuario=<?= e($r['id_usuario']) ?>">Ver</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h2>Listado</h2>
    <?php endif; ?>

    <!-- ===== Filtros (GET) ===== -->
    <form method="GET" action="tareas.php">
        <label for="f_estado">Estado: </label>
        <select id="f_estado" name="estado">
            <option value="pendiente"  <?= $filtroEstado === 'pendiente'  ? 'selected' : '' ?>>Pendientes</option>
            <option value="completada" <?= $filtroEstado === 'completada' ? 'selected' : '' ?>>Completadas</option>
            <option value="todas"      <?= $filtroEstado === ''           ? 'selected' : '' ?>>Todas</option>
        </select>

        <label for="f_cliente">Cliente: </label>
        <select id="f_cliente" name="id_cliente">
            <option value="0">Todos</option>
            <?php foreach ($clientes as $c): ?>
                <option value="<?= e($c['id_cliente']) ?>" <?= $filtroCliente === (int) $c['id_cliente'] ? 'selected' : '' ?>><?= e($c['nombre_completo']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="f_op">Oportunidad: </label>
        <select id="f_op" name="id_oportunidad">
            <option value="0">Todas</option>
            <?php foreach ($oportunidades as $o): ?>
                <option value="<?= e($o['id_oportunidad']) ?>" <?= $filtroOportunidad === (int) $o['id_oportunidad'] ? 'selected' : '' ?>>
                    <?= e($o['titulo']) ?> — <?= e($o['nombre_cliente']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (esAdmin()): ?>
            <label for="f_usuario">Usuario: </label>
            <select id="f_usuario" name="id_usuario">
                <option value="0">Todos</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= e($u['id_usuario']) ?>" <?= $filtroUsuario === (int) $u['id_usuario'] ? 'selected' : '' ?>><?= e($u['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <button type="submit">Filtrar</button>
        <a href="tareas.php">Quitar filtros</a>
    </form>

    <p>
        <a href="tarea_form.php<?= $filtroOportunidad ? '?id_oportunidad=' . e($filtroOportunidad) : '' ?>">+ Nueva tarea</a>
    </p>

    <p><?= count($tareas) ?> tarea(s)</p>

    <!-- ===== Listado ===== -->
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Oportunidad</th>
                <th>Cliente</th>
                <?php if (esAdmin()): ?><th>Responsable</th><?php endif; ?>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tareas as $t): ?>
                <?php $vencida = $t['estado'] === 'pendiente' && $t['fecha'] < $ahora; ?>
                <tr>
                    <td style="<?= $vencida ? 'color: red; font-weight: bold;' : '' ?>">
                        <?= e(date('d/m/Y H:i', strtotime($t['fecha']))) ?>
                        <?= $vencida ? '<br><small>Vencida</small>' : '' ?>
                    </td>
                    <td><?= nl2br(e($t['descripcion'])) ?></td>
                    <td>
                        <a href="tareas.php?estado=todas&id_oportunidad=<?= e($t['id_oportunidad']) ?>"><?= e($t['titulo_oportunidad']) ?></a>
                        <br><small>(<?= e($t['estado_oportunidad']) ?>)</small>
                    </td>
                    <td><?= e($t['nombre_cliente']) ?></td>
                    <?php if (esAdmin()): ?><td><?= e($t['nombre_responsable']) ?></td><?php endif; ?>
                    <td><?= $t['estado'] === 'completada' ? '✔ Completada' : 'Pendiente' ?></td>
                    <td>
                        <?php if (puedeGestionar($t)): ?>
                            <a href="tarea_form.php?id=<?= e($t['id_tarea']) ?>">Editar</a>

                            <form method="POST" action="../controller/tareas_controller.php" style="display: inline;">
                                <?= campoCSRF() ?>
                                <input type="hidden" name="id_tarea" value="<?= e($t['id_tarea']) ?>">
                                <input type="hidden" name="volver" value="<?= e($queryFiltros) ?>">
                                <?php if ($t['estado'] === 'pendiente'): ?>
                                    <input type="hidden" name="accion" value="completar">
                                    <button type="submit">Completar</button>
                                <?php else: ?>
                                    <input type="hidden" name="accion" value="reabrir">
                                    <button type="submit">Reabrir</button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$tareas): ?>
                <tr><td colspan="<?= esAdmin() ? 7 : 6 ?>">No hay tareas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
