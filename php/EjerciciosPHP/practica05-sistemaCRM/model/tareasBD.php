<?php

require_once __DIR__ . '/conexionBD.php';

/**
 * Tareas / actividades. Cada tarea pertenece a una oportunidad y, a traves
 * de ella, a un cliente y a un usuario responsable.
 * Los permisos se comprueban en el controlador.
 */
class TareasBD {

    const ESTADOS = ['pendiente', 'completada'];

    // Parte comun de las consultas: tarea + oportunidad + cliente + responsable
    const SELECT_BASE =
        "SELECT t.*, o.titulo AS titulo_oportunidad, o.estado AS estado_oportunidad,
                o.usuario_responsable, c.id_cliente, c.nombre_completo AS nombre_cliente,
                u.nombre AS nombre_responsable
         FROM tareas t
         JOIN oportunidades o ON o.id_oportunidad = t.id_oportunidad
         JOIN clientes c      ON c.id_cliente = o.id_cliente
         JOIN usuarios u      ON u.id_usuario = o.usuario_responsable";

    //===== LEER =====\\
    /**
     * @param $filtros -> array con claves opcionales:
     *   'estado'         => 'pendiente' | 'completada'
     *   'id_oportunidad' => int
     *   'id_cliente'     => int
     *   'id_usuario'     => int (responsable; el vendedor siempre lleva el suyo)
     * Pendientes primero y por fecha (la mas proxima arriba).
     */
    public static function listar($filtros = []) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return [];
        }

        $sql   = self::SELECT_BASE . " WHERE 1 = 1";
        $datos = [];

        if (!empty($filtros['estado'])) {
            $sql .= " AND t.estado = :estado";
            $datos['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['id_oportunidad'])) {
            $sql .= " AND t.id_oportunidad = :op";
            $datos['op'] = $filtros['id_oportunidad'];
        }
        if (!empty($filtros['id_cliente'])) {
            $sql .= " AND c.id_cliente = :cliente";
            $datos['cliente'] = $filtros['id_cliente'];
        }
        if (!empty($filtros['id_usuario'])) {
            $sql .= " AND o.usuario_responsable = :usuario";
            $datos['usuario'] = $filtros['id_usuario'];
        }
        $sql .= " ORDER BY t.estado = 'completada', t.fecha ASC, t.id_tarea ASC";

        $stmt = $BD->prepare($sql);
        $stmt->execute($datos);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve una tarea (con usuario_responsable de su oportunidad) o false.
     */
    public static function obtener($id) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        $stmt = $BD->prepare(self::SELECT_BASE . " WHERE t.id_tarea = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Resumen de tareas PENDIENTES por usuario (para el administrador):
     * cuantas tiene cada uno y cuantas estan ya vencidas.
     */
    public static function pendientesPorUsuario() {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return [];
        }
        // La hora actual se pasa desde PHP para usar la misma zona horaria
        $stmt = $BD->prepare(
            "SELECT u.id_usuario, u.nombre, u.rol, u.estado,
                    COUNT(t.id_tarea)                   AS pendientes,
                    COALESCE(SUM(t.fecha < :ahora), 0)  AS vencidas
             FROM usuarios u
             LEFT JOIN oportunidades o ON o.usuario_responsable = u.id_usuario
             LEFT JOIN tareas t        ON t.id_oportunidad = o.id_oportunidad
                                      AND t.estado = 'pendiente'
             GROUP BY u.id_usuario, u.nombre, u.rol, u.estado
             ORDER BY pendientes DESC, u.nombre"
        );
        $stmt->execute(['ahora' => date('Y-m-d H:i:s')]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //===== CREAR =====\\
    /**
     * @param $fecha -> 'Y-m-d H:i:s'
     */
    public static function crear($idOportunidad, $descripcion, $fecha) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $stmt = $BD->prepare(
                "INSERT INTO tareas (id_oportunidad, descripcion, fecha)
                 VALUES (:op, :descripcion, :fecha)"
            );
            return $stmt->execute([
                'op'          => $idOportunidad,
                'descripcion' => $descripcion,
                'fecha'       => $fecha,
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear tarea: " . $e->getMessage());
            return false;
        }
    }

    //===== ACTUALIZAR =====\\
    /**
     * @param $fecha -> 'Y-m-d H:i:s'
     */
    public static function actualizar($id, $idOportunidad, $descripcion, $fecha, $estado) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $stmt = $BD->prepare(
                "UPDATE tareas
                 SET id_oportunidad = :op, descripcion = :descripcion,
                     fecha = :fecha, estado = :estado
                 WHERE id_tarea = :id"
            );
            return $stmt->execute([
                'id'          => $id,
                'op'          => $idOportunidad,
                'descripcion' => $descripcion,
                'fecha'       => $fecha,
                'estado'      => $estado,
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar tarea: " . $e->getMessage());
            return false;
        }
    }

    //===== MARCAR COMPLETADA / PENDIENTE =====\\
    public static function cambiarEstado($id, $estado) {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }
        try {
            $stmt = $BD->prepare("UPDATE tareas SET estado = :estado WHERE id_tarea = :id");
            return $stmt->execute(['id' => $id, 'estado' => $estado]);
        } catch (PDOException $e) {
            error_log("Error al cambiar estado de tarea: " . $e->getMessage());
            return false;
        }
    }
}
?>
