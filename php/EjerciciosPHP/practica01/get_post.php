<?php

/*
* get - envia datos a través de la url y estos quedan visibles en ella
* (limitado a 2000 carácteres)
* post - envía datos pero es de forma más segura ya que estos no se muestran en la url
*/

print_r($_GET); //de esta forma podemos mostrar la información que se está recogiendo
print_r($_GET['usuario']); //recojer nombre del usuario


//print_r($_POST); //para recojer la informacion del formulario cuando se envia por post
//echo "<br>";

if (isset($_GET['usuario'])) {
    print_r($_GET['usuario']);
}


/*
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "Mostrar informacion del usuario: ".print_r( $_POST['usuario']);
}
*/

/*
print_r($_FILES); //mostrar información fichero (es un array, podremos acceder por elementos)
print_r($_FILES['Fichero']['name']) ; 
*/

?>