<?php
/**
 * Formulario para CREAR (sin ?id) o EDITAR (con ?id=X) un usuario.
 */
require_once __DIR__ . '/../controller/auth.php';

requerirRol('administrador');

$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;

// Valores por defecto
$datos = ['nombre' => '', 'email' => '', 'rol' => 'vendedor', 'estado' => 'Activo'];

if ($editando) {
    $usuario = UsuariosBD::obtener($id);
    if (!$usuario) {
        redirigir('view/usuarios.php?error=noexiste');
    }
    $datos = $usuario;
}

// Si venimos de un error EN ESTE MISMO formulario, recuperar lo que se habia escrito
$anterior = $_SESSION['form_usuario'] ?? null;
unset($_SESSION['form_usuario']);
if ($anterior && $anterior['id'] === $id && isset($_GET['error'])) {
    unset($anterior['id']);
    $datos = array_merge($datos, $anterior);
}

$errores = [
    'vacio'     => 'Rellena todos los campos obligatorios.',
    'nombre'    => 'El nombre no puede tener más de 100 caracteres.',
    'email'     => 'El email no es válido.',
    'valores'   => 'Rol o estado no válidos.',
    'corta'     => 'La contraseña debe tener al menos 8 caracteres.',
    'duplicado' => 'Ya existe otro usuario con ese email.',
    'propio'    => 'No puedes quitarte el rol de administrador ni desactivar tu propia cuenta.',
    'bd'        => 'Error al guardar en la base de datos.',
];
$error = $errores[$_GET['error'] ?? ''] ?? null;

$esMiCuenta = $editando && $id === $_SESSION['id_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editando ? 'Editar usuario' : 'Nuevo usuario' ?></title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <?php require __DIR__ . '/menu.php'; ?>

    <h1><?= $editando ? 'Editar usuario #' . e($id) : 'Nuevo usuario' ?></h1>

    <?php if ($error): ?>
        <p style="color: red;"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="../controller/usuarios_controller.php">
        <?= campoCSRF() ?>
        <input type="hidden" name="accion" value="<?= $editando ? 'editar' : 'crear' ?>">
        <input type="hidden" name="id_usuario" value="<?= e($id) ?>">

        <label for="nombre">Nombre: </label>
        <input type="text" id="nombre" name="nombre" maxlength="100" required
               value="<?= e($datos['nombre']) ?>"> <br>

        <label for="email">Email: </label>
        <input type="email" id="email" name="email" maxlength="150" required
               value="<?= e($datos['email']) ?>"> <br>

        <label for="rol">Rol: </label>
        <select id="rol" name="rol" <?= $esMiCuenta ? 'disabled' : '' ?>>
            <?php foreach (UsuariosBD::ROLES as $r): ?>
                <option value="<?= e($r) ?>" <?= $datos['rol'] === $r ? 'selected' : '' ?>><?= e($r) ?></option>
            <?php endforeach; ?>
        </select> <br>

        <label for="estado">Estado: </label>
        <select id="estado" name="estado" <?= $esMiCuenta ? 'disabled' : '' ?>>
            <?php foreach (UsuariosBD::ESTADOS as $est): ?>
                <option value="<?= e($est) ?>" <?= $datos['estado'] === $est ? 'selected' : '' ?>><?= e($est) ?></option>
            <?php endforeach; ?>
        </select> <br>

        <?php if ($esMiCuenta): ?>
            <!-- Un select disabled no se envia: mandamos los valores fijos -->
            <input type="hidden" name="rol" value="administrador">
            <input type="hidden" name="estado" value="Activo">
            <small>No puedes cambiar tu propio rol ni estado.</small> <br>
        <?php endif; ?>

        <label for="contra">Contraseña: </label>
        <input type="password" id="contra" name="contra" minlength="8"
               <?= $editando ? '' : 'required' ?>>
        <?php if ($editando): ?>
            <small>(déjala vacía para no cambiarla)</small>
        <?php endif; ?>
        <br>

        <button type="submit"><?= $editando ? 'Guardar cambios' : 'Crear usuario' ?></button>
        <a href="usuarios.php">Cancelar</a>
    </form>

</body>
</html>
