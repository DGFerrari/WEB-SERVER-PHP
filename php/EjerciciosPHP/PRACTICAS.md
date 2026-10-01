# Ejercicios de clase
Cada practica con info de su contenido y enlaces a entregas.


## ▶ 01 Ejemplo de GET de Sandra
Explicacion y ejemplo de como funciona un GET en PHP
* Un poco lioso esto que copie de Sandra, pero bueno...

| Archivo | Función |
|-|-|
|**index.php**|Formulario **GET** con los siguientes campos: **text** => Usuario y **file** => Fichero|
|**get_post.php**|Pilla los datos del **GET** y los muestra por pantalla (hay mucho **codigo comentado**)|

## ▶ 02 Practica usando GET y POST

#### Ejercicio 1: Formulario usando GET

La página mostrará el mensaje “producto no encontrado” en caso de que el campo esté vacío. De lo contrario, deberá de mostrar la información introducida.

Para que esto funcione, en la página debe de existir información de productos, la información que se mostrará es: código de producto y nombre de producto

| Archivo | Función |
|-|-|
|ejercicio1/index.php|Formulario **GET** con: **text** => Nombre Producto|
|ejercicio1/getProductos.php|Comprueba que el **Producto** pasado con GET este en el array de Productos. Si esta devuelve un mensaje con el nombre del producto, si no esta devuelve un mensaje avisando que el producto no existe|

#### Ejercicio 2: Formulario usando POST

Crear archivo que contenga un formulario con un campo de texto para ingresar el nombre y el apellido del alumno.

Luego, otro archivo que contenga el formulario para mostrar la información del alumno. Esta página, mostrará “Alumno no introducido” en caso de que los campos estén vacíos.

- Aqui va tabla con archivos