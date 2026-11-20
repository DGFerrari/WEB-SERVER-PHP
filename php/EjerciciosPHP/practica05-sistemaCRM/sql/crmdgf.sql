-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-11-2026 a las 01:23:50
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `crmdgf`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `tlf` varchar(20) DEFAULT NULL,
  `empresa` varchar(150) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_responsable` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre_completo`, `email`, `tlf`, `empresa`, `fecha_registro`, `usuario_responsable`) VALUES
(2, 'Pablo Mendoza', 'PaMe@gmail.com', '123456789', 'Mercadona', '2026-11-20 00:09:10', 3),
(3, 'Julia del Pino', 'Jpino@g.com', '987654321', 'Carrefour', '2026-11-20 00:09:59', 1),
(4, 'Alec Hernandez', 'alecmolon@rewind.es', '202602027', 'Youtube', '2026-11-20 00:14:24', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oportunidades`
--

CREATE TABLE `oportunidades` (
  `id_oportunidad` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `valor_estimado` decimal(10,2) DEFAULT 0.00,
  `estado` enum('progreso','ganada','perdida') NOT NULL DEFAULT 'progreso',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_responsable` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `oportunidades`
--

INSERT INTO `oportunidades` (`id_oportunidad`, `id_cliente`, `titulo`, `descripcion`, `valor_estimado`, `estado`, `fecha_creacion`, `usuario_responsable`) VALUES
(2, 3, 'Quiere Donar mucho dinero', 'Julia a visto que vale la pena invertir dinero en nosotros, asi que esta dispuesta a pagar 1.000€ vamooos.', 1000.00, 'ganada', '2026-11-20 00:11:20', 1),
(3, 2, 'Nos a denunciado', 'A pablo no le gusto nuestros servicios, así que nos a denunciado con un importe de 500€', 500.00, 'perdida', '2026-11-20 00:11:41', 3),
(4, 4, 'Bizzum para rewind', 'Tenemos que pagar a alec molon', 6.00, 'progreso', '2026-11-20 00:15:03', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id_tarea` int(11) NOT NULL,
  `id_oportunidad` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` datetime NOT NULL,
  `estado` enum('pendiente','completada') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id_tarea`, `id_oportunidad`, `descripcion`, `fecha`, `estado`) VALUES
(1, 4, 'Hay que tramitar el bizzum que sino nos quedamos sin rewind', '2026-12-31 09:00:00', 'completada'),
(2, 3, 'Toca buscar abogados para solucionar esto', '2026-11-21 09:00:00', 'pendiente'),
(3, 3, 'Buscar el motivo de la denuncia', '2026-11-21 09:00:00', 'pendiente'),
(4, 2, 'Pasarle nuestra cuenta del banco', '2026-11-18 09:00:00', 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('administrador','vendedor') NOT NULL DEFAULT 'vendedor',
  `estado` enum('Activo','Pendiente','Deshabilitado') NOT NULL DEFAULT 'Activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `password`, `rol`, `estado`, `fecha_registro`) VALUES
(1, 'Manolo Santiago', 'msantiago@crm.com', '$2y$10$ANv.oHMtFXPRtFX8wD.cDuf5wfk1w8j/wb7e8qasBIvxlBSzS/pVW', 'vendedor', 'Activo', '2026-11-19 22:41:52'),
(3, 'Daniel Guedes', 'danielguedes@crm.com', '$2y$10$4r2uMSgZKRzPJ1Wln8l2IuTzLaPIHte6tRBLCNqFXSKN8LnzI6jkG', 'administrador', 'Activo', '2026-11-20 00:04:31'),
(4, 'Biel Sanchez', 'sape@crm.com', '$2y$10$HybzRa/lSfA4a1vywwnyRuMN2ySeKVgk9PYqV/fGn9G4bTNxogl7C', 'administrador', 'Activo', '2026-11-20 00:05:51'),
(5, 'Andrei Istoc', 'grill@crm.com', '$2y$10$4AwWSB.yPjxYbKbU176Ztuqbi1.ZIT/kyiXGi85xpYM5trfQwYQoi', 'vendedor', 'Deshabilitado', '2026-11-20 00:06:12'),
(6, 'Don Pendiente', 'dp@crm.com', '$2y$10$2krSsL4cNEFIWK5Q.PH7PeECs7I1.c5ipASffFVj/64HmadG8UaR2', 'vendedor', 'Pendiente', '2026-11-20 00:06:42'),
(7, 'Alejandro Bellon', 'ab@crm.com', '$2y$10$z7prO7m71mTAO4GDVyeTmufVxq4SaOgYwUk/Yh3msPl2TddF8Rl3K', 'vendedor', 'Activo', '2026-11-20 00:07:10');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `usuario_responsable` (`usuario_responsable`);

--
-- Indices de la tabla `oportunidades`
--
ALTER TABLE `oportunidades`
  ADD PRIMARY KEY (`id_oportunidad`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `usuario_responsable` (`usuario_responsable`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id_tarea`),
  ADD KEY `id_oportunidad` (`id_oportunidad`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `oportunidades`
--
ALTER TABLE `oportunidades`
  MODIFY `id_oportunidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id_tarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`usuario_responsable`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `oportunidades`
--
ALTER TABLE `oportunidades`
  ADD CONSTRAINT `oportunidades_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `oportunidades_ibfk_2` FOREIGN KEY (`usuario_responsable`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`id_oportunidad`) REFERENCES `oportunidades` (`id_oportunidad`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
