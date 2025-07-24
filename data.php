<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Carga el archivo .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Recupera variables del entorno
$server = $_ENV['DB_HOST'];
$user   = $_ENV['DB_USER'];
$pass   = $_ENV['DB_PASSWORD'];
$db     = $_ENV['DB_NAME'];

// Conexión
$conexion = new mysqli($server, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Ta malito: " . $conexion->connect_error);
} else {
    echo "Conectado correctamente";
}
?>
