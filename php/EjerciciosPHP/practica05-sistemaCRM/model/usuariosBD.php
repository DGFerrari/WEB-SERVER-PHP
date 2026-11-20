<?php

require_once __DIR__ . '/conexionBD.php';

/**
 * CRUD de la tabla usuarios (lo usa el administrador).
 * Las funciones de login/registro siguen en ConsultasBD.
 */
class UsuariosBD {

    const ROLES   = ['administrador', 'vendedor'];
    const ESTADOS = ['Activo', 'Pendiente', 'Deshabilitado'];

    //===== LEER =====\\
    /**
     * Devuelve todos los usuarios (sin la contraseña) o [] si falla.
     */
    public static function listar() {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return [];
        }
        $stmt = $BD->query(
            "SELECT id_usuario, nombre, email, rol, estado, fecha_registro
             FROM usuarios
             ORDER BY id_usuario"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve un usuario por su id (sin la contraseña) o false.
     */
    public static function obtener($id) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        $stmt = $BD->prepare(
            "SELECT id_usuario, nombre, email, rol, estado, fecha_registro
             FROM usuarios
             WHERE id_usuario = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * true si el email ya lo usa OTRO usuario (se excluye $idExcluir al editar).
     */
    public static function emailEnUso($email, $idExcluir = 0) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return true; // por seguridad, si no hay BD no dejamos continuar
        }
        $stmt = $BD->prepare(
            "SELECT COUNT(*) FROM usuarios WHERE email = :email AND id_usuario <> :id"
        );
        $stmt->execute(['email' => $email, 'id' => $idExcluir]);
        return $stmt->fetchColumn() > 0;
    }

    //===== CREAR =====\\
    /**
     * @param $hash -> contraseña YA encriptada con password_hash()
     */
    public static function crear($nombre, $email, $hash, $rol, $estado) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $stmt = $BD->prepare(
                "INSERT INTO usuarios (nombre, email, password, rol, estado)
                 VALUES (:nombre, :email, :password, :rol, :estado)"
            );
            return $stmt->execute([
                'nombre'   => $nombre,
                'email'    => $email,
                'password' => $hash,
                'rol'      => $rol,
                'estado'   => $estado,
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }

    //===== ACTUALIZAR =====\\
    /**
     * Si $hash es null, la contraseña no se cambia.
     */
    public static function actualizar($id, $nombre, $email, $rol, $estado, $hash = null) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }

        $sql = "UPDATE usuarios
                SET nombre = :nombre, email = :email, rol = :rol, estado = :estado";
        $datos = [
            'id'     => $id,
            'nombre' => $nombre,
            'email'  => $email,
            'rol'    => $rol,
            'estado' => $estado,
        ];
        if ($hash !== null) {
            $sql .= ", password = :password";
            $datos['password'] = $hash;
        }
        $sql .= " WHERE id_usuario = :id";

        try {
            $stmt = $BD->prepare($sql);
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    //===== ELIMINAR =====\\
    /**
     * Devuelve 'ok', 'relaciones' (tiene clientes/oportunidades asignados,
     * la BD no deja borrarlo) o 'error'.
     */
    public static function eliminar($id) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return 'error';
        }
        try {
            $stmt = $BD->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0 ? 'ok' : 'error';
        } catch (PDOException $e) {
            // 23000 = violacion de clave foranea
            if ($e->getCode() === '23000') {
                return 'relaciones';
            }
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return 'error';
        }
    }
}
?>
