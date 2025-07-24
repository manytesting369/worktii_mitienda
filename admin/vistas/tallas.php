<?php
// Agregar talla
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_talla'])) {
    $nueva = trim($_POST['nueva_talla']);
    if ($nueva !== '') {
        $stmt = $pdo->prepare("INSERT INTO tallas (nombre) VALUES (?)");
        $stmt->execute([$nueva]);
        header("Location: dashboard.php?vista=tallas");
        exit;
    }
}

// Eliminar talla si no está en uso
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM producto_tallas_colores WHERE talla_id = ?");
    $check->execute([$id]);
    $enUso = $check->fetchColumn();

    if ($enUso == 0) {
        $del = $pdo->prepare("DELETE FROM tallas WHERE id = ?");
        $del->execute([$id]);
    }
    header("Location: dashboard.php?vista=tallas");
    exit;
}

// Listado de tallas
$tallas = $pdo->query("SELECT * FROM tallas ORDER BY nombre")->fetchAll();
?>

<div class="card">
    <h2>📏 Tallas</h2>

    <form method="POST" style="margin-bottom:20px;">
        <input type="text" name="nueva_talla" placeholder="Nueva talla" required>
        <button type="submit">➕ Agregar</button>
    </form>

    <?php if (count($tallas) === 0): ?>
        <p>No hay tallas registradas.</p>
    <?php else: ?>
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color:#f1f1f1;">
                    <th style="padding:8px;">#</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tallas as $t): ?>
                    <tr>
                        <td style="padding:6px;"><?= $t['id'] ?></td>
                        <td><?= htmlspecialchars($t['nombre']) ?></td>
                        <td>
                            <a href="dashboard.php?vista=editar_talla&id=<?= $t['id'] ?>">✏️ Editar</a>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM producto_tallas_colores WHERE talla_id = ?");
                            $stmt->execute([$t['id']]);
                            if ($stmt->fetchColumn() == 0): ?>
                                &nbsp; <a href="dashboard.php?vista=tallas&eliminar=<?= $t['id'] ?>" onclick="return confirm('¿Eliminar esta talla?')">🗑️ Eliminar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
