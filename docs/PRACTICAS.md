# Ejercicios de clase [↩](README.md)
Cada práctica con información de su contenido y enlaces a entregas.


## ▶ 01 Ejemplo de GET de Sandra
Explicación y ejemplo de cómo funciona un GET en PHP.
* Un poco lioso esto que copié de Sandra, pero bueno...

| Archivo | Función |
|-|-|
|[index.php](../php/EjerciciosPHP/practica01/index.php)|Formulario **GET** con los siguientes campos:<br>Usuario => text <br>Fichero => file|
|[get_post.php](../php/EjerciciosPHP/practica01/get_post.php)|Pilla los datos del **GET** y los muestra por pantalla (hay mucho **código comentado**)|

## ▶ 02 Práctica usando GET y POST

#### Ejercicio 1: Formulario usando GET

La página mostrará el mensaje “producto no encontrado” en caso de que el campo esté vacío. De lo contrario, deberá mostrar la información introducida.

Para que esto funcione, en la página debe de existir información de productos, la información que se mostrará es: código de producto y nombre de producto

| Archivo | Función |
|-|-|
|[ejercicio1/index.php](../php/EjerciciosPHP/practica02/ejercicio1/index.php)|Formulario **GET** con: <br>Nombre Producto => text|
|[ejercicio1/getProductos.php](../php/EjerciciosPHP/practica02/ejercicio1/getProductos.php)|Comprueba que el **Producto** pasado con GET esté en el array de productos. Si está, devuelve un mensaje con el nombre del producto; si no está, devuelve un mensaje avisando de que el producto no existe.|

#### Ejercicio 2: Formulario usando POST

Crear un archivo que contenga un formulario con un campo de texto para ingresar el nombre y el apellido del alumno.

Luego, otro archivo que contenga el formulario para mostrar la información del alumno. Esta página mostrará “Alumno no introducido” en caso de que los campos estén vacíos.

| Archivo | Función |
|-|-|
|[ejercicio2/datosIntroducidos.php](../php/EjerciciosPHP/practica02/ejercicio2/datosIntroducidos.php)|Formulario POST con los campos: <br>Nombre => text <br>Apellido => text|
|[ejercicio2/datosAlumno.php](../php/EjerciciosPHP/practica02/ejercicio2/datosAlumno.php)|Creamos variables con la información del **POST** y la mostramos por pantalla. Si se envió el formulario vacío, saltará un mensaje avisando de que no se ha introducido a ningún alumno.|

## ▶ 03 Práctica de Sesiones y redireccionamiento

Aplicación básica de inicio y cierre de sesión mediante un formulario y variables de sesión. El formulario envía el usuario y la contraseña a `bienvenida.php` mediante **POST**. Si ambos campos no están vacíos, se guarda el usuario en la sesión y se muestra un mensaje de bienvenida; si alguno está vacío, se redirige a `index.php`. Por ahora, no se comprueban credenciales específicas ni se verifica que exista una sesión activa al abrir la página de bienvenida.

| Archivo | Función |
|-|-|
|[bienvenida.php](../php/EjerciciosPHP/practica03/bienvenida.php)|Inicia la sesión, recibe los datos del formulario y, si no están vacíos, guarda el usuario en `$_SESSION` y muestra un saludo. Si algún campo está vacío, redirige a `index.php`. Incluye un enlace para cerrar la sesión.|
|[index.php](../php/EjerciciosPHP/practica03/index.php)|Muestra un formulario de acceso con campos para el usuario y la contraseña; envía los datos por **POST** a `bienvenida.php`.|
|[logout.php](../php/EjerciciosPHP/practica03/logout.php)|Inicia la sesión, elimina sus variables con `session_unset()`, la destruye con `session_destroy()` y redirige a `index.php`.|

## ▶ 04 Práctica de arquitectura MVC [📝](../php/EjerciciosPHP/practica04-arquitecturaMVC/Arquitectura.md)

Aplicación de login con contraseñas encriptadas organizada con el patrón **Modelo-Vista-Controlador**. El usuario se autentica desde `login.php`; el controlador comprueba las credenciales contra la base de datos (**PDO** con `password_verify()`) y, según el rol, redirige al panel de **admin** o de **user**. El panel de usuario permite cambiar el email y el de administrador muestra el listado de todos los usuarios. Las contraseñas se guardan cifradas con `password_hash()`.

#### Modelo

| Archivo | Función |
|-|-|
|[modelo/conexionBD.php](../php/EjerciciosPHP/practica04-arquitecturaMVC/modelo/conexionBD.php)|Define las constantes de conexión y crea la conexión a la base de datos con **PDO** dentro de un `try/catch`. Incluye comentada una alternativa con `mysqli`.|

#### Vista

| Archivo | Función |
|-|-|
|[vista/login.php](../php/EjerciciosPHP/practica04-arquitecturaMVC/vista/login.php)|Formulario de acceso con usuario y contraseña que envía por **POST** a `loginController.php`. Si existe un mensaje de error en la sesión lo muestra y luego destruye la sesión.|
|[vista/admin.php](../php/EjerciciosPHP/practica04-arquitecturaMVC/vista/admin.php)|Panel de administrador. Incluye `adminController.php` y muestra en una tabla el listado de todos los usuarios (usuario, nombre, apellidos, email y rol).|
|[vista/user.php](../php/EjerciciosPHP/practica04-arquitecturaMVC/vista/user.php)|Panel de usuario. Muestra los datos guardados en la sesión y un formulario para cambiar el email. Si existe un mensaje en la sesión (p. ej. tras cambiar el correo) lo muestra.|

#### Controlador

| Archivo | Función |
|-|-|
|[controlador/loginController.php](../php/EjerciciosPHP/practica04-arquitecturaMVC/controlador/loginController.php)|Recibe el formulario por **POST**, busca el usuario en la BD y verifica la contraseña con `password_verify()`. Si es correcta, guarda los datos en `$_SESSION` y redirige al panel según el rol (`admin` o `user`); si no, guarda un mensaje de error y vuelve a `login.php`.|
|[controlador/adminController.php](../php/EjerciciosPHP/practica04-arquitecturaMVC/controlador/adminController.php)|Hace un `SELECT` de todos los usuarios (sin la contraseña) y construye el HTML de las filas de la tabla que pinta la vista de admin.|
|[controlador/userController.php](../php/EjerciciosPHP/practica04-arquitecturaMVC/controlador/userController.php)|Recibe el nuevo email por **POST**, lo actualiza en la BD para el usuario de la sesión con un `UPDATE` y guarda un mensaje de confirmación antes de volver a `user.php`.|
