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

    if (eliminarUsuarioPorNombre($usuario)) {
        header("Location: ../admin_usuarios.php?msg=eliminado");
        exit;
    } else {
        header("Location: ../admin_usuarios.php?error=eliminar");
        exit;
    }
} else {
    // Redirige si no vino con POST válido
    header("Location: ../admin_usuarios.php?error=notfound");
    exit;
}
?>
