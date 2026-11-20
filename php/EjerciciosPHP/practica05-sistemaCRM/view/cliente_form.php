<?php
/**
 * Formulario para CREAR (sin ?id) o EDITAR (con ?id=X) un cliente.
 */
require_once __DIR__ . '/../controller/auth.php';
require_once __DIR__ . '/../model/clientesBD.php';

requerirLogin();

$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;

// Valores por defecto (nuevo cliente: responsable = yo)
$datos = [
    'nombre_completo'     => '',
    'email'               => '',
    'tlf'                 => '',
    'empresa'             => '',
    'usuario_responsable' => $_SESSION['id_usuario'],
];

if ($editando) {
    $cliente = ClientesBD::obtener($id);
    if (!$cliente) {
        redirigir('view/clientes.php?error=noexiste');
    }
    // Un vendedor no puede ni abrir el formulario de un cliente ajeno
    if (!puedeGestionar($cliente)) {
        redirigir('view/clientes.php?error=permiso');
    }
    $datos = $cliente;
}

// Si venimos de un error EN ESTE MISMO formulario, recuperar lo escrito
$anterior = $_SESSION['form_cliente'] ?? null;
unset($_SESSION['form_cliente']);
if ($anterior && $anterior['id'] === $id && isset($_GET['error'])) {
    unset($anterior['id']);
    $datos = array_merge($datos, $anterior);
}

$errores = [
    'vacio'       => 'El nombre es obligatorio.',
    'largo'       => 'El nombre y la empresa no pueden tener más de 150 caracteres.',
    'email'       => 'El email no es válido.',
    'tlf'         => 'El teléfono solo puede tener números, espacios, +, - y paréntesis (6-20 caracteres).',
    'responsable' => 'El usuario responsable no es válido.',
    'bd'          => 'Error al guardar en la base de datos.',
];
$error = $errores[$_GET['error'] ?? ''] ?? null;

// El admin puede elegir responsable entre los usuarios
$usuarios = esAdmin() ? UsuariosBD::listar() : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editando ? 'Editar cliente' : 'Nuevo cliente' ?></title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <?php require __DIR__ . '/menu.php'; ?>

    <h1><?= $editando ? 'Editar cliente #' . e($id) : 'Nuevo cliente' ?></h1>

    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="../controller/clientes_controller.php">
        <?= campoCSRF() ?>
        <input type="hidden" name="accion" value="<?= $editando ? 'editar' : 'crear' ?>">
        <input type="hidden" name="id_cliente" value="<?= e($id) ?>">

        <label for="nombre_completo">Nombre completo*: </label>
        <input type="text" id="nombre_completo" name="nombre_completo" maxlength="150" required
               value="<?= e($datos['nombre_completo']) ?>"> <br>

        <label for="email">Email: </label>
        <input type="email" id="email" name="email" maxlength="150"
               value="<?= e($datos['email']) ?>"> <br>

        <label for="tlf">Teléfono: </label>
        <input type="tel" id="tlf" name="tlf" maxlength="20"
               value="<?= e($datos['tlf']) ?>"> <br>

        <label for="empresa">Empresa: </label>
        <input type="text" id="empresa" name="empresa" maxlength="150"
               value="<?= e($datos['empresa']) ?>"> <br>

        <?php if (esAdmin()): ?>
            <label for="usuario_responsable">Responsable: </label>
            <select id="usuario_responsable" name="usuario_responsable">
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= e($u['id_usuario']) ?>"
                        <?= (int) $datos['usuario_responsable'] === (int) $u['id_usuario'] ? 'selected' : '' ?>>
                        <?= e($u['nombre']) ?> (<?= e($u['rol']) ?><?= $u['estado'] !== 'Activo' ? ', ' . e($u['estado']) : '' ?>)
                    </option>
                <?php endforeach; ?>
            </select> <br>
            <?php if ($editando): ?>
                <small>Si cambias el responsable, sus oportunidades (y tareas) pasan también al nuevo responsable.</small> <br>
            <?php endif; ?>
        <?php endif; ?>

        <small>* Campo obligatorio</small> <br>

        <button type="submit"><?= $editando ? 'Guardar cambios' : 'Crear cliente' ?></button>
        <a href="clientes.php">Cancelar</a>
    </form>

</body>
</html>
