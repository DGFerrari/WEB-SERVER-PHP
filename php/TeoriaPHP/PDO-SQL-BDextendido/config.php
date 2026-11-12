<?php
/**
 * Configuración compartida de la base de datos.
 *
 * Buenas prácticas:
 *  - En un proyecto real NO subas credenciales a Git. Léelas de variables
 *    de entorno (getenv) o de un archivo .env ignorado por .gitignore.
 *  - Usa siempre utf8mb4 (soporta emojis y todo Unicode). "utf8" en MySQL
 *    es un alias antiguo e incompleto.
 */
declare(strict_types=1);

return [
    'host'    => getenv('DB_HOST') ?: '127.0.0.1',
    'port'    => (int) (getenv('DB_PORT') ?: 3306),
    'dbname'  => getenv('DB_NAME') ?: 'curso_php',
    'user'    => getenv('DB_USER') ?: 'root',
    'pass'    => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];
