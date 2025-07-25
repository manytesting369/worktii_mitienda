<?php
require_once __DIR__ . '/../../../config/conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>ID de categoría no especificado o inválido.</p>";
    exit;
}

$id = (int) $_GET['id'];

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

<div style="max-width: 500px; margin: 30px auto; padding: 25px; border: 1px solid #ccc; border-radius: 10px; background-color: #f9f9f9; box-shadow: 0 4px 8px rgba(0,0,0,0.05); font-family: Arial, sans-serif;">
    <h2 style="text-align: center; margin-bottom: 20px;">✏️ Editar Categoría</h2>
    <form method="POST">
        <label for="nombre" style="font-weight: bold;">Nuevo nombre:</label><br>
        <input 
            type="text" 
            id="nombre" 
            name="nombre" 
            value="<?= htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') ?>" 
            required 
            style="width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #ccc; font-size: 16px;"
        ><br>
        <button 
            type="submit" 
            style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;"
        >💾 Guardar cambios</button>
        &nbsp;
        <a 
            href="dashboard.php?vista=categorias" 
            style="padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px; font-size: 16px;"
        >🔙 Volver</a>
    </form>
</div>


