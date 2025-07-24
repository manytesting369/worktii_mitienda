<?php
require_once __DIR__ . '/../../config/conexion.php';


// Obtener promociones con nombre de producto
$stmt = $pdo->query("
    SELECT pr.*, p.nombre AS producto 
    FROM promociones pr 
    JOIN productos p ON pr.producto_id = p.id 
    ORDER BY pr.fecha_inicio DESC
");
$promociones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <h2>🎉 Promociones</h2>
    <a href="dashboard.php?vista=promociones_nuevo" class="btn-agregar">➕ Nueva promoción</a>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Vigencia</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($promociones as $promo): ?>
                <tr>
                    <td><?= htmlspecialchars($promo['producto']) ?></td>
                    <td><?= htmlspecialchars($promo['titulo']) ?></td>
                    <td><?= ucfirst($promo['tipo']) ?></td>
                    <td>
                        <?= $promo['tipo'] === 'porcentaje' ? $promo['valor'].'%' :
                            ($promo['tipo'] === 'fijo' ? 'S/. '.$promo['valor'] :
                            ($promo['tipo'] === 'envio_gratis' ? 'Gratis' : '')) ?>
                    </td>
                    <td><?= $promo['fecha_inicio'] ?> - <?= $promo['fecha_fin'] ?></td>
                    <td>
                        <span style="color: <?= $promo['estado'] === 'activo' ? 'green' : 'gray' ?>;">
                            <?= ucfirst($promo['estado']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="dashboard.php?vista=promociones_editar&id=<?= $promo['id'] ?>">✏️ Editar</a>
                        |
                        <a href="dashboard.php?vista=promociones_eliminar&id=<?= $promo['id'] ?>" onclick="return confirm('¿Eliminar esta promoción?')">🗑️ Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
    .btn-agregar {
        display: inline-block;
        background: var(--color-primario, #007bff);
        color: white;
        padding: 8px 12px;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 15px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th, td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background: #f0f0f0;
    }

    tr:hover {
        background: #fafafa;
    }
</style>
