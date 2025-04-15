<?php
include("includes/header.php");
include("includes/nav.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_usuario = [
        "usuario" => $_POST["usuario"],
        "correo" => $_POST["correo"],
        "contraseña" => $_POST["password"],
        "rol" => "tallerista"
    ];

    $file = "data/usuarios_registrados.json";
    $usuarios_actuales = [];

    if (file_exists($file)) {
        $usuarios_actuales = json_decode(file_get_contents($file), true);
    }

    $existe = false;
    foreach ($usuarios_actuales as $u) {
        if ($u['usuario'] === $nuevo_usuario['usuario']) {
            $existe = true;
            break;
        }
    }

    if ($existe) {
        $mensaje = '<div class="alert alert-danger">El usuario ya existe.</div>';
    } else {
        $usuarios_actuales[] = $nuevo_usuario;
        file_put_contents($file, json_encode($usuarios_actuales, JSON_PRETTY_PRINT));
        $mensaje = '<div class="alert alert-success">Usuario registrado exitosamente.</div>';
    }
}
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <form method="post" action="#" class="card p-4 shadow-sm">
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
