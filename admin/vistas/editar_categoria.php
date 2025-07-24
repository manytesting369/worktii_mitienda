<?php
if (!isset($_GET['id'])) {
    echo "<p>ID de categoría no especificado.</p>";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
$stmt->execute([$id]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    echo "<p>Categoría no encontrada.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = trim($_POST['nombre']);
    if ($nuevo_nombre !== '') {
        $update = $pdo->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
        $update->execute([$nuevo_nombre, $id]);
        header("Location: dashboard.php?vista=categorias");
        exit;
    } else {
        echo "<p style='color:red;'>El nombre no puede estar vacío.</p>";
    }
}
?>

<div class="card">
    <h2>✏️ Editar Categoría</h2>

    <form method="POST">
        <label>Nuevo nombre:</label><br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" required style="width: 100%; max-width: 400px;"><br><br>
        <button type="submit">💾 Guardar cambios</button>
        &nbsp;
        <a href="dashboard.php?vista=categorias">🔙 Volver</a>
    </form>
</div>
