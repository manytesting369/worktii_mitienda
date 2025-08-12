<?php
// Obtener configuración actual
$usuariosLista = $pdo->query("
    SELECT id, nombre, puesto
    FROM usuarios
    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);


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
    $titulo = $_POST['titulo'] ?? '';
    $tipografia = $_POST['tipografia'] ?? 'Arial, sans-serif';
    $facebook = $_POST['link_facebook'] ?? '';
    $instagram = $_POST['link_instagram'] ?? '';
    $tiktok = $_POST['link_tiktok'] ?? '';
    $whatsapp = $_POST['whatsapp_defecto'] ?? '';
    $usuario_presentacion = $_POST['usuario_presentacion'] ?? null;

    // Carpeta destino (fuera de /admin/)
    $carpeta_logo = __DIR__ . '/../../../img/logo/';
    if (!is_dir($carpeta_logo)) {
        mkdir($carpeta_logo, 0777, true);
    }

    if (!empty($_FILES['logo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $permitidos)) {
            $nombre_logo = 'logo_sitio.webp';
            $ruta_destino = $carpeta_logo . $nombre_logo;

            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    $imagen = imagecreatefromjpeg($_FILES['logo']['tmp_name']);
                    break;
                case 'png':
                    $imagen = imagecreatefrompng($_FILES['logo']['tmp_name']);
                    imagepalettetotruecolor($imagen);
                    imagealphablending($imagen, true);
                    imagesavealpha($imagen, true);
                    break;
                case 'webp':
                    $imagen = imagecreatefromwebp($_FILES['logo']['tmp_name']);
                    break;
            }

            if ($imagen && imagewebp($imagen, $ruta_destino, 80)) {
                imagedestroy($imagen);
                $logo_actual = 'img/logo/' . $nombre_logo;
            } else {
                $mensaje = "❌ Error al convertir el logo a WebP.";
            }
        } else {
            $mensaje = "❌ Formato de logo no permitido.";
        }
    }



    $stmt = $pdo->prepare("UPDATE configuracion SET 
        logo = ?, color_primario = ?, color_secundario = ?, titulo = ?, tipografia = ?, 
        link_facebook = ?, link_instagram = ?, link_tiktok = ?, whatsapp_defecto = ?, 
        usuario_presentacion = ?, fecha_actualizacion = NOW()
        WHERE id = ?");
    
    $stmt->execute([
        $logo_actual, $color_primario, $color_secundario, $titulo, $tipografia,
        $facebook, $instagram, $tiktok, $whatsapp,
        $usuario_presentacion, $config['id']
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

        <label>Título del sitio:</label><br>
        <input type="text" name="titulo" value="<?= htmlspecialchars($config['titulo']) ?>" style="width: 100%;"><br><br>

        <label>WhatsApp (número o enlace):</label><br>
        <input type="text" name="whatsapp_defecto" value="<?= htmlspecialchars($config['whatsapp_defecto']) ?>" style="width: 100%;"><br><br>

        <label>Facebook:</label><br>
        <input type="url" name="link_facebook" value="<?= htmlspecialchars($config['link_facebook']) ?>" style="width: 100%;"><br><br>

        <label>Instagram:</label><br>
        <input type="url" name="link_instagram" value="<?= htmlspecialchars($config['link_instagram']) ?>" style="width: 100%;"><br><br>

        <label>TikTok:</label><br>
        <input type="url" name="link_tiktok" value="<?= htmlspecialchars($config['link_tiktok']) ?>" style="width: 100%;"><br><br>

        <label>Presentación de usuario</label><br>
        <select name="usuario_presentacion">
            <option value="">-- Seleccionar usuario --</option>
            <?php foreach ($usuariosLista as $u): ?>
                <option value="<?= $u['id'] ?>" <?= ($config['usuario_presentacion'] == $u['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['nombre']) ?> - <?= htmlspecialchars($u['puesto']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">💾 Guardar configuración</button>
    </form>
</div>