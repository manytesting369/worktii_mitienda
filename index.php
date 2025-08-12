<?php
require_once 'config/conexion.php';

// Obtener configuración
$config = $pdo->query("SELECT * FROM configuracion LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Obtener datos del usuario de presentación
$usuarioVitrina = $pdo->prepare("
    SELECT nombre, pais, puesto, prefijo, numero, email, ubicacion,perfil_usuario,imagen_empresa
    FROM usuarios
    WHERE id = ?
");

$usuarioVitrina->execute([$config['usuario_presentacion']]);
$datosVitrina = $usuarioVitrina->fetch(PDO::FETCH_ASSOC);

$logo = $config['logo'] ?? '';
$nombreTienda = $config['titulo'] ?? 'Mi Tienda';
$colorPrimario = $config['color_primario'] ?? '#111';
$whatsapp = $config['whatsapp_defecto'];
$facebook = $config['link_facebook'] ?? '';
$instagram = $config['link_instagram'] ?? '';
$tiktok = $config['link_tiktok'] ?? '';
$logoBg = $colorPrimario;

// Filtros
$buscar = $_GET['buscar'] ?? '';
$categoria_id = $_GET['categoria'] ?? '';

// Listados
$categorias = $pdo->query("SELECT id, nombre FROM categorias")->fetchAll(PDO::FETCH_ASSOC);

// Consulta base
$sql = "
    SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock,
    (SELECT ruta FROM imagenes_producto WHERE producto_id = p.id LIMIT 1) AS imagen,
    (SELECT titulo FROM promociones WHERE producto_id = p.id AND estado = 'activo' LIMIT 1) AS promo
    FROM productos p
    WHERE estado_activo = 1
";

$stmt = $pdo->query("
    SELECT id, nombre, email
    FROM usuarios

");

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
    <!-- <link rel="icon" href="img/empresa.png"> -->
    <?php if ($logo): ?>
        <link rel="icon" href="<?= htmlspecialchars($logo) ?>" alt="icon">
    <?php endif; ?>
    <link rel="stylesheet" href="css/index.css">
    <script>
        function autoSubmit() {
            document.getElementById('formFiltros').submit();
        }
    </script>
    <!-- Añade esta línea si usas la fuente Playfair Display -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Agrega esto en tu <head> o antes del </body> -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <style>
        :root {
            --color-primario: <?= $colorPrimario ?>;
        }
    </style>

</head>
<body>
    <div class="page-wrapper">
        <header>
            <div class="logo_header">
                <a href="index.php" class="header-left">
                    <?php if ($logo): ?>
                        <img src="<?= htmlspecialchars($logo) ?>" alt="Logo">
                    <?php endif; ?>
                </a>
            </div>
            <div class="nombre_header">
                <a href="index.php" class="header-right">
                    <h1><?= htmlspecialchars($nombreTienda) ?></h1>
                </a>
            </div>
        </header>
        <!-- Banner visual superior -->
        <!-- Sección de tienda con logo y redes -->
        <div class="store-section">
            <!-- <div>

                <p class="store-description">
                    Vendemos productos informáticos, apto para la mejora de su negocio
                </p>
                
            </div>
             -->
            <div class="card-presentacion-container">
            <?php if ($datosVitrina): ?>
                <div class="card-presentacion-main">
                    <div class="card-presentacion-profile-bussiness">
                        <img 
                            class="card-presentacion-profile-bussiness" 
                            src="/img/perfil_empresa/<?= htmlspecialchars($datosVitrina['imagen_empresa']) ?>" 
                            alt="Foto de <?= htmlspecialchars($datosVitrina['nombre']) ?>"
                        >
                    </div>
                    <div class="card-presentacion-profile-section">
                        <img 
                            class="card-presentacion-profile-photo" 
                            src="/img/perfiles/<?= htmlspecialchars($datosVitrina['perfil_usuario']) ?>" 
                            alt="Foto de <?= htmlspecialchars($datosVitrina['nombre']) ?>"
                        >
                    </div>
                    <div class="card-presentacion-header">
                        <div class="card-presentacion-nombre">
                            <p><?= htmlspecialchars($datosVitrina['nombre']) ?></p>
                        </div>
                        <div class="card-presentacion-puesto">
                            <p><?= htmlspecialchars($datosVitrina['puesto']) ?></p>
                        </div>
                    </div>

                    <div class="card-presentacion-contactos">
                        <div class="card-presentacion-contact-item card-presentacion-telefono">
                            <div class="card-presentacion-icon">📞</div>
                            <a href="tel:<?= htmlspecialchars($datosVitrina['prefijo']) . htmlspecialchars($datosVitrina['numero']) ?>">
                                <p><?= htmlspecialchars($datosVitrina['prefijo']) . ' ' . htmlspecialchars($datosVitrina['numero']) ?></p>
                            </a>
                        </div>

                        <div class="card-presentacion-contact-item card-presentacion-email">
                            <div class="card-presentacion-icon">✉️</div>
                            <a href="mailto:<?= htmlspecialchars($datosVitrina['email']) ?>">
                                <p><?= htmlspecialchars($datosVitrina['email']) ?></p>
                            </a>
                        </div>

                        <div class="card-presentacion-contact-item card-presentacion-ubicacion">
                            <div class="card-presentacion-icon">📍</div>
                            <a href="http://maps.google.com/?q=<?= urlencode($datosVitrina['ubicacion']) ?>" target="_blank">
                                <p><?= htmlspecialchars($datosVitrina['ubicacion']) ?></p>
                            </a>
                        </div>
                        <div class="card-presentacion-contact-item card-presentacion-qr">
                            <?php
                            $vCard = "BEGIN:VCARD\n";
                            $vCard .= "VERSION:3.0\n";
                            $vCard .= "FN:" . $datosVitrina['nombre'] . "\n"; // Nombre completo
                            $vCard .= "TITLE:" . $datosVitrina['puesto'] . "\n"; // Puesto
                            $vCard .= "TEL;TYPE=CELL:" . $datosVitrina['prefijo'] . $datosVitrina['numero'] . "\n"; // Teléfono celular
                            $vCard .= "EMAIL:" . $datosVitrina['email'] . "\n"; // Email
                            // Puedes agregar dirección bien separada si tienes los datos:
                            // Ejemplo: ADR;TYPE=WORK:;;Calle 123;Ciudad;;Código Postal;País
                            $vCard .= "END:VCARD";
                            ?>                    
                            <button 
                                id="btnGenerarQR" 
                                class="btn-qr"
                                data-qr="<?= htmlspecialchars($vCard, ENT_QUOTES) ?>"
                            >📱 Generar QR</button>

                            <div id="qr-container" style="margin-top:10px;"></div>
                        </div>

                </div>
            <?php endif; ?>
        </div>


            <!-- Redes sociales -->
            <div class="social-icons-main">
                <?php if (!empty($whatsapp)): ?>
                    <a href="https://wa.me/<?= htmlspecialchars($whatsapp) ?>" target="_blank">
                        <img src="icons/whatsapp.png" alt="WhatsApp">
                    </a>
                <?php endif; ?>
                <?php if (!empty($facebook)): ?>
                    <a href="<?= htmlspecialchars($facebook) ?>" target="_blank">
                        <img src="icons/facebook.png" alt="Facebook">
                    </a>
                <?php endif; ?>
                <?php if (!empty($instagram)): ?>
                    <a href="<?= htmlspecialchars($instagram) ?>" target="_blank">
                        <img src="icons/instagram.png" alt="Instagram">
                    </a>
                <?php endif; ?>
                <?php if (!empty($tiktok)): ?>
                    <a href="<?= htmlspecialchars($tiktok) ?>" target="_blank">
                        <img src="icons/tiktok.png" alt="TikTok">
                    </a>
                <?php endif; ?>
                <!-- Puedes agregar otros iconos como YouTube, web, etc., si tienes los datos -->
            </div>
        </div>
        <div class="contenedor">
            <div class="titulo">Catálogo de productos</div>
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
                        </div>

                        
                        
                        <?php if ($whatsapp): ?>
                            <div class="whatsapp">
                                <a target="_blank" href="https://wa.me/<?= $whatsapp ?>?text=Hola, me interesó este producto: <?= urlencode($p['nombre']) ?>%0A necesito mas información">Consultar</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <footer>
            &copy; <?= date('Y') ?> Catálogo Web. Todos los derechos reservados.
        </footer>
    </div>
    
</body>
<!-- Script separado -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnQR = document.getElementById("btnGenerarQR");
    btnQR.addEventListener("click", function() {
        const qrText = btnQR.getAttribute("data-qr");

        document.getElementById("qr-container").innerHTML = ""; // Limpia anterior

        new QRCode(document.getElementById("qr-container"), {
            text: qrText,
            width: 200,
            height: 200,
            correctLevel: QRCode.CorrectLevel.L,
            version: 10 // aumenta capacidad
        });
    });
});
</script>
</html>
