<?php

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID inválido'); location.href = 'dashboard.php?vista=producto/productos_';</script>";
    exit;
}

$id_producto = (int) $_GET['id'];

// 1. Buscar imágenes asociadas al producto
$stmt = $pdo->prepare("SELECT ruta FROM imagenes_producto WHERE producto_id = ?");
$stmt->execute([$id_producto]);
$imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Eliminar archivos físicos
foreach ($imagenes as $img) {
    $ruta_fisica = '../../' . $img['ruta'];
    if (file_exists($ruta_fisica)) {
        unlink($ruta_fisica);
    }
}

// 3. Eliminar producto (ON DELETE CASCADE se encarga del resto)
$del = $pdo->prepare("DELETE FROM productos WHERE id = ?");
$del->execute([$id_producto]);

// 4. Redireccionar
echo "<script>location.href = 'dashboard.php?vista=producto/productos';</script>";

exit;

