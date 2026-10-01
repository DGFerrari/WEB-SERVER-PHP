# Ejercicios de clase [↩](./../../README.md)
Cada práctica con información de su contenido y enlaces a entregas.


## ▶ 01 Ejemplo de GET de Sandra
Explicación y ejemplo de cómo funciona un GET en PHP.
* Un poco lioso esto que copié de Sandra, pero bueno...

| Archivo | Función |
|-|-|
|[index.php](./practica01/index.php)|Formulario **GET** con los siguientes campos:<br>Usuario => text <br>Fichero => file|
|[get_post.php](./practica01/get_post.php)|Pilla los datos del **GET** y los muestra por pantalla (hay mucho **código comentado**)|

## ▶ 02 Práctica usando GET y POST

#### Ejercicio 1: Formulario usando GET

La página mostrará el mensaje “producto no encontrado” en caso de que el campo esté vacío. De lo contrario, deberá mostrar la información introducida.

Para que esto funcione, en la página debe de existir información de productos, la información que se mostrará es: código de producto y nombre de producto

| Archivo | Función |
|-|-|
|[ejercicio1/index.php](./practica02/ejercicio1/index.php)|Formulario **GET** con: <br>Nombre Producto => text|
|[ejercicio1/getProductos.php](./practica02/ejercicio1/getProductos.php)|Comprueba que el **Producto** pasado con GET esté en el array de productos. Si está, devuelve un mensaje con el nombre del producto; si no está, devuelve un mensaje avisando de que el producto no existe.|

#### Ejercicio 2: Formulario usando POST

Crear un archivo que contenga un formulario con un campo de texto para ingresar el nombre y el apellido del alumno.

Luego, otro archivo que contenga el formulario para mostrar la información del alumno. Esta página mostrará “Alumno no introducido” en caso de que los campos estén vacíos.

| Archivo | Función |
|-|-|
|[ejercicio2/datosIntroducidos.php](./practica02/ejercicio2/datosIntroducidos.php)|Formulario POST con los campos: <br>Nombre => text <br>Apellido => text|
|[ejercicio2/datosAlumno.php](./practica02/ejercicio2/datosAlumno.php)|Creamos variables con la información del **POST** y la mostramos por pantalla. Si se envió el formulario vacío, saltará un mensaje avisando de que no se ha introducido a ningún alumno.|

#### Ejercicio 3: Sesiones y redireccionamiento

Aplicación básica de inicio y cierre de sesión mediante un formulario y variables de sesión. El formulario envía el usuario y la contraseña a `bienvenida.php` mediante **POST**. Si ambos campos no están vacíos, se guarda el usuario en la sesión y se muestra un mensaje de bienvenida; si alguno está vacío, se redirige a `index.php`. Por ahora, no se comprueban credenciales específicas ni se verifica que exista una sesión activa al abrir la página de bienvenida.

| Archivo | Función |
|-|-|
|[bienvenida.php](./practica03/bienvenida.php)|Inicia la sesión, recibe los datos del formulario y, si no están vacíos, guarda el usuario en `$_SESSION` y muestra un saludo. Si algún campo está vacío, redirige a `index.php`. Incluye un enlace para cerrar la sesión.|
|[index.php](./practica03/index.php)|Muestra un formulario de acceso con campos para el usuario y la contraseña; envía los datos por **POST** a `bienvenida.php`.|
|[logout.php](./practica03/logout.php)|Inicia la sesión, elimina sus variables con `session_unset()`, la destruye con `session_destroy()` y redirige a `index.php`.|
