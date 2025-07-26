<?php
if (!isset($_GET['id'])) {
    echo "<p>ID de producto no especificado.</p>";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "<p>Producto no encontrado.</p>";
    exit;
}

// Obtener datos
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();

$imagenes_stmt = $pdo->prepare("SELECT * FROM imagenes_producto WHERE producto_id = ?");
$imagenes_stmt->execute([$id]);
$imagenes = $imagenes_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? null;
    $estado_activo = isset($_POST['estado_activo']) ? 1 : 0;
    $stock = $_POST['stock'] ?? 0;

    if ($nombre && $precio) {
        $stmt = $pdo->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, categoria_id=?, estado_activo=?, stock=? WHERE id=?");
        $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $estado_activo, $stock, $id]);

        // Subir nuevas imágenes (hasta 10)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM imagenes_producto WHERE producto_id = ?");
        $stmt->execute([$id]);
        $total_actual = $stmt->fetchColumn();

        if (!empty($_FILES['imagenes']['name'][0]) && $total_actual < 10) {
            $imagenes_archivo = $_FILES['imagenes'];
            $total_nuevas = count($imagenes_archivo['name']);
            $permitidas = ['jpg','jpeg','png','webp'];
            $limite = min(10 - $total_actual, $total_nuevas);

            for ($i = 0; $i < $limite; $i++) {
                $tmp = $imagenes_archivo['tmp_name'][$i];
                $name = basename($imagenes_archivo['name'][$i]);
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (in_array($ext, $permitidas)) {
                    $filename = uniqid('img_') . '.' . $ext;
                    $destino = dirname(__DIR__, 2) . '/uploads/' . $filename;
                    if (move_uploaded_file($tmp, $destino)) {
                        $ruta = 'uploads/' . $filename;
                        $stmt = $pdo->prepare("INSERT INTO imagenes_producto (producto_id, ruta) VALUES (?, ?)");
                        $stmt->execute([$id, $ruta]);
                    }
                }
            }
        }

        header("Location: dashboard.php?vista=productos");
        exit;
    } else {
        echo "<p style='color:red;'>Faltan datos obligatorios.</p>";
    }
}
?>

<div class="card">
    <h2>✏️ Editar Producto</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Nombre *</label><br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required style="width: 100%;"><br><br>

        <label>Descripción</label><br>
        <textarea name="descripcion" rows="4" style="width: 100%;"><?= htmlspecialchars($producto['descripcion']) ?></textarea><br><br>

        <label>Precio *</label><br>
        <input type="number" name="precio" step="0.01" value="<?= $producto['precio'] ?>" required style="width: 100%;"><br><br>

        <label>Categoría</label><br>
        <select name="categoria_id" style="width:100%;">
            <option value="">-- Seleccionar --</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $producto['categoria_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label><input type="checkbox" name="estado_activo" <?= $producto['estado_activo'] ? 'checked' : '' ?>> Producto activo</label><br><br>

        <label>Stock *</label><br>
        <input type="number" name="stock" min="0" value="<?= $producto['stock'] ?>" required style="width: 100%;"><br><br>

        <h3>🖼️ Imágenes actuales</h3>
        <?php foreach ($imagenes as $img): ?>
            <div style="display:inline-block; margin:10px;">
                <img src="../../<?= $img['ruta'] ?>" style="width:80px; height:80px; object-fit:cover;"><br>
                <a href="producto_eliminar_imagen.php?id=<?= $img['id'] ?>&producto=<?= $id ?>" onclick="return confirm('¿Eliminar esta imagen?')">❌ Eliminar</a>
            </div>
        <?php endforeach; ?>
        <br><br>

        <label>Subir nuevas imágenes (máx. 10)</label><br>
        <input type="file" name="imagenes[]" multiple accept=".jpg,.jpeg,.png,.webp"><br><br>

        <button type="submit">💾 Guardar cambios</button>
        &nbsp;
        <a href="dashboard.php?vista=productos">🔙 Volver</a>
    </form>
</div>
