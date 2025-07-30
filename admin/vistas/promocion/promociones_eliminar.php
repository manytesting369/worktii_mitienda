<?php

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alerta error'>ID no válido.</div>";
    return;
}

$id = (int) $_GET['id'];

// Verificar si existe la promoción
$stmt = $pdo->prepare("SELECT * FROM promociones WHERE id = ?");
$stmt->execute([$id]);
$promocion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$promocion) {
    echo "<div class='alerta error'>La promoción no existe.</div>";
    return;
}

// Eliminar la promoción
$delete = $pdo->prepare("DELETE FROM promociones WHERE id = ?");
$delete->execute([$id]);

// Redireccionar a la lista de promociones
echo "<script>location.href = 'dashboard.php?vista=promocion/promociones';</script>";
exit;
?>
