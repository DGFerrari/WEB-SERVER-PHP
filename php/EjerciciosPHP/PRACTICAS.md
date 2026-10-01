# Ejercicios de clase
Cada práctica con información de su contenido y enlaces a entregas.


## ▶ 01 Ejemplo de GET de Sandra
Explicación y ejemplo de cómo funciona un GET en PHP.
* Un poco lioso esto que copié de Sandra, pero bueno...

| Archivo | Función |
|-|-|
|**index.php**|Formulario **GET** con los siguientes campos:<br>Usuario => text <br>Fichero => file|
|**get_post.php**|Pilla los datos del **GET** y los muestra por pantalla (hay mucho **código comentado**)|

## ▶ 02 Práctica usando GET y POST

#### Ejercicio 1: Formulario usando GET

La página mostrará el mensaje “producto no encontrado” en caso de que el campo esté vacío. De lo contrario, deberá mostrar la información introducida.

Para que esto funcione, en la página debe de existir información de productos, la información que se mostrará es: código de producto y nombre de producto

| Archivo | Función |
|-|-|
|ejercicio1/index.php|Formulario **GET** con: <br>Nombre Producto => text|
|ejercicio1/getProductos.php|Comprueba que el **Producto** pasado con GET esté en el array de productos. Si está, devuelve un mensaje con el nombre del producto; si no está, devuelve un mensaje avisando de que el producto no existe.|

#### Ejercicio 2: Formulario usando POST

Crear un archivo que contenga un formulario con un campo de texto para ingresar el nombre y el apellido del alumno.

Luego, otro archivo que contenga el formulario para mostrar la información del alumno. Esta página mostrará “Alumno no introducido” en caso de que los campos estén vacíos.

| Archivo | Función |
|-|-|
|ejercicio2/datosIntroducidos.php|Formulario POST con los campos: <br>Nombre => text <br>Apellido => text|
|ejercicio2/datosAlumno.php|Creamos variables con la información del **POST** y la mostramos por pantalla. Si se envió el formulario vacío, saltará un mensaje avisando de que no se ha introducido a ningún alumno.|