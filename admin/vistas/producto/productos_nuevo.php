<?php
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $categoria_id = $_POST['categoria_id'] ?? '';
    $estado_activo = isset($_POST['estado_activo']) ? 1 : 0;
    $stock = intval($_POST['stock'] ?? 0);

    $errores = [];

    if ($nombre === '') $errores[] = "nombre";
    if ($precio <= 0) $errores[] = "precio";
    if ($categoria_id === '') $errores[] = "categoría";
    if (empty($_FILES['imagenes']['name'][0])) $errores[] = "imagen";
    if ($stock < 0) $errores[] = "stock";

    if (count($errores) > 0) {
        $mensaje = "❌ Faltan los siguientes campos: " . implode(", ", $errores);
    } else {
        // Verificar categoría válida
        $catStmt = $pdo->prepare("SELECT nombre FROM categorias WHERE id = ?");
        $catStmt->execute([$categoria_id]);
        $categoriaNombre = $catStmt->fetchColumn();

        if (!$categoriaNombre) {
            $mensaje = "❌ Categoría no válida.";
        } else {
            // Insertar producto
            $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria_id, estado_activo, stock) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $estado_activo, $stock]);
            $producto_id = $pdo->lastInsertId();


            // Procesar imágenes
            $imagenes = $_FILES['imagenes'];
            $total = count($imagenes['name']);
            $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

            if ($total > 10) {
                $mensaje = "❌ Solo se permiten hasta 10 imágenes por producto.";
            } else {
                // Limpiar nombre del producto y categoría
                $nombreProducto = preg_replace('/[^a-z0-9_-]/i', '_', strtolower($nombre));
                $categoriaLimpia = isset($_POST['categoria']) ? preg_replace('/[^a-z0-9_-]/i', '_', strtolower($_POST['categoria'])) : 'otros';

                // Ruta a la carpeta en la raíz del proyecto
                $carpetaCategoria = dirname(__DIR__, 3) . "/img/$categoriaLimpia";

                // Crear carpeta si no existe
                if (!is_dir($carpetaCategoria)) {
                    mkdir($carpetaCategoria, 0755, true);
                }

                for ($i = 0; $i < $total; $i++) {
                    $tmp = $imagenes['tmp_name'][$i];
                    $originalName = basename($imagenes['name'][$i]);
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                    if (in_array($ext, $permitidos)) {
                        $img = null;
                        if ($ext === 'jpg' || $ext === 'jpeg') $img = @imagecreatefromjpeg($tmp);
                        elseif ($ext === 'png') $img = @imagecreatefrompng($tmp);
                        elseif ($ext === 'webp') $img = @imagecreatefromwebp($tmp);

                        if ($img !== null) {
                            // Nombre del archivo
                            $filename = $nombreProducto . ($i > 0 ? " $i" : '') . '.webp';
                            $ruta_fisica = "$carpetaCategoria/$filename";
                            $ruta_guardada = "img/$categoriaLimpia/$filename";

                            if (imagewebp($img, $ruta_fisica, 80)) {
                                imagedestroy($img);
                                $stmt = $pdo->prepare("INSERT INTO imagenes_producto (producto_id, ruta) VALUES (?, ?)");
                                $stmt->execute([$producto_id, $ruta_guardada]);
                            }
                        }
                    }
                }

                // Redirigir tras éxito
                header("Location: dashboard.php?vista=producto/productos");
                exit;
            }
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

        <label>Stock *</label><br>
        <input type="number" name="stock" min="0" required style="width: 100%;"><br><br>

        <label><input type="checkbox" name="estado_activo" checked> Activar producto</label><br><br>

        <label>Imágenes del producto (máx. 10):</label><br>
        <input type="file" name="imagenes[]" id="rutaProducto" multiple accept=".jpg,.jpeg,.png,.webp"><br>
        <small style="color:gray;">Se recomienda subir imágenes de buena calidad en formato .jpg o .webp</small><br><br>

        <button type="submit">💾 Guardar producto</button>
        &nbsp; <a href="dashboard.php?vista=producto/productos">🔙 Volver</a>
    </form>
</div>

