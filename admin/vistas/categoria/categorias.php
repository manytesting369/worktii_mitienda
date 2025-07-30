<?php
// Agregar categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_categoria'])) {
    $nueva = trim($_POST['nueva_categoria']);
    if ($nueva !== '') {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)");
        $stmt->execute([$nueva]);
        header("Location: dashboard.php?vista=categoria/categorias");
        exit;
    }
}


// Eliminar categoría si no está en uso
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = ?");
    $check->execute([$id]);
    $enUso = $check->fetchColumn();

    if ($enUso == 0) {
        $del = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
        $del->execute([$id]);
    }
    header("Location: dashboard.php?vista=categoria/categorias");
    exit;
}


// Listado de categorías
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
?>

<div class="card">
    <h2>📁 Categorías</h2>

    <form method="POST" style="margin-bottom:20px;">
        <input type="text" name="nueva_categoria" placeholder="Nueva categoría" required>
        <button type="submit">➕ Agregar</button>
    </form>

    <?php if (count($categorias) === 0): ?>
        <p>No hay categorías registradas.</p>
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
                <?php 
                $contador = 1;
                foreach ($categorias as $cat): ?>
                    <tr>
                        <td style="padding:6px;"><?= $contador++ ?></td>
                        <td><?= htmlspecialchars($cat['nombre']) ?></td>
                        <td>
                            <a href="dashboard.php?vista=categoria/editar_categoria&id=<?= $cat['id'] ?>">✏️ Editar</a>

                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = ?");
                            $stmt->execute([$cat['id']]);
                            if ($stmt->fetchColumn() == 0): ?>
                                &nbsp; <a href="dashboard.php?vista=categoria/categorias&eliminar=<?= $cat['id'] ?>" onclick="return confirm('¿Eliminar esta categoría?')">🗑️ Eliminar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

