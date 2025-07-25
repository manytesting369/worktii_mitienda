<?php
session_start();
// Ajusta la ruta si hace falta; aquí supone que login.php está en /admin/
require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- DEBUG START ---
    var_dump($usuario);               // ¿Qué trae el fetch()? 
    if ($usuario) {
        $verificado = password_verify($pass, $usuario['contrasena']);
        var_dump($verificado);        // ¿password_verify? 
    }
    // --- DEBUG END ---

    if ($usuario && $verificado) {
        // Login correcto
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['rol']            = $usuario['rol'];
        header('Location: dashboard.php');
        exit;
    } else {
        $mensaje = 'Credenciales inválidas.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="main-container">
        <div class="logo-container">
            <img src="../img/empresa.png" alt="Logo" style="width: 140px; height: auto;">
        </div>
        <div class="container">
            <h2>Iniciar sesión</h2>
            <form method="POST">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" placeholder="ejemplo@correo.com" required>
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" placeholder="Ingresa tu contraseña" required>
                <button type="submit">Ingresar</button>
            </form>
            <?php if ($mensaje): ?>
                <div class="mensaje"><?= $mensaje ?></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
