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
$tallas = $pdo->query("SELECT * FROM tallas ORDER BY nombre")->fetchAll();
$colores = $pdo->query("SELECT * FROM colores ORDER BY nombre")->fetchAll();

$imagenes_stmt = $pdo->prepare("SELECT * FROM imagenes_producto WHERE producto_id = ?");
$imagenes_stmt->execute([$id]);
$imagenes = $imagenes_stmt->fetchAll();

$variantes = $pdo->prepare("SELECT ptc.*, t.nombre AS talla, c.nombre AS color FROM producto_tallas_colores ptc
                            JOIN tallas t ON ptc.talla_id = t.id
                            JOIN colores c ON ptc.color_id = c.id
                            WHERE ptc.producto_id = ?");
$variantes->execute([$id]);
$variantes = $variantes->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? null;
    $video_url = $_POST['video_url'] ?? '';
    $estado_activo = isset($_POST['estado_activo']) ? 1 : 0;

    if ($nombre && $precio) {
        $stmt = $pdo->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, categoria_id=?, video_url=?, estado_activo=? WHERE id=?");
        $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $video_url, $estado_activo, $id]);

        // Actualizar stock existente
        if (!empty($_POST['stock_existente'])) {
            foreach ($_POST['stock_existente'] as $var_id => $stock) {
                $update = $pdo->prepare("UPDATE producto_tallas_colores SET stock=? WHERE id=?");
                $update->execute([$stock, $var_id]);
            }
        }

        // Nuevas variantes
        if (!empty($_POST['talla']) && !empty($_POST['color']) && !empty($_POST['stock'])) {
            for ($i = 0; $i < count($_POST['talla']); $i++) {
                $t = $_POST['talla'][$i];
                $c = $_POST['color'][$i];
                $s = $_POST['stock'][$i];
                if ($t && $c && is_numeric($s)) {
                    $insert = $pdo->prepare("INSERT INTO producto_tallas_colores (producto_id, talla_id, color_id, stock) VALUES (?, ?, ?, ?)");
                    $insert->execute([$id, $t, $c, $s]);
                }
            }
        }

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

        <label>Video del producto</label><br>
        <input type="url" name="video_url" value="<?= $producto['video_url'] ?>" style="width: 100%;"><br><br>

        <label><input type="checkbox" name="estado_activo" <?= $producto['estado_activo'] ? 'checked' : '' ?>> Producto activo</label><br><br>

        <h3>📦 Variantes actuales (Talla + Color + Stock)</h3>
        <?php foreach ($variantes as $v): ?>
            <?= $v['talla'] ?> / <?= $v['color'] ?> → 
            <input type="number" name="stock_existente[<?= $v['id'] ?>]" value="<?= $v['stock'] ?>" style="width:60px;"> unidades<br>
        <?php endforeach; ?>
        <br>

        <h3>➕ Nuevas variantes</h3>
        <div id="variantes"></div>
        <button type="button" onclick="agregarFila()">➕ Añadir variante</button><br><br>

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

<script>
const tallas = <?= json_encode($tallas) ?>;
const colores = <?= json_encode($colores) ?>;

function agregarFila() {
    const div = document.createElement('div');
    div.style.marginBottom = '10px';

    const selectTalla = document.createElement('select');
    selectTalla.name = 'talla[]';
    tallas.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.id;
        opt.text = t.nombre;
        selectTalla.appendChild(opt);
    });

    const selectColor = document.createElement('select');
    selectColor.name = 'color[]';
    colores.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.text = c.nombre;
        selectColor.appendChild(opt);
    });

    const inputStock = document.createElement('input');
    inputStock.name = 'stock[]';
    inputStock.type = 'number';
    inputStock.min = 0;
    inputStock.placeholder = 'Stock';
    inputStock.style.width = '80px';
    inputStock.style.marginLeft = '10px';

    div.appendChild(selectTalla);
    div.appendChild(selectColor);
    div.appendChild(inputStock);

    document.getElementById('variantes').appendChild(div);
}
</script>
