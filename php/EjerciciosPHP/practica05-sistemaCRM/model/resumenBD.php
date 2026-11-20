<?php

require_once __DIR__ . '/conexionBD.php';
require_once __DIR__ . '/oportunidadesBD.php';

/**
 * Consultas de totales para la vista resumen del administrador.
 */
class ResumenBD {

    /**
     * Devuelve todos los totales en un array, o null si no hay conexion:
     *  - total_clientes
     *  - oportunidades: [estado => ['cantidad' => n, 'valor' => €]]  (los 3 estados, aunque sean 0)
     *  - total_oportunidades, valor_total
     *  - tareas_pendientes, tareas_vencidas
     */
    public static function obtener() {
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return null;
        }

        // 1) Total de clientes
        $totalClientes = (int) $BD->query("SELECT COUNT(*) FROM clientes")->fetchColumn();

        // 2) Oportunidades por estado (cantidad y valor estimado)
        $oportunidades = [];
        foreach (OportunidadesBD::ESTADOS as $estado) {
            $oportunidades[$estado] = ['cantidad' => 0, 'valor' => 0.0];
        }
        $stmt = $BD->query(
            "SELECT estado, COUNT(*) AS cantidad, COALESCE(SUM(valor_estimado), 0) AS valor
             FROM oportunidades
             GROUP BY estado"
        );
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $oportunidades[$fila['estado']] = [
                'cantidad' => (int) $fila['cantidad'],
                'valor'    => (float) $fila['valor'],
            ];
        }

        // 3) Tareas pendientes (y cuantas de ellas ya han vencido)
        $stmt = $BD->prepare(
            "SELECT COUNT(*) AS pendientes, COALESCE(SUM(fecha < :ahora), 0) AS vencidas
             FROM tareas
             WHERE estado = 'pendiente'"
        );
        $stmt->execute(['ahora' => date('Y-m-d H:i:s')]);
        $tareas = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_clientes'      => $totalClientes,
            'oportunidades'       => $oportunidades,
            'total_oportunidades' => array_sum(array_column($oportunidades, 'cantidad')),
            'valor_total'         => array_sum(array_column($oportunidades, 'valor')),
            'tareas_pendientes'   => (int) $tareas['pendientes'],
            'tareas_vencidas'     => (int) $tareas['vencidas'],
        ];
    }
}
?>
