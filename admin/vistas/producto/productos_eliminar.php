<?php
require_once '../config/conexion.php';

if (!isset($_GET['id']) || !isset($_GET['producto'])) {
    die('Parámetros incompletos.');
}

$id_imagen = $_GET['id'];
$id_producto = $_GET['producto'];

// Obtener la ruta del archivo
$stmt = $pdo->prepare("SELECT ruta FROM imagenes_producto WHERE id = ?");
$stmt->execute([$id_imagen]);
$imagen = $stmt->fetch(PDO::FETCH_ASSOC);

if ($imagen) {
    $ruta_fisica = '../../' . $imagen['ruta'];

    // Eliminar archivo físico si existe
    if (file_exists($ruta_fisica)) {
        unlink($ruta_fisica);
    }

    // Eliminar de la base de datos
    $del = $pdo->prepare("DELETE FROM imagenes_producto WHERE id = ?");
    $del->execute([$id_imagen]);
}

header("Location: dashboard.php?vista=producto_editar&id=" . $id_producto);
exit;
?>
