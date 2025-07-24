<?php

require __DIR__ . '/../vendor/autoload.php'; // Adjust path if composer's vendor is not directly in root

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); // Adjust path to your project root where .env is located
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASSWORD'];
$charset = $_ENV['DB_CHARSET'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit;
}
?>