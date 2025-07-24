<?php
// Agregar color
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['codigo_hex'])) {
    $nombre = trim($_POST['nombre']);
    $codigo = trim($_POST['codigo_hex']);

    if ($nombre !== '' && preg_match('/^#[a-f0-9]{6}$/i', $codigo)) {
        $stmt = $pdo->prepare("INSERT INTO colores (nombre, codigo_hex) VALUES (?, ?)");
        $stmt->execute([$nombre, $codigo]);
        header("Location: dashboard.php?vista=colores");
        exit;
    } else {
        echo "<p style='color:red;'>Nombre válido y color HEX requerido.</p>";
    }
}

// Eliminar color si no está en uso
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM producto_tallas_colores WHERE color_id = ?");
    $check->execute([$id]);
    if ($check->fetchColumn() == 0) {
        $del = $pdo->prepare("DELETE FROM colores WHERE id = ?");
        $del->execute([$id]);
    }
    header("Location: dashboard.php?vista=colores");
    exit;
}

// Listado
$colores = $pdo->query("SELECT * FROM colores ORDER BY nombre")->fetchAll();
?>

<div class="card">
    <h2>🎨 Colores</h2>

    <form method="POST" style="margin-bottom:20px;">
        <input type="text" name="nombre" placeholder="Nombre del color" required>
        <input type="color" name="codigo_hex" value="#000000" required>
        <button type="submit">➕ Agregar</button>
    </form>

    <?php if (count($colores) === 0): ?>
        <p>No hay colores registrados.</p>
    <?php else: ?>
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color:#f1f1f1;">
                    <th style="padding:8px;">#</th>
                    <th>Nombre</th>
                    <th>Color</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($colores as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['nombre']) ?></td>
                        <td><div style="width:30px; height:20px; background:<?= $c['codigo_hex'] ?>; border:1px solid #ccc;"></div></td>
                        <td>
                            <a href="dashboard.php?vista=editar_color&id=<?= $c['id'] ?>">✏️ Editar</a>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM producto_tallas_colores WHERE color_id = ?");
                            $stmt->execute([$c['id']]);
                            if ($stmt->fetchColumn() == 0): ?>
                                &nbsp; <a href="dashboard.php?vista=colores&eliminar=<?= $c['id'] ?>" onclick="return confirm('¿Eliminar este color?')">🗑️ Eliminar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
