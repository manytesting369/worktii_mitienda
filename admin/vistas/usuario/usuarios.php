<?php
// Obtener usuarioses con nombre de producto
$stmt = $pdo->query("
    SELECT u.id, u.nombre, u.email, r.nombre AS rol, u.fecha_creacion 
    FROM usuarios u
    JOIN roles r ON u.rol_id = r.id
");

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="/./css/usuario/usuarios.css">
<div class="card">
    <h2>Usuarios</h2>
    <a href="dashboard.php?vista=usuario/usuarios_nuevo" class="btn-agregar">➕ Nuevo usuario</a>


    <table>
        <thead>
            <tr>
                <th>Número</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Fecha de creación</th>
                <th>Acciones</th>
            </tr>
        </thead>


        <tbody>
            <?php foreach ($usuarios as $i => $usuario): ?>
                <tr>
                    <td><?= number_format($i +1) ?></td> 
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= ucfirst($usuario['rol']) ?></td>
                    <td><?= htmlspecialchars($usuario['fecha_creacion']) ?></td>
                    <td>
                        <a href="dashboard.php?vista=usuario/usuarios_editar&id=<?= $usuario['id'] ?>">✏️ Editar</a>
                        |
                        <a href="dashboard.php?vista=usuario/usuarios_eliminar&id=<?= $usuario['id'] ?>" onclick="return confirm('¿Eliminar este usuario?')">🗑️ Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

