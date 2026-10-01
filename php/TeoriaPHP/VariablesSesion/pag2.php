<?php

echo "Estamos en la pagina2";
echo "<br>";

header("location: pag3.php");
// header("location: page3.php?name=.". $_GET['name']); // Bulnerable, NO HACER ESTO

?>