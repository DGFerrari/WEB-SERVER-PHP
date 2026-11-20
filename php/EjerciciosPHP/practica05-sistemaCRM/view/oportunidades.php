<?php
require_once __DIR__ . '/../controller/auth.php';
require_once __DIR__ . '/../model/clientesBD.php';
require_once __DIR__ . '/../model/oportunidadesBD.php';

requerirLogin();

$mensajes = [
    'creada'    => 'Oportunidad creada correctamente.',
    'editada'   => 'Oportunidad actualizada correctamente.',
    'estado'    => 'Estado actualizado correctamente.',
    'eliminada' => 'Oportunidad eliminada correctamente.',
];
$errores = [
    'noexiste' => 'La oportunidad no existe.',
    'permiso'  => 'No tienes permiso sobre esa oportunidad.',
    'cerrada'  => 'La oportunidad ya está cerrada (ganada/perdida). Solo un administrador puede modificarla.',
    'estado'   => 'Estado no válido.',
    'bd'       => 'Error al acceder a la base de datos.',
];
$ok    = $mensajes[$_GET['ok'] ?? ''] ?? null;
$error = $errores[$_GET['error'] ?? ''] ?? null;

// ===== Filtros =====
$filtroEstado  = $_GET['estado'] ?? '';
if (!in_array($filtroEstado, OportunidadesBD::ESTADOS, true)) {
    $filtroEstado = '';
}
$filtroCliente = (int) ($_GET['id_cliente'] ?? 0);

// El admin ve todo; el vendedor solo lo suyo
$idResp        = esAdmin() ? null : $_SESSION['id_usuario'];
$oportunidades = OportunidadesBD::listar($filtroEstado, $filtroCliente, $idResp);
$clientes      = ClientesBD::listar('', $idResp); // para el desplegable del filtro

// Filtros actuales en formato URL (para que las acciones vuelvan aqui con ellos)
$queryFiltros = http_build_query(array_filter([
    'estado'     => $filtroEstado,
    'id_cliente' => $filtroCliente ?: '',
]));

// Totales por estado de lo que se esta mostrando
$totales = array_fill_keys(OportunidadesBD::ESTADOS, 0);
foreach ($oportunidades as $o) {
    $totales[$o['estado']] += $o['valor_estimado'];
}

function euros($valor) {
    return number_format((float) $valor, 2, ',', '.') . ' €';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oportunidades</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <?php require __DIR__ . '/menu.php'; ?>

    <h1><?= esAdmin() ? 'Oportunidades' : 'Mis oportunidades' ?></h1>

    <?php if ($ok): ?>
        <p style="color: green;"><?= e($ok) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <!-- Filtros por GET -->
    <form method="GET" action="oportunidades.php">
        <label for="f_estado">Estado: </label>
        <select id="f_estado" name="estado">
            <option value="">Todos</option>
            <?php foreach (OportunidadesBD::ESTADOS as $est): ?>
                <option value="<?= e($est) ?>" <?= $filtroEstado === $est ? 'selected' : '' ?>><?= e(ucfirst($est)) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="f_cliente">Cliente: </label>
        <select id="f_cliente" name="id_cliente">
            <option value="0">Todos</option>
            <?php foreach ($clientes as $c): ?>
                <option value="<?= e($c['id_cliente']) ?>" <?= $filtroCliente === (int) $c['id_cliente'] ? 'selected' : '' ?>>
                    <?= e($c['nombre_completo']) ?><?= $c['empresa'] ? ' (' . e($c['empresa']) . ')' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filtrar</button>
        <?php if ($queryFiltros !== ''): ?>
            <a href="oportunidades.php">Quitar filtros</a>
        <?php endif; ?>
    </form>

    <p>
        <a href="oportunidad_form.php<?= $filtroCliente ? '?id_cliente=' . e($filtroCliente) : '' ?>">+ Nueva oportunidad</a>
    </p>

    <p>
        <?= count($oportunidades) ?> oportunidad(es) —
        En progreso: <?= euros($totales['progreso']) ?> |
        Ganadas: <?= euros($totales['ganada']) ?> |
        Perdidas: <?= euros($totales['perdida']) ?>
    </p>

    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Cliente</th>
                <th>Valor estimado</th>
                <th>Estado</th>
                <th>Creada</th>
                <?php if (esAdmin()): ?><th>Responsable</th><?php endif; ?>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($oportunidades as $o): ?>
                <tr>
                    <td><?= e($o['id_oportunidad']) ?></td>
                    <td>
                        <?= e($o['titulo']) ?>
                        <?php if ($o['descripcion']): ?>
                            <br><small><?= nl2br(e($o['descripcion'])) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= e($o['nombre_cliente']) ?><?= $o['empresa'] ? '<br><small>' . e($o['empresa']) . '</small>' : '' ?></td>
                    <td style="text-align: right;"><?= euros($o['valor_estimado']) ?></td>
                    <td>
                        <?php if (puedeModificarOportunidad($o)): ?>
                            <!-- Cambio rapido de estado -->
                            <form method="POST" action="../controller/oportunidades_controller.php" style="display: inline;">
                                <?= campoCSRF() ?>
                                <input type="hidden" name="accion" value="estado">
                                <input type="hidden" name="id_oportunidad" value="<?= e($o['id_oportunidad']) ?>">
                                <input type="hidden" name="volver" value="<?= e($queryFiltros) ?>">
                                <select name="estado">
                                    <?php foreach (OportunidadesBD::ESTADOS as $est): ?>
                                        <option value="<?= e($est) ?>" <?= $o['estado'] === $est ? 'selected' : '' ?>><?= e(ucfirst($est)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Cambiar</button>
                            </form>
                        <?php else: ?>
                            <strong><?= e(ucfirst($o['estado'])) ?></strong> 🔒
                        <?php endif; ?>
                    </td>
                    <td><?= e($o['fecha_creacion']) ?></td>
                    <?php if (esAdmin()): ?><td><?= e($o['nombre_responsable']) ?></td><?php endif; ?>
                    <td>
                        <a href="tareas.php?estado=todas&id_oportunidad=<?= e($o['id_oportunidad']) ?>">Tareas</a>

                        <?php if (puedeModificarOportunidad($o)): ?>
                            <a href="oportunidad_form.php?id=<?= e($o['id_oportunidad']) ?>">Editar</a>

                            <form method="POST" action="../controller/oportunidades_controller.php" style="display: inline;"
                                  onsubmit="return confirm(<?= e(json_encode('¿Eliminar la oportunidad "' . $o['titulo'] . "\"?\nSe borrarán también sus tareas.", JSON_UNESCAPED_UNICODE)) ?>);">
                                <?= campoCSRF() ?>
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_oportunidad" value="<?= e($o['id_oportunidad']) ?>">
                                <input type="hidden" name="volver" value="<?= e($queryFiltros) ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$oportunidades): ?>
                <tr><td colspan="<?= esAdmin() ? 8 : 7 ?>">No hay oportunidades.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!esAdmin()): ?>
        <p><small>🔒 Las oportunidades ganadas o perdidas quedan cerradas. Solo un administrador puede reabrirlas o modificarlas.</small></p>
    <?php endif; ?>

</body>
</html>
