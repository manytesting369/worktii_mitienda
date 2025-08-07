<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Configuraciones globales
$config = $pdo->query("SELECT * FROM configuracion LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$colorPrimario = $config['color_primario'] ?? '#3498db';
$colorSecundario = $config['color_secundario'] ?? '#2ecc71';
$tipografia = $config['tipografia'] ?? 'Segoe UI, sans-serif';

// Datos de usuario
$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? 'editor';

// Vistas seguras permitidas
$vista = $_GET['vista'] ?? 'dashboard_home';
$vistaPermitida = preg_match('/^[a-zA-Z0-9_\/]+$/', $vista) ? $vista : 'dashboard_home';
$rutaVista = __DIR__ . "/vistas/{$vistaPermitida}.php";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Catálogo Web</title>
    <link rel="stylesheet" href="/./css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --color-primario: <?= $colorPrimario ?>;
            --color-secundario: <?= $colorSecundario ?>;
        }
        body {
            font-family: <?= $tipografia ?>;
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/header.php' ?>

<div class="main">
<?php

    if (file_exists($rutaVista)) {
        include $rutaVista;
    } else {
        echo "<p>⚠️ Vista no encontrada.</p>";
    }

?>
</div>


</body>
</html>
