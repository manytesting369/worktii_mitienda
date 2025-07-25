-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 24-07-2025 a las 13:22:47
-- Versión del servidor: 10.6.22-MariaDB-cll-lve
-- Versión de PHP: 8.1.32
drop database worktii_mitienda2;
create database worktii_mitienda2;
use worktii_mitienda; 
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- SET SQL_SAFE_UPDATES = 0;
-- DELETE FROM categorias;
-- delete from imagenes_producto;
-- SET SQL_SAFE_UPDATES = 1;

-- drop table colores;
-- drop table producto_tallas_colores;
-- drop table tallas;
-- select* from tallas;

-- Estructura de tabla para la tabla `configuracion`

-- Base de datos y configuración inicial
CREATE DATABASE IF NOT EXISTS worktii_mitienda2;
USE worktii_mitienda2;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

START TRANSACTION;

-- Tabla: categorias
CREATE TABLE `categorias` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: configuracion
CREATE TABLE `configuracion` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `logo` VARCHAR(255) DEFAULT NULL,
  `color_primario` VARCHAR(7) DEFAULT '#000000',
  `color_secundario` VARCHAR(7) DEFAULT '#ffffff',
  `tipografia` VARCHAR(100) DEFAULT 'Arial, sans-serif',
  `link_facebook` VARCHAR(255) DEFAULT NULL,
  `link_instagram` VARCHAR(255) DEFAULT NULL,
  `link_tiktok` VARCHAR(255) DEFAULT NULL,
  `whatsapp_defecto` VARCHAR(255) DEFAULT NULL,
  `modo_oscuro` TINYINT(1) DEFAULT 0,
  `plantilla` INT(11) DEFAULT 1,
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Tabla: productos (nota: columna video_url eliminada como pediste)
CREATE TABLE `productos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `categoria_id` INT(11) DEFAULT NULL,
  `estado_activo` TINYINT(1) DEFAULT 1,
  `redes` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`redes`)),
  `visitas` INT(11) DEFAULT 0,
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: imagenes_producto
CREATE TABLE `imagenes_producto` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `producto_id` INT(11) DEFAULT NULL,
  `ruta` VARCHAR(255) DEFAULT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `fk_imagenes_producto_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: promociones
CREATE TABLE `promociones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `producto_id` INT(11) NOT NULL,
  `titulo` VARCHAR(100) DEFAULT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `tipo` ENUM('porcentaje','fijo','envio_gratis') DEFAULT 'porcentaje',
  `valor` DECIMAL(10,2) DEFAULT 0.00,
  `fecha_inicio` DATE DEFAULT NULL,
  `fecha_fin` DATE DEFAULT NULL,
  `estado` ENUM('activo','inactivo') DEFAULT 'activo',
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `fk_promociones_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: usuarios
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `contrasena` VARCHAR(255) NOT NULL,
  `rol` ENUM('administrador','editor') DEFAULT 'editor',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert admin por defecto
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `contrasena`, `rol`, `fecha_creacion`) VALUES
(1, 'D_user', 'admin@demo.com', '$2y$10$Mv75tYpq7pMN6l4/FS2hC.UxdQm9vrTjdx.xcRVG0vz6kbmmY6hKq', 'administrador', '2025-07-08 22:44:21');
COMMIT;

