<?php
$conexionPath = __DIR__ . '/../../config/conexion.php';
if (!file_exists($conexionPath)) {
    die("Error: No se encontró el archivo de conexión en $conexionPath");
}
require_once $conexionPath;

?>