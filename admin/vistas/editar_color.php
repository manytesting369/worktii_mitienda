<?php
if (!isset($_GET['id'])) {
    echo "<p>ID de color no especificado.</p>";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM colores WHERE id = ?");
$stmt->execute([$id]);
$color = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$color) {
    echo "<p>Color no encontrado.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = trim($_POST['nombre']);
    $nuevo_hex = trim($_POST['codigo_hex']);

    if ($nuevo_nombre !== '' && preg_match('/^#[a-f0-9]{6}$/i', $nuevo_hex)) {
        $update = $pdo->prepare("UPDATE colores SET nombre = ?, codigo_hex = ? WHERE id = ?");
        $update->execute([$nuevo_nombre, $nuevo_hex, $id]);
        header("Location: dashboard.php?vista=colores");
        exit;
    } else {
        echo "<p style='color:red;'>Nombre válido y color HEX requerido.</p>";
    }
}
?>

<div class="card">
    <h2>✏️ Editar Color</h2>

    <form method="POST">
        <label>Nombre del color:</label><br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($color['nombre']) ?>" required style="width: 100%; max-width: 400px;"><br><br>

        <label>Código de color:</label><br>
        <input type="color" name="codigo_hex" value="<?= htmlspecialchars($color['codigo_hex']) ?>" required><br><br>

        <button type="submit">💾 Guardar cambios</button>
        &nbsp;
        <a href="dashboard.php?vista=colores">🔙 Volver</a>
    </form>
</div>
