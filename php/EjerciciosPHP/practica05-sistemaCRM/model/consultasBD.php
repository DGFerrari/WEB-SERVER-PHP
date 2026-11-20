<?php

require_once __DIR__ . '/conexionBD.php';

class ConsultasBD {

    //===== LOGIN =====\\
    /**
     * @param $email -> Email con el que el usuario inicia sesion
     * Devuelve los datos del usuario (incluido el hash de la contraseña)
     * o false si no existe. Devuelve null si no hay conexion con la BD.
     **/
    public static function login($email) {

        // Conexion a la BD
        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return null;
        }

        // Preparar y ejecutar sentencia
        $stmt = $BD->prepare(
            "SELECT id_usuario, nombre, email, password, rol, estado
             FROM usuarios
             WHERE email = :email"
        );
        $stmt->execute(['email' => $email]);

        // devolver valor
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //===== REGISTRO =====\\
    /**
     * @param $email -> Email a comprobar
     * Devuelve true si ya hay un usuario con ese email, false si no.
     * Devuelve null si no hay conexion con la BD.
     **/
    public static function existeEmail($email) {

        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return null;
        }

        $stmt = $BD->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * @param $nombre -> Nombre del usuario
     * @param $email  -> Email (unico)
     * @param $hash   -> Contraseña YA encriptada con password_hash()
     * Inserta un usuario nuevo. El rol ('vendedor') y el estado ('Activo')
     * se quedan con el valor por defecto de la tabla.
     * Devuelve true si se ha insertado, false si no.
     **/
    public static function registrar($nombre, $email, $hash) {

        $BD = ConexionBD::conectar();
        if ($BD === null) {
            return false;
        }

        try {
            $stmt = $BD->prepare(
                "INSERT INTO usuarios (nombre, email, password)
                 VALUES (:nombre, :email, :password)"
            );
            return $stmt->execute([
                'nombre'   => $nombre,
                'email'    => $email,
                'password' => $hash,
            ]);
        } catch (PDOException $e) {
            error_log("Error al registrar: " . $e->getMessage());
            return false;
        }
    }
}
?>
