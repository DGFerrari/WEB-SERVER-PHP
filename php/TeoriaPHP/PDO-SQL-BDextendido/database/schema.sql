-- Base de datos de práctica para los ejemplos de PDO y MySQLi.
-- Importar con:  mysql -u root -p < database/schema.sql
-- (o pegarlo en phpMyAdmin / MySQL Workbench)

CREATE DATABASE IF NOT EXISTS curso_php
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE curso_php;

DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS cuentas;

CREATE TABLE usuarios (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    -- Guarda el HASH, nunca la contraseña. VARCHAR(255) porque el tamaño
    -- del hash puede crecer si PHP cambia de algoritmo (bcrypt = 60 chars).
    password_hash VARCHAR(255) NOT NULL,
    edad       TINYINT UNSIGNED NULL,
    creado_en  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla para el ejemplo de transacciones (InnoDB es obligatorio para transacciones)
CREATE TABLE cuentas (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titular  VARCHAR(100) NOT NULL,
    saldo    DECIMAL(10,2) NOT NULL DEFAULT 0 CHECK (saldo >= 0)
) ENGINE=InnoDB;

-- Contraseña de todos los usuarios de prueba: secreto123
-- (hashes generados con password_hash(); cada uno es distinto por la "sal")
INSERT INTO usuarios (nombre, email, password_hash, edad) VALUES
    ('Ana López',    'ana@example.com',    '$2y$12$rYO.jEhizJ7HH24IV88OkejNjWDctCePMLUqSL0RP4sTvmTIGLCgu', 28),
    ('Luis Pérez',   'luis@example.com',   '$2y$12$Nw2IkGgc5udiC4h9HgS.z.iCbf6qn5VDI2qciY9Vm4lixRdIrT5JK', 35),
    ('María García', 'maria@example.com',  '$2y$12$19Q0Lk57CdeNCp/ON10be.o.MAnw7GMQZGt6/icETwpXnyYBoWgJ6', 22),
    ('Carlos Ruiz',  'carlos@example.com', '$2y$12$7emz/gKeigmac9D9fNbqW.JXG67rb5LBCv/AH4g7c9rNiF0aB0Grm', NULL);

INSERT INTO cuentas (titular, saldo) VALUES
    ('Ana López',  500.00),
    ('Luis Pérez', 100.00);
