<?php
if (!isset($_GET['id'])) {
    echo "<p>ID no especificado.</p>";
    exit;
}


$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "<p>Usuario no encontrado.</p>";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $cambiar_contrasena = isset($_POST['cambiar_contrasena']);

    if ($cambiar_contrasena) {
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

        if ($contrasena !== $confirmar_contrasena) {
            $mensaje = "Las contraseñas no coinciden.";
        } elseif (strlen($contrasena) < 6) {
            $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        } else {
            $hash = password_hash($contrasena, PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios SET nombre=?, email=?, contrasena=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $email, $hash, $id]);

            header("Location: dashboard.php?vista=usuario/usuarios");
            exit;
        }
    } else {
        $sql = "UPDATE usuarios SET nombre=?, email=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $email, $id]);

        header("Location: dashboard.php?vista=usuario/usuarios");
        exit;
    }
}

?>

<link rel="stylesheet" href="/./css/usuario/usuarios_editar.css">

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($usuario['nombre']) ?>">
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($usuario['email']) ?>">
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
    function togglePasswordFields() {
        const checkbox = document.getElementById('cambiar_contrasena');
        const fields = document.getElementById('password_fields');
        fields.style.display = checkbox.checked ? 'block' : 'none';
    }
</script>


