<?php

if (!isset($_GET['id'])) {
    echo "<p>ID no especificado.</p>";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM promociones WHERE id = ?");
$stmt->execute([$id]);
$promo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$promo) {
    echo "<p>Promoción no encontrada.</p>";
    exit;
}

// Obtener productos activos
$productos = $pdo->query("SELECT id, nombre FROM productos WHERE estado_activo = 1 ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = $_POST['producto_id'];
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $tipo = $_POST['tipo'];
    $valor = $_POST['valor'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $estado = $_POST['estado'];

    $stmt = $pdo->prepare("UPDATE promociones SET producto_id=?, titulo=?, descripcion=?, tipo=?, valor=?, fecha_inicio=?, fecha_fin=?, estado=? WHERE id=?");
    $stmt->execute([$producto_id, $titulo, $descripcion, $tipo, $valor, $fecha_inicio, $fecha_fin, $estado, $id]);

    header("Location: dashboard.php?vista=promociones");
    exit;
}
?>

<div class="card">
    <h2>✏️ Editar Promoción</h2>
    <form method="POST">
        <label>Producto asociado *</label><br>
        <select name="producto_id" required style="width:100%;">
            <option value="">-- Seleccionar producto --</option>
            <?php foreach ($productos as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id'] == $promo['producto_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Título *</label><br>
        <input type="text" name="titulo" value="<?= htmlspecialchars($promo['titulo']) ?>" required style="width:100%;"><br><br>

        <label>Descripción</label><br>
        <textarea name="descripcion" rows="3" style="width:100%;"><?= htmlspecialchars($promo['descripcion']) ?></textarea><br><br>

        <label>Tipo de promoción *</label><br>
        <select name="tipo" required>
            <option value="porcentaje" <?= $promo['tipo'] == 'porcentaje' ? 'selected' : '' ?>>Porcentaje</option>
            <option value="fijo" <?= $promo['tipo'] == 'fijo' ? 'selected' : '' ?>>Descuento fijo</option>
            <option value="envio_gratis" <?= $promo['tipo'] == 'envio_gratis' ? 'selected' : '' ?>>Envío gratis</option>
        </select><br><br>

        <label>Valor *</label><br>
        <input type="number" name="valor" step="0.01" value="<?= $promo['valor'] ?>" required><br><br>

        <label>Fecha inicio *</label><br>
        <input type="date" name="fecha_inicio" value="<?= $promo['fecha_inicio'] ?>" required><br><br>

        <label>Fecha fin *</label><br>
        <input type="date" name="fecha_fin" value="<?= $promo['fecha_fin'] ?>" required><br><br>

        <label>Estado *</label><br>
        <select name="estado" required>
            <option value="activo" <?= $promo['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= $promo['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select><br><br>

        <button type="submit">💾 Guardar cambios</button>
        &nbsp;
        <a href="dashboard.php?vista=promocion/promociones">🔙 Volver</a>
    </form>
</div>
