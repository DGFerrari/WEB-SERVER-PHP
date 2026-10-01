<?php

    // Array productos -> id y nombre de cada producto
    $productos = [
        ["nom" => "Kebab"],
        ["nom" => "Durum"],
        ["nom" => "Falafel"],
        ["nom" => "Taco"]
    ];

    // Comprobamos si se ha enviado el formulario
    if (isset($_GET['producto']) && trim($_GET['producto']) !== "") {
        
        // Variables para guardar lo que buscamos y l resultado
        $busqueda = trim($_GET['producto']);
        $encontrado = false;

        echo "Resultados para: " . htmlspecialchars($busqueda) . "<br>";

        // Hacemo un foreach para comprovar si existe el producto
        foreach ($productos as $item) {

            // Buscando producto
            if (mb_stripos($item['nom'], $busqueda) !== false) {
                echo "Amego quiere que te prepare un " . $item['nom'] . "?";
                $encontrado = true;
            }
        }

        // Si no hay item, pues no hay
        if (!$encontrado) {
            echo "No queda amego, producto no encontrado";
        }

    } else {
        // Por si no pasan nada por el formulario
        echo "No puedes buscar aire";
    }

?>