<?php
// Obtener roles desde la tabla 'roles'
$roles = $pdo->query("SELECT id, nombre FROM roles ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Insertar nuevo usuario si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol_id = $_POST['rol_id'] ?? null;
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];


    // Validación básica
    if ($contrasena !== $confirmar_contrasena) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {
        // Encriptar contraseña
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);

        // Insertar en la base de datos
        $sql = "INSERT INTO usuarios (nombre, email, contrasena, rol_id) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $email, $hash, $rol_id]);

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
    <form method="POST">
        <label>Nombre *</label><br>
        <input type="text" name="nombre" required style="width: 100%;"><br><br>

        <label>Email *</label><br>
        <input type="email" name="email" required style="width: 100%;"><br><br>

        <label>Rol *</label><br>
        <select name="rol_id" required style="width: 100%;">
            <option value="">-- Seleccionar Rol --</option>
            <?php foreach ($roles as $rol): ?>
                <option value="<?= $rol['id'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Contraseña *</label><br>
        <input type="password" name="contrasena" required style="width: 100%;"><br><br>

        <label>Confirmar contraseña *</label><br>
        <input type="password" name="confirmar_contrasena" required style="width: 100%;"><br><br>

        <button type="submit">💾 Guardar usuario</button>
        &nbsp; <a href="dashboard.php?vista=usuario/usuarios">🔙 Volver</a>
    </form>
</div>

