# Teoría de PHP
Ejemplos sobre el uso de variables de sesión y redirecciones entre páginas.

## Variables de sesión

Las variables de sesión permiten guardar información asociada a una sesión del navegador y consultarla desde distintas páginas. Para utilizarlas, se inicia la sesión con `session_start()` y se accede a sus datos mediante `$_SESSION`.

| Archivo | Función |
|-|-|
|**sesion1.php**|Inicia la sesión, guarda el valor `contenido_sesion` en `$_SESSION['hola']` y ofrece un enlace a la página que lo consulta.|
|**sesion2.php**|Inicia la sesión, muestra el valor guardado en `$_SESSION['hola']` y también muestra el identificador de la sesión.|

#### ▶ Redirecciones y parámetros GET

Estos archivos muestran ejemplos de navegación entre páginas y de lectura de un parámetro enviado mediante **GET**.

| Archivo | Función |
|-|-|
|**redirect.php**|Muestra un enlace a `sesion2.php`.|
|**pag2.php**|Muestra un mensaje e intenta redirigir a `pag3.php` mediante `header()`. La redirección se ejecuta después de imprimir contenido.|
|**pag3.php**|Muestra un mensaje e intenta imprimir el parámetro `name` recibido mediante **GET**.|
