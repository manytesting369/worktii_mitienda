<?php
class Conexion {
    private static $pdo;

    public static function conectar() {
        if (self::$pdo == null) {
            $host = 'localhost';
            $db = 'worktii_mitienda2';
            $user = 'admi';
            $pass = 'admi';
            $charset = 'utf8mb4';

            try {
                self::$pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo "Error de conexión: " . $e->getMessage();
                exit;
            }
        }
        return self::$pdo;
    }
}
?>
