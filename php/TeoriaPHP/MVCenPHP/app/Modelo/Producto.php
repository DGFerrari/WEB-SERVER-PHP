<?php
class Producto {

    public static function todos() {

    $pdo = new PDO($dsn, $u, $p);
    return $pdo->query(
        'SELECT nombre, precio
        FROM productos'
    )->fetchAll(PDO::FETCH_ASSOC);

    }

}
?>