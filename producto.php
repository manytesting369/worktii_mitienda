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

    <style>
        :root {
            --color-primario: <?= $colorPrimario ?>;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
        }

        header {
            background: var(--color-primario);
            color: #fff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header img {
            height: 40px;
        }

        .btn-volver {
            background: #fff;
            color: var(--color-primario);
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .galeria {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .swiper {
            width: 100%;
            max-width: 480px;
            border-radius: 10px;
            overflow: hidden;
        }

        .swiper-slide img {
            width: 100%;
            height: auto;
            object-fit: contain;
            cursor: pointer;
        }

        .info {
            flex: 1;
            min-width: 250px;
        }

        .info h2 {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .info p {
            margin-bottom: 12px;
        }

        .precio {
            color: #e74c3c;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .btn-wsp,
        .btn-video {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
        }

        .btn-wsp {
            background: #25D366;
            color: #fff;
        }

        .btn-video {
            background: var(--color-primario);
            color: #fff;
            margin-left: 10px;
        }

        .promo {
            margin-top: 15px;
            padding: 12px;
            border-radius: 6px;
            background: linear-gradient(90deg, #f44336, #ff9800);
            color: #fff;
        }

        .promo strong {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 4px;
        }

        .promo small {
            display: block;
            margin-top: 6px;
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .swiper-thumb {
            margin-top: 20px;
        }

        .swiper-thumb .swiper-slide {
            opacity: 0.5;
            border-radius: 8px;
            overflow: hidden;
        }

        .swiper-thumb .swiper-slide-thumb-active {
            opacity: 1;
            border: 2px solid var(--color-primario);
        }

        .swiper-thumb img {
            width: 100%;
            height: 60px;
            object-fit: cover;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal.active {
            display: flex;
        }

        .modal .cerrar {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 30px;
            color: #fff;
            cursor: pointer;
        }

        .modal .swiper {
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            overflow: hidden;
        }

        .modal .swiper-slide img {
            width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }

        @media (max-width:768px) {
            .galeria {
                flex-direction: column;
                align-items: center;
            }

            .info {
                text-align: center;
            }
        }

        footer {
            margin-top: 40px;
            background: var(--color-primario);
            color: #fff;
            text-align: center;
            padding: 15px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <header>
        <div>
            <?php if ($logo): ?>
                <img src="<?= htmlspecialchars($logo) ?>" alt="Logo">
            <?php else: ?>
                <strong><?= htmlspecialchars($nombreTienda) ?></strong>
            <?php endif; ?>
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
                <div class="precio">S/. <?= number_format($producto['precio'], 2) ?></div>

                <!-- Botón WhatsApp con enlace al producto -->
                <?php if ($whatsapp): ?>
                    <a class="btn-wsp"
                        href="https://wa.me/<?= htmlspecialchars($whatsapp) ?>?text=<?= $wspTextEnc ?>"
                        target="_blank">
                        Consultar por WhatsApp
                    </a>
                <?php endif; ?>

                <!-- Botón Video -->
                <?php if (!empty($producto['video_url'])): ?>
                    <a class="btn-video"
                        href="<?= htmlspecialchars($producto['video_url']) ?>"
                        target="_blank">
                        Ver video del producto
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