-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 13-06-2025 a las 18:43:41
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `archivos_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE `archivos` (
  `id` int NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ruta` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_registrador` int NOT NULL,
  `autorizado` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `archivos`
--

INSERT INTO `archivos` (`id`, `nombre`, `ruta`, `fecha_subida`, `id_registrador`, `autorizado`) VALUES
(112, 'gaider_gete.docx', '../uploads/gaider_gete.docx', '2025-06-13 04:13:54', 7, 0),
(113, 'BillPrint.pdf', '../uploads/BillPrint.pdf', '2025-06-13 04:14:48', 7, 0),
(118, '_PLAN DE NEGOCIO Final.docx', '../uploads/_PLAN DE NEGOCIO Final.docx', '2025-06-13 15:30:50', 4, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro`
--

CREATE TABLE `registro` (
  `id_registro` int NOT NULL,
  `id_registrador` int DEFAULT NULL,
  `docentes` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `estudiantes` text COLLATE utf8mb4_general_ci NOT NULL,
  `id_tipo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `ano` date NOT NULL,
  `modalidad` text COLLATE utf8mb4_general_ci NOT NULL,
  `nom_proyecto` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `id_programa` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `ids_estudiantes` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registro`
--

INSERT INTO `registro` (`id_registro`, `id_registrador`, `docentes`, `estudiantes`, `id_tipo`, `ano`, `modalidad`, `nom_proyecto`, `id_programa`, `ids_estudiantes`) VALUES
(115, 7, 'agafaf', 'afafaf', 'Convocatoria externa', '2025-06-13', 'Convocatoria externa', 'aadad', 'Facultad de Ciencias de la Educación', '2234234'),
(116, 7, 'ffafas', 'fafad', 'Convocatoria de creación de semilleros', '2025-06-13', 'Convocatoria de financiación de semilleros', 'rrrarara', 'Facultad de Ingeniería en Sistemas', '34234324'),
(119, 4, 'mario', 'joseluis', 'Proyectos de inteligencia artificial', '2025-06-13', 'Proyecto institucional', 'proyecto preparado', 'Facultad de Música y Producción Audiovisual', '123456');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_registrador` int NOT NULL,
  `Nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `sexo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `estatuto` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contraseña` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `correo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_registrador`, `Nombre`, `sexo`, `estatuto`, `contraseña`, `correo`) VALUES
(3, 'Frank correa', 'Masculino', 'Docente', '$2y$10$3V4v8/oVgaPtSa0EALoX7Osx.Nu.37k2ffHkvAE5YciW.j0IiD2Hi', 'Frankcoadmin@amigmail.com'),
(4, 'Usuario1', 'Masculino', 'Estudiante', '$2y$10$MwC/VRjJc1ambAY0vj.mIOc3dJg.jKMcl35Inv0gE.V2gXBzUonR6', 'Usuario1@gmail.com'),
(5, 'Angel David Guzman', 'Masculino', 'Estudiante', '$2y$10$7GD0G39LWorNhJGynfXgkuNbQQBSJErIw/7q124m0DJfambOdOtlm', 'aguzmantor@gmail.com'),
(6, 'usuario2', 'Masculino', 'Estudiante', '$2y$10$Uk3qqfPjSu.9fxk90zZG5eTrClioXIonkLQTD6X4eqlxrJBB2bbua', 'piwi1423@uniguajira.edu.co'),
(7, 'Udilva', 'Masculino', 'Docente', '$2y$10$QfJdeINXMVsXzqWNRyohEeDx5jrhjT/mQ4BnZYF7j1HMf00bgDtq6', 'nunezhector153@uniguajira.edu.co'),
(20, 'hector jose chamaco', 'Masculino', 'Docente', '$2y$10$GtebJgIxObGZLkbUJZK/lefn790wcTxnACNYDkaSwe9BHfaPbqB72', 'nunezhector153@gmail.com'),
(21, 'hectorjosechamaco', 'Masculino', 'Docente', '$2y$10$0XidPfQErFfnRKSbWYDKKO07IFjXe6.O9OAk8RnC0bKfmYsut86i2', 'hjchamorro@uniguajira.edu.co');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_registrador` (`id_registrador`);

--
-- Indices de la tabla `registro`
--
ALTER TABLE `registro`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_docente` (`id_registrador`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_registrador`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivos`
--
ALTER TABLE `archivos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT de la tabla `registro`
--
ALTER TABLE `registro`
  MODIFY `id_registro` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_registrador` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD CONSTRAINT `archivos_ibfk_1` FOREIGN KEY (`id_registrador`) REFERENCES `usuarios` (`id_registrador`);

--
-- Filtros para la tabla `registro`
--
ALTER TABLE `registro`
  ADD CONSTRAINT `registro_ibfk_1` FOREIGN KEY (`id_registrador`) REFERENCES `usuarios` (`id_registrador`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
