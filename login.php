<?php
session_start();

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_input = $_POST['usuario'];
    $password = $_POST['password'];

    $archivo = "data/usuarios_registrados.json";
    $usuarios_final = [];

    if (file_exists($archivo)) {
        $usuarios_final = json_decode(file_get_contents($archivo), true);
    }

    $usuario = array_filter($usuarios_final, fn($u) => $u['usuario'] === $usuario_input && $u['contraseña'] === $password);

    if (!empty($usuario)) {
        $_SESSION['usuario'] = reset($usuario);
        header("Location: index.php");
        exit;
    } else {
        $mensaje = "Usuario o contraseña incorrectos";
    }
}

include("includes/header.php");
include("includes/nav.php");
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <form method="post" class="card p-4 shadow-sm">
        <h2 class="text-center mb-4">Iniciar Sesión</h2>

        <?php if (!empty($mensaje)): ?>
          <div class="alert alert-danger"><?= $mensaje ?></div>
        <?php endif; ?>

        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="usuario" name="usuario" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Ingresar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>
