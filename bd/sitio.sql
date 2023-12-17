-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306:3306
-- Tiempo de generación: 10-12-2023 a las 16:33:50
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sitio`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cascos`
--

CREATE TABLE `cascos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `imagen` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cascos`
--

INSERT INTO `cascos` (`id`, `nombre`, `imagen`) VALUES
(5, 'AGV', '1697487801_agv.jpg'),
(6, 'ICON BLACK', '1697500964_icon2.jpg'),
(8, 'ICONE RED', '1697510660_icon.jpg'),
(9, 'ICON BLUE', '1697510694_agv1.jpg'),
(10, 'BLACK DARK', '1697510740_icone3.jpg'),
(11, 'SHARK', '1697511031_shark.jpg'),
(12, 'BLACK FRIDAY', '1697511060_Shaft.jpg'),
(15, 'SAGITARIO', '1697512431_Picis.jpg');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cascos`
--
ALTER TABLE `cascos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cascos`
--
ALTER TABLE `cascos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
