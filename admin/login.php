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
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <h2>Panel de Administración</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Correo" required><br>
        <input type="password" name="password" placeholder="Contraseña" required><br>
        <button type="submit">Ingresar</button>
    </form>
    <p><?= $mensaje ?></p>
</body>
</html>
