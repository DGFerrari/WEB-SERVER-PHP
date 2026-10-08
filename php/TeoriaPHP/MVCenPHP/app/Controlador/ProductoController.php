<?php
class ProductoController {

    public function index(): void {
        $productos = Producto::todos();
        require 'Views/productos.php';

    }
}
?>