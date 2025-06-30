<?php
include("includes/header.php");
include("includes/nav.php");
require_once("includes/db.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["usuario"]);
    $correo = filter_var($_POST["correo"], FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $rol = "tallerista";

    if (empty($usuario) || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($password) < 4) {
        $mensaje = '<div class="alert alert-danger">Datos inválidos.</div>';
    } else {
        try {
            $pdo = conectarDB();

            // Verificar si ya existe el usuario
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
            $stmt->execute([$usuario]);

            if ($stmt->rowCount() > 0) {
                $mensaje = '<div class="alert alert-danger">El usuario ya existe.</div>';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, correo, contraseña, rol) VALUES (?, ?, ?, ?)");
                $stmt->execute([$usuario, $correo, $hash, $rol]);
                $mensaje = '<div class="alert alert-success">Usuario registrado exitosamente.</div>';
            }
        } catch (PDOException $e) {
            $mensaje = '<div class="alert alert-danger">Error al registrar. Intente más tarde.</div>';
        }
    }
}
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <form method="post" class="card p-4 shadow-sm">
        <h2 class="text-center mb-4">Registro de Usuario</h2>

        <?= $mensaje ?>

        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="usuario" name="usuario" required>
        </div>

        <div class="mb-3">
          <label for="correo" class="form-label">Correo</label>
          <input type="email" class="form-control" id="correo" name="correo" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-success">Registrarse</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>
