<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// asumo $pdo ya está disponible aquí
if (!isset($_GET['id'])) {
    echo "<p>ID no especificado.</p>";
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "<p>Usuario no encontrado.</p>";
    exit;
}

// Obtener roles
$roles = $pdo->query("SELECT id, nombre FROM roles ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Leer países
$paises_json = file_get_contents(__DIR__ . '/../../../config/countries.json');
$paises = json_decode($paises_json, true);

// rol del usuario logueado
$es_admin = isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1;

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Campos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol_id = $es_admin ? ($_POST['rol_id'] ?? $usuario['rol_id']) : $usuario['rol_id'];
    $puesto = $_POST['puesto'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $prefijo = $_POST['prefijo'] ?? '';
    $pais = $_POST['pais'] ?? '';
    $pronombre = $_POST['pronombre'] ?? '';
    $cambiar_contrasena = isset($_POST['cambiar_contrasena']);

    // RUTA ABSOLUTA A img/perfiles (raíz del proyecto)
    $rutaBase = dirname(__DIR__, 3) . '/img/perfiles/';
    if (!is_dir($rutaBase)) {
        mkdir($rutaBase, 0775, true);
    }

    // ---------- Manejo imagen ----------
    // Soportar ambos nombres de input por compatibilidad
    $fileKey = null;
    if (!empty($_FILES['imagen_perfil']['name'])) $fileKey = 'imagen_perfil';
    elseif (!empty($_FILES['perfil_usuario']['name'])) $fileKey = 'perfil_usuario';

    // Nombre limpio para filename (basado en nuevo nombre)
    $nombreLimpio = preg_replace('/[^a-z0-9_\-]/i', '_', mb_strtolower($nombre));
    if ($nombreLimpio === '') $nombreLimpio = 'usuario_' . $id;
    $nuevoFilename = $nombreLimpio . '.webp';
    $rutaFinal = $rutaBase . $nuevoFilename;

    // Si subieron archivo nuevo: procesarlo y sobrescribir (o crear)
    if ($fileKey && isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $origName = $_FILES[$fileKey]['name'];
        $tmp = $_FILES[$fileKey]['tmp_name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $extPermitidas = ['jpg','jpeg','png','gif','webp'];

        if (!in_array($ext, $extPermitidas)) {
            $mensaje = "Formato de imagen no permitido.";
        } else {
            // Si existe imagen vieja y tiene otro nombre, eliminarla para evitar duplicados
            if (!empty($usuario['perfil_usuario'])) {
                $rutaVieja = $rutaBase . $usuario['perfil_usuario'];
                if (file_exists($rutaVieja) && is_file($rutaVieja)) {
                    @unlink($rutaVieja);
                }
            }

            // Si es webp subir directamente (rename/move)
            if ($ext === 'webp') {
                if (!move_uploaded_file($tmp, $rutaFinal)) {
                    $mensaje = "Error al mover archivo WebP.";
                } else {
                    // opción: ajustar permisos
                    @chmod($rutaFinal, 0644);
                }
            } else {
                // Necesitamos GD y support para imagewebp
                if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
                    $mensaje = "GD con soporte WebP es requerido en el servidor.";
                } else {
                    switch ($ext) {
                        case 'jpg':
                        case 'jpeg':
                            $imagen = @imagecreatefromjpeg($tmp);
                            break;
                        case 'png':
                            $imagen = @imagecreatefrompng($tmp);
                            if ($imagen) {
                                imagepalettetotruecolor($imagen);
                                imagealphablending($imagen, true);
                                imagesavealpha($imagen, true);
                            }
                            break;
                        case 'gif':
                            $imagen = @imagecreatefromgif($tmp);
                            break;
                        default:
                            $imagen = null;
                    }

                    if (!$imagen) {
                        $mensaje = "No se pudo procesar la imagen subida.";
                    } else {
                        // Guardar como WebP
                        if (!imagewebp($imagen, $rutaFinal, 80)) {
                            $mensaje = "Error al convertir la imagen a WebP.";
                        }
                        imagedestroy($imagen);
                        @chmod($rutaFinal, 0644);
                    }
                }
            }

            // Si no hubo errores, actualizar campo en BD (solo filename)
            if ($mensaje === '') {
                $stmt = $pdo->prepare("UPDATE usuarios SET perfil_usuario = ? WHERE id = ?");
                $stmt->execute([$nuevoFilename, $id]);
                // actualizar variable local para mostrar inmediatamente si hay reload parcial
                $usuario['perfil_usuario'] = $nuevoFilename;
            }
        }
    } else {
        // Si NO subieron archivo pero cambiaron nombre, renombrar archivo existente si aplica
        if (!empty($usuario['perfil_usuario'])) {
            $rutaVieja = $rutaBase . $usuario['perfil_usuario'];
            if (file_exists($rutaVieja) && is_file($rutaVieja)) {
                // si el nombre viejo difiere del nuevo filename, renombrar
                if ($usuario['perfil_usuario'] !== $nuevoFilename) {
                    $rutaNueva = $rutaFinal;
                    if (@rename($rutaVieja, $rutaNueva)) {
                        $stmt = $pdo->prepare("UPDATE usuarios SET perfil_usuario = ? WHERE id = ?");
                        $stmt->execute([$nuevoFilename, $id]);
                        $usuario['perfil_usuario'] = $nuevoFilename;
                    }
                }
            }
        }
    }

    // RUTA ABSOLUTA A img/perfil_empresa (raíz del proyecto)
    $rutaBase = dirname(__DIR__, 3) . '/img/perfil_empresa/';
    if (!is_dir($rutaBase)) {
        mkdir($rutaBase, 0775, true);
    }

    // ---------- Manejo imagen ----------
    // Soportar ambos nombres de input por compatibilidad
    $fileKey = null;
    if (!empty($_FILES['imagen_empresa']['name'])) $fileKey = 'imagen_empresa';
    elseif (!empty($_FILES['imagen_empresa']['name'])) $fileKey = 'imagen_empresa';

    // Nombre limpio para filename (basado en nuevo nombre)
    $nombreLimpio = preg_replace('/[^a-z0-9_\-]/i', '_', mb_strtolower($nombre));
    if ($nombreLimpio === '') $nombreLimpio = 'empresa_' . $id;
    $nuevoFilename = $nombreLimpio . '.webp';
    $rutaFinal = $rutaBase . $nuevoFilename;

    // Si subieron archivo nuevo: procesarlo y sobrescribir (o crear)
    if ($fileKey && isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $origName = $_FILES[$fileKey]['name'];
        $tmp = $_FILES[$fileKey]['tmp_name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $extPermitidas = ['jpg','jpeg','png','gif','webp'];

        if (!in_array($ext, $extPermitidas)) {
            $mensaje = "Formato de imagen no permitido.";
        } else {
            // Si existe imagen vieja y tiene otro nombre, eliminarla para evitar duplicados
            if (!empty($usuario['imagen_empresa'])) {
                $rutaVieja = $rutaBase . $usuario['imagen_empresa'];
                if (file_exists($rutaVieja) && is_file($rutaVieja)) {
                    @unlink($rutaVieja);
                }
            }

            // Si es webp subir directamente (rename/move)
            if ($ext === 'webp') {
                if (!move_uploaded_file($tmp, $rutaFinal)) {
                    $mensaje = "Error al mover archivo WebP.";
                } else {
                    @chmod($rutaFinal, 0644);
                }
            } else {
                // Necesitamos GD y soporte para imagewebp
                if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
                    $mensaje = "GD con soporte WebP es requerido en el servidor.";
                } else {
                    switch ($ext) {
                        case 'jpg':
                        case 'jpeg':
                            $imagen = @imagecreatefromjpeg($tmp);
                            break;
                        case 'png':
                            $imagen = @imagecreatefrompng($tmp);
                            if ($imagen) {
                                imagepalettetotruecolor($imagen);
                                imagealphablending($imagen, true);
                                imagesavealpha($imagen, true);
                            }
                            break;
                        case 'gif':
                            $imagen = @imagecreatefromgif($tmp);
                            break;
                        default:
                            $imagen = null;
                    }

                    if (!$imagen) {
                        $mensaje = "No se pudo procesar la imagen subida.";
                    } else {
                        // Guardar como WebP
                        if (!imagewebp($imagen, $rutaFinal, 80)) {
                            $mensaje = "Error al convertir la imagen a WebP.";
                        }
                        imagedestroy($imagen);
                        @chmod($rutaFinal, 0644);
                    }
                }
            }

            // Si no hubo errores, actualizar campo en BD (solo filename)
            if ($mensaje === '') {
                $stmt = $pdo->prepare("UPDATE usuarios SET imagen_empresa = ? WHERE id = ?");
                $stmt->execute([$nuevoFilename, $id]);
                $usuario['imagen_empresa'] = $nuevoFilename;
            }
        }
    } else {
        // Si NO subieron archivo pero cambiaron nombre, renombrar archivo existente si aplica
        if (!empty($usuario['imagen_empresa'])) {
            $rutaVieja = $rutaBase . $usuario['imagen_empresa'];
            if (file_exists($rutaVieja) && is_file($rutaVieja)) {
                if ($usuario['imagen_empresa'] !== $nuevoFilename) {
                    $rutaNueva = $rutaFinal;
                    if (@rename($rutaVieja, $rutaNueva)) {
                        $stmt = $pdo->prepare("UPDATE usuarios SET imagen_empresa = ? WHERE id = ?");
                        $stmt->execute([$nuevoFilename, $id]);
                        $usuario['imagen_empresa'] = $nuevoFilename;
                    }
                }
            }
        }
    }


    // ---------- Manejo de contraseña y demás campos ----------
    if ($cambiar_contrasena) {
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
        if ($contrasena !== $confirmar_contrasena) {
            $mensaje = $mensaje ?: "Las contraseñas no coinciden.";
        } elseif (strlen($contrasena) < 6) {
            $mensaje = $mensaje ?: "La contraseña debe tener al menos 6 caracteres.";
        } else {
            $hash = password_hash($contrasena, PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios 
                    SET nombre=?, email=?, contrasena=?, rol_id=?, puesto=?, ubicacion=?, numero=?, prefijo=?, pais=?, pronombre=? 
                    WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $email, $hash, $rol_id, $puesto, $ubicacion, $numero, $prefijo, $pais, $pronombre, $id]);
            header("Location: dashboard.php?vista=usuario/usuarios");
            exit;
        }
    } else {
        $sql = "UPDATE usuarios 
                SET nombre=?, email=?, rol_id=?, puesto=?, ubicacion=?, numero=?, prefijo=?, pais=?, pronombre=? 
                WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $email, $rol_id, $puesto, $ubicacion, $numero, $prefijo, $pais, $pronombre, $id]);
        header("Location: dashboard.php?vista=usuario/usuarios");
        exit;
    }
}
?>
<link rel="stylesheet" href="/css/usuario/usuarios_editar.css">

<div class="card">
    <?php if (!empty($mensaje)): ?>
        <p style="color:red;"><strong><?= htmlspecialchars($mensaje) ?></strong></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <!-- Imagen de perfil actual -->
        <div class="form-group center">
            <?php
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
            $rutaChequeo = $docRoot . "/img/perfiles/" . ($usuario['perfil_usuario'] ?? '');
            if (!empty($usuario['perfil_usuario']) && file_exists($rutaChequeo)): ?>
                <img src="/img/perfiles/<?= htmlspecialchars($usuario['perfil_usuario']) ?>" alt="Imagen de perfil" width="120" style="border-radius:50%;">
            <?php else: ?>
                <img src="/img/perfiles/default.webp" alt="Imagen de perfil" width="120" style="border-radius:50%;">
            <?php endif; ?>
        </div>

        <!-- Campo para subir nueva imagen -->
        <div class="form-group">
            <label>Cambiar imagen de perfil</label>
            <input type="file" name="imagen_perfil" accept=".jpg,.jpeg,.png,.gif,.webp">
            <small>Se convertirá a .webp y se renombrará como el nombre de usuario.</small>
        </div>

        <div class="form-group">
            <label>Cambiar imagen de tu empresa</label>
            <input type="file" name="imagen_empresa" accept=".jpg,.jpeg,.png,.gif,.webp">
            <small>Se convertirá a .webp y se renombrará como el nombre de usuario.</small>
        </div>

        <!-- Resto de campos -->
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($usuario['nombre']) ?>">
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($usuario['email']) ?>">
        </div>

        <?php if ($es_admin): ?>
        <div class="form-group">
            <label>Rol *</label>
            <select name="rol_id" required>
                <option value="">-- Seleccionar Rol --</option>
                <?php foreach ($roles as $rol): ?>
                    <option value="<?= $rol['id'] ?>" <?= $rol['id'] == $usuario['rol_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($rol['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
            <input type="hidden" name="rol_id" value="<?= htmlspecialchars($usuario['rol_id']) ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Puesto *</label>
            <input type="text" name="puesto" required value="<?= htmlspecialchars($usuario['puesto']) ?>">
        </div>

        <div class="form-group">
            <label>Ubicación *</label>
            <input type="text" name="ubicacion" required value="<?= htmlspecialchars($usuario['ubicacion']) ?>">
        </div>

        <div class="form-group">
            <label>País *</label>
            <select id="selectPais" style="width: 100%;">
                <option value="">-- Seleccionar País --</option>
                <?php foreach ($paises as $paisOpt): ?>
                    <option value="<?= htmlspecialchars($paisOpt['pronombre']) ?>"
                            data-prefijo="<?= htmlspecialchars($paisOpt['prefijo']) ?>"
                            data-nombre="<?= htmlspecialchars($paisOpt['pais']) ?>"
                            <?= $paisOpt['pais'] == $usuario['pais'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($paisOpt['pais']) ?> (+<?= htmlspecialchars($paisOpt['prefijo']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <input type="hidden" name="prefijo" id="prefijo" value="<?= htmlspecialchars($usuario['prefijo']) ?>">
        <input type="hidden" name="pais" id="pais" value="<?= htmlspecialchars($usuario['pais']) ?>">
        <input type="hidden" name="pronombre" id="pronombre" value="<?= htmlspecialchars($usuario['pronombre']) ?>">

        <div class="form-group">
            <label>Número *</label>
            <input type="text" name="numero" required value="<?= htmlspecialchars($usuario['numero']) ?>">
        </div>

        <div class="form-group center">
            <label>
                <input type="checkbox" id="cambiar_contrasena" name="cambiar_contrasena" onchange="togglePasswordFields()"> Cambiar contraseña
            </label>
        </div>

        <div id="password_fields" style="display: none;">
            <div class="form-group">
                <label>Nueva Contraseña *</label>
                <input type="password" name="contrasena">
            </div>

            <div class="form-group">
                <label>Confirmar Nueva Contraseña *</label>
                <input type="password" name="confirmar_contrasena">
            </div>
        </div>

        <div class="form-group center">
            <button type="submit">💾 Guardar cambios</button>
            <br><br>
            <a href="dashboard.php?vista=usuario/usuarios">🔙 Volver</a>
        </div>
    </form>
</div>

<script>
document.getElementById('selectPais').addEventListener('change', function() {
    let option = this.options[this.selectedIndex];
    document.getElementById('prefijo').value = "+" + option.dataset.prefijo;
    document.getElementById('pais').value = option.dataset.nombre;
    document.getElementById('pronombre').value = option.value;
});

function togglePasswordFields() {
    const checkbox = document.getElementById('cambiar_contrasena');
    const fields = document.getElementById('password_fields');
    fields.style.display = checkbox.checked ? 'block' : 'none';
}
</script>
