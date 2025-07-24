<?php
// Obtener configuración actual
$stmt = $pdo->query("SELECT * FROM configuracion LIMIT 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe aún, crearla
if (!$config) {
    $pdo->exec("INSERT INTO configuracion (id) VALUES (NULL)");
    $config = $pdo->query("SELECT * FROM configuracion LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $color_primario = $_POST['color_primario'] ?? '#000000';
    $color_secundario = $_POST['color_secundario'] ?? '#ffffff';
    $tipografia = $_POST['tipografia'] ?? 'Arial, sans-serif';
    $facebook = $_POST['link_facebook'] ?? '';
    $instagram = $_POST['link_instagram'] ?? '';
    $tiktok = $_POST['link_tiktok'] ?? '';
    $whatsapp = $_POST['whatsapp_defecto'] ?? '';
    $modo_oscuro = isset($_POST['modo_oscuro']) ? 1 : 0;
    $plantilla = $_POST['plantilla'] ?? 1;

    // Manejo del logo
    $logo_actual = $config['logo'] ?? '';

    // Crear carpeta de uploads si no existe
    $uploads_path = dirname(__DIR__, 2) . '/uploads'; // /catalogoweb/uploads
    if (!is_dir($uploads_path)) {
        mkdir($uploads_path, 0777, true);
    }

    if (!empty($_FILES['logo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $permitidos)) {
            $nombre_logo = uniqid('logo_') . '.' . $ext;
            $ruta = $uploads_path . '/' . $nombre_logo;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $ruta)) {
                $logo_actual = 'uploads/' . $nombre_logo;
            } else {
                $mensaje = "❌ Error al mover el archivo del logo.";
            }
        } else {
            $mensaje = "❌ Formato de logo no permitido.";
        }
    }

    $stmt = $pdo->prepare("UPDATE configuracion SET 
        logo = ?, color_primario = ?, color_secundario = ?, tipografia = ?, 
        link_facebook = ?, link_instagram = ?, link_tiktok = ?, whatsapp_defecto = ?, 
        modo_oscuro = ?, plantilla = ?, fecha_actualizacion = NOW()
        WHERE id = ?");
    
    $stmt->execute([
        $logo_actual, $color_primario, $color_secundario, $tipografia,
        $facebook, $instagram, $tiktok, $whatsapp,
        $modo_oscuro, $plantilla, $config['id']
    ]);

    if ($mensaje === '') {
        $mensaje = "✅ Configuración actualizada correctamente.";
    }

    $config = $pdo->query("SELECT * FROM configuracion LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="card">
    <h2>⚙️ Configuración general del sitio</h2>
    <?php if ($mensaje): ?>
        <p style="color:<?= str_starts_with($mensaje, '✅') ? 'green' : 'red' ?>; font-weight:bold;"><?= $mensaje ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Logo actual:</label><br>
        <?php if ($config['logo']): ?>
            <img src="../<?= $config['logo'] ?>" style="max-height: 80px;">

        <?php endif; ?>
        <input type="file" name="logo" accept="image/*"><br><br>

        <label>Color primario:</label><br>
        <input type="color" name="color_primario" value="<?= $config['color_primario'] ?>"><br><br>

        <label>Color secundario:</label><br>
        <input type="color" name="color_secundario" value="<?= $config['color_secundario'] ?>"><br><br>

        <label>Tipografía:</label><br>
        <input type="text" name="tipografia" value="<?= htmlspecialchars($config['tipografia']) ?>" style="width: 100%;"><br><br>

        <label>WhatsApp (número o enlace):</label><br>
        <input type="text" name="whatsapp_defecto" value="<?= htmlspecialchars($config['whatsapp_defecto']) ?>" style="width: 100%;"><br><br>

        <label>Facebook:</label><br>
        <input type="url" name="link_facebook" value="<?= htmlspecialchars($config['link_facebook']) ?>" style="width: 100%;"><br><br>

        <label>Instagram:</label><br>
        <input type="url" name="link_instagram" value="<?= htmlspecialchars($config['link_instagram']) ?>" style="width: 100%;"><br><br>

        <label>TikTok:</label><br>
        <input type="url" name="link_tiktok" value="<?= htmlspecialchars($config['link_tiktok']) ?>" style="width: 100%;"><br><br>

        <label><input type="checkbox" name="modo_oscuro" <?= $config['modo_oscuro'] ? 'checked' : '' ?>> Activar modo oscuro</label><br><br>

        <label>Plantilla visual (1 o 2):</label><br>
        <select name="plantilla">
            <option value="1" <?= $config['plantilla'] == 1 ? 'selected' : '' ?>>Plantilla 1</option>
            <option value="2" <?= $config['plantilla'] == 2 ? 'selected' : '' ?>>Plantilla 2</option>
        </select><br><br>

        <button type="submit">💾 Guardar configuración</button>
    </form>
</div>
