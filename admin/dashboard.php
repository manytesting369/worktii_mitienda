<?php
session_start();
require_once '../config/conexion.php';

// Verificar login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? 'editor';

// Configuración
$config = $pdo->query("SELECT * FROM configuracion LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$colorPrimario = $config['color_primario'] ?? '#3498db';
$colorSecundario = $config['color_secundario'] ?? '#2ecc71';
$tipografia = $config['tipografia'] ?? 'Segoe UI, sans-serif';

// MÉTRICAS
$total_productos = $pdo->query("SELECT COUNT(*) FROM productos WHERE estado_activo = 1")->fetchColumn();
$total_promos = $pdo->query("SELECT COUNT(*) FROM promociones WHERE estado = 'activo'")->fetchColumn();
$total_visitas = $pdo->query("SELECT SUM(visitas) FROM productos")->fetchColumn();
$sin_imagenes = $pdo->query("
    SELECT COUNT(*) FROM productos 
    WHERE estado_activo = 1 AND id NOT IN 
    (SELECT DISTINCT producto_id FROM imagenes_producto)
")->fetchColumn();

$top5 = $pdo->query("
    SELECT id, nombre, visitas 
    FROM productos 
    WHERE estado_activo = 1 
    ORDER BY visitas DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Catálogo Web</title>
    <link rel="stylesheet" href="../css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/dashboard.css">
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

<div class="sidebar">
    <h2>Admin</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="dashboard.php?vista=productos">📦 Productos</a>
    <a href="dashboard.php?vista=categorias">📁 Categorías</a>
    <a href="dashboard.php?vista=tallas">📏 Tallas</a>
    <a href="dashboard.php?vista=colores">🎨 Colores</a>
    <a href="dashboard.php?vista=promociones">🎯 Promociones</a>
    <a href="dashboard.php?vista=configuracion">⚙️ Configuración</a>
    <a href="#">👥 Usuarios</a>
    <a href="logout.php">🚪 Cerrar sesión</a>
</div>

<div class="topbar">
    <div><strong>Catálogo Web</strong></div>
    <div class="user">👤 <?= htmlspecialchars($nombre) ?> (<?= $rol ?>)</div>
</div>

<div class="main">
<?php
$vista = $_GET['vista'] ?? 'inicio';

switch ($vista) {
    case 'productos': include 'vistas/productos_listado.php'; break;
    case 'producto_nuevo': include 'vistas/productos_nuevo.php'; break;
    case 'producto_editar': include 'vistas/productos_editar.php'; break;
    case 'categorias': include 'vistas/categorias.php'; break;
    case 'editar_categoria': include 'vistas/editar_categoria.php'; break;
    case 'tallas': include 'vistas/tallas.php'; break;
    case 'editar_talla': include 'vistas/editar_talla.php'; break;
    case 'colores': include 'vistas/colores.php'; break;
    case 'configuracion': include 'vistas/configuracion.php'; break;
    case 'promociones': include 'vistas/promociones.php'; break;
    case 'promociones_nuevo': include 'vistas/promociones_nuevo.php'; break;
    case 'promociones_editar': include 'vistas/promociones_editar.php'; break;
    case 'promociones_eliminar': include 'vistas/promociones_eliminar.php'; break;
    default:
        echo '<div class="card"><h1>📊 Bienvenido al panel de administración</h1><p>Desde aquí puedes gestionar todos los módulos del sistema.</p></div>';
        echo '<div class="tarjetas">
            <div class="metric-card"><h2>'. $total_productos .'</h2><p>Productos activos</p></div>
            <div class="metric-card"><h2>'. $total_promos .'</h2><p>Promociones activas</p></div>
            <div class="metric-card"><h2>'. ($total_visitas ?? 0) .'</h2><p>Total de visitas</p></div>
            <div class="metric-card"><h2>'. $sin_imagenes .'</h2><p>Productos sin imagen</p></div>
        </div>';

        if ($top5):
            echo '<div class="card"><h3>🔥 Top 5 productos más vistos</h3>';
            echo '<table><thead><tr><th>Producto</th><th>Visitas</th><th></th></tr></thead><tbody>';
            foreach ($top5 as $p) {
                echo "<tr>
                        <td>".htmlspecialchars($p['nombre'])."</td>
                        <td>{$p['visitas']}</td>
                        <td><a href=\"../producto.php?id={$p['id']}\" class=\"btn-ver\" target=\"_blank\">Ver producto</a></td>
                    </tr>";
            }
            echo '</tbody></table></div>';

            // GRÁFICO
            $labels = json_encode(array_column($top5, 'nombre'));
            $data = json_encode(array_column($top5, 'visitas'));

            echo '
            <div class="card grafico">
                <h3>📊 Gráfico de vistas por producto</h3>
                <canvas id="graficoTop5" height="150"></canvas>
            </div>
            <script>
                const ctx = document.getElementById("graficoTop5").getContext("2d");
                new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: '. $labels .',
                        datasets: [{
                            label: "Visitas",
                            data: '. $data .',
                            backgroundColor: "var(--color-primario)"
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            </script>';
        endif;
        break;
}
?>
</div>
</body>
</html>
