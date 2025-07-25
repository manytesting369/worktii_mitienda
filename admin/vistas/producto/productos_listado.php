<?php
$productos = $pdo->query("
    SELECT p.*, 
    COALESCE(SUM(ptc.stock), 0) AS stock_total
    FROM productos p
    LEFT JOIN producto_tallas_colores ptc ON ptc.producto_id = p.id
    GROUP BY p.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <h2>📦 Lista de Productos</h2>
    <a href="dashboard.php?vista=producto_nuevo" style="color:green; font-weight:bold;">➕ Agregar nuevo producto</a><br><br>

    <table style="width:100%; border-collapse: collapse; font-family: sans-serif;">
        <thead>
            <tr style="background:#f4f4f4; border-bottom: 2px solid #ccc;">
                <th style="padding:10px; text-align: left;">#</th>
                <th style="padding:10px; text-align: left;">Nombre</th>
                <th style="padding:10px; text-align: left;">Precio</th>
                <th style="padding:10px; text-align: left;">Stock</th>
                <th style="padding:10px; text-align: left;">Estado</th>
                <th style="padding:10px; text-align: left;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($productos) == 0): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:12px; color:gray;">No hay productos registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $index => $producto): ?>
                    <tr style="border-bottom: 1px solid #e0e0e0;">
                        <td style="padding:10px;"><?= $index + 1 ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td style="padding:10px;">S/. <?= number_format($producto['precio'], 2) ?></td>
                        <td style="padding:10px;"><?= $producto['stock_total'] ?></td>
                        <td style="padding:10px;">
                            <?= $producto['estado_activo'] ? '<span style="color:green;">✅ Activo</span>' : '<span style="color:red;">❌ Inactivo</span>' ?>
                        </td>
                        <td style="padding:10px;">
                            <a href="dashboard.php?vista=producto_editar&id=<?= $producto['id'] ?>" title="Editar" style="margin-right: 10px;">✏️</a>
                            <a href="dashboard.php?vista=producto_eliminar&id=<?= $producto['id'] ?>" title="Eliminar" onclick="return confirm('¿Eliminar este producto?');">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
