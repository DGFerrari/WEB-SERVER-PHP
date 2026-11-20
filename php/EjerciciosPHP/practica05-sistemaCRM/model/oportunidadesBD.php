<?php

require_once __DIR__ . '/conexionBD.php';

/**
 * CRUD de la tabla oportunidades.
 * Los permisos (quien puede ver/editar que) se comprueban en el controlador.
 */
class OportunidadesBD {

    const ESTADOS = ['progreso', 'ganada', 'perdida'];

    //===== LEER =====\\
    /**
     * @param $estado        -> filtrar por estado ('' = todos)
     * @param $idCliente     -> filtrar por cliente (0 = todos)
     * @param $idResponsable -> solo las de ese usuario (null = todas, admin)
     * Devuelve las oportunidades con el nombre del cliente y del responsable.
     */
    public static function listar($estado = '', $idCliente = 0, $idResponsable = null) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return [];
        }

        $sql = "SELECT o.*, c.nombre_completo AS nombre_cliente, c.empresa,
                       u.nombre AS nombre_responsable
                FROM oportunidades o
                JOIN clientes c ON c.id_cliente = o.id_cliente
                JOIN usuarios u ON u.id_usuario = o.usuario_responsable
                WHERE 1 = 1";
        $datos = [];

        if ($estado !== '') {
            $sql .= " AND o.estado = :estado";
            $datos['estado'] = $estado;
        }
        if ($idCliente > 0) {
            $sql .= " AND o.id_cliente = :cliente";
            $datos['cliente'] = $idCliente;
        }
        if ($idResponsable !== null) {
            $sql .= " AND o.usuario_responsable = :resp";
            $datos['resp'] = $idResponsable;
        }
        $sql .= " ORDER BY o.fecha_creacion DESC, o.id_oportunidad DESC";

        $stmt = $BD->prepare($sql);
        $stmt->execute($datos);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve una oportunidad por su id o false si no existe.
     */
    public static function obtener($id) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        $stmt = $BD->prepare("SELECT * FROM oportunidades WHERE id_oportunidad = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //===== CREAR =====\\
    public static function crear($idCliente, $titulo, $descripcion, $valor, $estado, $responsable) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $stmt = $BD->prepare(
                "INSERT INTO oportunidades
                    (id_cliente, titulo, descripcion, valor_estimado, estado, usuario_responsable)
                 VALUES (:cliente, :titulo, :descripcion, :valor, :estado, :resp)"
            );
            return $stmt->execute([
                'cliente'     => $idCliente,
                'titulo'      => $titulo,
                'descripcion' => $descripcion,
                'valor'       => $valor,
                'estado'      => $estado,
                'resp'        => $responsable,
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear oportunidad: " . $e->getMessage());
            return false;
        }
    }

    //===== ACTUALIZAR =====\\
    public static function actualizar($id, $idCliente, $titulo, $descripcion, $valor, $estado, $responsable) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $stmt = $BD->prepare(
                "UPDATE oportunidades
                 SET id_cliente = :cliente, titulo = :titulo, descripcion = :descripcion,
                     valor_estimado = :valor, estado = :estado, usuario_responsable = :resp
                 WHERE id_oportunidad = :id"
            );
            return $stmt->execute([
                'id'          => $id,
                'cliente'     => $idCliente,
                'titulo'      => $titulo,
                'descripcion' => $descripcion,
                'valor'       => $valor,
                'estado'      => $estado,
                'resp'        => $responsable,
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar oportunidad: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia solo el estado (lo usa el cambio rapido del listado).
     */
    public static function cambiarEstado($id, $estado) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $stmt = $BD->prepare("UPDATE oportunidades SET estado = :estado WHERE id_oportunidad = :id");
            return $stmt->execute(['id' => $id, 'estado' => $estado]);
        } catch (PDOException $e) {
            error_log("Error al cambiar estado: " . $e->getMessage());
            return false;
        }
    }

    //===== ELIMINAR =====\\
    /**
     * OJO: por el ON DELETE CASCADE, se borran tambien sus tareas.
     */
    public static function eliminar($id) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $stmt = $BD->prepare("DELETE FROM oportunidades WHERE id_oportunidad = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al eliminar oportunidad: " . $e->getMessage());
            return false;
        }
    }
}
?>
