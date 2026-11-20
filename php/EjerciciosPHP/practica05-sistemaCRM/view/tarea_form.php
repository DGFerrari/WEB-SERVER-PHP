<?php
/**
 * Formulario para CREAR (sin ?id) o EDITAR (con ?id=X) una tarea.
 * Al crear se puede preseleccionar la oportunidad con ?id_oportunidad=X
 */
require_once __DIR__ . '/../controller/auth.php';
require_once __DIR__ . '/../model/oportunidadesBD.php';
require_once __DIR__ . '/../model/tareasBD.php';

requerirLogin();

$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;

// Valores por defecto: mañana a las 9:00
$datos = [
    'id_oportunidad' => (int) ($_GET['id_oportunidad'] ?? 0),
    'descripcion'    => '',
    'fecha'          => date('Y-m-d\T09:00', strtotime('+1 day')),
    'estado'         => 'pendiente',
];

if ($editando) {
    $tarea = TareasBD::obtener($id);
    if (!$tarea) {
        redirigir('view/tareas.php?error=noexiste');
    }
    if (!puedeGestionar($tarea)) {
        redirigir('view/tareas.php?error=permiso');
    }
    $datos = [
        'id_oportunidad' => $tarea['id_oportunidad'],
        'descripcion'    => $tarea['descripcion'],
        // De '2026-11-20 10:30:00' (BD) a '2026-11-20T10:30' (input)
        'fecha'          => date('Y-m-d\TH:i', strtotime($tarea['fecha'])),
        'estado'         => $tarea['estado'],
    ];
}

// Si venimos de un error EN ESTE MISMO formulario, recuperar lo escrito
$anterior = $_SESSION['form_tarea'] ?? null;
unset($_SESSION['form_tarea']);
if ($anterior && $anterior['id'] === $id && isset($_GET['error'])) {
    unset($anterior['id']);
    $datos = array_merge($datos, $anterior);
}

$errores = [
    'vacio'       => 'La descripción y la fecha son obligatorias.',
    'largo'       => 'La descripción no puede tener más de 2000 caracteres.',
    'fecha'       => 'La fecha no es válida.',
    'estado'      => 'Estado no válido.',
    'oportunidad' => 'Selecciona una oportunidad válida.',
    'bd'          => 'Error al guardar en la base de datos.',
];
$error = $errores[$_GET['error'] ?? ''] ?? null;

// Oportunidades donde puede tener tareas: admin todas, vendedor las suyas
$oportunidades = OportunidadesBD::listar('', 0, esAdmin() ? null : $_SESSION['id_usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editando ? 'Editar tarea' : 'Nueva tarea' ?></title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <?php require __DIR__ . '/menu.php'; ?>

    <h1><?= $editando ? 'Editar tarea #' . e($id) : 'Nueva tarea' ?></h1>

    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if (!$oportunidades): ?>
        <p>Primero tienes que <a href="oportunidad_form.php">crear una oportunidad</a>.</p>
    <?php else: ?>

    <form method="POST" action="../controller/tareas_controller.php">
        <?= campoCSRF() ?>
        <input type="hidden" name="accion" value="<?= $editando ? 'editar' : 'crear' ?>">
        <input type="hidden" name="id_tarea" value="<?= e($id) ?>">

        <label for="id_oportunidad">Oportunidad*: </label>
        <select id="id_oportunidad" name="id_oportunidad" required>
            <option value="">-- Selecciona --</option>
            <?php foreach ($oportunidades as $o): ?>
                <option value="<?= e($o['id_oportunidad']) ?>"
                    <?= (int) $datos['id_oportunidad'] === (int) $o['id_oportunidad'] ? 'selected' : '' ?>>
                    <?= e($o['titulo']) ?> — <?= e($o['nombre_cliente']) ?> (<?= e($o['estado']) ?>)
                </option>
            <?php endforeach; ?>
        </select> <br>

        <label for="fecha">Fecha*: </label>
        <input type="datetime-local" id="fecha" name="fecha" required
               value="<?= e($datos['fecha']) ?>"> <br>

        <label for="descripcion">Descripción*: </label> <br>
        <textarea id="descripcion" name="descripcion" rows="4" cols="50" maxlength="2000" required
                  placeholder="Ej: Llamar para confirmar el presupuesto"><?= e($datos['descripcion']) ?></textarea> <br>

        <?php if ($editando): ?>
            <label for="estado">Estado: </label>
            <select id="estado" name="estado">
                <?php foreach (TareasBD::ESTADOS as $est): ?>
                    <option value="<?= e($est) ?>" <?= $datos['estado'] === $est ? 'selected' : '' ?>><?= e(ucfirst($est)) ?></option>
                <?php endforeach; ?>
            </select> <br>
        <?php endif; ?>

        <small>* Campos obligatorios</small> <br>

        <button type="submit"><?= $editando ? 'Guardar cambios' : 'Crear tarea' ?></button>
        <a href="tareas.php">Cancelar</a>
    </form>

    <?php endif; ?>

</body>
</html>
