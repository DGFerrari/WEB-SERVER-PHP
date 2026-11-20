<?php
/**
 * Formulario para CREAR (sin ?id) o EDITAR (con ?id=X) una oportunidad.
 * Al crear se puede preseleccionar el cliente con ?id_cliente=X
 */
require_once __DIR__ . '/../controller/auth.php';
require_once __DIR__ . '/../model/clientesBD.php';
require_once __DIR__ . '/../model/oportunidadesBD.php';

requerirLogin();

$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;

// Valores por defecto
$datos = [
    'id_cliente'          => (int) ($_GET['id_cliente'] ?? 0),
    'titulo'              => '',
    'descripcion'         => '',
    'valor_estimado'      => '',
    'estado'              => 'progreso',
];

if ($editando) {
    $oportunidad = OportunidadesBD::obtener($id);
    if (!$oportunidad) {
        redirigir('view/oportunidades.php?error=noexiste');
    }
    if (!puedeGestionar($oportunidad)) {
        redirigir('view/oportunidades.php?error=permiso');
    }
    if (!puedeModificarOportunidad($oportunidad)) {
        redirigir('view/oportunidades.php?error=cerrada');
    }
    $datos = $oportunidad;
}

// Si venimos de un error EN ESTE MISMO formulario, recuperar lo escrito
$anterior = $_SESSION['form_oportunidad'] ?? null;
unset($_SESSION['form_oportunidad']);
if ($anterior && $anterior['id'] === $id && isset($_GET['error'])) {
    unset($anterior['id']);
    $datos = array_merge($datos, $anterior);
}

$errores = [
    'vacio'       => 'El título es obligatorio.',
    'largo'       => 'El título no puede tener más de 150 caracteres.',
    'valor'       => 'El valor estimado debe ser un número entre 0 y 99.999.999,99.',
    'estado'      => 'Estado no válido.',
    'cliente'     => 'Selecciona un cliente válido.',
    'bd'        => 'Error al guardar en la base de datos.',
];
$error = $errores[$_GET['error'] ?? ''] ?? null;

// Clientes que puede elegir: admin todos, vendedor solo los suyos
$clientes = ClientesBD::listar('', esAdmin() ? null : $_SESSION['id_usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editando ? 'Editar oportunidad' : 'Nueva oportunidad' ?></title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <?php require __DIR__ . '/menu.php'; ?>

    <h1><?= $editando ? 'Editar oportunidad #' . e($id) : 'Nueva oportunidad' ?></h1>

    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if (!$clientes): ?>
        <p>Primero tienes que <a href="cliente_form.php">crear un cliente</a>.</p>
    <?php else: ?>

    <form method="POST" action="../controller/oportunidades_controller.php">
        <?= campoCSRF() ?>
        <input type="hidden" name="accion" value="<?= $editando ? 'editar' : 'crear' ?>">
        <input type="hidden" name="id_oportunidad" value="<?= e($id) ?>">

        <label for="id_cliente">Cliente*: </label>
        <select id="id_cliente" name="id_cliente" required>
            <option value="">-- Selecciona --</option>
            <?php foreach ($clientes as $c): ?>
                <option value="<?= e($c['id_cliente']) ?>"
                    <?= (int) $datos['id_cliente'] === (int) $c['id_cliente'] ? 'selected' : '' ?>>
                    <?= e($c['nombre_completo']) ?><?= $c['empresa'] ? ' (' . e($c['empresa']) . ')' : '' ?>
                </option>
            <?php endforeach; ?>
        </select> <br>

        <label for="titulo">Título*: </label>
        <input type="text" id="titulo" name="titulo" maxlength="150" required
               value="<?= e($datos['titulo']) ?>"> <br>

        <label for="descripcion">Descripción: </label> <br>
        <textarea id="descripcion" name="descripcion" rows="4" cols="50"><?= e($datos['descripcion']) ?></textarea> <br>

        <label for="valor_estimado">Valor estimado (€): </label>
        <input type="text" id="valor_estimado" name="valor_estimado" inputmode="decimal"
               placeholder="0,00" value="<?= e($datos['valor_estimado']) ?>"> <br>

        <label for="estado">Estado: </label>
        <select id="estado" name="estado">
            <?php foreach (OportunidadesBD::ESTADOS as $est): ?>
                <option value="<?= e($est) ?>" <?= $datos['estado'] === $est ? 'selected' : '' ?>><?= e(ucfirst($est)) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!esAdmin()): ?>
            <small>(si la marcas como ganada o perdida ya no podrás modificarla)</small>
        <?php endif; ?>
        <br>

        <small>* Campos obligatorios</small> <br>
        <?php if (esAdmin()): ?>
            <small>El responsable de la oportunidad será el responsable del cliente elegido.</small> <br>
        <?php endif; ?>

        <button type="submit"><?= $editando ? 'Guardar cambios' : 'Crear oportunidad' ?></button>
        <a href="oportunidades.php">Cancelar</a>
    </form>

    <?php endif; ?>

</body>
</html>
