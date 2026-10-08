<?php

// require del modelo y del controlador
$ruta = parse_url(
    $_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($ruta === '/productos') {
    (new ProductoController())->index();
} else {
    http_response_code(404);
}

?>