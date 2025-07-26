<?php
// Conexión ya está establecida desde dashboard.php

// Consultas de métricas
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

<div class="card">
    <h1>📊 Bienvenido al panel de administración</h1>
    <p>Desde aquí puedes gestionar todos los módulos del sistema.</p>
</div>

<div class="tarjetas">
    <div class="metric-card">
        <h2><?= $total_productos ?></h2>
        <p>Productos activos</p>
    </div>
    <div class="metric-card">
        <h2><?= $total_promos ?></h2>
        <p>Promociones activas</p>
    </div>
    <div class="metric-card">
        <h2><?= $total_visitas ?? 0 ?></h2>
        <p>Total de visitas</p>
    </div>
    <div class="metric-card">
        <h2><?= $sin_imagenes ?></h2>
        <p>Productos sin imagen</p>
    </div>
</div>

<!-- vistas/dashboard_home.php -->

<?php if (!empty($top5)): ?>
    <div class="card">
        <h3>🔥 Top 5 productos más vistos</h3>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Visitas</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top5 as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= $p['visitas'] ?></td>
                        <td>
                            <a href="../producto.php?id=<?= $p['id'] ?>" class="btn-ver" target="_blank">
                                Ver producto
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card grafico">
        <h3>📊 Gráfico de vistas por producto</h3>
        <canvas id="graficoTop5" height="150"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById("graficoTop5").getContext("2d");
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: <?= json_encode(array_column($top5, 'nombre')) ?>,
                datasets: [{
                    label: "Visitas",
                    data: <?= json_encode(array_column($top5, 'visitas')) ?>,
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
    </script>
<?php else: ?>
    <p>No hay datos disponibles del top 5.</p>
<?php endif; ?>

