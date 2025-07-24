<?php
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$tallas = $pdo->query("SELECT * FROM tallas ORDER BY nombre")->fetchAll();
$colores = $pdo->query("SELECT * FROM colores ORDER BY nombre")->fetchAll();

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $categoria_id = $_POST['categoria_id'] ?? '';
    $estado_activo = isset($_POST['estado_activo']) ? 1 : 0;

    // Validaciones
    $errores = [];
    if ($nombre === '') $errores[] = "nombre";
    if ($precio <= 0) $errores[] = "precio";
    if ($categoria_id === '') $errores[] = "categoría";
    if (empty($_FILES['imagenes']['name'][0])) $errores[] = "imagen";
    if (!isset($_POST['talla'][0], $_POST['color'][0], $_POST['stock'][0]) || $_POST['talla'][0] === '' || $_POST['color'][0] === '' || $_POST['stock'][0] === '') {
        $errores[] = "variante";
    }

    if (count($errores) > 0) {
        $mensaje = "Faltan los siguientes datos obligatorios: " . implode(", ", $errores) . ".";
    } else {
        // Insertar producto
        $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria_id, estado_activo) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $estado_activo]);
        $producto_id = $pdo->lastInsertId();

        // Guardar variantes
        $tallas_post = $_POST['talla'];
        $colores_post = $_POST['color'];
        $stocks_post = $_POST['stock'];

        for ($i = 0; $i < count($tallas_post); $i++) {
            $t = $tallas_post[$i];
            $c = $colores_post[$i];
            $s = $stocks_post[$i];

            if ($t && $c && is_numeric($s)) {
                $insert = $pdo->prepare("INSERT INTO producto_tallas_colores (producto_id, talla_id, color_id, stock) 
                                         VALUES (?, ?, ?, ?)");
                $insert->execute([$producto_id, $t, $c, $s]);
            }
        }

        // Guardar imágenes convertidas a .webp
        $imagenes = $_FILES['imagenes'];
        $total = count($imagenes['name']);
        $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

        if ($total > 10) {
            $mensaje = "❌ Solo se permiten hasta 10 imágenes por producto.";
        } else {
            // Obtener nombre de la categoría
            $catStmt = $pdo->prepare("SELECT nombre FROM categorias WHERE id = ?");
            $catStmt->execute([$categoria_id]);
            $categoriaNombre = $catStmt->fetchColumn();

            // Crear carpeta si no existe
            $directorioCategoria = __DIR__ . '/../../img/' . $categoriaNombre;
            if (!is_dir($directorioCategoria)) {
                mkdir($directorioCategoria, 0755, true);
            }

            for ($i = 0; $i < $total; $i++) {
                $tmp = $imagenes['tmp_name'][$i];
                $originalName = basename($imagenes['name'][$i]);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                if (in_array($ext, $permitidos)) {
                    // Convertir imagen a .webp
                    $img = null;
                    if ($ext === 'jpg' || $ext === 'jpeg') {
                        $img = imagecreatefromjpeg($tmp);
                    } elseif ($ext === 'png') {
                        $img = imagecreatefrompng($tmp);
                    } elseif ($ext === 'webp') {
                        $img = imagecreatefromwebp($tmp);
                    }

                    if ($img !== null) {
                        $nombreSanitizado = preg_replace('/[^a-z0-9_-]/i', '_', strtolower($nombre));
                        $filename = $nombreSanitizado . '_' . $i . '.webp';
                        $ruta_fisica = $directorioCategoria . '/' . $filename;
                        $ruta_guardada = 'img/' . $categoriaNombre . '/' . $filename;

                        if (imagewebp($img, $ruta_fisica, 80)) {
                            imagedestroy($img);
                            $stmt = $pdo->prepare("INSERT INTO imagenes_producto (producto_id, ruta) VALUES (?, ?)");
                            $stmt->execute([$producto_id, $ruta_guardada]);
                        }
                    }
                }
            }

            header("Location: dashboard.php?vista=productos");
            exit;
        }
    }
}

?>

<div class="card">
    <h2>➕ Agregar nuevo producto</h2>
    <?php if ($mensaje): ?>
        <p style="color:red;"><strong><?= $mensaje ?></strong></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Nombre *</label><br>
        <input type="text" name="nombre" required style="width: 100%;"><br><br>

        <label>Descripción</label><br>
        <textarea name="descripcion" rows="4" style="width: 100%;"></textarea><br><br>

        <label>Precio *</label><br>
        <input type="number" name="precio" step="0.01" required style="width: 100%;"><br><br>

        <label>Categoría</label><br>
        <select name="categoria_id" style="width:100%;" required>
            <option value="">-- Seleccionar --</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
        </select><br><br>
        <label><input type="checkbox" name="estado_activo" checked> Activar producto</label><br><br>

        <h3>🧩 Variantes: Talla + Color + Stock</h3>
        <div id="variantes"></div>
        <button type="button" onclick="agregarFila()">➕ Añadir variante</button><br><br>

        <label>Imágenes del producto (máx. 10):</label><br>
        <input type="file" name="imagenes[]" multiple accept=".jpg,.jpeg,.png,.webp"><br>
        <small style="color:gray;">Se recomienda subir imágenes de buena calidad en formato .jpg o .webp</small><br><br>

        <button type="submit">💾 Guardar producto</button>
        &nbsp; <a href="dashboard.php?vista=productos">🔙 Volver</a>
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
    selectTalla.required = true;
    tallas.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.id;
        opt.text = t.nombre;
        selectTalla.appendChild(opt);
    });

    const selectColor = document.createElement('select');
    selectColor.name = 'color[]';
    selectColor.required = true;
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
    inputStock.required = true;
    inputStock.style.width = '80px';
    inputStock.style.marginLeft = '10px';

    div.appendChild(selectTalla);
    div.appendChild(selectColor);
    div.appendChild(inputStock);

    document.getElementById('variantes').appendChild(div);
}

window.onload = () => {
    agregarFila();
};
</script>
