<?php
require_once 'config/conexion.php';

if (!isset($_GET['id'])) {
    echo "<p>ID de producto no especificado.</p>";
    exit;
}

$id = $_GET['id'];

// Registrar visita (si existe la columna 'visitas')
try {
    $pdo->exec("UPDATE productos SET visitas = visitas + 1 WHERE id = $id");
} catch (PDOException $e) {
    // Silencioso: no hacer nada si la columna no existe
}

// 1) Cargar producto
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$producto) {
    echo "<p>Producto no encontrado.</p>";
    exit;
}

// 2) Cargar configuración
$config = $pdo->query("SELECT * FROM configuracion LIMIT 1")->fetch();
$logo           = $config['logo']             ?? '';
$nombreTienda   = $config['nombre_tienda']    ?? 'Mi Tienda';
$colorPrimario  = $config['color_primario']   ?? '#007bff';
$whatsapp       = $config['whatsapp_defecto'] ?? '';

// 3) Construir URL actual para WhatsApp
$scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$currentUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$wspText    = "Hola! Estoy interesado en el producto \"{$producto['nombre']}\". Link: {$currentUrl}";
$wspTextEnc = urlencode($wspText);

// 4) Cargar imágenes
$imgStmt = $pdo->prepare("SELECT * FROM imagenes_producto WHERE producto_id = ?");
$imgStmt->execute([$id]);
$imagenes = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Cargar promoción activa (si existe)
$promoStmt = $pdo->prepare("
    SELECT * 
    FROM promociones 
    WHERE producto_id = ? 
      AND estado = 'activo'
    ORDER BY fecha_inicio DESC
    LIMIT 1
");
$promoStmt->execute([$id]);
$promo = $promoStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($producto['nombre']) ?> – <?= htmlspecialchars($nombreTienda) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <link rel="stylesheet" href="css/producto_inicio.css">
    <style>
        :root {
            --color-primario: <?= $colorPrimario ?>;
        }
    </style>
</head>

<body>

    <header>
        <div class="logo_header">
            <a href="/inicio" class="header-left">
                <?php if ($logo): ?>
                    <img src="<?= htmlspecialchars($logo) ?>" alt="Logo">
                <?php endif; ?>
            </a>
        </div>
        <a href="index.php" class="btn-volver">← Volver</a>
    </header>



    <div class="container">
        <div class="galeria">
            <!-- Swiper principal -->
            <div class="swiper swiper-main" onclick="abrirZoom()">
                <div class="swiper-wrapper">
                    <?php foreach ($imagenes as $img): ?>
                        <div class="swiper-slide">
                            <img src="<?= htmlspecialchars($img['ruta']) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Información -->
            <div class="info">
                <h2><?= htmlspecialchars($producto['nombre']) ?></h2>
                <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
                <?php
                $precioOriginal = $producto['precio'];
                $precioFinal = $precioOriginal;

                if ($promo && $promo['estado'] === 'activo') {
                    if ($promo['tipo'] === 'porcentaje') {
                        $descuento = $precioOriginal * ($promo['valor'] / 100);
                        $precioFinal = $precioOriginal - $descuento;
                    } elseif ($promo['tipo'] === 'fijo') {
                        $precioFinal = $precioOriginal - $promo['valor'];
                    }
                }
                ?>

                <div class="precios">
                    <?php if ($promo && $promo['estado'] === 'activo'): ?>
                        <div class="precio-original">S/. <?= number_format($precioOriginal, 2) ?></div>
                        <div class="precio-promocion">S/. <?= number_format($precioFinal, 2) ?></div>
                    <?php else: ?>
                        <div class="precio-unico">S/. <?= number_format($precioOriginal, 2) ?></div>
                    <?php endif; ?>
                </div>
                <div class="stock">
                    Stock disponible: <?= intval($producto['stock']) ?> unidades
                </div>


                <!-- Botón WhatsApp con enlace al producto -->
                <?php if ($whatsapp): ?>
                    <a class="btn-wsp"
                        href="https://wa.me/<?= htmlspecialchars($whatsapp) ?>?text=<?= $wspTextEnc ?>"
                        target="_blank">
                        Consultar por WhatsApp
                    </a>
                <?php endif; ?>


                <!-- Promo -->
                <?php if ($promo):
                    if ($promo['tipo'] === 'porcentaje') {
                        $val = number_format($promo['valor'], 0) . '%';
                    } elseif ($promo['tipo'] === 'fijo') {
                        $val = 'S/. ' . number_format($promo['valor'], 2);
                    } else {
                        $val = 'Envío Gratis';
                    }
                ?>
                    <div class="promo">
                        <strong><?= htmlspecialchars($promo['titulo']) ?> – <?= $val ?></strong>
                        <?= nl2br(htmlspecialchars($promo['descripcion'])) ?>
                        <small>Vigencia: <?= date('d/m/Y', strtotime($promo['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($promo['fecha_fin'])) ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Miniaturas -->
        <?php if (count($imagenes) > 1): ?>
            <div class="swiper swiper-thumb">
                <div class="swiper-wrapper">
                    <?php foreach ($imagenes as $img): ?>
                        <div class="swiper-slide">
                            <img src="<?= htmlspecialchars($img['ruta']) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Zoom Swiper -->
    <div class="modal" id="zoomModal">
        <span class="cerrar" onclick="cerrarZoom()">✖</span>
        <div class="swiper swiper-zoom">
            <div class="swiper-wrapper">
                <?php foreach ($imagenes as $img): ?>
                    <div class="swiper-slide">
                        <img src="<?= htmlspecialchars($img['ruta']) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y') ?> <?= htmlspecialchars($nombreTienda) ?>. Todos los derechos reservados.
    </footer>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        const swiperThumb = new Swiper('.swiper-thumb', {
            spaceBetween: 10,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesProgress: true
        });
        const swiperMain = new Swiper('.swiper-main', {
            spaceBetween: 10,
            thumbs: {
                swiper: swiperThumb
            }
        });
        const swiperZoom = new Swiper('.swiper-zoom', {
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            }
        });

        function abrirZoom() {
            document.getElementById('zoomModal').classList.add('active');
            swiperZoom.slideTo(swiperMain.activeIndex);
        }

        function cerrarZoom() {
            document.getElementById('zoomModal').classList.remove('active');
        }
    </script>

</body>
 
</html>