<?php
require_once 'config/conexion.php';

// Configuración general
$config = $pdo->query("SELECT * FROM configuracion LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$logo = $config['logo'] ?? '';
$nombreTienda = 'Catálogo de Ropa';
$colorPrimario = $config['color_primario'] ?? '#111';
$whatsapp = $config['whatsapp_defecto'];
$facebook = $config['link_facebook'] ?? '';
$instagram = $config['link_instagram'] ?? '';
$tiktok = $config['link_tiktok'] ?? '';

// Filtros
$buscar = $_GET['buscar'] ?? '';
$categoria_id = $_GET['categoria'] ?? '';

// Listados
$categorias = $pdo->query("SELECT id, nombre FROM categorias")->fetchAll(PDO::FETCH_ASSOC);

// Consulta base
$sql = "
    SELECT p.id, p.nombre, p.descripcion, p.precio,
    (SELECT ruta FROM imagenes_producto WHERE producto_id = p.id LIMIT 1) AS imagen,
    (SELECT titulo FROM promociones WHERE producto_id = p.id AND estado = 'activo' LIMIT 1) AS promo
    FROM productos p
    WHERE estado_activo = 1
";

$params = [];

if (!empty($buscar)) {
    $sql .= " AND (p.nombre LIKE :busqueda OR p.descripcion LIKE :busqueda)";
    $params[':busqueda'] = "%$buscar%";
}


if (!empty($categoria_id)) {
    $sql .= " AND p.categoria_id = :categoria_id";
    $params[':categoria_id'] = $categoria_id;
}

$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= $nombreTienda ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <script>
        function autoSubmit() {
            document.getElementById('formFiltros').submit();
        }
    </script>
    <!-- Añade esta línea si usas la fuente Playfair Display -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-primario: <?= $colorPrimario ?>;
        }
    </style>

</head>
<body>

<header>
    <div class="header-left">
        <?php if ($logo): ?>
            <img src="<?= $logo ?>" alt="Logo">
        <?php endif; ?>
        <h1><?= $nombreTienda ?></h1>
    </div>
    <div class="redes">
        <?php if ($facebook): ?><a href="<?= $facebook ?>" target="_blank"><img src="icons/facebook.png"></a><?php endif; ?>
        <?php if ($instagram): ?><a href="<?= $instagram ?>" target="_blank"><img src="icons/instagram.png"></a><?php endif; ?>
        <?php if ($tiktok): ?><a href="<?= $tiktok ?>" target="_blank"><img src="icons/tiktok.png"></a><?php endif; ?>
        <?php if ($whatsapp): ?><a href="https://wa.me/<?= $whatsapp ?>" target="_blank"><img src="icons/whatsapp.png"></a><?php endif; ?>
    </div>
</header>

<div class="contenedor">
    <div class="titulo">Catálogo de Ropa</div>

    <form id="formFiltros" class="filtros" method="get">
        <input type="text" name="buscar" placeholder="Buscar productos..." value="<?= htmlspecialchars($buscar) ?>" onchange="autoSubmit()">
        <select name="categoria" onchange="autoSubmit()">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $categoria_id == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="productos">
        <?php foreach ($productos as $p): ?>
            <div class="card">
                <div class="img-container">
                    <a href="producto.php?id=<?= $p['id'] ?>">
                        <img src="<?= $p['imagen'] ?? 'img/default.jpg' ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                        <?php if ($p['promo']): ?>
                            <div class="promo-badge"><?= htmlspecialchars($p['promo']) ?></div>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="contenido">
                    <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                    <p><?= htmlspecialchars($p['descripcion']) ?></p>
                    <div class="precio">S/. <?= number_format($p['precio'], 2) ?></div>
                </div>
                <?php if ($whatsapp): ?>
                    <div class="whatsapp">
                        <a target="_blank" href="https://wa.me/918731713<?= $whatsapp ?>?text=Hola, me interesa el producto: <?= urlencode($p['nombre']) ?>">Consultar por WhatsApp</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<footer>
    &copy; <?= date('Y') ?> Catálogo Web. Todos los derechos reservados.
</footer>

</body>
</html>
