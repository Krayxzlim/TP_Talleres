<?php
require_once("db.php");
require_once("funciones.php");
session_start();

// Solo admins
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

$pdo = conectarDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'])) {
    $usuario = $_POST['usuario'];

    // Obtener rol actual
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE usuario = :usuario");
    $stmt->execute(['usuario' => $usuario]);
    $actual = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($actual) {
        $nuevoRol = ($actual['rol'] === 'admin') ? 'tallerista' : 'admin';

        if (cambiarRolUsuario($usuario, $nuevoRol)) {
            header("Location: ../admin_usuarios.php?msg=rol");
            exit;
        } else {
            header("Location: ../admin_usuarios.php?error=rol");
            exit;
        }
    } else {
        header("Location: ../admin_usuarios.php?error=notfound");
        exit;
    }
}
?>
