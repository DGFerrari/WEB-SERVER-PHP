<?php

require_once __DIR__ . '/conexionBD.php';

/**
 * CRUD de la tabla clientes.
 * Los permisos (quien puede ver/editar que) se comprueban en el controlador.
 */
class ClientesBD {

    //===== LEER =====\\
    /**
     * @param $busqueda      -> texto a buscar en nombre o empresa ('' = todos)
     * @param $idResponsable -> si se indica, solo los clientes de ese usuario
     *                          (null = todos, lo usa el administrador)
     * Devuelve los clientes con el nombre de su responsable, o [] si falla.
     */
    public static function listar($busqueda = '', $idResponsable = null) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return [];
        }

        $sql = "SELECT c.*, u.nombre AS nombre_responsable
                FROM clientes c
                JOIN usuarios u ON u.id_usuario = c.usuario_responsable
                WHERE 1 = 1";
        $datos = [];

        if ($busqueda !== '') {
            // Se escapan % y _ para que se busquen como texto normal
            $like = '%' . addcslashes($busqueda, '%_\\') . '%';
            $sql .= " AND (c.nombre_completo LIKE :b1 OR c.empresa LIKE :b2)";
            $datos['b1'] = $like;
            $datos['b2'] = $like;
        }
        if ($idResponsable !== null) {
            $sql .= " AND c.usuario_responsable = :resp";
            $datos['resp'] = $idResponsable;
        }
        $sql .= " ORDER BY c.nombre_completo";

        $stmt = $BD->prepare($sql);
        $stmt->execute($datos);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve un cliente por su id o false si no existe.
     */
    public static function obtener($id) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        $stmt = $BD->prepare("SELECT * FROM clientes WHERE id_cliente = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //===== CREAR =====\\
    public static function crear($nombre, $email, $tlf, $empresa, $responsable) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $stmt = $BD->prepare(
                "INSERT INTO clientes (nombre_completo, email, tlf, empresa, usuario_responsable)
                 VALUES (:nombre, :email, :tlf, :empresa, :resp)"
            );
            return $stmt->execute([
                'nombre'  => $nombre,
                'email'   => $email,
                'tlf'     => $tlf,
                'empresa' => $empresa,
                'resp'    => $responsable,
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear cliente: " . $e->getMessage());
            return false;
        }
    }

    //===== ACTUALIZAR =====\\
    /**
     * Si cambia el responsable, sus oportunidades pasan tambien al nuevo
     * responsable (el de la oportunidad siempre coincide con el del cliente).
     * Se hace en una TRANSACCION: o se guardan los dos cambios o ninguno.
     */
    public static function actualizar($id, $nombre, $email, $tlf, $empresa, $responsable) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $BD->beginTransaction();

            $stmt = $BD->prepare(
                "UPDATE clientes
                 SET nombre_completo = :nombre, email = :email, tlf = :tlf,
                     empresa = :empresa, usuario_responsable = :resp
                 WHERE id_cliente = :id"
            );
            $stmt->execute([
                'id'      => $id,
                'nombre'  => $nombre,
                'email'   => $email,
                'tlf'     => $tlf,
                'empresa' => $empresa,
                'resp'    => $responsable,
            ]);

            $stmt = $BD->prepare(
                "UPDATE oportunidades SET usuario_responsable = :resp WHERE id_cliente = :id"
            );
            $stmt->execute(['id' => $id, 'resp' => $responsable]);

            $BD->commit();
            return true;
        } catch (PDOException $e) {
            $BD->rollBack();
            error_log("Error al actualizar cliente: " . $e->getMessage());
            return false;
        }
    }

    //===== ELIMINAR =====\\
    /**
     * OJO: por el ON DELETE CASCADE de la BD, al borrar un cliente
     * se borran tambien sus oportunidades (y las tareas de estas).
     */
    public static function eliminar($id) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $stmt = $BD->prepare("DELETE FROM clientes WHERE id_cliente = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al eliminar cliente: " . $e->getMessage());
            return false;
        }
    }
}
?>
