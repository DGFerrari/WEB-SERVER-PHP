# Temario: Bases de datos en PHP con PDO y MySQLi

Requisitos: PHP 8.2+ con las extensiones `pdo_mysql` y `mysqli`, y un servidor MySQL/MariaDB (XAMPP, Laragon, Docker...).

## Puesta en marcha

1. Importa la base de datos: `mysql -u root -p < database/schema.sql`
2. Ajusta credenciales en `config.php` (o con variables de entorno `DB_HOST`, `DB_USER`...).
3. Ejecuta cada tema desde la raíz: `php pdo/01_conexion.php`
4. Para el MVC: entra en `pdo/mvc` (o `mysqli/mvc`), ejecuta `php -S localhost:8000` y abre http://localhost:8000

## Estructura

```
config.php              Credenciales compartidas
database/schema.sql     Tablas y datos de prueba
pdo/                    mysqli/              (mismos temas en ambas)
├── 01_conexion.php         Conexión, opciones, errores de conexión
├── 02_consultas_select.php Leer datos y formas de recorrerlos
├── 03_consultas_preparadas.php  ★ Seguridad: parámetros, IN, LIKE, LIMIT
├── 04_insert_update_delete.php  CRUD, último id, filas afectadas, duplicados
├── 05_transacciones.php    Todo o nada: commit / rollback
├── 06_contrasenas.php      ★ password_hash / verify / needs_rehash + registro y login
├── conexion.php            Conexión reutilizada por los temas 02-05
└── mvc/
    ├── index.php           Front controller (único punto de entrada web)
    ├── ejemplo_uso.php     Cómo llamar al modelo desde cualquier sitio
    ├── config/Database.php Una conexión compartida
    ├── models/Model.php    Clase base: todos(), buscarPorId(), contar(), eliminar()
    ├── models/Usuario.php  Modelo concreto: crear(), autenticar(), cambiarPassword(), paginar()...
    ├── services/Password.php  Hash, verificación, rehash y reglas de contraseña
    ├── controllers/UsuarioController.php
    └── views/usuarios/     index.php, ver.php
```

Los usuarios de prueba de `schema.sql` tienen todos la contraseña `secreto123`.

Orden recomendado: temas 01→06 de `pdo/`, luego `pdo/mvc/ejemplo_uso.php`, luego la web MVC. Después repite con `mysqli/` comparando archivo a archivo.

## Chuleta PDO vs MySQLi

| Tarea | PDO | MySQLi |
|---|---|---|
| Conectar | `new PDO($dsn, $u, $p, $opts)` | `new mysqli($h, $u, $p, $db, $port)` |
| Charset | en el DSN: `charset=utf8mb4` | `$db->set_charset('utf8mb4')` |
| Excepciones | `PDO::ATTR_ERRMODE => ERRMODE_EXCEPTION` | `mysqli_report(MYSQLI_REPORT_ERROR \| MYSQLI_REPORT_STRICT)` |
| Marcadores | `?` y `:nombre` | solo `?` |
| Preparar y ejecutar | `prepare()` + `execute([...])` | `execute_query($sql, [...])` o `prepare` + `bind_param` |
| Una fila | `fetch()` (false si no hay) | `fetch_assoc()` (null si no hay) |
| Todas | `fetchAll()` | `fetch_all(MYSQLI_ASSOC)` |
| Un valor | `fetchColumn()` | `fetch_column()` |
| Último id | `$pdo->lastInsertId()` | `$stmt->insert_id` |
| Filas afectadas | `$stmt->rowCount()` | `$stmt->affected_rows` |
| Transacción | `beginTransaction / commit / rollBack` | `begin_transaction / commit / rollback` |
| Código error MySQL | `$e->errorInfo[1]` | `$e->getCode()` |

## ¿Cuál uso?

PDO, salvo que tengas un motivo concreto. Funciona con muchos motores, admite marcadores con nombre, tiene más modos de fetch y es lo que usan por debajo Laravel, Symfony/Doctrine y casi todo el ecosistema. MySQLi es perfectamente válido si solo vas a usar MySQL o mantienes código que ya lo usa, o si necesitas algo específico de MySQL (consultas asíncronas, `multi_query`).

## Buenas prácticas (lo "óptimo")

1. Sentencias preparadas siempre que haya datos externos. Nunca concatenes variables en el SQL.
2. Lo que no puede ser parámetro (tabla, columna, `ASC/DESC`) se valida contra una lista blanca.
3. Excepciones activadas, `utf8mb4` y, en PDO, `ATTR_EMULATE_PREPARES => false` y `FETCH_ASSOC`.
4. Nunca enseñes al usuario el mensaje real del error: va a `error_log()`, al usuario un mensaje genérico.
5. Una conexión por petición, compartida (`Database::getConexion()`).
6. Los modelos reciben la conexión por constructor (inyección de dependencias), no la crean.
7. El modelo solo habla con la BD: no hace `echo`, no lee `$_POST`. Traduce errores técnicos (1062) a excepciones de negocio (`DomainException`).
8. El controlador valida la entrada; la vista escapa la salida con `htmlspecialchars()`.
9. Selecciona columnas concretas en lugar de `SELECT *`.
10. Operaciones que dependen unas de otras van en una transacción (tablas InnoDB).
11. Acciones que modifican datos solo por `POST`, y tras guardar, redirección (patrón PRG).
12. Contraseñas: solo `password_hash()` con `PASSWORD_DEFAULT` y `password_verify()`; nunca md5/sha1 ni cifrado reversible. Columna `VARCHAR(255)`, el hash nunca sale del modelo, `password_needs_rehash()` en el login y mensaje genérico "email o contraseña incorrectos".
13. En proyectos reales: Composer con autoload PSR-4, credenciales en `.env` fuera de Git y protección CSRF en formularios (este ejemplo no la incluye para no complicarlo).
