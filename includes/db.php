<?php
function conectarDB() {
    $host = "localhost";
    $db = "portal_talleres";
    $user = "root";
    $pass = "";
    $charset = "utf8mb4";

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        error_log("Error al conectar a la base de datos: " . $e->getMessage()); // log del server
        header("Location: error_db.php"); // Redirige a pagina error
        exit;
    }
}
?>
