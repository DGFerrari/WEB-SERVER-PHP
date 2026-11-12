<?php

require_once 'conexionDB';

class consultasDB {

    //===== LOGIN =====\\
    /** 
     * @param $user -> Usuario del login
     * Consulta SQL que devuelve la contraseña del usuario
     * que intenta iniciar sesion.
     **/
    public static function login($user) {

    // Conexion a la BD
    $BD = $conexion::conectar();

    // Preparar y ejecutar sentencia
    $stmt = $BD->prepare("SELECT id_usuario, password FROM usuarios WHERE id_usuario = :u");
    $stmt->execute(['u' => $user]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // devolver valor
    return $result;

    }    
}
?>