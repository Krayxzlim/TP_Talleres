-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-07-2025 a las 20:01:42
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `portal_talleres`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agenda`
--

CREATE TABLE `agenda` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `taller_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `agenda`
--

INSERT INTO `agenda` (`id`, `colegio_id`, `fecha`, `hora`, `taller_id`) VALUES
(2, 5, '2025-06-06', '14:33:00', 2),
(3, 8, '2025-05-09', '09:20:00', 2),
(4, 7, '2025-06-04', '11:20:00', 3),
(5, 3, '2025-07-15', '08:00:00', 3),
(6, 1, '2025-07-16', '15:08:00', 3),
(7, 7, '2025-04-24', '20:39:00', 3),
(8, 6, '2025-04-24', '21:07:00', 1),
(10, 2, '2025-07-17', '02:10:00', 3),
(11, 7, '2025-07-08', '21:28:00', 3),
(12, 7, '2025-07-04', '14:08:00', 1),
(13, 7, '2025-07-09', '01:11:00', 3),
(15, 7, '2025-07-10', '02:10:00', 3),
(16, 2, '2025-07-02', '12:03:00', 1),
(21, 7, '2025-07-22', '14:49:00', 3),
(25, 7, '2025-07-01', '15:04:00', 3),
(26, 7, '2025-07-11', '14:08:00', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agenda_talleristas`
--

CREATE TABLE `agenda_talleristas` (
  `agenda_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `agenda_talleristas`
--

INSERT INTO `agenda_talleristas` (`agenda_id`, `usuario_id`) VALUES
(2, 4),
(3, 4),
(5, 1),
(5, 4),
(6, 4),
(7, 2),
(10, 1),
(10, 4),
(11, 4),
(11, 9),
(12, 1),
(12, 9),
(13, 1),
(13, 4),
(16, 1),
(16, 4),
(25, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colegios`
--

CREATE TABLE `colegios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `primario` tinyint(1) DEFAULT 0,
  `secundario` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `colegios`
--

INSERT INTO `colegios` (`id`, `nombre`, `direccion`, `primario`, `secundario`) VALUES
(1, 'Escuela Primaria San Martín', 'Av. Belgrano 1234, CABA', 1, 0),
(2, 'Colegio Secundario Mariano Moreno', 'Calle Mitre 789, Rosario', 0, 1),
(3, 'Instituto Nuestra Señora del Carmen', 'Av. Colón 4321, Córdoba', 1, 1),
(4, 'Escuela N° 15 Domingo Faustino Sarmiento', 'Ruta 8 km 45, San Antonio de Areco', 1, 0),
(5, 'Colegio Integral Buenos Aires', 'Pasaje Rivarola 550, CABA', 1, 1),
(6, 'Instituto Técnico José de San Martín', 'Bv. Oroño 2100, Rosario', 0, 1),
(7, 'Colegio Bilingüe El Faro', 'Camino del Bajo 987, San Isidro', 1, 1),
(8, 'Escuela Rural Nº 32', 'Paraje Los Robles, La Pampa', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `talleres`
--

CREATE TABLE `talleres` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `talleres`
--

INSERT INTO `talleres` (`id`, `nombre`) VALUES
(3, 'Ciencia en Acción'),
(1, 'Creatividad Digital'),
(2, 'Cuidado del Entorno');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `rol` enum('tallerista','admin') NOT NULL DEFAULT 'tallerista',
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `correo`, `contraseña`, `rol`, `foto`) VALUES
(1, 'luciano', 'luciano.martin@talleres.com', '$2y$10$oRrLUY935m.KgK6bz6kxb.YO8hUaMrFsYiBrbXXQglz/7AvFTcwWO', 'tallerista', 'perfil_686bdc8cc37775.85736775.png'),
(2, 'pepe', 'pepe.gomez@talleres.com', '$2y$10$1iSZG3h0KZCQ1qSLQe9Dn.JdFdh8Vi2ELHw10cT3OskJx0dIxBRhm', 'tallerista', NULL),
(3, 'maria', 'maria.fernandez@talleres.com', '$2y$10$jBFiFZeEHmtpbkFkZ.WU0e/NiFXyKaNB2B7jCbD5D/WugP3VDhJcW', 'admin', NULL),
(4, 'juanperez', 'juan.perez@talleres.com', '$2y$10$LrbwG.2JMrzx6d5TSzJnF.cQygLYOymuhgQrkzWPoKPw0/IQi3b9a', 'tallerista', NULL),
(5, 'sofia', 'sofia.sanchez@talleres.com', '$2y$10$2wz/64EJCQTnoTW4mPCdx.JfqRXHZTQ6rnEil5DL5JX3DqXQC/toa', 'tallerista', NULL),
(6, 'ana', 'ana.martinez@talleres.com', '$2y$10$ZWUASTM7sS6loZumVKyFnuLrbTeimuqd0aMLvBW3hj5APMQjvPB9O', 'admin', NULL),
(8, 'lucia', 'lucia.garcia@talleres.com', '$2y$10$JWvKY/hdu74V9Dmg2fnMW.Uudku./l.YDf9KZaeZwh0gmxVFEUapq', 'admin', NULL),
(9, 'luciano2', 'asdsssqq@sda.com', '$2y$10$vq5rtFx2mTB1L2SuwdtsLOyy8zkXn1gXVQJGvRABCdl/JBf5Wf46u', 'tallerista', NULL),
(10, 'admin', 'admin@talleres.com', '$2y$10$mtMBpcUIMtwFki9zCdcpL.0iY8OmQ4TLNQ2iVkgb0EHCiSjxPn2Wy', 'admin', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `colegio_id` (`colegio_id`),
  ADD KEY `fk_taller` (`taller_id`);

--
-- Indices de la tabla `agenda_talleristas`
--
ALTER TABLE `agenda_talleristas`
  ADD PRIMARY KEY (`agenda_id`,`usuario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `colegios`
--
ALTER TABLE `colegios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `talleres`
--
ALTER TABLE `talleres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `colegios`
--
ALTER TABLE `colegios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `talleres`
--
ALTER TABLE `talleres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agenda`
--
ALTER TABLE `agenda`
  ADD CONSTRAINT `agenda_ibfk_1` FOREIGN KEY (`colegio_id`) REFERENCES `colegios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_taller` FOREIGN KEY (`taller_id`) REFERENCES `talleres` (`id`);

--
-- Filtros para la tabla `agenda_talleristas`
--
ALTER TABLE `agenda_talleristas`
  ADD CONSTRAINT `agenda_talleristas_ibfk_1` FOREIGN KEY (`agenda_id`) REFERENCES `agenda` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agenda_talleristas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
