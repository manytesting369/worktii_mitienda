-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 24-07-2025 a las 13:22:47
-- Versión del servidor: 10.6.22-MariaDB-cll-lve
-- Versión de PHP: 8.1.32

create database worktii_mitienda;
use worktii_mitienda;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

UPDATE configuracion
SET whatsapp_defecto = 'https://wa.me/993499188'
WHERE id = 1;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
select * from categorias;
--
-- Base de datos: `worktii_catalogoweb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Abrigo'),
(2, 'Pantalon'),
(3, 'Blazer'),
(4, 'Blusa'),
(5, 'Falda'),
(6, 'Pantalon Palazzo'),
(7, 'Top');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colores`
--

CREATE TABLE `colores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `codigo_hex` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `colores`
--

INSERT INTO `colores` (`id`, `nombre`, `codigo_hex`) VALUES
(1, 'Azul', '#0000ff'),
(2, 'Rojo', '#ff0000'),
(3, 'Beige', '#c0a890'),
(4, 'Vino', '#883646'),
(5, 'Verde Agua', '#9fdbd3'),
(7, 'Naranja', '#f66b44'),
(8, 'Negro', '#000000'),
(9, 'Verde', '#02946a'),
(10, 'Verde Cemento', '#7faab3'),
(11, 'Hueso', '#e3dfdc'),
(12, 'Azul Electrico', '#0053bd'),
(13, 'Rosa', '#fc939a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `color_primario` varchar(7) DEFAULT '#000000',
  `color_secundario` varchar(7) DEFAULT '#ffffff',
  `tipografia` varchar(100) DEFAULT 'Arial, sans-serif',
  `link_facebook` varchar(255) DEFAULT NULL,
  `link_instagram` varchar(255) DEFAULT NULL,
  `link_tiktok` varchar(255) DEFAULT NULL,
  `whatsapp_defecto` varchar(255) DEFAULT NULL,
  `modo_oscuro` tinyint(1) DEFAULT 0,
  `plantilla` int(11) DEFAULT 1,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `logo`, `color_primario`, `color_secundario`, `tipografia`, `link_facebook`, `link_instagram`, `link_tiktok`, `whatsapp_defecto`, `modo_oscuro`, `plantilla`, `fecha_actualizacion`) VALUES
(1, 'uploads/logo_6877b9bb998d9.png', '#007bff', '#ffffff', 'Arial, sans-serif', 'https://www.facebook.com/share/1Cgc6Bg8Et/', 'https://www.instagram.com/estiloinnato.pe?igsh=YzZ2N2V1aXQ0NzRw', 'https://www.tiktok.com/@estiloinnato.pe?is_from_webapp=1&sender_device=pc', '993499188', 1, 1, '2025-07-16 15:16:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_producto`
--

CREATE TABLE `imagenes_producto` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `ruta` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `imagenes_producto`
--

INSERT INTO `imagenes_producto` (`id`, `producto_id`, `ruta`, `descripcion`) VALUES
(1, 2, 'uploads/img_6869f0babbc55.jpg', NULL),
(2, 1, 'uploads/img_686a0e05a594a.jpg', NULL),
(3, 1, 'uploads/img_686a0e22b287e.jpg', NULL),
(4, 1, 'uploads/img_686a0e22b3124.jpg', NULL),
(5, 1, 'uploads/img_686a0e22b374c.jpg', NULL),
(6, 1, 'uploads/img_686a0e22b3ff8.jpg', NULL),
(7, 3, 'uploads/img_6875c0bcaaf5a.jpg', NULL),
(8, 4, 'uploads/img_6875c226da4c9.jpg', NULL),
(9, 4, 'uploads/img_6875c2272e032.jpg', NULL),
(10, 5, 'uploads/img_6877297da8587.jpg', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `estado_activo` tinyint(1) DEFAULT 1,
  `redes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`redes`)),
  `visitas` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `categoria_id`, `video_url`, `estado_activo`, `redes`, `visitas`) VALUES
(1, 'Luana', 'dgshddyrhtsfjrrmjsrrym}\r\nd\r\nd\r\nd\r\nd\r\n\r\n', 285.00, 1, 'https://www.tiktok.com/@estiloinnato.pe/video/7421320497834659077', 1, NULL, 24),
(2, 'asdsad', 'adadsa', 60.00, 2, '', 1, NULL, 5),
(3, 'dasddas', 'adasdasd', 30.00, 1, '', 1, NULL, 10),
(4, 'Ajajjaj', 'Hajajaj', 80.00, 1, '', 1, NULL, 25),
(5, 'adasddasdasd', 'asdadda', 50.00, 2, '', 1, NULL, 25);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_tallas_colores`
--

CREATE TABLE `producto_tallas_colores` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `talla_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto_tallas_colores`
--

INSERT INTO `producto_tallas_colores` (`id`, `producto_id`, `talla_id`, `color_id`, `stock`) VALUES
(1, 1, 3, 1, 20),
(2, 2, 3, 1, 2),
(3, 3, 3, 1, 5),
(4, 4, 3, 1, 5),
(5, 5, 3, 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promociones`
--

CREATE TABLE `promociones` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('porcentaje','fijo','envio_gratis') DEFAULT 'porcentaje',
  `valor` decimal(10,2) DEFAULT 0.00,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `promociones`
--

INSERT INTO `promociones` (`id`, `producto_id`, `titulo`, `descripcion`, `tipo`, `valor`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
(2, 1, 'Oferta', 'Por la compra de 3 productos', 'porcentaje', 30.00, '2025-07-10', '2025-07-20', 'activo'),
(3, 4, 'Descuento', 'X la compra de 3 productos', 'porcentaje', 30.00, '2025-07-14', '2025-07-17', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tallas`
--

CREATE TABLE `tallas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tallas`
--

INSERT INTO `tallas` (`id`, `nombre`) VALUES
(1, 'S'),
(3, 'L'),
(4, 'XL'),
(5, 'XS'),
(6, 'M');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('administrador','editor') DEFAULT 'editor',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `contrasena`, `rol`, `fecha_creacion`) VALUES
(1, 'D_user', 'admin@demo.com', '$2y$10$Mv75tYpq7pMN6l4/FS2hC.UxdQm9vrTjdx.xcRVG0vz6kbmmY6hKq', 'administrador', '2025-07-08 22:44:21');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `colores`
--
ALTER TABLE `colores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `imagenes_producto`
--
ALTER TABLE `imagenes_producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `producto_tallas_colores`
--
ALTER TABLE `producto_tallas_colores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `talla_id` (`talla_id`),
  ADD KEY `color_id` (`color_id`);

--
-- Indices de la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `tallas`
--
ALTER TABLE `tallas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `colores`
--
ALTER TABLE `colores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `imagenes_producto`
--
ALTER TABLE `imagenes_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `producto_tallas_colores`
--
ALTER TABLE `producto_tallas_colores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tallas`
--
ALTER TABLE `tallas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `imagenes_producto`
--
ALTER TABLE `imagenes_producto`
  ADD CONSTRAINT `imagenes_producto_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `producto_tallas_colores`
--
ALTER TABLE `producto_tallas_colores`
  ADD CONSTRAINT `producto_tallas_colores_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `producto_tallas_colores_ibfk_2` FOREIGN KEY (`talla_id`) REFERENCES `tallas` (`id`),
  ADD CONSTRAINT `producto_tallas_colores_ibfk_3` FOREIGN KEY (`color_id`) REFERENCES `colores` (`id`);

--
-- Filtros para la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD CONSTRAINT `promociones_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
