<?php
// Obtener roles
$roles = $pdo->query("SELECT id, nombre FROM roles ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Leer el JSON de países
$paises_json = file_get_contents(__DIR__ . '/../../../config/countries.json');
$paises = json_decode($paises_json, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = $_POST['email'];
    $rol_id = $_POST['rol_id'] ?? null;
    $puesto = $_POST['puesto'];
    $ubicacion = $_POST['ubicacion'];
    $numero = $_POST['numero'];
    $prefijo = $_POST['prefijo'];
    $pais = $_POST['pais'];
    $pronombre = $_POST['pronombre'];
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    if ($contrasena !== $confirmar_contrasena) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);

        // Insertar usuario
        $sql = "INSERT INTO usuarios 
        (nombre, email, contrasena, rol_id, puesto, ubicacion, numero, prefijo, pais, pronombre) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nombre, $email, $hash, $rol_id,
            $puesto, $ubicacion, $numero, $prefijo, $pais, $pronombre
        ]);

        // Obtener el ID del usuario recién creado
        $id = $pdo->lastInsertId();

        // --- PROCESAR IMAGEN DE PERFIL ---
        if (isset($_FILES['perfil_usuario']) && $_FILES['perfil_usuario']['error'] === UPLOAD_ERR_OK) {

            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $nombreOriginal = $_FILES['perfil_usuario']['name'];
            $extensionArchivo = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

            if (!in_array($extensionArchivo, $extensionesPermitidas)) {
                $mensaje = "Formato de imagen no permitido.";
            } else {
                // Ruta absoluta a /img/perfiles en la raíz del proyecto
                $rutaBase = dirname(__DIR__, 3) . "/img/perfiles/";
                if (!file_exists($rutaBase)) {
                    mkdir($rutaBase, 0777, true);
                }

                // Nombre de archivo = nombre del usuario + .webp
                $nombreArchivo = preg_replace('/[^a-z0-9_\-]/i', '_', strtolower($nombre)) . ".webp";
                
                $nombreArchivo = $nombreLimpio . ".webp";
                $rutaFinal = $rutaBase . $nombreArchivo;

                // Convertir a WebP
                $tmp = $_FILES['perfil_usuario']['tmp_name'];
                switch ($extensionArchivo) {
                    case 'jpg':
                    case 'jpeg':
                        $imagen = imagecreatefromjpeg($tmp);
                        break;
                    case 'png':
                        $imagen = imagecreatefrompng($tmp);
                        imagepalettetotruecolor($imagen);
                        imagealphablending($imagen, true);
                        imagesavealpha($imagen, true);
                        break;
                    case 'gif':
                        $imagen = imagecreatefromgif($tmp);
                        break;
                    case 'webp':
                        $imagen = imagecreatefromwebp($tmp);
                        break;
                    default:
                        $imagen = null;
                }

                if ($imagen) {
                    imagewebp($imagen, $rutaFinal, 80);
                    imagedestroy($imagen);

                    // Guardar ruta relativa en la base de datos
                    $rutaEnBD = "img/perfiles/" . $nombreArchivo;
                    $stmt = $pdo->prepare("UPDATE usuarios SET perfil_usuario = ? WHERE id = ?");
                    $stmt->execute([$rutaEnBD, $id]);
                } else {
                    $mensaje = "Error al procesar la imagen.";
                }
            }
        }
        // --- FIN PROCESAR IMAGEN ---
        // --- PROCESAR LOGO DE EMPRESA DEL USUARIO ---
        if (isset($_FILES['usuario_empresa']) && $_FILES['usuario_empresa']['error'] === UPLOAD_ERR_OK) {

            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $nombreOriginal = $_FILES['usuario_empresa']['name'];
            $extensionArchivo = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

            if (!in_array($extensionArchivo, $extensionesPermitidas)) {
                $mensaje = "Formato de imagen no permitido.";
            } else {
                // Ruta absoluta a /img/usuario_logo en la raíz del proyecto
                $rutaBase = dirname(__DIR__, 3) . "/img/usuario_logo/";
                if (!file_exists($rutaBase)) {
                    mkdir($rutaBase, 0777, true);
                }

                // Limpiar el nombre y usarlo para el archivo .webp
                $nombreLimpio = preg_replace('/[^a-z0-9_\-]/i', '_', strtolower($nombre));
                $nombreArchivo = $nombreLimpio . ".webp";
                $rutaFinal = $rutaBase . $nombreArchivo;

                // Convertir a WebP
                $tmp = $_FILES['usuario_empresa']['tmp_name'];
                switch ($extensionArchivo) {
                    case 'jpg':
                    case 'jpeg':
                        $imagen = imagecreatefromjpeg($tmp);
                        break;
                    case 'png':
                        $imagen = imagecreatefrompng($tmp);
                        imagepalettetotruecolor($imagen);
                        imagealphablending($imagen, true);
                        imagesavealpha($imagen, true);
                        break;
                    case 'gif':
                        $imagen = imagecreatefromgif($tmp);
                        break;
                    case 'webp':
                        $imagen = imagecreatefromwebp($tmp);
                        break;
                    default:
                        $imagen = null;
                }

                if ($imagen) {
                    imagewebp($imagen, $rutaFinal, 80);
                    imagedestroy($imagen);

                    // Guardar ruta relativa en la base de datos
                    $rutaEnBD = "img/usuario_logo/" . $nombreArchivo;
                    $stmt = $pdo->prepare("UPDATE usuarios SET usuario_empresa = ? WHERE id = ?");
                    $stmt->execute([$rutaEnBD, $id]);
                } else {
                    $mensaje = "Error al procesar la imagen.";
                }
            }
        }
// --- FIN PROCESAR LOGO ---

        header("Location: dashboard.php?vista=usuario/usuarios");
        exit;
    }
}
?>
<link rel="stylesheet" href="/./css/usuario/usuarios_nuevo.css">
<div class="card">
    <h2>➕ Agregar Usuario</h2>
    <?php if (!empty($mensaje)): ?>
        <p style="color:red;"><strong><?= $mensaje ?></strong></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Nombre *</label><br>
        <input type="text" name="nombre" required style="width: 100%;"><br><br>

        <label>Email *</label><br>
        <input type="email" name="email" required style="width: 100%;"><br><br>

        <label for="perfil_usuario">Subir imagen de perfil:</label>
        <input type="file" name="perfil_usuario" accept=".webp,.png,.jpg,.jpeg,.gif"><br><br>
        
        <label for="usuario_empresa">Subir imagen de tu empresa:</label>
        <input type="file" name="usuario_empresa" accept=".webp,.png,.jpg,.jpeg,.gif"><br><br>
        
        <label>Rol *</label><br>
        <select name="rol_id" required style="width: 100%;">
            <option value="">-- Seleccionar Rol --</option>
            <?php foreach ($roles as $rol): ?>
                <option value="<?= $rol['id'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Puesto *</label><br>
        <input type="text" name="puesto" required style="width: 100%;"><br><br>

        <label>Ubicación *</label><br>
        <input type="text" name="ubicacion" required style="width: 100%;"><br><br>

        <label>País *</label><br>
        <select id="selectPais" style="width: 100%;">
            <option value="">-- Seleccionar País --</option>
            <?php foreach ($paises as $pais): ?>
                <option value="<?= htmlspecialchars($pais['pronombre']) ?>" 
                        data-prefijo="<?= htmlspecialchars($pais['prefijo']) ?>" 
                        data-nombre="<?= htmlspecialchars($pais['pais']) ?>">
                    <?= htmlspecialchars($pais['pais']) ?> (+<?= htmlspecialchars($pais['prefijo']) ?>)
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <input type="hidden" name="prefijo" id="prefijo">
        <input type="hidden" name="pais" id="pais">
        <input type="hidden" name="pronombre" id="pronombre">

        <label>Número *</label><br>
        <input type="text" name="numero" required style="width: 100%;"><br><br>

        <label>Contraseña *</label><br>
        <input type="password" name="contrasena" required style="width: 100%;"><br><br>

        <label>Confirmar contraseña *</label><br>
        <input type="password" name="confirmar_contrasena" required style="width: 100%;"><br><br>

        <button type="submit">💾 Guardar usuario</button>
        &nbsp; <a href="dashboard.php?vista=usuario/usuarios">🔙 Volver</a>
    </form>
</div>

<script>
document.getElementById('selectPais').addEventListener('change', function() {
    let option = this.options[this.selectedIndex];
    document.getElementById('prefijo').value = "+" + option.dataset.prefijo;
    document.getElementById('pais').value = option.dataset.nombre;
    document.getElementById('pronombre').value = option.value;
});
</script>
