# Teoría de PHP [↩](/../README.md)
Ejemplos sobre el uso de variables de sesión, redirecciones entre páginas, conexión a base de datos y el patrón MVC.

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

## Conexión a base de datos

Ejemplos de conexión a una base de datos **MySQL** y de lectura de datos desde una tabla (`alumnos`). La conexión se centraliza en un archivo que se incluye con `require` desde las páginas que la necesitan.

| Archivo | Función |
|-|-|
|**conexion.php**|Define las constantes de conexión (`HOST_DB`, `USER_DB`, `PASS_DB`, `NAME_DB`) y crea la conexión a la base de datos con `mysqli`.|
|**bdInfo1.php**|Incluye `conexion.php`, lanza un `SELECT * FROM alumnos` y recorre el resultado con `mysqli_fetch_array()` mostrando únicamente el nombre de cada alumno.|
|**infoBD.php**|Incluye `conexion.php` y muestra los datos en una tabla HTML. Comprueba con `mysqli_num_rows()` si hay filas: si no hay, avisa de que no hay alumnos; si las hay, recorre el resultado con `mysqli_fetch_assoc()` e imprime nombre y apellido.|

## Patrón MVC en PHP

Ejemplo de estructura **Modelo-Vista-Controlador** aplicada a un listado de productos. El punto de entrada enruta la petición, el controlador pide los datos al modelo y la vista los pinta. Es un ejemplo en construcción que sirve de base para entender la separación de responsabilidades.

| Archivo | Función |
|-|-|
|**public/index.php**|Punto de entrada. Lee la ruta de la petición con `parse_url()` y, si es `/productos`, instancia `ProductoController` y llama a su método `index()`. En cualquier otra ruta devuelve un **404**.|
|**app/Controlador/ProductoController.php**|Controlador que pide al modelo todos los productos (`Producto::todos()`) y carga la vista de productos.|
|**app/Controlador/ConexionSQL.php**|Define los parámetros de conexión por **PDO** (DSN y opciones) pensados para encapsular la conexión a la base de datos.|
|**app/Modelo/Producto.php**|Modelo que realiza la consulta `SELECT nombre, precio FROM productos` mediante **PDO** y devuelve los resultados.|
|**app/Vista/Productos.php**|Vista que recorre el array de productos recibido y los muestra en una lista, escapando la salida con `htmlspecialchars()`.|
